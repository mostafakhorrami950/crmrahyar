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
            $where .= " AND r.hotel_name LIKE :hotel";
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

        $rates = $db->fetchAll(
            "SELECT r.*, u.full_name as creator_name
             FROM hotel_rate_list r
             LEFT JOIN users u ON r.created_by = u.id
             {$where}
             ORDER BY r.date_from DESC, r.hotel_name ASC, r.room_type ASC",
            $params
        );

        $hotels = $db->fetchAll("SELECT DISTINCT hotel_name FROM hotel_rate_list WHERE is_active = 1 ORDER BY hotel_name");

        View::render('hotel_rate/index', [
            'title' => 'نرخنامه هتل‌ها',
            'rates' => $rates,
            'hotels' => $hotels,
            'hotelFilter' => $hotelFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function store(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();

        $data = [
            'hotel_name' => trim($_POST['hotel_name'] ?? ''),
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

        if (empty($data['hotel_name']) || empty($data['room_type'])) {
            $_SESSION['error'] = 'نام هتل و نوع اتاق الزامی است.';
            View::redirect('/hotel-rates');
            return;
        }

        try {
            $db->insert('hotel_rate_list', $data);
            $_SESSION['success'] = 'نرخ با موفقیت ثبت شد.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'خطا در ثبت: ' . $e->getMessage();
        }

        View::redirect('/hotel-rates');
    }

    public function update(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();

        $id = (int)$params['id'];

        $data = [
            'hotel_name' => trim($_POST['hotel_name'] ?? ''),
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
            $_SESSION['success'] = 'نرخ با موفقیت بروزرسانی شد.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'خطا در بروزرسانی: ' . $e->getMessage();
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
            $_SESSION['error'] = 'خطا در حذف: ' . $e->getMessage();
        }
        View::redirect('/hotel-rates');
    }

    public function getData(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)$params['id'];
        $rate = $db->fetch("SELECT * FROM hotel_rate_list WHERE id = :id", [':id' => $id]);
        echo json_encode($rate ?: []);
        exit;
    }

    public function display(): void
    {
        $db = Database::getInstance();
        $hotelFilter = trim($_GET['hotel'] ?? '');

        $where = "WHERE is_active = 1";
        $params = [];
        if ($hotelFilter) {
            $where .= " AND hotel_name LIKE :hotel";
            $params[':hotel'] = '%' . $hotelFilter . '%';
        }

        $rates = $db->fetchAll(
            "SELECT * FROM hotel_rate_list {$where} ORDER BY hotel_name ASC, room_type ASC, date_from DESC",
            $params
        );

        $grouped = [];
        foreach ($rates as $r) {
            $grouped[$r->hotel_name][] = $r;
        }

        $hotels = $db->fetchAll("SELECT DISTINCT hotel_name FROM hotel_rate_list WHERE is_active = 1 ORDER BY hotel_name");

        $invoiceSettings = [];
        try {
            $settings = $db->fetchAll("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'invoice'");
            foreach ($settings as $s) {
                $invoiceSettings[$s->setting_key] = $s->setting_value;
            }
        } catch (\Exception $e) {}

        View::render('hotel_rate/display', [
            'title' => 'نرخنامه هتل‌ها',
            'grouped' => $grouped,
            'hotels' => $hotels,
            'hotelFilter' => $hotelFilter,
            'invoiceSettings' => $invoiceSettings,
        ]);
    }
}