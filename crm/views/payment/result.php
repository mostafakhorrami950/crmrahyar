<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتیجه پرداخت | <?php echo htmlspecialchars($config['name'] ?? 'CRM'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background: #0f0c29;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            direction: rtl;
            overflow-x: hidden;
        }

        body::before, body::after {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            opacity: 0.03;
            pointer-events: none;
            z-index: 0;
        }
        body::before {
            background: #667eea;
            top: -200px;
            right: -200px;
            animation: float 20s ease-in-out infinite;
        }
        body::after {
            background: #764ba2;
            bottom: -200px;
            left: -200px;
            animation: float 25s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(50px, -50px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
        }

        .result-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            animation: containerIn 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }

        @keyframes containerIn {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .result-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 
                0 32px 64px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
        }

        /* Header with animated icon */
        .result-header {
            padding: 40px 32px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .result-header.success {
            background: linear-gradient(135deg, #11998e 0%, #28b487 50%, #11998e 100%);
        }
        .result-header.failed {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 50%, #eb3349 100%);
        }
        .result-header.error {
            background: linear-gradient(135deg, #f2994a 0%, #f2c94c 50%, #f2994a 100%);
        }

        .result-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: shimmer 6s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%, 100% { transform: translate(-10%, -10%) rotate(0deg); }
            50% { transform: translate(10%, 10%) rotate(10deg); }
        }

        .result-header .icon-wrap {
            position: relative;
            display: inline-flex;
            width: 96px;
            height: 96px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin-bottom: 16px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            animation: iconPop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.3s both;
        }
        @keyframes iconPop {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .result-header .status-label {
            position: relative;
            color: #fff;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .result-header .status-sub {
            position: relative;
            color: rgba(255,255,255,0.85);
            font-size: 14px;
            margin: 8px 0 0;
            font-weight: 400;
            line-height: 1.7;
        }

        /* Confetti animation for success */
        .confetti-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }
        .confetti {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 2px;
            opacity: 0.6;
            animation: confettiFall linear forwards;
        }
        @keyframes confettiFall {
            0% { transform: translateY(-20px) rotate(0deg); opacity: 0.8; }
            100% { transform: translateY(200px) rotate(720deg); opacity: 0; }
        }

        /* Body */
        .result-body {
            padding: 28px 32px 32px;
        }

        .result-details {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
            border-radius: 20px;
            padding: 20px 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        .result-details .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.08);
        }
        .result-details .row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .result-details .row:first-child {
            padding-top: 0;
        }
        .result-details .label {
            color: #6c7293;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .result-details .value {
            font-weight: 700;
            color: #1e1e2d;
            font-size: 14px;
            text-align: left;
            direction: ltr;
        }
        .result-details .amount-value {
            font-size: 22px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Divider */
        .divider-line {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 20px 0;
        }
        .divider-line::before, .divider-line::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e0e0e0, transparent);
        }
        .divider-line span {
            color: #b0b0c0;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }

        /* Action buttons */
        .result-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn-return {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 16px 24px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 16px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        .btn-return.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.35);
        }
        .btn-return.primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(102, 126, 234, 0.45);
        }
        .btn-return.secondary {
            background: #f0f2ff;
            color: #667eea;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        .btn-return.secondary:hover {
            background: #e8eaff;
            transform: translateY(-2px);
        }

        /* Footer */
        .result-footer {
            text-align: center;
            margin-top: 16px;
            color: rgba(255,255,255,0.3);
            font-size: 11px;
            font-weight: 400;
            letter-spacing: 0.5px;
        }
        .result-footer a {
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            transition: color 0.2s;
        }
        .result-footer a:hover {
            color: rgba(255,255,255,0.8);
        }

        /* Responsive */
        @media (max-width: 480px) {
            body { padding: 12px; }
            .result-body { padding: 20px; }
            .result-header { padding: 28px 20px 24px; }
            .result-header .icon-wrap { width: 72px; height: 72px; font-size: 36px; }
            .result-header .status-label { font-size: 20px; }
            .result-details .amount-value { font-size: 18px; }
        }
    </style>
