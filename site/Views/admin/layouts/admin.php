<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($meta['title'] ?? 'پنل مدیریت'); ?> - مدیریت سایت</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Vazirmatn, sans-serif; background: #f1f5f9; color: #1e293b; display: flex; min-height: 100vh; }
        a { color: inherit; text-decoration: none; }

        /* Sidebar */
        .sidebar { width: 260px; background: #1e293b; color: #e2e8f0; padding: 0; position: fixed; top: 0; right: 0; height: 100vh; overflow-y: auto; z-index: 200; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid #334155; text-align: center; }
        .sidebar-header h2 { font-size: 18px; font-weight: 900; color: #fff; }
        .sidebar-header small { color: #94a3b8; font-size: 11px; }
        .sidebar-menu { padding: 10px 0; }
        .sidebar-menu .group-title { padding: 10px 20px 4px; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 20px; font-size: 13px; font-weight: 600; color: #94a3b8; transition: 0.2s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #334155; color: #fff; }
        .sidebar-menu a .icon { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid #334155; font-size: 11px; color: #64748b; }
        .sidebar-footer a { color: #94a3b8; }

        /* Main */
        .main { margin-right: 260px; flex: 1; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .topbar h1 { font-size: 18px; font-weight: 800; }
        .topbar .user-info { display: flex; align-items: center; gap: 12px; font-size: 13px; }
        .content { padding: 24px; }

        /* Cards */
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; }
        .stat-card .num { font-size: 32px; font-weight: 900; }
        .stat-card .label { font-size: 12px; color: #64748b; font-weight: 600; }

        /* Forms */
        .form-group { margin-bottom: 14px; }
        .form-group label { font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font: inherit; font-size: 13px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #4f46e5; outline: none; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }

        /* Buttons */
        .btn { display: inline-block; padding: 8px 16px; border: none; border-radius: 6px; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.2s; text-decoration: none; }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-success { background: #059669; color: #fff; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #475569; }
        .btn-sm { padding: 4px 10px; font-size: 11px; }

        /* Table */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #1e293b; color: #fff; padding: 10px 12px; text-align: right; font-weight: 700; }
        td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
        tr:hover { background: #f8fafc; }

        /* Alert */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 600; }
        .alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-right: 0; }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
            .stat-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>⚙️ مدیریت سایت</h2>
            <small>پنل مدیریت رهیار</small>
        </div>
        <nav class="sidebar-menu">
            <a href="/admin" class="<?php echo ($_SERVER['REQUEST_URI'] === '/admin') ? 'active' : ''; ?>">
                <span class="icon">📊</span> داشبورد
            </a>

            <div class="group-title">مدیریت محتوا</div>
            <a href="/admin/hotels" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/hotels') === 0) ? 'active' : ''; ?>">
                <span class="icon">🏨</span> هتل‌ها و اتاق‌ها
            </a>
            <a href="/admin/cities" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/cities') === 0) ? 'active' : ''; ?>">
                <span class="icon">🏙️</span> شهرها و محله‌ها
            </a>
            <a href="/admin/blog" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/blog') === 0) ? 'active' : ''; ?>">
                <span class="icon">📝</span> بلاگ
            </a>
            <a href="/admin/pages" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/pages') === 0) ? 'active' : ''; ?>">
                <span class="icon">📄</span> صفحات
            </a>
            <a href="/admin/faqs" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/faqs') === 0) ? 'active' : ''; ?>">
                <span class="icon">❓</span> سوالات متداول
            </a>

            <div class="group-title">رزروها</div>
            <a href="/admin/bookings" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/bookings') === 0) ? 'active' : ''; ?>">
                <span class="icon">📋</span> رزروها
            </a>

            <div class="group-title">سئو و تنظیمات</div>
            <a href="/admin/seo" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/seo') === 0) ? 'active' : ''; ?>">
                <span class="icon">🔍</span> سئو
            </a>
            <a href="/admin/settings" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/settings') === 0) ? 'active' : ''; ?>">
                <span class="icon">⚙️</span> تنظیمات سایت
            </a>
            <a href="/admin/database" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/database') === 0) ? 'active' : ''; ?>">
                <span class="icon">🗄️</span> تعمیرات دیتابیس
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="/crm">💼 بازگشت به CRM</a> · <a href="/" target="_blank">🌐 مشاهده سایت</a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main">
        <div class="topbar">
            <h1><?php echo htmlspecialchars($meta['title'] ?? 'پنل مدیریت'); ?></h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'ادمین'); ?></span>
                <a href="/logout" style="color: #dc2626; font-size: 12px;">خروج</a>
            </div>
        </div>
        <div class="content">
            <?php if (!empty($_GET['updated'])): ?>
            <div class="alert alert-success">✅ با موفقیت ذخیره شد.</div>
            <?php endif; ?>
            <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </div>
    </div>
</body>
</html>