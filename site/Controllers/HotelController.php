<?php
namespace Site\Controllers;

use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Services\SEOService;
use Shared\Services\SearchService;
use Shared\Services\PricingService;
use Shared\Services\CacheService;
use Shared\Repositories\HotelRepository;
use Shared\Repositories\RoomRepository;
use Shared\Repositories\SettingsRepository;

class HotelController
{
    private Database $db;
    private Config $config;
    private SEOService $seo;
    private SearchService $search;
    private PricingService $pricing;
    private CacheService $cache;
    private HotelRepository $hotelRepo;
    private RoomRepository $roomRepo;
    private SettingsRepository $settings;

    public function __construct()
    {
        $c = \Shared\Core\Container::getInstance();
        $this->db = $c->make(Database::class);
        $this->config = $c->make(Config::class);
        $this->seo = $c->make(SEOService::class);
        $this->search = $c->make(SearchService::class);
        $this->pricing = $c->make(PricingService::class);
        $this->cache = new CacheService();
        $this->hotelRepo = new HotelRepository($this->db);
        $this->roomRepo = new RoomRepository($this->db);
        $this->settings = new SettingsRepository($this->db);
    }

    public function index(array $params = []): void
    {
        $citySlug = $params['city_slug'] ?? $_GET['city'] ?? null;
        $sort = $_GET['sort'] ?? 'featured';
        $nearHaram = isset($_GET['near_haram']);

        $where = ['hp.is_active = 1', 'hp.deleted_at IS NULL'];
        $queryParams = [];

        $city = null;
        if ($citySlug) {
            $city = $this->db->fetch("SELECT * FROM site_cities WHERE slug = :s AND is_active = 1", [':s' => $citySlug]);
            if ($city) {
                $where[] = 'hp.city_id = :city_id';
                $queryParams[':city_id'] = $city->id;
            }
        }

        if ($nearHaram) {
            $where[] = 'hp.distance_to_haram_km IS NOT NULL';
        }

        $orderBy = 'hp.featured DESC, hp.sort_order ASC, hp.id DESC';
        if ($sort === 'name') $orderBy = 'hp.slug ASC';
        elseif ($sort === 'rating') $orderBy = 'hp.featured DESC';
        elseif ($sort === 'haram') $orderBy = 'hp.distance_to_haram_km ASC';

        $whereStr = implode(' AND ', $where);
        $hotels = $this->db->fetchAll("
            SELECT hp.*, c.name as city_name, c.slug as city_slug,
                   COALESCE(h.hotel_name, hp.slug) as hotel_name,
                   COALESCE(h.star_rating, 0) as star_rating
            FROM site_hotel_profiles hp
            LEFT JOIN site_cities c ON hp.city_id = c.id
            LEFT JOIN hotel_rate_hotels h ON hp.crm_hotel_id = h.id
            WHERE {$whereStr}
            ORDER BY {$orderBy}
            LIMIT 50
        ", $queryParams);

        $cities = $this->db->fetchAll("SELECT * FROM site_cities WHERE is_active = 1 ORDER BY name");

        $title = 'لیست هتل‌ها';
        if ($city) $title = 'هتل‌های ' . $city->name;

        $meta = [
            'title' => $title,
            'description' => 'لیست هتل‌ها با قیمت و امکانات',
            'canonical' => $this->config->url() . '/hotels',
        ];

        $this->render('hotels/index', [
            'hotels' => $hotels,
            'cities' => $cities,
            'city' => $city,
            'meta' => $meta,
            'filters' => array_merge($_GET, ['city_slug' => $citySlug]),
        ]);
    }

    public function show(array $params = []): void
    {
        $slug = $params['slug'] ?? '';
        $hotel = $this->hotelRepo->findBySlugWithJoins($slug);

        // Fallback: try to find by CRM hotel name (for hotels without profile)
        if (!$hotel) {
            $decodedSlug = urldecode($slug);
            $crmHotel = $this->db->fetch("SELECT * FROM hotel_rate_hotels WHERE hotel_name = :name", [':name' => $decodedSlug]);
            if (!$crmHotel) {
                // Try transliterated match
                $crmHotel = $this->db->fetch("SELECT * FROM hotel_rate_hotels WHERE id = :id", [':id' => (int)$slug]);
            }
            if ($crmHotel) {
                // Auto-create profile and redirect
                $newSlug = $this->slugify($crmHotel->hotel_name ?? 'hotel-' . $crmHotel->id);
                $this->db->insert('site_hotel_profiles', [
                    'crm_hotel_id' => $crmHotel->id,
                    'slug' => $newSlug,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                header('Location: /hotel/' . $newSlug);
                exit;
            }
            http_response_code(404);
            $this->render('errors/404', ['meta' => ['title' => 'هتل یافت نشد', 'description' => '']]);
            return;
        }

        // Get rooms
        $rooms = $this->roomRepo->findByHotel($hotel->crm_hotel_id);

        // Get pricing for each room (next 30 days)
        $dateFrom = date('Y-m-d');
        $dateTo = date('Y-m-d', strtotime('+30 days'));
        $roomsWithPricing = [];
        foreach ($rooms as $room) {
            $pricing = $this->pricing->calculate($room->id, $dateFrom, $dateTo, 2);
            $roomsWithPricing[] = [
                'room' => $room,
                'pricing' => $pricing,
            ];
        }

        // Related hotels
        $related = $this->search->getRelated($hotel->crm_hotel_id, 4);

        // FAQs for this hotel
        $faqs = $this->db->fetchAll(
            "SELECT * FROM site_faqs WHERE entity_type = 'hotel' AND entity_id = :id AND is_active = 1 ORDER BY sort_order",
            [':id' => $hotel->id]
        );

        // Reviews
        $reviews = $this->db->fetchAll(
            "SELECT r.*, u.full_name as user_name FROM site_reviews r LEFT JOIN users u ON r.user_id = u.id
             WHERE r.crm_hotel_id = :hid AND r.is_approved = 1 ORDER BY r.created_at DESC LIMIT 10",
            [':hid' => $hotel->crm_hotel_id]
        );

        $meta = $this->seo->generateMeta('hotel', $hotel->id);
        $schema = $this->seo->generateSchema('hotel', $hotel->id);

        $this->render('hotels/show', [
            'hotel' => $hotel,
            'roomsWithPricing' => $roomsWithPricing,
            'related' => $related,
            'faqs' => $faqs,
            'reviews' => $reviews,
            'meta' => $meta,
            'schema' => $schema,
        ]);
    }

    public function room(array $params = []): void
    {
        $hotelSlug = $params['slug'] ?? '';
        $roomSlug = $params['room'] ?? '';

        $hotel = $this->hotelRepo->findBySlugWithJoins($hotelSlug);
        if (!$hotel) { http_response_code(404); $this->render('errors/404', ['meta' => ['title' => 'یافت نشد']]); return; }

        $room = $this->roomRepo->findBySlug($roomSlug);
        if (!$room) { http_response_code(404); $this->render('errors/404', ['meta' => ['title' => 'یافت نشد']]); return; }

        $dateFrom = date('Y-m-d');
        $dateTo = date('Y-m-d', strtotime('+7 days'));
        $pricing = $this->pricing->calculate($room->id, $dateFrom, $dateTo, 2);

        $meta = [
            'title' => $room->room_type_key . ' - ' . ($hotel->hotel_name ?? $hotel->slug),
            'description' => $room->description ?? '',
        ];
        $schema = $this->seo->generateSchema('room', $room->id);

        $this->render('hotels/room', [
            'hotel' => $hotel,
            'room' => $room,
            'pricing' => $pricing,
            'meta' => $meta,
            'schema' => $schema,
        ]);
    }

    public function city(array $params = []): void
    {
        $slug = $params['slug'] ?? '';
        $city = $this->db->fetch("SELECT * FROM site_cities WHERE slug = :s AND is_active = 1", [':s' => $slug]);
        if (!$city) { http_response_code(404); $this->render('errors/404', ['meta' => ['title' => 'شهر یافت نشد']]); return; }

        $hotels = $this->hotelRepo->findActiveByCity($city->id);

        $meta = [
            'title' => 'هتل‌های ' . $city->name,
            'description' => $city->description ?? ('لیست هتل‌های ' . $city->name),
            'canonical' => $this->config->url() . '/city/' . $slug,
        ];

        $this->render('hotels/city', [
            'city' => $city,
            'hotels' => $hotels,
            'meta' => $meta,
        ]);
    }

    private function slugify(string $text): string
    {
        $text = trim($text);
        $persianMap = [
            'آ' => 'a', 'ا' => 'a', 'ب' => 'b', 'پ' => 'p', 'ت' => 't', 'ث' => 's',
            'ج' => 'j', 'چ' => 'ch', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'z',
            'ر' => 'r', 'ز' => 'z', 'ژ' => 'zh', 'س' => 's', 'ش' => 'sh', 'ص' => 's',
            'ض' => 'z', 'ط' => 't', 'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f',
            'ق' => 'gh', 'ک' => 'k', 'گ' => 'g', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'و' => 'v', 'ه' => 'h', 'ی' => 'y', 'ة' => 'h', 'ئ' => 'y', 'ؤ' => 'v',
        ];
        $result = '';
        $len = mb_strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1);
            if (isset($persianMap[$char])) $result .= $persianMap[$char];
            elseif (preg_match('/[a-zA-Z0-9]/', $char)) $result .= $char;
            elseif (in_array($char, [' ', '-', '_', '‌'])) $result .= '-';
        }
        $result = preg_replace('/\-+/', '-', $result);
        return trim($result, '-') ?: 'hotel-' . time();
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "View not found: {$view}";
        }
    }
}