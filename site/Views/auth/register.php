<?php
ob_start();
?>
<div class="container" style="max-width: 420px; margin: 0 auto; padding: 60px 20px;">
    <div style="background: #fff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
        <h1 style="font-size: 24px; font-weight: 900; text-align: center; margin-bottom: 24px;">ایجاد حساب کاربری</h1>

        <?php if (!empty($error)): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="/register">
            <div style="margin-bottom: 12px;">
                <label style="font-size: 13px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">نام و نام خانوادگی</label>
                <input type="text" name="full_name" required style="width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 14px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 13px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">ایمیل</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 14px;" placeholder="email@example.com">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 13px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">شماره موبایل</label>
                <input type="text" name="phone" style="width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 14px;" placeholder="0912XXXXXXX">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 13px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">رمز عبور</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 14px;" placeholder="حداقل ۶ کاراکتر">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">تکرار رمز عبور</label>
                <input type="password" name="password_confirm" required style="width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 14px;">
            </div>
            <button type="submit" style="width: 100%; padding: 12px; background: #4f46e5; color: #fff; border: none; border-radius: 10px; font: inherit; font-size: 15px; font-weight: 800; cursor: pointer;">ثبت‌نام</button>
        </form>

        <div style="text-align: center; margin-top: 16px; font-size: 13px; color: #64748b;">
            قبلاً ثبت‌نام کرده‌اید؟ <a href="/login" style="color: #4f46e5; font-weight: 700;">ورود</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'ثبت‌نام', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>