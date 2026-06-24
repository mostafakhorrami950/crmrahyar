<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ورود به سیستم | CRM آژانس مسافرتی</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $config['url']; ?>/assets/css/app.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <div class="logo-icon">✈️</div>
            <h2 class="fw-bold text-dark mb-1">CRM آژانس مسافرتی</h2>
            <p class="text-muted small">سیستم مدیریت ارتباط با مشتریان</p>
        </div>

        <?php 
        $flashes = \Core\Session::getFlashes();
        foreach ($flashes as $flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type'] === 'danger' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>

        <form method="POST" action="<?php echo $config['url']; ?>/login">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">نام کاربری</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="admin" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small fw-medium">رمز عبور</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold" style="background: linear-gradient(135deg, #4361ee, #7209b7); border: none; border-radius: 12px; padding: 14px;">
                <i class="bi bi-box-arrow-in-left me-2"></i>ورود به سیستم
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">CRM Travel Agency v2.0.0</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>