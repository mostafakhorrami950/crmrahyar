<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت آنلاین | <?php echo htmlspecialchars($config['name'] ?? 'CRM'); ?></title>
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
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background particles */
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

        .payment-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            animation: containerIn 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }

        @keyframes containerIn {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .payment-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 32px;
            padding: 0;
            overflow: hidden;
            box-shadow: 
                0 32px 64px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
        }

        /* Card Header Gradient */
        .card-header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .card-header-gradient::before {
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
        .card-header-gradient .icon-wrap {
            position: relative;
            display: inline-flex;
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 24px;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 16px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .card-header-gradient h1 {
            position: relative;
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header-gradient p {
            position: relative;
            color: rgba(255,255,255,0.8);
            font-size: 13px;
            margin: 6px 0 0;
            font-weight: 400;
        }

        .card-body {
            padding: 28px 32px 32px;
        }

        /* Deal Info Box */
        .deal-info {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
            border-radius: 20px;
            padding: 20px 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        .deal-info .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.08);
        }
        .deal-info .row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .deal-info .row:first-child {
            padding-top: 0;
        }
        .deal-info .label {
            color: #6c7293;
            font-size: 13px;
            font-weight: 500;
        }
        .deal-info .value {
            font-weight: 700;
            color: #1e1e2d;
            font-size: 14px;
        }
        .deal-info .amount-value {
            font-size: 26px;
            font-weight: 900;
            background: linear-gradient(135deg, #28a745, #20c997);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .deal-info .amount-label {
            font-size: 13px;
            color: #6c7293;
            font-weight: 500;
        }

        /* Description */
        .deal-description {
            background: #fff;
            border-radius: 12px;
            padding: 14px 18px;
            margin: 12px 0 0;
            border: 1px solid #e8eaff;
            font-size: 13px;
            color: #4a4a6a;
            line-height: 1.8;
        }
        .deal-description .label {
            display: block;
            font-size: 12px;
            color: #6c7293;
            margin-bottom: 4px;
            font-weight: 500;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 20px 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e0e0e0, transparent);
        }
        .divider span {
            color: #b0b0c0;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }

        /* Error box */
        .error-box {
            background: #fff2f2;
            border: 1px solid #ffc0c0;
            color: #c0392b;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 16px;
            display: none;
            font-size: 13px;
            font-weight: 500;
            text-align: center;
            animation: shake 0.4s ease;
        }
        .error-box.show { display: block; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        /* Pay Button */
        .btn-pay {
            width: 100%;
            padding: 18px 24px;
            font-size: 18px;
            font-weight: 800;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #fff;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(40, 167, 69, 0.35);
        }
        .btn-pay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 100%);
            transition: all 0.3s;
        }
        .btn-pay:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(40, 167, 69, 0.45);
        }
        .btn-pay:hover::before {
            opacity: 0;
        }
        .btn-pay:active {
            transform: translateY(-1px);
        }
        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        .btn-pay .spinner {
            display: none;
            width: 22px;
            height: 22px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        .btn-pay.loading .spinner { display: inline-block; }
        .btn-pay.loading .btn-text { opacity: 0.7; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Secure badge */
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 16px;
            color: #8a8aaa;
            font-size: 12px;
            font-weight: 500;
        }
        .secure-badge svg {
            width: 16px;
            height: 16px;
        }

        /* Loading Overlay */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }
        .overlay.show { display: flex; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .overlay-box {
            background: #fff;
            padding: 40px 36px;
            border-radius: 24px;
            text-align: center;
            max-width: 340px;
            box-shadow: 0 32px 64px rgba(0,0,0,0.3);
            animation: popIn 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        .overlay-box .loader {
            display: inline-block;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 4px solid #e8eaff;
            border-top-color: #667eea;
            animation: spin 0.8s linear infinite;
            margin-bottom: 16px;
        }
        .overlay-box h3 {
            color: #1e1e2d;
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 6px;
        }
        .overlay-box p {
            color: #6c7293;
            font-size: 13px;
            margin: 0;
        }

        /* Footer */
        .payment-footer {
            text-align: center;
            margin-top: 16px;
            color: rgba(255,255,255,0.3);
            font-size: 11px;
            font-weight: 400;
            letter-spacing: 0.5px;
        }
        .payment-footer a {
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            transition: color 0.2s;
        }
        .payment-footer a:hover {
            color: rgba(255,255,255,0.8);
        }

        /* Responsive */
        @media (max-width: 480px) {
            body { padding: 12px; }
            .card-body { padding: 20px; }
            .card-header-gradient { padding: 24px; }
            .card-header-gradient .icon-wrap { width: 64px; height: 64px; font-size: 32px; }
            .card-header-gradient h1 { font-size: 18px; }
            .deal-info .amount-value { font-size: 22px; }
            .btn-pay { font-size: 16px; padding: 16px 20px; }
        }
    </style>
</head>
<body>
    <div class="payment-wrapper">
        <!-- Loading Overlay -->
        <div class="overlay" id="loadingOverlay">
            <div class="overlay-box">
                <div class="loader"></div>
                <h3>در حال اتصال به درگاه پرداخت</h3>
                <p>لطفاً شکیبا باشید...</p>
            </div>
        </div>

        <div class="payment-card">
            <!-- Header -->
            <div class="card-header-gradient">
                <div class="icon-wrap"><i class="bi bi-credit-card me-1"></i></div>
                <h1>پرداخت آنلاین</h1>
                <p>با اطمینان و امنیت کامل پرداخت کنید</p>
            </div>

            <!-- Body -->
            <div class="card-body">
                <div class="error-box" id="errorBox"></div>

                <!-- Deal Info -->
                <div class="deal-info">
                    <div class="row">
                        <span class="label"><i class="bi bi-list-task me-1"></i> عنوان پرداخت</span>
                        <span class="value"><?php echo htmlspecialchars($payment->description ?: 'پرداخت آنلاین'); ?></span>
                    </div>
                    <?php if (!empty($deal)): ?>
                    <div class="row">
                        <span class="label"><i class="bi bi-person me-1"></i> مشتری</span>
                        <span class="value"><?php echo htmlspecialchars($deal->contact_name ?? 'نامشخص'); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($deal) && !empty($deal->contact_phone)): ?>
                    <div class="row">
                        <span class="label">📱 شماره تماس</span>
                        <span class="value" dir="ltr" style="direction:ltr;"><?php echo htmlspecialchars($deal->contact_phone); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="row">
                        <span class="label"><i class="bi bi-calendar me-1"></i> تاریخ</span>
                        <span class="value"><?php echo \Core\JDate::displayDate(date('Y-m-d')); ?></span>
                    </div>
                    <div class="row" style="margin-top:4px;">
                        <span class="amount-label"><i class="bi bi-cash me-1"></i> مبلغ</span>
                        <span class="amount-value"><?php echo number_format($payment->amount); ?> تومان</span>
                    </div>
                </div>

                <?php if (!empty($payment->description)): ?>
                <div class="deal-description">
                    <span class="label"><i class="bi bi-journal-text me-1"></i> توضیحات</span>
                    <?php echo nl2br(htmlspecialchars($payment->description)); ?>
                </div>
                <?php endif; ?>

                <div class="divider">
                    <span>درگاه پرداخت امن</span>
                </div>

                <!-- Pay Button -->
                <form id="payForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($payment->public_token); ?>">
                    <button type="submit" class="btn-pay" id="payBtn">
                        <span class="spinner"></span>
                        <span class="btn-text"><i class="bi bi-credit-card me-1"></i> پرداخت وجه</span>
                    </button>
                </form>

                <div class="secure-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    تراکنش امن و رمزگذاری شده توسط درگاه زیبال
                </div>
            </div>
        </div>

        <div class="payment-footer">
            <a href="<?php echo htmlspecialchars($config['url']); ?>"><?php echo htmlspecialchars($config['name'] ?? 'CRM'); ?></a>
        </div>
    </div>

<script>
(function() {
    'use strict';
    
    var form = document.getElementById('payForm');
    var btn = document.getElementById('payBtn');
    var overlay = document.getElementById('loadingOverlay');
    var errorBox = document.getElementById('errorBox');

    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Disable button
        btn.disabled = true;
        btn.classList.add('loading');
        errorBox.classList.remove('show');
        errorBox.style.display = 'none';
        overlay.classList.add('show');

        var token = form.querySelector('input[name="token"]').value;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.getAttribute('action') || '<?php echo $config['url']; ?>/pay/submit', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function() {
            try {
                var data = JSON.parse(xhr.responseText);
                
                if (data.success && data.redirect) {
                    // Redirect to Zibal gateway
                    window.location.href = data.redirect;
                } else {
                    overlay.classList.remove('show');
                    btn.disabled = false;
                    btn.classList.remove('loading');
                    errorBox.textContent = data.message || 'خطا در اتصال به درگاه پرداخت';
                    errorBox.classList.add('show');
                    errorBox.style.display = 'block';
                }
            } catch(e) {
                overlay.classList.remove('show');
                btn.disabled = false;
                btn.classList.remove('loading');
                errorBox.textContent = 'خطا در پردازش درخواست';
                errorBox.classList.add('show');
                errorBox.style.display = 'block';
            }
        };

        xhr.onerror = function() {
            overlay.classList.remove('show');
            btn.disabled = false;
            btn.classList.remove('loading');
            errorBox.textContent = 'خطا در برقراری ارتباط با سرور';
            errorBox.classList.add('show');
            errorBox.style.display = 'block';
        };

        xhr.send('token=' + encodeURIComponent(token));
    });
})();
</script>
</body>
</html>