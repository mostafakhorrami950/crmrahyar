<?php
namespace Site\Controllers;

use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Services\SearchService;
use Shared\Services\SEOService;
use Shared\Services\PricingService;
use Shared\Services\CacheService;
use Shared\Repositories\HotelRepository;
use Shared\Repositories\RoomRepository;

class SearchController
{
    private Database $db;
    private Config $config;
    private SearchService $search;
    private SEOService $seo;
    private PricingService $pricing;
    private CacheService $cache;
    private HotelRepository $hotelRepo;
    private RoomRepository $roomRepo;

    public function __construct()
    {
        $c = \Shared\Core\Container::getInstance();
        $this->db = $c->make(Database::class);
        $this->config = $c->make(Config::class);
        $this->search = $c->make(SearchService::class);
        $this->seo = $c->make(SEOService::class);
        $this->pricing = $c->make(PricingService::class);
        $this->cache = new CacheService();
        $this->hotelRepo = new HotelRepository($this->db);
        $this->roomRepo = new RoomRepository($this->db);
    }

    public function index(array $params = []): void
    {
        $query = $_GET['q'] ?? '';
        $city = $_GET['city'] ?? '';
        $checkin = $_GET['checkin'] ?? '';
        $checkout = $_GET['checkout'] ?? '';
        $guests = (int)($_GET['guests'] ?? 2);
        $sort = $_GET['sort'] ?? 'relevance';
        $page = max(1, (int)($_GET['page'] ?? 1));

        $filters = [];
        if ($city) {
            $cityRow = $this->db->fetch("SELECT id FROM site_cities WHERE slug = :s", [':s' => $city]);
            if ($cityRow) $filters['city_id'] = $cityRow->id;
        }
        if (!empty($_GET['star_rating'])) $filters['star_rating'] = (int)$_GET['star_rating'];
        if (!empty($_GET['price_min'])) $filters['price_min'] = (float)$_GET['price_min'];
        if (!empty($_GET['price_max'])) $filters['price_max'] = (float)$_GET['price_max'];

        $results = $this->search->search($query, $filters, $sort, $page);
        $cities = $this->db->fetchAll("SELECT * FROM site_cities WHERE is_active = 1 ORDER BY name");

        // Enrich results with pricing
        $enriched = [];
        foreach ($results->results ?? [] as $r) {
            if ($r->entity_type === 'hotel' && $checkin && $checkout) {
                $rooms = $this->roomRepo->findByHotel($r->entity_id);
                $minPrice = PHP_INT_MAX;
                foreach ($rooms as $room) {
                    $pricing = $this->pricing->calculate($room->id, $checkin, $checkout, $guests);
                    if ($pricing->total_price > 0 && $pricing->total_price < $minPrice) {
                        $minPrice = $pricing->total_price;
                    }
                }
                $r->min_price = $minPrice < PHP_INT_MAX ? $minPrice : null;
            }
            $enriched[] = $r;
        }

        $meta = [
            'title' => $query ? "نتایج جستجو: {$query}" : 'جستجوی هتل',
            'description' => 'جستجو و رزرو هتل با بهترین قیمت',
            'robots' => 'noindex, follow',
        ];

        $this->render('search/index', [
            'results' => $enriched,
            'pagination' => $results,
            'cities' => $cities,
            'meta' => $meta,
            'filters' => $_GET,
        ]);
    }

    public function api(array $params = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $query = $_GET['q'] ?? '';
        $filters = [];
        if (!empty($_GET['city_id'])) $filters['city_id'] = (int)$_GET['city_id'];
        if (!empty($_GET['star_rating'])) $filters['star_rating'] = (int)$_GET['star_rating'];

        $results = $this->search->search($query, $filters, $_GET['sort'] ?? 'relevance', (int)($_GET['page'] ?? 1));
        echo json_encode(['success' => true, 'data' => $results]);
    }

    public function autocomplete(array $params = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $term = $_GET['q'] ?? '';
        $results = $this->search->autocomplete($term, 8);
        echo json_encode(['success' => true, 'data' => $results]);
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