<?php
ob_start();
$step = $step ?? 'phone';
?>
<div class="container" style="max-width: 420px; margin: 0 auto; padding: 60px 20px;">
    <div style="background: #fff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
        <h1 style="font-size: 24px; font-weight: 900; text-align: center; margin-bottom: 24px;">
            <?php echo $step === 'verify' ? 'تایید کد ورود' : 'ورود به حساب'; ?>
        </h1>

        <?php if (!empty($error)): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($step === 'verify'): ?>
        <div style="text-align: center; margin-bottom: 16px;">
            <p style="color: #64748b; font-size: 13px;">کد ورود به شماره <strong><?php echo htmlspecialchars($_SESSION['login_phone'] ?? ''); ?></strong> ارسال شد.</p>
        </div>
        <form method="POST" action="/login">
            <input type="hidden" name="step" value="verify">
            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">کد تایید</label>
                <input type="text" name="code" required maxlength="5" dir="ltr" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 24px; text-align: center; letter-spacing: 8px; font-weight: 900;" placeholder="-----">
            </div>
            <button type="submit" style="width: 100%; padding: 12px; background: #059669; color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 800; cursor: pointer;">✅ ورود</button>
        </form>
        <div style="text-align: center; margin-top: 16px; font-size: 12px; color: #94a3b8;">
            کد دریافت نکردید؟ <a href="/login" style="color: #4f46e5; font-weight: 700;">ارسال مجدد</a>
        </div>

        <?php else: ?>
        <form method="POST" action="/login">
            <input type="hidden" name="step" value="send_code">
            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">شماره موبایل</label>
                <input type="text" name="phone" required dir="ltr" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px;" placeholder="0912XXXXXXX" maxlength="11">
            </div>
            <button type="submit" style="width: 100%; padding: 12px; background: #4f46e5; color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 800; cursor: pointer;">📱 ارسال کد ورود</button>
        </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 16px; font-size: 13px; color: #64748b;">
            حساب ندارید؟ <a href="/register" style="color: #4f46e5; font-weight: 700;">ثبت‌نام</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'ورود', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>