<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo $config['url']; ?>/">
    <title><?php echo $title ?? 'CRM Travel Agency'; ?> | CRM آژانس مسافرتی</title>
    <link rel="stylesheet" href="assets/css/app.css?v=1.0.0">
    <script src="assets/js/app.js?v=1.0.0"></script>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <h3>CRM آژانس</h3>
                <span>مسافرتی</span>
            </div>
            
            <div class="sidebar-section">ناوبری اصلی</div>
            <nav class="sidebar-nav">
                <a href="<?php echo $config['url']; ?>/dashboard" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : ''; ?>">
                    <span class="icon">📊</span> داشبورد
                </a>
                
                <?php if (\Core\Auth::hasPermission('pipelines.view')): ?>
                <a href="<?php echo $config['url']; ?>/pipelines" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/pipelines') !== false && strpos($_SERVER['REQUEST_URI'], '/pipelines/kanban') === false ? 'active' : ''; ?>">
                    <span class="icon">📋</span> پایپ لاین‌ها
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('deals.view')): ?>
                <a href="<?php echo $config['url']; ?>/deals" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/deals') !== false ? 'active' : ''; ?>">
                    <span class="icon">💼</span> معاملات
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('contacts.view')): ?>
                <a href="<?php echo $config['url']; ?>/contacts" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/contacts') !== false ? 'active' : ''; ?>">
                    <span class="icon">👥</span> مخاطبان
                </a>
                <?php endif; ?>
                
                <?php if ($config['features']['payment_gateway'] && \Core\Auth::hasPermission('payments.view')): ?>
                <a href="<?php echo $config['url']; ?>/payment/history" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/payment') !== false ? 'active' : ''; ?>">
                    <span class="icon">💳</span> پرداخت‌ها
                </a>
                <?php endif; ?>
                
                <?php if ($config['features']['sms'] && \Core\Auth::hasPermission('sms.send')): ?>
                <a href="<?php echo $config['url']; ?>/sms/history" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/sms') !== false ? 'active' : ''; ?>">
                    <span class="icon">✉️</span> پیامک‌ها
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('reports.view')): ?>
                <div class="sidebar-section">گزارشات</div>
                <a href="<?php echo $config['url']; ?>/reports" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/reports') !== false && strpos($_SERVER['REQUEST_URI'], '/reports/sales') === false && strpos($_SERVER['REQUEST_URI'], '/reports/pipeline') === false && strpos($_SERVER['REQUEST_URI'], '/reports/activities') === false && strpos($_SERVER['REQUEST_URI'], '/reports/contacts') === false) ? 'active' : ''; ?>">
                    <span class="icon">📈</span> داشبورد گزارشات
                </a>
                <?php endif; ?>
                
                <div class="sidebar-section">مدیریت</div>
                
                <?php if (\Core\Auth::hasPermission('users.manage')): ?>
                <a href="<?php echo $config['url']; ?>/users" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/users') !== false ? 'active' : ''; ?>">
                    <span class="icon">👤</span> کاربران
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('roles.manage')): ?>
                <a href="<?php echo $config['url']; ?>/roles" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/roles') !== false ? 'active' : ''; ?>">
                    <span class="icon">🛡️</span> نقش‌ها
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('settings.manage')): ?>
                <a href="<?php echo $config['url']; ?>/settings" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/settings') !== false ? 'active' : ''; ?>">
                    <span class="icon">⚙️</span> تنظیمات
                </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="<?php echo $config['url']; ?>/logout">🚪 خروج</a>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="d-flex align-center gap-12">
                    <button class="mobile-menu-btn" id="sidebarToggle">☰</button>
                    <h4><?php echo $title ?? 'داشبورد'; ?></h4>
                </div>
                <div class="user-area">
                    <span class="date"><?php echo date('Y/m/d'); ?></span>
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <span>👤</span>
                            <?php echo \Core\Auth::user()->full_name ?? ''; ?>
                            <span style="font-size:10px;">▼</span>
                        </button>
                        <div class="user-dropdown-menu">
                            <span class="item-text"><?php echo \Core\Auth::user()->role_name ?? ''; ?></span>
                            <div class="divider"></div>
                            <a href="<?php echo $config['url']; ?>/logout" class="item danger">🚪 خروج</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Flash Messages -->
                <div class="flash-container">
                    <?php 
                    $flashes = \Core\Session::getFlashes();
                    foreach ($flashes as $flash): 
                    ?>
                        <div class="flash flash-<?php echo $flash['type']; ?>">
                            <span><?php 
                                $icons = ['success'=>'✅', 'danger'=>'❌', 'warning'=>'⚠️', 'info'=>'ℹ️'];
                                echo $icons[$flash['type']] ?? 'ℹ️';
                            ?></span>
                            <span><?php echo htmlspecialchars($flash['message']); ?></span>
                            <button class="close-btn">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php echo $content ?? ''; ?>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Flash auto-hide
        document.querySelectorAll('.flash').forEach(function(flash) {
            setTimeout(function() {
                flash.style.opacity = '0';
                flash.style.transform = 'translateX(80px)';
                flash.style.transition = 'all 0.3s ease';
                setTimeout(function() { flash.remove(); }, 300);
            }, 5000);
        });
        // Flash close
        document.querySelectorAll('.flash .close-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                this.parentElement.remove();
            });
        });
        // User dropdown
        document.querySelectorAll('.user-dropdown-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var menu = this.nextElementSibling;
                menu.classList.toggle('show');
            });
        });
        document.addEventListener('click', function() {
            document.querySelectorAll('.user-dropdown-menu.show').forEach(function(m) {
                m.classList.remove('show');
            });
        });
        // Sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });
        document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('open');
            this.classList.remove('show');
        });
    });
    </script>
</body>
</html>