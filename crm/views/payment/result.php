<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتیجه پرداخت</title>
    <link rel="stylesheet" href="<?php echo $config['url']; ?>/assets/css/app.css?v=1.0.0">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Vazir', 'Tahoma', sans-serif;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        .result-card {
            background: #fff;
            border-radius: 24px;
            padding: 48px 32px;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            text-align: center;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .result-icon {
            font-size: 72px;
            margin-bottom: 16px;
        }
        .result-card h1 {
            font-size: 22px;
            margin: 0 0 8px;
            color: #1a1a2e;
        }
        .result-card p {
            color: #6c757d;
            font-size: 14px;
            margin: 0 0 24px;
            line-height: 1.7;
        }
        .result-details {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: right;
        }
        .result-details .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }
        .result-details .row:last-child {
            border-bottom: none;
        }
        .result-details .label {
            color: #6c757d;
        }
        .result-details .value {
            font-weight: bold;
            color: #1a1a2e;
        }
        .btn-return {
            display: inline-block;
            padding: 12px 32px;
            background: #667eea;
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .btn-return:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .powered {
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.7);
            font-size: 12px;
        }
        .powered a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="result-card">
        <?php if ($success): ?>
            <div class="result-icon">✅</div>
            <h1>پرداخت با موفقیت انجام شد</h1>
            <p>پرداخت شما با موفقیت تایید شد. از اعتماد شما سپاسگزاریم.</p>
        <?php else: ?>
            <div class="result-icon">❌</div>
            <h1>پرداخت ناموفق</h1>
            <p><?php echo htmlspecialchars($message ?? 'متاسفانه پرداخت شما ناموفق بود. لطفاً مجدداً تلاش کنید.'); ?></p>
        <?php endif; ?>

        <div class="result-details">
            <?php if (!empty($trackId)): ?>
            <div class="row">
                <span class="label">کد پیگیری</span>
                <span class="value"><?php echo htmlspecialchars($trackId); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($refNumber)): ?>
            <div class="row">
                <span class="label">شماره مرجع</span>
                <span class="value"><?php echo htmlspecialchars($refNumber); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($amount)): ?>
            <div class="row">
                <span class="label">مبلغ</span>
                <span class="value"><?php echo number_format($amount); ?> تومان</span>
            </div>
            <?php endif; ?>
            <div class="row">
                <span class="label">تاریخ</span>
                <span class="value"><?php echo date('Y/m/d H:i'); ?></span>
            </div>
        </div>

        <?php if ($success && !empty($returnUrl)): ?>
            <a href="<?php echo htmlspecialchars($returnUrl); ?>" class="btn-return">🔄 بازگشت به سایت</a>
        <?php else: ?>
            <a href="<?php echo $config['url']; ?>" class="btn-return">🏠 بازگشت به صفحه اصلی</a>
        <?php endif; ?>
    </div>

    <div class="powered">
        <a href="<?php echo $config['url']; ?>">CRM آژانس مسافرتی</a>
    </div>
</body>
</html>