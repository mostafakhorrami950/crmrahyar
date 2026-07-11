<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($meta['title'] ?? 'رزرو هتل'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta['description'] ?? ''); ?>">
    <?php if (!empty($meta['canonical'])): ?><link rel="canonical" href="<?php echo $meta['canonical']; ?>"><?php endif; ?>
    <meta name="robots" content="<?php echo $meta['robots'] ?? 'index, follow'; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($meta['og_title'] ?? $meta['title'] ?? ''); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta['og_description'] ?? $meta['description'] ?? ''); ?>">
    <?php if (!empty($meta['og_image'])): ?><meta property="og:image" content="<?php echo $meta['og_image']; ?>"><?php endif; ?>
    <meta property="og:type" content="<?php echo $meta['og_type'] ?? 'website'; ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Vazirmatn, sans-serif; background: #f8fafc; color: #1e293b; line-height: 1.6; }
        a { color: inherit; text-decoration: none; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* Header */
        .site-header { background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 100; }
        .header-inner { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; }
        .logo { font-size: 20px; font-weight: 900; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .logo span { color: #4f46e5; }
        .nav { display: flex; gap: 24px; align-items: center; }
        .nav a { font-size: 14px; font-weight: 600; color: #64748b; transition: color 0.2s; }
        .nav a:hover { color: #1e293b; }
        .nav .btn-login { background: #4f46e5; color: #fff; padding: 8px 20px; border-radius: 8px; font-weight: 700; }
        .nav .btn-login:hover { background: #4338ca; }

        /* Hero */
        .hero { background: linear-gradient(135deg, #1e1b4b, #312e81, #4338ca); padding: 60px 0; text-align: center; color: #fff; }
        .hero h1 { font-size: 36px; font-weight: 900; margin-bottom: 12px; }
        .hero p { font-size: 16px; opacity: 0.8; margin-bottom: 30px; }
        .search-box { background: #fff; border-radius: 16px; padding: 20px; max-width: 700px; margin: 0 auto; display: flex; gap: 10px; flex-wrap: wrap; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .search-box select, .search-box input { padding: 10px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font: inherit; font-size: 14px; flex: 1; min-width: 140px; }
        .search-box select:focus, .search-box input:focus { border-color: #4f46e5; outline: none; }
        .search-box button { background: #4f46e5; color: #fff; border: none; padding: 10px 24px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .search-box button:hover { background: #4338ca; transform: translateY(-1px); }

        /* Hotel Grid */
        .section-title { font-size: 24px; font-weight: 900; margin: 40px 0 20px; color: #1e293b; }
        .hotel-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .hotel-card { background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; transition: all 0.3s; cursor: pointer; }
        .hotel-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.1); border-color: #c7d2fe; }
        .hotel-img { height: 180px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 40px; }
        .hotel-body { padding: 16px; }
        .hotel-body h3 { font-size: 16px; font-weight: 800; margin-bottom: 4px; }
        .hotel-body .city { font-size: 12px; color: #64748b; margin-bottom: 8px; }
        .hotel-body .stars { color: #f59e0b; font-size: 12px; margin-bottom: 8px; }
        .hotel-tags { display: flex; gap: 4px; flex-wrap: wrap; }
        .hotel-tags span { background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; }

        /* Footer */
        .site-footer { background: #1e293b; color: #94a3b8; padding: 30px 0; text-align: center; font-size: 12px; margin-top: 40px; }

        /* Responsive */
        @media (max-width: 768px) {
            .search-box { flex-direction: column; }
            .hero h1 { font-size: 24px; }
            .nav { gap: 12px; }
            .hotel-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="/" class="logo"><span>🏨</span> <?php echo htmlspecialchars(\Shared\Core\Config::getInstance()->get('site_title', 'رزرو هتل')); ?></a>
            <nav class="nav">
                <a href="/hotels">هتل‌ها</a>
                <a href="/blog">بلاگ</a>
                <a href="/contact">تماس</a>
                <a href="/login" class="btn-login">ورود</a>
            </nav>
        </div>
    </header>

    <?php echo $content ?? ''; ?>

    <footer class="site-footer">
        <div class="container">
            © <?php echo date('Y'); ?> <?php echo htmlspecialchars($company ?? 'آژانس مسافرتی'); ?> — تمامی حقوق محفوظ است.
        </div>
    </footer>
</body>
</html>