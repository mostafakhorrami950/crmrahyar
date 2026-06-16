<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم | CRM آژانس مسافرتی</title>
    <link rel="stylesheet" href="<?php echo $config['url']; ?>/assets/css/app.css">
    <style>
        body { 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%); 
            padding: 20px;
            font-family: 'Tahoma', 'Segoe UI', sans-serif;
        }
        .login-box {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px 36px;
            max-width: 400px;
            width: 100%;
        }
        .login-logo { text-align: center; margin-bottom: 32px; }
        .login-logo .logo-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #4361ee, #7209b7);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px; font-size: 28px; color: #fff;
        }
        .login-logo h2 { font-size: 20px; color: #333; font-weight: bold; }
        .login-logo p { font-size: 13px; color: #888; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; color: #555; margin-bottom: 6px; font-weight: 500; }
        .form-input {
            width: 100%; padding: 12px 15px;
            border: 2px solid #e8ecf1; border-radius: 10px;
            font-size: 14px; outline: none; box-sizing: border-box;
        }
        .form-input:focus { border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
        .btn-login {
            background: linear-gradient(135deg, #4361ee, #7209b7);
            color: #fff; border: none; padding: 12px; border-radius: 10px;
            font-size: 16px; font-weight: bold; width: 100%; cursor: pointer;
            transition: all 0.2s;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(67,97,238,0.4); }
        .flash { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .version { text-align: center; margin-top: 25px; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <div class="logo-icon">✈️</div>
            <h2>CRM آژانس مسافرتی</h2>
            <p>سیستم مدیریت ارتباط با مشتریان</p>
        </div>

        <?php 
        $flashes = \Core\Session::getFlashes();
        foreach ($flashes as $flash): 
        ?>
            <div class="flash flash-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endforeach; ?>

        <form method="POST" action="<?php echo $config['url']; ?>/login">
            <div class="form-group">
                <label class="form-label">نام کاربری</label>
                <input type="text" name="username" class="form-input" placeholder="admin" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">رمز عبور</label>
                <input type="password" name="password" class="form-input" placeholder="••••••" required>
            </div>
            <button type="submit" class="btn-login">ورود به سیستم</button>
        </form>

        <div class="version">CRM Travel Agency v1.0.0</div>
    </div>
</body>
</html>