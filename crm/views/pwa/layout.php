<?php $config = $GLOBALS['app_config']; $pwaUrl = $config['url']; ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?php echo $pageTitle ?? 'سفیر اسمان'; ?> | PWA</title>
    <meta name="theme-color" content="#1a1a2e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="سفیر اسمان">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="description" content="اپلیکیشن مدیریت آژانس مسافرتی علاءالدین سفیر اسمان">
    <link rel="manifest" href="<?php echo $pwaUrl; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo $pwaUrl; ?>/pwa/icon/icon-192x192.svg">
    <link rel="icon" type="image/svg+xml" href="<?php echo $pwaUrl; ?>/pwa/icon/icon-192x192.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <style>
        :root {
            --pwa-bg: #0f0f23;
            --pwa-card: #1a1a2e;
            --pwa-primary: #4361ee;
            --pwa-secondary: #7209b7;
            --pwa-accent: #f72585;
            --pwa-text: #e8e8e8;
            --pwa-muted: #8b8b9e;
            --pwa-success: #06d6a0;
            --pwa-warning: #ffd166;
            --pwa-danger: #ef476f;
            --pwa-border: rgba(255,255,255,0.08);
            --safe-top: env(safe-area-inset-top, 0px);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background: var(--pwa-bg);
            color: var(--pwa-text);
            min-height: 100vh;
            min-height: 100dvh;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        /* PWA Header */
        .pwa-header {
            background: linear-gradient(135deg, var(--pwa-card), #16213e);
            padding: calc(var(--safe-top) + 12px) 16px 12px;
            border-bottom: 1px solid var(--pwa-border);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        .pwa-header h5 { font-size: 16px; font-weight: 700; margin: 0; }
        .pwa-header .brand-sm { font-size: 11px; color: var(--pwa-muted); }
        /* Bottom Nav */
        .pwa-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--pwa-card);
            border-top: 1px solid var(--pwa-border);
            padding: 8px 0 calc(var(--safe-bottom) + 8px);
            z-index: 100;
            display: flex;
            justify-content: space-around;
        }
        .pwa-bottom-nav a {
            color: var(--pwa-muted);
            text-decoration: none;
            text-align: center;
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }
        .pwa-bottom-nav a.active {
            color: var(--pwa-primary);
            background: rgba(67,97,238,0.1);
        }
        .pwa-bottom-nav a i { font-size: 20px; }
        /* Content area */
        .pwa-content {
            padding: 16px;
            padding-bottom: calc(70px + var(--safe-bottom));
            min-height: calc(100vh - 60px);
            min-height: calc(100dvh - 60px);
        }
        /* Cards */
        .pwa-card {
            background: var(--pwa-card);
            border-radius: 16px;
            border: 1px solid var(--pwa-border);
            padding: 16px;
            margin-bottom: 12px;
        }
        .pwa-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .pwa-card-title { font-size: 14px; font-weight: 600; }
        /* Stat cards */
        .pwa-stat {
            background: var(--pwa-card);
            border-radius: 14px;
            border: 1px solid var(--pwa-border);
            padding: 14px;
            text-align: center;
        }
        .pwa-stat-value { font-size: 22px; font-weight: 800; }
        .pwa-stat-label { font-size: 11px; color: var(--pwa-muted); margin-top: 4px; }
        /* List items */
        .pwa-list-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid var(--pwa-border);
            transition: background 0.2s;
        }
        .pwa-list-item:last-child { border-bottom: none; }
        .pwa-list-item:active { background: rgba(255,255,255,0.03); }
        .pwa-list-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }
        .pwa-list-title { font-size: 13px; font-weight: 600; }
        .pwa-list-sub { font-size: 11px; color: var(--pwa-muted); margin-top: 2px; }
        .pwa-list-right { margin-right: auto; text-align: left; }
        /* Badges */
        .pwa-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .pwa-badge-success { background: rgba(6,214,160,0.15); color: var(--pwa-success); }
        .pwa-badge-warning { background: rgba(255,209,102,0.15); color: var(--pwa-warning); }
        .pwa-badge-danger { background: rgba(239,71,111,0.15); color: var(--pwa-danger); }
        .pwa-badge-primary { background: rgba(67,97,238,0.15); color: var(--pwa-primary); }
        /* Buttons */
        .pwa-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 12px;
            border: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #fff;
        }
        .pwa-btn-primary { background: linear-gradient(135deg, var(--pwa-primary), var(--pwa-secondary)); }
        .pwa-btn-ghost { background: transparent; border: 1px solid var(--pwa-border); color: var(--pwa-text); }
        .pwa-btn-block { width: 100%; }
        /* Form controls */
        .pwa-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--pwa-border);
            border-radius: 12px;
            color: var(--pwa-text);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .pwa-input:focus { border-color: var(--pwa-primary); }
        .pwa-input::placeholder { color: var(--pwa-muted); }
        .pwa-select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--pwa-border);
            border-radius: 12px;
            color: var(--pwa-text);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            appearance: none;
        }
        .pwa-label { font-size: 12px; color: var(--pwa-muted); margin-bottom: 6px; display: block; }
        /* Section title */
        .pwa-section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--pwa-muted);
            margin-bottom: 10px;
            padding-right: 4px;
        }
        /* Quick actions grid */
        .pwa-quick-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        .pwa-quick-item {
            text-align: center;
            padding: 14px 6px;
            background: var(--pwa-card);
            border-radius: 14px;
            border: 1px solid var(--pwa-border);
            text-decoration: none;
            color: var(--pwa-text);
            transition: transform 0.2s;
        }
        .pwa-quick-item:active { transform: scale(0.95); }
        .pwa-quick-item i { font-size: 22px; display: block; margin-bottom: 6px; }
        .pwa-quick-item span { font-size: 10px; font-weight: 600; }
        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
        .pwa-fade { animation: fadeIn 0.3s ease; }
        /* Skeleton loading */
        .pwa-skeleton {
            background: linear-gradient(90deg, rgba(255,255,255,0.03) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.03) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        /* Pull to refresh indicator */
        .pwa-ptr { text-align: center; padding: 8px; color: var(--pwa-muted); font-size: 12px; }
        /* Alert */
        .pwa-alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 12px;
        }
        .pwa-alert-danger { background: rgba(239,71,111,0.1); border: 1px solid rgba(239,71,111,0.2); color: var(--pwa-danger); }
        .pwa-alert-success { background: rgba(6,214,160,0.1); border: 1px solid rgba(6,214,160,0.2); color: var(--pwa-success); }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--pwa-border); border-radius: 4px; }
    </style>
</head>
<body>
    <?php echo $pwaContent ?? ''; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    var PWA_BASE = '<?php echo $config["url"]; ?>';
    
    // Register Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(PWA_BASE + '/sw.js').then(function(reg) {
            console.log('[PWA] SW registered');
        }).catch(function(e) { console.log('[PWA] SW error:', e); });
    }
    
    // AJAX helper
    function pwaFetch(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers['X-Requested-With'] = 'XMLHttpRequest';
        return fetch(url, options).then(function(r) { return r.json(); });
    }
    
    // Format number
    function pwaFormatNum(n) {
        return parseInt(n).toLocaleString('en-US');
    }
    
    // Show toast
    function pwaToast(msg, type) {
        var toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;top:80px;left:50%;transform:translateX(-50%);z-index:9999;padding:10px 20px;border-radius:12px;font-size:13px;font-family:Vazirmatn,sans-serif;animation:fadeIn 0.3s;max-width:90%;text-align:center;';
        toast.style.background = type === 'error' ? 'rgba(239,71,111,0.9)' : 'rgba(6,214,160,0.9)';
        toast.style.color = '#fff';
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }
    </script>
</body>
</html>