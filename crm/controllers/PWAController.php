<?php
namespace Controllers;

/**
 * PWA Controller - Complete standalone PWA frontend
 * Separate from the main platform with its own login, dashboard, and pages
 */
class PWAController
{
    /**
     * PWA Entry point - redirect to app or login
     */
    public function index(array $params = []): void
    {
        if (\Core\Auth::check()) {
            header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/app');
        } else {
            header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/login');
        }
        exit;
    }

    /**
     * PWA Login page
     */
    public function loginForm(array $params = []): void
    {
        if (\Core\Auth::check()) {
            header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/app');
            exit;
        }
        $error = '';
        include __DIR__ . '/../views/pwa/login.php';
    }

    /**
     * PWA Login handler
     */
    public function login(array $params = []): void
    {
        $config = $GLOBALS['app_config'];
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'نام کاربری و رمز عبور الزامی است';
            include __DIR__ . '/../views/pwa/login.php';
            return;
        }

        $db = \Core\Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);

        if (!$user || !password_verify($password, $user->password)) {
            $error = 'نام کاربری یا رمز عبور اشتباه است';
            include __DIR__ . '/../views/pwa/login.php';
            return;
        }

        // Set session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['logged_in'] = true;

        header('Location: ' . $config['url'] . '/pwa/app');
        exit;
    }

    /**
     * PWA Logout
     */
    public function logout(array $params = []): void
    {
        session_destroy();
        header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/login');
        exit;
    }

    /**
     * PWA Dashboard/App
     */
    public function app(array $params = []): void
    {
        \Core\Auth::requireAuth();
        include __DIR__ . '/../views/pwa/app.php';
    }

    /**
     * PWA Deals list
     */
    public function deals(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $userId = \Core\Auth::id();

        $search = trim($_GET['q'] ?? '');
        $where = "WHERE d.assigned_to = ?";
        $queryParams = [$userId];

        if ($search) {
            $where .= " AND (d.title LIKE ? OR c.full_name LIKE ?)";
            $queryParams[] = "%$search%";
            $queryParams[] = "%$search%";
        }

        $deals = $db->fetchAll(
            "SELECT d.*, s.name as stage_name, s.color as stage_color, c.full_name as contact_name 
             FROM deals d 
             LEFT JOIN pipeline_stages s ON d.stage_id = s.id 
             LEFT JOIN contacts c ON d.contact_id = c.id 
             {$where} ORDER BY d.created_at DESC LIMIT 50",
            $queryParams
        );

        ob_start();
        ?>
        <div class="pwa-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <a href="<?php echo $config['url']; ?>/pwa/app" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a>
                    <h5 style="font-size:15px;">معاملات من</h5>
                </div>
                <span class="pwa-badge pwa-badge-primary"><?php echo count($deals); ?></span>
            </div>
        </div>
        <div class="pwa-content pwa-fade">
            <form method="GET" style="margin-bottom:16px;">
                <input type="text" name="q" class="pwa-input" placeholder="🔍 جستجو در معاملات..." value="<?php echo htmlspecialchars($search); ?>" style="font-size:13px;">
            </form>
            <?php if (empty($deals)): ?>
            <div style="text-align:center;padding:40px;color:var(--pwa-muted);">
                <i class="bi bi-inbox" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                <p>معامله‌ای یافت نشد</p>
            </div>
            <?php else: ?>
            <?php foreach ($deals as $deal): ?>
            <div class="pwa-list-item">
                <div class="pwa-list-icon" style="background:rgba(67,97,238,0.1);color:var(--pwa-primary);">
                    <i class="bi bi-briefcase"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="pwa-list-title"><?php echo htmlspecialchars(mb_substr($deal->title, 0, 35)); ?></div>
                    <div class="pwa-list-sub">
                        <?php if ($deal->contact_name): ?>
                        <i class="bi bi-person me-1"></i><?php echo htmlspecialchars(mb_substr($deal->contact_name, 0, 20)); ?>
                        <?php endif; ?>
                        <?php if ($deal->stage_name): ?>
                        <span class="pwa-badge pwa-badge-primary" style="font-size:9px;margin-right:4px;"><?php echo htmlspecialchars($deal->stage_name); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="pwa-list-right">
                    <div style="font-size:13px;font-weight:700;">
                        <?php if ($deal->is_won): ?>
                        <span style="color:var(--pwa-success);">✅</span>
                        <?php elseif ($deal->is_lost): ?>
                        <span style="color:var(--pwa-danger);">❌</span>
                        <?php else: ?>
                        <?php echo number_format($deal->amount); ?>
                        <?php endif; ?>
                    </div>
                    <small style="color:var(--pwa-muted);font-size:10px;"><?php echo \Core\JDate::displayDate($deal->created_at); ?></small>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <nav class="pwa-bottom-nav">
            <a href="<?php echo $config['url']; ?>/pwa/app"><i class="bi bi-house-door"></i><span>خانه</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/deals" class="active"><i class="bi bi-briefcase-fill"></i><span>معاملات</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/contacts"><i class="bi bi-people"></i><span>مخاطبان</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/activities"><i class="bi bi-calendar-check"></i><span>فعالیت‌ها</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/more"><i class="bi bi-three-dots"></i><span>بیشتر</span></a>
        </nav>
        <?php
        $pwaContent = ob_get_clean();
        $pageTitle = 'معاملات';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    /**
     * PWA Contacts list
     */
    public function contacts(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();

        $search = trim($_GET['q'] ?? '');
        $where = $search ? "WHERE full_name LIKE ? OR phone LIKE ?" : "";
        $queryParams = $search ? ["%$search%", "%$search%"] : [];

        $contacts = $db->fetchAll(
            "SELECT * FROM contacts {$where} ORDER BY created_at DESC LIMIT 50",
            $queryParams
        );

        ob_start();
        ?>
        <div class="pwa-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <a href="<?php echo $config['url']; ?>/pwa/app" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a>
                    <h5 style="font-size:15px;">مخاطبان</h5>
                </div>
                <span class="pwa-badge pwa-badge-success"><?php echo count($contacts); ?></span>
            </div>
        </div>
        <div class="pwa-content pwa-fade">
            <form method="GET" style="margin-bottom:16px;">
                <input type="text" name="q" class="pwa-input" placeholder="🔍 جستجوی مخاطب..." value="<?php echo htmlspecialchars($search); ?>" style="font-size:13px;">
            </form>
            <?php if (empty($contacts)): ?>
            <div style="text-align:center;padding:40px;color:var(--pwa-muted);">
                <i class="bi bi-people" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                <p>مخاطبی یافت نشد</p>
            </div>
            <?php else: ?>
            <?php foreach ($contacts as $c): ?>
            <div class="pwa-list-item">
                <div class="pwa-list-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:14px;font-weight:700;">
                    <?php echo mb_substr($c->full_name, 0, 1); ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="pwa-list-title"><?php echo htmlspecialchars($c->full_name); ?></div>
                    <div class="pwa-list-sub">
                        <?php if ($c->phone): ?><i class="bi bi-phone me-1"></i><span dir="ltr"><?php echo htmlspecialchars($c->phone); ?></span><?php endif; ?>
                    </div>
                </div>
                <?php if ($c->phone): ?>
                <a href="tel:<?php echo htmlspecialchars($c->phone); ?>" style="color:var(--pwa-success);font-size:18px;text-decoration:none;"><i class="bi bi-telephone-fill"></i></a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <nav class="pwa-bottom-nav">
            <a href="<?php echo $config['url']; ?>/pwa/app"><i class="bi bi-house-door"></i><span>خانه</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/deals"><i class="bi bi-briefcase"></i><span>معاملات</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/contacts" class="active"><i class="bi bi-people-fill"></i><span>مخاطبان</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/activities"><i class="bi bi-calendar-check"></i><span>فعالیت‌ها</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/more"><i class="bi bi-three-dots"></i><span>بیشتر</span></a>
        </nav>
        <?php
        $pwaContent = ob_get_clean();
        $pageTitle = 'مخاطبان';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    /**
     * PWA Activities list
     */
    public function activities(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $userId = \Core\Auth::id();

        $activities = $db->fetchAll(
            "SELECT al.*, d.title as deal_title FROM activity_logs al LEFT JOIN deals d ON al.deal_id = d.id WHERE al.user_id = ? ORDER BY al.activity_date DESC LIMIT 50",
            [$userId]
        );

        ob_start();
        ?>
        <div class="pwa-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <a href="<?php echo $config['url']; ?>/pwa/app" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a>
                    <h5 style="font-size:15px;">فعالیت‌ها</h5>
                </div>
                <span class="pwa-badge pwa-badge-warning"><?php echo count($activities); ?></span>
            </div>
        </div>
        <div class="pwa-content pwa-fade">
            <?php if (empty($activities)): ?>
            <div style="text-align:center;padding:40px;color:var(--pwa-muted);">
                <i class="bi bi-calendar-check" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                <p>فعالیتی یافت نشد</p>
            </div>
            <?php else: ?>
            <?php foreach ($activities as $act): ?>
            <?php $isOverdue = !$act->is_done && $act->activity_date && strtotime($act->activity_date) < time(); ?>
            <div class="pwa-list-item" style="<?php echo $act->is_done ? 'opacity:0.5;' : ''; ?>">
                <div class="pwa-list-icon" style="background:<?php echo $isOverdue ? 'rgba(239,71,111,0.1)' : ($act->is_done ? 'rgba(6,214,160,0.1)' : 'rgba(255,209,102,0.1)'); ?>;color:<?php echo $isOverdue ? 'var(--pwa-danger)' : ($act->is_done ? 'var(--pwa-success)' : 'var(--pwa-warning)'); ?>;">
                    <i class="bi bi-<?php echo $act->type === 'call' ? 'telephone' : ($act->type === 'meeting' ? 'people' : ($act->type === 'email' ? 'envelope' : 'pin')); ?>"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="pwa-list-title" style="<?php echo $act->is_done ? 'text-decoration:line-through;' : ''; ?>"><?php echo htmlspecialchars(mb_substr($act->subject ?? '-', 0, 35)); ?></div>
                    <div class="pwa-list-sub">
                        <?php if ($act->deal_title): ?><i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars(mb_substr($act->deal_title, 0, 25)); ?><?php endif; ?>
                    </div>
                </div>
                <div class="pwa-list-right">
                    <small style="color:<?php echo $isOverdue ? 'var(--pwa-danger)' : 'var(--pwa-muted)'; ?>;font-size:10px;font-weight:<?php echo $isOverdue ? '700' : '400'; ?>;">
                        <?php echo $act->activity_date ? \Core\JDate::displayDate($act->activity_date) : '-'; ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <nav class="pwa-bottom-nav">
            <a href="<?php echo $config['url']; ?>/pwa/app"><i class="bi bi-house-door"></i><span>خانه</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/deals"><i class="bi bi-briefcase"></i><span>معاملات</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/contacts"><i class="bi bi-people"></i><span>مخاطبان</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/activities" class="active"><i class="bi bi-calendar-check-fill"></i><span>فعالیت‌ها</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/more"><i class="bi bi-three-dots"></i><span>بیشتر</span></a>
        </nav>
        <?php
        $pwaContent = ob_get_clean();
        $pageTitle = 'فعالیت‌ها';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    /**
     * PWA More/Settings
     */
    public function more(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $user = \Core\Auth::user();

        ob_start();
        ?>
        <div class="pwa-header">
            <div class="d-flex align-items-center justify-content-between">
                <h5 style="font-size:15px;">بیشتر</h5>
                <a href="<?php echo $config['url']; ?>/pwa/logout" style="color:var(--pwa-danger);text-decoration:none;font-size:13px;"><i class="bi bi-box-arrow-right me-1"></i>خروج</a>
            </div>
        </div>
        <div class="pwa-content pwa-fade">
            <!-- User Info -->
            <div class="pwa-card" style="text-align:center;">
                <div style="width:60px;height:60px;margin:0 auto 10px;background:linear-gradient(135deg,#4361ee,#7209b7);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff;font-weight:700;">
                    <?php echo mb_substr($user->full_name ?? '?', 0, 1); ?>
                </div>
                <div style="font-weight:700;font-size:15px;"><?php echo htmlspecialchars($user->full_name ?? ''); ?></div>
                <div style="color:var(--pwa-muted);font-size:12px;"><?php echo htmlspecialchars($user->role_name ?? ''); ?></div>
            </div>

            <!-- Menu Items -->
            <div class="pwa-card" style="padding:0;">
                <a href="<?php echo $config['url']; ?>/dashboard" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);">
                    <div class="pwa-list-icon" style="background:rgba(67,97,238,0.1);color:var(--pwa-primary);"><i class="bi bi-globe"></i></div>
                    <span class="pwa-list-title">نسخه وب کامل</span>
                    <div class="pwa-list-right"><i class="bi bi-chevron-left" style="color:var(--pwa-muted);"></i></div>
                </a>
                <a href="<?php echo $config['url']; ?>/pwa/calendar" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);">
                    <div class="pwa-list-icon" style="background:rgba(247,37,133,0.1);color:var(--pwa-accent);"><i class="bi bi-calendar3"></i></div>
                    <span class="pwa-list-title">تقویم</span>
                    <div class="pwa-list-right"><i class="bi bi-chevron-left" style="color:var(--pwa-muted);"></i></div>
                </a>
                <a href="<?php echo $config['url']; ?>/pwa/logout" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-danger);">
                    <div class="pwa-list-icon" style="background:rgba(239,71,111,0.1);color:var(--pwa-danger);"><i class="bi bi-box-arrow-right"></i></div>
                    <span class="pwa-list-title">خروج از حساب</span>
                </a>
            </div>

            <p style="text-align:center;color:var(--pwa-muted);font-size:11px;margin-top:20px;">
                علاءالدین سفیر اسمان - نسخه PWA<br>v1.0.0
            </p>
        </div>
        <nav class="pwa-bottom-nav">
            <a href="<?php echo $config['url']; ?>/pwa/app"><i class="bi bi-house-door"></i><span>خانه</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/deals"><i class="bi bi-briefcase"></i><span>معاملات</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/contacts"><i class="bi bi-people"></i><span>مخاطبان</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/activities"><i class="bi bi-calendar-check"></i><span>فعالیت‌ها</span></a>
            <a href="<?php echo $config['url']; ?>/pwa/more" class="active"><i class="bi bi-three-dots"></i><span>بیشتر</span></a>
        </nav>
        <?php
        $pwaContent = ob_get_clean();
        $pageTitle = 'بیشتر';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    // ========== STATIC FILE SERVING ==========

    /**
     * Serve Service Worker
     */
    public function serviceWorker(array $params = []): void
    {
        $swPath = __DIR__ . '/../public/sw.js';
        if (!file_exists($swPath)) { http_response_code(404); exit; }
        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: /');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        readfile($swPath);
        exit;
    }

    /**
     * Serve Manifest
     */
    public function manifest(array $params = []): void
    {
        $manifestPath = __DIR__ . '/../public/manifest.json';
        if (!file_exists($manifestPath)) { http_response_code(404); exit; }
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: no-cache');
        header('Access-Control-Allow-Origin: *');
        readfile($manifestPath);
        exit;
    }

    /**
     * Serve Offline page
     */
    public function offline(array $params = []): void
    {
        $offlinePath = __DIR__ . '/../public/offline.html';
        if (!file_exists($offlinePath)) { http_response_code(404); exit; }
        header('Content-Type: text/html; charset=utf-8');
        readfile($offlinePath);
        exit;
    }

    /**
     * Serve PWA icons
     */
    public function icon(array $params = []): void
    {
        $filename = $params['filename'] ?? '';
        $iconPath = __DIR__ . '/../public/assets/icons/' . basename($filename);
        if (!file_exists($iconPath)) { http_response_code(404); exit; }
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeTypes = ['svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','ico'=>'image/x-icon'];
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=604800');
        header('Access-Control-Allow-Origin: *');
        readfile($iconPath);
        exit;
    }

    /**
     * Push notification subscription
     */
    public function subscribe(array $params = []): void
    {
        header('Content-Type: application/json');
        if (!\Core\Auth::check()) { echo json_encode(['success'=>false]); exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['endpoint'])) { echo json_encode(['success'=>false]); exit; }
        
        $db = \Core\Database::getInstance();
        $userId = \Core\Auth::id();
        
        try {
            $db->query("CREATE TABLE IF NOT EXISTS push_subscriptions (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, endpoint TEXT NOT NULL, p256dh VARCHAR(255) DEFAULT '', auth VARCHAR(255) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_user_id (user_id), INDEX idx_endpoint (endpoint(255))) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->query("INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE p256dh=VALUES(p256dh), auth=VALUES(auth), updated_at=NOW()", [$userId, $input['endpoint'], $input['keys']['p256dh'] ?? '', $input['keys']['auth'] ?? '']);
        } catch (\Exception $e) {}
        
        echo json_encode(['success'=>true]);
        exit;
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(array $params = []): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['endpoint'])) {
            try {
                \Core\Database::getInstance()->query("DELETE FROM push_subscriptions WHERE endpoint = ?", [$input['endpoint']]);
            } catch (\Exception $e) {}
        }
        echo json_encode(['success'=>true]);
        exit;
    }
}