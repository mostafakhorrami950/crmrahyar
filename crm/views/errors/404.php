<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحه یافت نشد | 404</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $config['url']; ?>/assets/css/app.css">
</head>
<body>
    <div class="d-flex align-items-center justify-content-center min-vh-100 p-3" style="background: linear-gradient(135deg, #f0f2f5, #e2e6ea);">
        <div class="text-center" style="max-width: 500px;">
            <div style="font-size: 120px; opacity: 0.15;">✈️</div>
            <h1 class="fw-bold display-1 text-primary">404</h1>
            <h4 class="fw-bold mb-3">صفحه مورد نظر یافت نشد</h4>
            <p class="text-muted mb-4">متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد یا منتقل شده است.</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="<?php echo $config['url']; ?>/dashboard" class="btn btn-primary"><i class="bi bi-speedometer2 me-1"></i>داشبورد</a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>