</head>
<body>
    <div class="result-wrapper">
        <div class="result-card">
            <?php if ($success): ?>
            <!-- SUCCESS HEADER -->
            <div class="result-header success">
                <div class="confetti-container" id="confettiContainer"></div>
                <div class="icon-wrap"><i class="bi bi-check-circle text-success me-1"></i></div>
                <h1 class="status-label">پرداخت با موفقیت انجام شد</h1>
                <p class="status-sub">از اعتماد شما سپاسگزاریم. پرداخت شما با موفقیت تایید شد.</p>
            </div>
            <?php elseif (!empty($trackId) || !empty($message)): ?>
            <!-- FAILED HEADER -->
            <div class="result-header failed">
                <div class="icon-wrap"><i class="bi bi-x-circle text-danger me-1"></i></div>
                <h1 class="status-label">پرداخت ناموفق</h1>
                <p class="status-sub"><?php echo htmlspecialchars($message ?: 'متاسفانه پرداخت شما ناموفق بود.'); ?></p>
            </div>
            <?php else: ?>
            <!-- ERROR HEADER -->
            <div class="result-header error">
                <div class="icon-wrap">⚠️</div>
                <h1 class="status-label">خطا</h1>
                <p class="status-sub"><?php echo htmlspecialchars($message ?: 'اطلاعات پرداخت یافت نشد.'); ?></p>
            </div>
            <?php endif; ?>

            <!-- BODY -->
            <div class="result-body">
                <div class="result-details">
                    <?php if (!empty($trackId)): ?>
                    <div class="row">
                        <span class="label">🔖 کد پیگیری</span>
                        <span class="value"><?php echo htmlspecialchars($trackId); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($refNumber)): ?>
                    <div class="row">
                        <span class="label">🏦 شماره مرجع</span>
                        <span class="value"><?php echo htmlspecialchars($refNumber); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($amount)): ?>
                    <div class="row">
                        <span class="label"><i class="bi bi-cash me-1"></i> مبلغ</span>
                        <span class="value amount-value"><?php echo number_format($amount); ?> تومان</span>
                    </div>
                    <?php endif; ?>
                    <div class="row">
                        <span class="label"><i class="bi bi-calendar me-1"></i> تاریخ</span>
                        <span class="value"><?php echo \Core\JDate::displayDateTime(date('Y-m-d H:i:s')); ?></span>
                    </div>
                </div>

                <div class="divider-line">
                    <span>اقدام بعدی</span>
                </div>

                <div class="result-actions">
                    <?php if ($success): ?>
                        <?php if (!empty($dealId)): ?>
                        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $dealId; ?>" class="btn-return primary">
                            🏠 بازگشت به معامله
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo $config['url']; ?>" class="btn-return secondary">
                            🏠 بازگشت به سایت
                        </a>
                    <?php else: ?>
                        <?php if (!empty($publicToken)): ?>
                        <a href="<?php echo $config['url']; ?>/pay/<?php echo htmlspecialchars($publicToken); ?>" class="btn-return primary">
                            <i class="bi bi-arrow-repeat me-1"></i> تلاش مجدد
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo $config['url']; ?>" class="btn-return secondary">
                            🏠 بازگشت به سایت
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="result-footer">
            <a href="<?php echo htmlspecialchars($config['url']); ?>"><?php echo htmlspecialchars($config['name'] ?? 'CRM'); ?></a>
        </div>
    </div>

<script>
(function() {
    'use strict';
    // Confetti animation for success
    var container = document.getElementById('confettiContainer');
    if (container) {
        var colors = ['#ffd700', '#ff6b6b', '#48dbfb', '#ff9ff3', '#54a0ff', '#5f27cd', '#01a3a4', '#f368e0'];
        for (var i = 0; i < 40; i++) {
            var el = document.createElement('div');
            el.className = 'confetti';
            el.style.left = Math.random() * 100 + '%';
            el.style.background = colors[Math.floor(Math.random() * colors.length)];
            el.style.width = (Math.random() * 6 + 4) + 'px';
            el.style.height = (Math.random() * 6 + 4) + 'px';
            el.style.animationDuration = (Math.random() * 2 + 2) + 's';
            el.style.animationDelay = (Math.random() * 1.5) + 's';
            el.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            container.appendChild(el);
        }
    }
})();
</script>
</body>
</html>