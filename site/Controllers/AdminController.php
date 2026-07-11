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

    protected function requireAdmin(): void
    {
        // Check if user is logged in (CRM session)
        if (!isset($_SESSION['user_id'])) {
            header('Location: /crm/login');
            exit;
        }
        // Check if user has site access permission
        $user = $this->db->fetch(
            "SELECT u.*, r.slug as role_slug FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :id",
            [':id' => $_SESSION['user_id']]
        );
        if (!$user) {
            header('Location: /crm/login');
            exit;
        }
        // Check permission: super_admin always has access, others need site.access
        if ($user->role_slug !== 'super_admin') {
            $hasPerm = $this->db->fetch(
                "SELECT id FROM role_permissions WHERE role_id = :rid AND permission = 'site.access'",
                [':rid' => $user->role_id]
            );
            if (!$hasPerm) {
                header('Content-Type: text/html; charset=utf-8');
                echo '<div style="text-align:center;padding:60px;font-family:Vazirmatn,sans-serif;"><h2>⛔ دسترسی محدود</h2><p>شما به پنل مدیریت سایت دسترسی ندارید.</p><a href="/crm">بازگشت به CRM</a></div>';
                exit;
            }
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
        try {
            $r = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_cities WHERE is_active = 1");
            $stats['cities'] = $r->cnt ?? 0;
        } catch (\Exception $e) { $stats['cities'] = 0; }
        try {
            $r = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_rooms WHERE deleted_at IS NULL");
            $stats['rooms'] = $r->cnt ?? 0;
        } catch (\Exception $e) { $stats['rooms'] = 0; }

        $this->renderAdmin('admin/dashboard', ['stats' => $stats, 'meta' => ['title' => 'داشبورد مدیریت']]);
    }

    public function database(array $params = []): void
    {
        // Redirect to unified CRM system repair page
        header('Location: /crm/system/repair');
        exit;
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

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) { require $viewPath; } else { echo "View not found: {$view}"; }
    }

    /**
     * Render with admin layout
     */
    protected function renderAdmin(string $view, array $data = []): void
    {
        ob_start();
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) { require $viewPath; } else { echo "View not found: {$view}"; }
        $content = ob_get_clean();
        require __DIR__ . '/../Views/admin/layouts/admin.php';
    }
}