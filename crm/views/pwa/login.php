<?php
$config = $GLOBALS['app_config'];
$error = $error ?? '';

ob_start();
?>
<div class="pwa-fade" style="min-height:100vh;min-height:100dvh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;">
    <!-- Logo -->
    <div style="text-align:center;margin-bottom:32px;">
        <div style="width:80px;height:80px;margin:0 auto 16px;background:linear-gradient(135deg,#4361ee,#7209b7);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:36px;box-shadow:0 8px 32px rgba(67,97,238,0.3);">
            ✈️
        </div>
        <h4 style="font-weight:800;margin-bottom:4px;">علاءالدین سفیر اسمان</h4>
        <p style="font-size:12px;color:var(--pwa-muted);">اپلیکیشن مدیریت آژانس مسافرتی</p>
    </div>

    <!-- Login Form -->
    <div class="pwa-card" style="width:100%;max-width:380px;">
        <?php if ($error): ?>
        <div class="pwa-alert pwa-alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i><?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo $config['url']; ?>/pwa/login">
            <div style="margin-bottom:16px;">
                <label class="pwa-label"><i class="bi bi-person me-1"></i>نام کاربری</label>
                <input type="text" name="username" class="pwa-input" placeholder="نام کاربری خود را وارد کنید" required autofocus autocomplete="username">
            </div>
            <div style="margin-bottom:20px;">
                <label class="pwa-label"><i class="bi bi-lock me-1"></i>رمز عبور</label>
                <input type="password" name="password" class="pwa-input" placeholder="رمز عبور خود را وارد کنید" required autocomplete="current-password">
            </div>
            <button type="submit" class="pwa-btn pwa-btn-primary pwa-btn-block">
                <i class="bi bi-box-arrow-in-left"></i> ورود
            </button>
        </form>
    </div>

    <p style="font-size:11px;color:var(--pwa-muted);margin-top:24px;text-align:center;">
        <a href="<?php echo $config['url']; ?>" style="color:var(--pwa-primary);text-decoration:none;">ورود به نسخه وب</a>
    </p>
</div>
<?php
$pwaContent = ob_get_clean();
$pageTitle = 'ورود';
include __DIR__ . '/layout.php';