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
            $has = $this->db->fetch("SELECT p.id FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = :rid AND p.slug = 'site.manage_hotels'", [':rid' => $user->role_id]);
            if (!$has) { echo '⛔ دسترسی ندارید'; exit; }
        }
    }

    public function index(array $params = []): void
    {
        $this->requireAuth();
        $hotels = $this->db->fetchAll("
            SELECT hp.*, h.hotel_name, h.star_rating,
                   c.name as city_name
            FROM site_hotel_profiles hp
            LEFT JOIN hotel_rate_hotels h ON hp.crm_hotel_id = h.id
            LEFT JOIN site_cities c ON hp.city_id = c.id
            WHERE hp.deleted_at IS NULL
            ORDER BY hp.featured DESC, hp.sort_order, h.hotel_name
        ");
        $this->render('admin/hotels/index', ['hotels' => $hotels, 'meta' => ['title' => 'مدیریت هتل‌ها']]);
    }

    public function edit(array $params = []): void
    {
        $this->requireAuth();
        $id = (int)($params['id'] ?? 0);
        $hotel = $this->db->fetch("SELECT * FROM site_hotel_profiles WHERE id = :id", [':id' => $id]);
        if (!$hotel) { echo 'هتل یافت نشد'; exit; }

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

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) { require $viewPath; } else { echo "View not found: {$view}"; }
    }
}