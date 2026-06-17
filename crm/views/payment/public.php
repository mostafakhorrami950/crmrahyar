<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت <?php echo htmlspecialchars($payment->description ?? 'آنلاین'); ?></title>
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
        .payment-card {
            background: #fff;
            border-radius: 24px;
            padding: 40px 32px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .payment-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .payment-header .icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        .payment-header h1 {
            font-size: 20px;
            color: #1a1a2e;
            margin: 0;
        }
        .payment-header p {
            color: #6c757d;
            font-size: 13px;
            margin: 6px 0 0;
        }
        .deal-summary {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .deal-summary .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .deal-summary .row:last-child {
            border-bottom: none;
        }
        .deal-summary .label {
            color: #6c757d;
            font-size: 13px;
        }
        .deal-summary .value {
            font-weight: bold;
            color: #1a1a2e;
            font-size: 14px;
        }
        .deal-summary .amount {
            font-size: 24px;
            color: #28a745;
        }
        .payment-footer {
            margin-top: 24px;
        }
        .btn-pay {
            width: 100%;
            padding: 16px;
            font-size: 18px;
            font-weight: bold;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }
        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .btn-pay .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        .btn-pay.loading .spinner {
            display: inline-block;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .secure-badge {
            text-align: center;
            margin-top: 16px;
            color: #6c757d;
            font-size: 12px;
        }
        .secure-badge i {
            font-style: normal;
        }
        .error-box {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            display: none;
            font-size: 13px;
            text-align: center;
        }
        .error-box.show {
            display: block;
        }
        .loading-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .loading-overlay.show {
            display: flex;
        }
        .loading-box {
            background: #fff;
            padding: 32px;
            border-radius: 16px;
            text-align: center;
        }
        .loading-box .lds-ring {
            display: inline-block;
            width: 48px;
            height: 48px;
        }
        .loading-box .lds-ring div {
            box-sizing: border-box;
            display: block;
            position: absolute;
            width: 40px;
            height: 40px;
            margin: 4px;
            border: 4px solid #667eea;
            border-radius: 50%;
            animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            border-color: #667eea transparent transparent transparent;
        }
        .loading-box p {
            margin-top: 16px;
            color: #1a1a2e;
            font-weight: bold;
        }
        @keyframes lds-ring {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-box">
            <div class="lds-ring"><div></div></div>
            <p>در حال اتصال به درگاه پرداخت...</p>
        </div>
    </div>

    <div class="payment-card">
        <div class="payment-header">
            <div class="icon">💳</div>
            <h1>پرداخت آنلاین</h1>
            <p>اطلاعات پرداخت خود را بررسی کنید</p>
        </div>

        <div class="error-box" id="errorBox"></div>

        <div class="deal-summary">
            <div class="row">
                <span class="label">📋 عنوان معامله</span>
                <span class="value"><?php echo htmlspecialchars($payment->description ?: 'پرداخت'); ?></span>
            </div>
            <?php if (!empty($deal)): ?>
            <div class="row">
                <span class="label">👤 مشتری</span>
                <span class="value"><?php echo htmlspecialchars($deal->contact_name ?? 'نامشخص'); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($deal) && !empty($deal->contact_phone)): ?>
            <div class="row">
                <span class="label">📱 موبایل</span>
                <span class="value" dir="ltr" style="direction:ltr;text-align:left;"><?php echo htmlspecialchars($deal->contact_phone); ?></span>
            </div>
            <?php endif; ?>
            <div class="row" style="border-bottom:none;">
                <span class="label">💰 مبلغ پرداخت</span>
                <span class="value amount"><?php echo number_format($payment->amount); ?> تومان</span>
            </div>
        </div>

        <div class="payment-footer">
            <form id="payForm" method="POST" action="<?php echo $config['url']; ?>/pay/submit">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($payment->public_token); ?>">
                <button type="submit" class="btn-pay" id="payBtn">
                    <span class="spinner"></span>
                    <span class="btn-text">💳 پرداخت وجه</span>
                </button>
            </form>
            <div class="secure-badge">
                <i>🔒 این تراکنش امن بوده و توسط درگاه زیبال پردازش می‌شود</i>
            </div>
        </div>
    </div>

    <div class="powered">
        <a href="<?php echo $config['url']; ?>">CRM آژانس مسافرتی</a>
    </div>

    <script src="<?php echo $config['url']; ?>/assets/js/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#payForm').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#payBtn');
            var $overlay = $('#loadingOverlay');
            var $errorBox = $('#errorBox');

            $btn.prop('disabled', true).addClass('loading');
            $errorBox.removeClass('show').hide();
            $overlay.addClass('show');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        $overlay.removeClass('show');
                        $btn.prop('disabled', false).removeClass('loading');
                        $errorBox.text(response.message || 'خطا در اتصال به درگاه پرداخت').addClass('show');
                    }
                },
                error: function() {
                    $overlay.removeClass('show');
                    $btn.prop('disabled', false).removeClass('loading');
                    $errorBox.text('خطا در برقراری ارتباط با سرور').addClass('show');
                }
            });
        });
    });
    </script>
</body>
</html>