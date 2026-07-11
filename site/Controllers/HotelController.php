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
        $cityId = $_GET['city_id'] ?? null;
        $sort = $_GET['sort'] ?? 'featured';

        $filters = ['is_active' => 1];
        if ($cityId) $filters['city_id'] = (int)$cityId;

        $orderBy = 'featured DESC, sort_order ASC, id DESC';
        if ($sort === 'name') $orderBy = 'hotel_name ASC';
        elseif ($sort === 'rating') $orderBy = 'star_rating DESC';

        $hotels = $this->hotelRepo->findAll($filters, $orderBy, 50);
        $cities = $this->db->fetchAll("SELECT * FROM site_cities WHERE is_active = 1 ORDER BY name");

        $meta = [
            'title' => 'لیست هتل‌ها',
            'description' => 'لیست تمام هتل‌ها با قیمت و امکانات',
            'canonical' => $this->config->url() . '/hotels',
        ];

        $this->render('hotels/index', [
            'hotels' => $hotels,
            'cities' => $cities,
            'meta' => $meta,
            'filters' => $_GET,
        ]);
    }

    public function show(array $params = []): void
    {
        $slug = $params['slug'] ?? '';
        $hotel = $this->hotelRepo->findBySlugWithJoins($slug);

        if (!$hotel) {
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