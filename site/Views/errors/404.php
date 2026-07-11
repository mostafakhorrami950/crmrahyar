<?php
ob_start();
?>
<div class="container" style="text-align: center; padding: 80px 20px;">
    <div style="font-size: 80px; margin-bottom: 20px;">🔍</div>
    <h1 style="font-size: 36px; font-weight: 900; color: #1e293b; margin-bottom: 12px;">صفحه یافت نشد</h1>
    <p style="font-size: 16px; color: #64748b; margin-bottom: 30px;">متأسفانه صفحه مورد نظر شما وجود ندارد یا حذف شده است.</p>
    <a href="/" style="display: inline-flex; align-items: center; gap: 8px; background: #4f46e5; color: #fff; padding: 12px 28px; border-radius: 10px; font-weight: 700; font-size: 14px; transition: 0.2s;">بازگشت به صفحه اصلی</a>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'صفحه یافت نشد', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>