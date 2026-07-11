<?php
namespace Site\Controllers;

use Shared\Core\Database;
use Shared\Core\Config;

class AdminHotelController
{
    private Database $db;
    private Config $config;

    public function __construct()
    {
        $c = \Shared\Core\Container::getInstance();
        $this->db = $c->make(Database::class);
        $this->config = $c->make(Config::class);
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) { header('Location: /crm/login'); exit; }
        $user = $this->db->fetch(
            "SELECT u.*, r.slug as role_slug FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :id",
            [':id' => $_SESSION['user_id']]
        );
        if (!$user) { header('Location: /crm/login'); exit; }
        if ($user->role_slug !== 'super_admin') {
            $has = $this->db->fetch("SELECT id FROM role_permissions WHERE role_id = :rid AND permission = 'site.manage_hotels'", [':rid' => $user->role_id]);
            if (!$has) { echo '⛔ دسترسی ندارید'; exit; }
        }
    }

    public function index(array $params = []): void
    {
        $this->requireAuth();
        // Show hotels from both CRM (hotel_rate_hotels) and site profiles
        $hotels = $this->db->fetchAll("
            SELECT h.id as crm_hotel_id, h.hotel_name, h.star_rating, h.city,
                   hp.id as profile_id, hp.slug, hp.is_active, hp.featured,
                   hp.meta_title, hp.meta_description, hp.description_short,
                   hp.distance_to_haram_km, hp.latitude, hp.longitude
            FROM hotel_rate_hotels h
            LEFT JOIN site_hotel_profiles hp ON hp.crm_hotel_id = h.id
            ORDER BY hp.featured DESC, h.hotel_name ASC
        ");
        $this->render('admin/hotels/index', ['hotels' => $hotels, 'meta' => ['title' => 'مدیریت هتل‌ها']]);
    }

    public function edit(array $params = []): void
    {
        $this->requireAuth();
        $id = (int)($params['id'] ?? 0);
        // Try to find profile, or create one from CRM hotel
        $hotel = $this->db->fetch("SELECT * FROM site_hotel_profiles WHERE crm_hotel_id = :id", [':id' => $id]);
        if (!$hotel) {
            // Auto-create profile from CRM hotel
            $crmHotel = $this->db->fetch("SELECT * FROM hotel_rate_hotels WHERE id = :id", [':id' => $id]);
            if (!$crmHotel) { echo 'هتل یافت نشد'; exit; }
            $slug = $this->slugify($crmHotel->hotel_name ?? 'hotel-' . $id);
            $this->db->insert('site_hotel_profiles', [
                'crm_hotel_id' => $id,
                'slug' => $slug,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $hotel = $this->db->fetch("SELECT * FROM site_hotel_profiles WHERE crm_hotel_id = :id", [':id' => $id]);
        }

        $crmHotel = $this->db->fetch("SELECT * FROM hotel_rate_hotels WHERE id = :id", [':id' => $hotel->crm_hotel_id]);
        $cities = $this->db->fetchAll("SELECT * FROM site_cities WHERE is_active = 1 ORDER BY name");
        $neighborhoods = $this->db->fetchAll("SELECT * FROM site_neighborhoods WHERE city_id = :cid AND is_active = 1 ORDER BY name", [':cid' => $hotel->city_id ?? 0]);
        $rooms = $this->db->fetchAll("SELECT * FROM site_rooms WHERE crm_hotel_id = :hid AND deleted_at IS NULL ORDER BY sort_order", [':hid' => $hotel->crm_hotel_id]);

        $this->render('admin/hotels/edit', [
            'hotel' => $hotel, 'crmHotel' => $crmHotel,
            'cities' => $cities, 'neighborhoods' => $neighborhoods, 'rooms' => $rooms,
            'meta' => ['title' => 'ویرایش هتل'],
        ]);
    }

    public function update(array $params = []): void
    {
        $this->requireAuth();
        $id = (int)($params['id'] ?? 0);

        $data = [
            'address' => trim($_POST['address'] ?? ''),
            'description_long' => trim($_POST['description_long'] ?? ''),
            'description_short' => trim($_POST['description_short'] ?? ''),
            'distance_to_haram_km' => !empty($_POST['distance_to_haram_km']) ? (float)$_POST['distance_to_haram_km'] : null,
            'city_id' => !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null,
            'neighborhood_id' => !empty($_POST['neighborhood_id']) ? (int)$_POST['neighborhood_id'] : null,
            'family_friendly' => isset($_POST['family_friendly']) ? 1 : 0,
            'couple_friendly' => isset($_POST['couple_friendly']) ? 1 : 0,
            'budget_friendly' => isset($_POST['budget_friendly']) ? 1 : 0,
            'luxury' => isset($_POST['luxury']) ? 1 : 0,
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        // Handle slug
        if (!empty($_POST['slug'])) {
            $data['slug'] = trim($_POST['slug']);
        }

        $this->db->update('site_hotel_profiles', $data, 'id = :id', [':id' => $id]);

        header('Location: /admin/hotels?updated=1');
        exit;
    }

    public function rooms(array $params = []): void
    {
        $this->requireAuth();
        $hotelId = (int)($params['id'] ?? 0);
        $hotel = $this->db->fetch("SELECT * FROM site_hotel_profiles WHERE id = :id", [':id' => $hotelId]);
        if (!$hotel) { echo 'هتل یافت نشد'; exit; }

        $rooms = $this->db->fetchAll("SELECT * FROM site_rooms WHERE crm_hotel_id = :hid AND deleted_at IS NULL ORDER BY sort_order", [':hid' => $hotel->crm_hotel_id]);
        $crmRooms = $this->db->fetchAll("SELECT DISTINCT room_type FROM hotel_rate_list WHERE hotel_id = :hid", [':hid' => $hotel->crm_hotel_id]);

        $this->render('admin/hotels/rooms', [
            'hotel' => $hotel, 'rooms' => $rooms, 'crmRooms' => $crmRooms,
            'meta' => ['title' => 'مدیریت اتاق‌ها'],
        ]);
    }

    public function roomEdit(array $params = []): void
    {
        $this->requireAuth();
        $id = (int)($params['id'] ?? 0);
        $room = $this->db->fetch("SELECT * FROM site_rooms WHERE id = :id", [':id' => $id]);
        if (!$room) { echo 'اتاق یافت نشد'; exit; }

        $hotel = $this->db->fetch("SELECT * FROM site_hotel_profiles WHERE crm_hotel_id = :hid", [':hid' => $room->crm_hotel_id]);

        $this->render('admin/hotels/room_edit', [
            'room' => $room, 'hotel' => $hotel,
            'meta' => ['title' => 'ویرایش اتاق'],
        ]);
    }

    public function roomUpdate(array $params = []): void
    {
        $this->requireAuth();
        $id = (int)($params['id'] ?? 0);

        $data = [
            'capacity_adults' => (int)($_POST['capacity_adults'] ?? 2),
            'capacity_children' => (int)($_POST['capacity_children'] ?? 0),
            'bed_type' => trim($_POST['bed_type'] ?? ''),
            'size_sqm' => !empty($_POST['size_sqm']) ? (int)$_POST['size_sqm'] : null,
            'description' => trim($_POST['description'] ?? ''),
            'max_inventory' => (int)($_POST['max_inventory'] ?? 10),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        if (!empty($_POST['slug'])) {
            $data['slug'] = trim($_POST['slug']);
        }

        $this->db->update('site_rooms', $data, 'id = :id', [':id' => $id]);

        $room = $this->db->fetch("SELECT * FROM site_rooms WHERE id = :id", [':id' => $id]);
        header('Location: /admin/hotels/' . ($room->crm_hotel_id ?? 0) . '/rooms?updated=1');
        exit;
    }

    public function addRoom(array $params = []): void
    {
        $this->requireAuth();
        $hotelId = (int)($params['id'] ?? 0);
        $hotel = $this->db->fetch("SELECT * FROM site_hotel_profiles WHERE id = :id", [':id' => $hotelId]);
        if (!$hotel) { echo 'هتل یافت نشد'; exit; }

        $roomType = trim($_POST['room_type_key'] ?? '');
        if (empty($roomType)) { echo 'نوع اتاق الزامی است'; exit; }

        // Check if already exists
        $exists = $this->db->fetch("SELECT id FROM site_rooms WHERE crm_hotel_id = :hid AND room_type_key = :rt", [':hid' => $hotel->crm_hotel_id, ':rt' => $roomType]);
        if ($exists) { echo 'این اتاق قبلاً ثبت شده'; exit; }

        $this->db->insert('site_rooms', [
            'crm_hotel_id' => $hotel->crm_hotel_id,
            'room_type_key' => $roomType,
            'slug' => \Shared\Services\SEOService::slugify($roomType),
            'capacity_adults' => 2,
            'max_inventory' => 10,
            'is_active' => 1,
        ]);

        header('Location: /admin/hotels/' . $hotelId . '/rooms?added=1');
        exit;
    }

    private function slugify(string $text): string
    {
        $text = trim($text);
        $text = str_replace([' ', '‌', '،', '؟'], '-', $text);
        $text = preg_replace('/[^a-zA-Z0-9\x{0600}-\x{06FF}\-]/u', '', $text);
        $text = preg_replace('/\-+/', '-', $text);
        return rtrim($text, '-') ?: 'hotel-' . time();
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) { require $viewPath; } else { echo "View not found: {$view}"; }
    }
}