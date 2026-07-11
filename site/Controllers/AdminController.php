<?php
namespace Site\Controllers;

use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Core\Logger;
use Shared\Core\MigrationRunner;
use Shared\Services\SettingsService;
use Shared\Services\FeatureService;

class AdminController
{
    private Database $db;
    private Config $config;

    public function __construct()
    {
        $c = \Shared\Core\Container::getInstance();
        $this->db = $c->make(Database::class);
        $this->config = $c->make(Config::class);
    }

    private function requireAdmin(): void
    {
        \Shared\Core\Auth::requireAuth();
        $user = \Shared\Core\Auth::user();
        if (!$user || $user->role_slug !== 'super_admin') {
            \Shared\Core\Session::setFlash('danger', 'فقط مدیر اصلی به این بخش دسترسی دارد.');
            header('Location: /');
            exit;
        }
    }

    public function index(array $params = []): void
    {
        $this->requireAdmin();

        // Dashboard stats
        $stats = [];
        try {
            $r = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_hotel_profiles WHERE is_active = 1 AND deleted_at IS NULL");
            $stats['hotels'] = $r->cnt ?? 0;
        } catch (\Exception $e) { $stats['hotels'] = 0; }
        try {
            $r = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_bookings WHERE deleted_at IS NULL");
            $stats['bookings'] = $r->cnt ?? 0;
        } catch (\Exception $e) { $stats['bookings'] = 0; }
        try {
            $r = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_bookings WHERE booking_status = 'paid'");
            $stats['paid'] = $r->cnt ?? 0;
        } catch (\Exception $e) { $stats['paid'] = 0; }
        try {
            $r = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_blog_posts WHERE is_published = 1 AND deleted_at IS NULL");
            $stats['posts'] = $r->cnt ?? 0;
        } catch (\Exception $e) { $stats['posts'] = 0; }

        $this->render('admin/index', ['stats' => $stats, 'meta' => ['title' => 'پنل مدیریت سایت']]);
    }

    public function database(array $params = []): void
    {
        $this->requireAdmin();

        $action = $_GET['action'] ?? '';
        $results = [];
        $message = '';

        if ($action === 'migrate') {
            $runner = new MigrationRunner($this->db);
            $results = $runner->run();
            $message = 'مایگریشن اجرا شد.';
        } elseif ($action === 'rollback') {
            $runner = new MigrationRunner($this->db);
            $results = $runner->rollback();
            $message = 'بازگشت مایگریشن انجام شد.';
        } elseif ($action === 'repair') {
            $results = $this->repairTables();
            $message = 'تعمیر دیتابیس انجام شد.';
        }

        // Get migration status
        $applied = [];
        try {
            $applied = $this->db->fetchAll("SELECT migration, batch, applied_at FROM site_migrations ORDER BY id DESC");
        } catch (\Exception $e) {}

        $this->render('admin/database', [
            'applied' => $applied,
            'results' => $results,
            'message' => $message,
            'meta' => ['title' => 'تعمیرات دیتابیس'],
        ]);
    }

    private function repairTables(): array
    {
        $results = [];
        $tables = [
            'site_cities', 'site_neighborhoods', 'site_hotel_profiles', 'site_rooms',
            'site_room_daily_rates', 'site_pricing_rules', 'site_campaigns',
            'site_bookings', 'site_booking_guests', 'site_booking_status_log', 'site_booking_snapshots',
            'site_reservations', 'site_agencies', 'site_agency_transactions', 'site_ledger',
            'site_media', 'site_search_index', 'site_workflows', 'site_workflow_transitions',
            'site_blog_posts', 'site_pages', 'site_faqs', 'site_reviews', 'site_notifications',
            'site_settings', 'site_event_logs', 'site_analytics', 'site_seo_redirects',
            'site_queue_jobs', 'site_feature_flags', 'site_outbox', 'site_idempotency_keys',
            'site_audit_logs', 'site_migrations',
        ];

        foreach ($tables as $table) {
            try {
                $this->db->fetch("SELECT 1 FROM `{$table}` LIMIT 1");
                $results[] = ['table' => $table, 'status' => 'ok'];
            } catch (\Exception $e) {
                $results[] = ['table' => $table, 'status' => 'missing', 'error' => $e->getMessage()];
            }
        }

        // Try to create missing tables by running migrations
        $runner = new MigrationRunner($this->db);
        $migrateResults = $runner->run();
        foreach ($migrateResults as $mr) {
            if ($mr['status'] === 'applied') {
                $results[] = ['table' => $mr['name'], 'status' => 'created'];
            }
        }

        return $results;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) { require $viewPath; } else { echo "View not found: {$view}"; }
    }
}