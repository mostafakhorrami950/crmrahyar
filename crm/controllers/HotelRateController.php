<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\View;

class HotelRateController
{
    public function index(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();

        $hotelFilter = trim($_GET['hotel'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');

        $where = "WHERE r.is_active = 1";
        $params = [];

        if ($hotelFilter) {
            $where .= " AND h.hotel_name LIKE :hotel";
            $params[':hotel'] = '%' . $hotelFilter . '%';
        }
        if ($dateFrom) {
            $where .= " AND r.date_to >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND r.date_from <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        try {
            $rates = $db->fetchAll(
                "SELECT r.*, h.hotel_name, h.description as hotel_desc, h.facilities, h.star_rating, h.city, u.full_name as creator_name
                 FROM hotel_rate_list r
                 JOIN hotel_rate_hotels h ON r.hotel_id = h.id
                 LEFT JOIN users u ON r.created_by = u.id
                 {$where}
                 ORDER BY h.hotel_name ASC, r.date_from DESC, r.room_type ASC",
                $params
            );
        } catch (\Exception $e) {
            $rates = [];
        }

        $hotels = [];
        try {
            $hotels = $db->fetchAll("SELECT * FROM hotel_rate_hotels WHERE is_active = 1 ORDER BY hotel_name");
        } catch (\Exception $e) {
            // Table might not exist yet
        }

        View::render('hotel_rate/index', [
            'title' => 'نرخنامه هتل‌ها',
            'rates' => $rates,
            'hotels' => $hotels,
            'hotelFilter' => $hotelFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    // ===== HOTEL CRUD =====
    public function storeHotel(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();

        $data = [
            'hotel_name' => trim($_POST['hotel_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'facilities' => trim($_POST['facilities'] ?? ''),
            'star_rating' => !empty($_POST['star_rating']) ? (int)$_POST['star_rating'] : null,
            'city' => trim($_POST['city'] ?? ''),
            'created_by' => Auth::id(),
        ];

        if (empty($data['hotel_name'])) {
            $_SESSION['error'] = 'نام هتل الزامی است.';
            View::redirect('/hotel-rates');
            return;
        }

        try {
            $db->insert('hotel_rate_hotels', $data);
            $_SESSION['success'] = 'هتل با موفقیت ثبت شد.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'خطا: ' . $e->getMessage();
        }
        View::redirect('/hotel-rates');
    }

    public function updateHotel(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $id = (int)$params['id'];

        $data = [
            'hotel_name' => trim($_POST['hotel_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'facilities' => trim($_POST['facilities'] ?? ''),
            'star_rating' => !empty($_POST['star_rating']) ? (int)$_POST['star_rating'] : null,
            'city' => trim($_POST['city'] ?? ''),
        ];

        try {
            $db->update('hotel_rate_hotels', $data, 'id = :id', [':id' => $id]);
            $_SESSION['success'] = 'هتل بروزرسانی شد.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'خطا: ' . $e->getMessage();
        }
        View::redirect('/hotel-rates');
    }

    public function deleteHotel(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $id = (int)$params['id'];
        try {
            $db->update('hotel_rate_hotels', ['is_active' => 0], 'id = :id', [':id' => $id]);
            $_SESSION['success'] = 'هتل حذف شد.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'خطا: ' . $e->getMessage();
        }
        View::redirect('/hotel-rates');
    }

    public function getHotelData(array $params): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json; charset=utf-8');
        $db = Database::getInstance();
        $id = (int)$params['id'];
        $hotel = $db->fetch("SELECT * FROM hotel_rate_hotels WHERE id = :id", [':id' => $id]);
        echo json_encode($hotel ?: []);
        exit;
    }

    // ===== RATE CRUD =====
    public function store(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();

        // Ensure table has required columns
        $this->ensureRateTableColumns($db);

        $data = [
            'hotel_id' => (int)($_POST['hotel_id'] ?? 0),
            'room_type' => trim($_POST['room_type'] ?? ''),
            'date_from' => $_POST['date_from'] ?? date('Y-m-d'),
            'date_to' => $_POST['date_to'] ?? date('Y-m-d'),
            'season_label' => trim($_POST['season_label'] ?? ''),
            'price_ekht' => (int)($_POST['price_ekht'] ?? 0),
            'price_sobhaneh' => (int)($_POST['price_sobhaneh'] ?? 0),
            'price_nahar' => (int)($_POST['price_nahar'] ?? 0),
            'price_entekhabifulboard' => (int)($_POST['price_entekhabifulboard'] ?? 0),
            'price_fulboard_boufeh' => (int)($_POST['price_fulboard_boufeh'] ?? 0),
            'notes' => trim($_POST['notes'] ?? ''),
            'created_by' => Auth::id(),
        ];

        if (empty($data['hotel_id']) || empty($data['room_type'])) {
            $_SESSION['error'] = 'هتل و نوع اتاق الزامی است.';
            View::redirect('/hotel-rates');
            return;
        }

        try {
            $db->insert('hotel_rate_list', $data);
            $_SESSION['success'] = 'نرخ با موفقیت ثبت شد.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'خطا در ثبت نرخ: ' . $e->getMessage();
        }
        View::redirect('/hotel-rates');
    }

    private function ensureRateTableColumns(Database $db): void
    {
        $requiredColumns = [
            'hotel_id' => "INT NOT NULL DEFAULT 0",
            'date_from' => "DATE NULL",
            'date_to' => "DATE NULL",
            'price_fulboard_boufeh' => "DECIMAL(15,0) DEFAULT 0",
            'price_entekhabifulboard' => "DECIMAL(15,0) DEFAULT 0",
        ];

        try {
            $existing = $db->fetchAll("SHOW COLUMNS FROM hotel_rate_list");
            $existingNames = array_map(function($c) { return $c->Field; }, $existing);

            foreach ($requiredColumns as $col => $def) {
                if (!in_array($col, $existingNames)) {
                    try {
                        $db->query("ALTER TABLE hotel_rate_list ADD COLUMN `{$col}` {$def}");
                    } catch (\Exception $e) {
                        // Column might already exist, ignore
                    }
                }
            }
        } catch (\Exception $e) {
            // Table might not exist, ignore
        }
    }

    public function update(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $id = (int)$params['id'];

        $data = [
            'hotel_id' => (int)($_POST['hotel_id'] ?? 0),
            'room_type' => trim($_POST['room_type'] ?? ''),
            'date_from' => $_POST['date_from'] ?? date('Y-m-d'),
            'date_to' => $_POST['date_to'] ?? date('Y-m-d'),
            'season_label' => trim($_POST['season_label'] ?? ''),
            'price_ekht' => (int)($_POST['price_ekht'] ?? 0),
            'price_sobhaneh' => (int)($_POST['price_sobhaneh'] ?? 0),
            'price_nahar' => (int)($_POST['price_nahar'] ?? 0),
            'price_entekhabifulboard' => (int)($_POST['price_entekhabifulboard'] ?? 0),
            'price_fulboard_boufeh' => (int)($_POST['price_fulboard_boufeh'] ?? 0),
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        try {
            $db->update('hotel_rate_list', $data, 'id = :id', [':id' => $id]);
            $_SESSION['success'] = 'نرخ بروزرسانی شد.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'خطا: ' . $e->getMessage();
        }
        View::redirect('/hotel-rates');
    }

    public function delete(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $id = (int)$params['id'];
        try {
            $db->update('hotel_rate_list', ['is_active' => 0], 'id = :id', [':id' => $id]);
            $_SESSION['success'] = 'نرخ حذف شد.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'خطا: ' . $e->getMessage();
        }
        View::redirect('/hotel-rates');
    }

    public function getData(array $params): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json; charset=utf-8');
        $db = Database::getInstance();
        $id = (int)$params['id'];
        $rate = $db->fetch("SELECT * FROM hotel_rate_list WHERE id = :id", [':id' => $id]);
        echo json_encode($rate ?: []);
        exit;
    }

    // ===== PUBLIC DISPLAY =====
    public function display(): void
    {
        $db = Database::getInstance();
        $hotelFilter = trim($_GET['hotel'] ?? '');
        $config = $GLOBALS['app_config'];

        $hotels = [];
        $ratesByHotel = [];

        try {
            $whereHotel = "WHERE h.is_active = 1";
            $params = [];
            if ($hotelFilter) {
                $whereHotel .= " AND h.hotel_name LIKE :hotel";
                $params[':hotel'] = '%' . $hotelFilter . '%';
            }

            $hotels = $db->fetchAll(
                "SELECT h.* FROM hotel_rate_hotels h {$whereHotel} ORDER BY h.hotel_name ASC",
                $params
            );

            foreach ($hotels as $hotel) {
                $ratesByHotel[$hotel->id] = $db->fetchAll(
                    "SELECT * FROM hotel_rate_list WHERE hotel_id = :hid AND is_active = 1 ORDER BY room_type ASC, date_from DESC",
                    [':hid' => $hotel->id]
                );
            }
        } catch (\Exception $e) {
            // Tables might not exist yet
        }

        $allHotels = [];
        try {
            $allHotels = $db->fetchAll("SELECT DISTINCT h.hotel_name FROM hotel_rate_hotels h WHERE h.is_active = 1 ORDER BY h.hotel_name");
        } catch (\Exception $e) {}

        $invoiceSettings = [];
        try {
            $settings = $db->fetchAll("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'invoice'");
            foreach ($settings as $s) {
                $invoiceSettings[$s->setting_key] = $s->setting_value;
            }
        } catch (\Exception $e) {}

        // Render standalone (no layout/sidebar)
        $title = 'نرخنامه هتل‌ها';
        $invoiceSettings = $invoiceSettings;
        $allHotels = $allHotels;
        $hotels = $hotels;
        $ratesByHotel = $ratesByHotel;
        $hotelFilter = $hotelFilter;
        require __DIR__ . '/../views/hotel_rate/display.php';
        exit;
    }

    // Simple markdown to HTML converter
    public static function md(string $text): string
    {
        $text = htmlspecialchars($text);
        // Bold: **text**
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        // Italic: *text*
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
        // Line breaks
        $text = nl2br($text);
        // List items: - item
        $text = preg_replace('/^- (.+)/m', '<li>$1</li>', $text);
        $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
        return $text;
    }
}