<?php
ob_start();
?>
<div class="container" style="max-width: 900px; margin: 0 auto; padding: 30px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 900;">⚙️ پنل مدیریت سایت</h1>
        <a href="/admin/database" style="background: #1e293b; color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none;">🗄️ تعمیرات دیتابیس</a>
    </div>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px;">
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="font-size: 32px; font-weight: 900; color: #4f46e5;"><?php echo $stats['hotels'] ?? 0; ?></div>
            <div style="font-size: 12px; color: #64748b; font-weight: 600;">هتل فعال</div>
        </div>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="font-size: 32px; font-weight: 900; color: #f59e0b;"><?php echo $stats['bookings'] ?? 0; ?></div>
            <div style="font-size: 12px; color: #64748b; font-weight: 600;">کل رزروها</div>
        </div>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="font-size: 32px; font-weight: 900; color: #059669;"><?php echo $stats['paid'] ?? 0; ?></div>
            <div style="font-size: 12px; color: #64748b; font-weight: 600;">رزرو پرداخت شده</div>
        </div>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="font-size: 32px; font-weight: 900; color: #ec4899;"><?php echo $stats['posts'] ?? 0; ?></div>
            <div style="font-size: 12px; color: #64748b; font-weight: 600;">مقاله منتشر شده</div>
        </div>
    </div>

    <!-- Quick Links -->
    <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">دسترسی سریع</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
        <a href="/admin/database" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-decoration: none; color: #1e293b; transition: 0.2s;" onmouseover="this.style.borderColor='#4f46e5'" onmouseout="this.style.borderColor='#e2e8f0'">
            <div style="font-size: 24px; margin-bottom: 6px;">🗄️</div>
            <div style="font-weight: 700; font-size: 13px;">تعمیرات دیتابیس</div>
            <div style="font-size: 11px; color: #64748b;">مایگریشن و تعمیر جداول</div>
        </a>
        <a href="<?php echo $config['url'] ?? ''; ?>/crm" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-decoration: none; color: #1e293b; transition: 0.2s;" onmouseover="this.style.borderColor='#4f46e5'" onmouseout="this.style.borderColor='#e2e8f0'">
            <div style="font-size: 24px; margin-bottom: 6px;">💼</div>
            <div style="font-weight: 700; font-size: 13px;">پنل CRM</div>
            <div style="font-size: 11px; color: #64748b;">مدیریت معاملات و فاکتورها</div>
        </a>
        <a href="/" target="_blank" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-decoration: none; color: #1e293b; transition: 0.2s;" onmouseover="this.style.borderColor='#4f46e5'" onmouseout="this.style.borderColor='#e2e8f0'">
            <div style="font-size: 24px; margin-bottom: 6px;">🌐</div>
            <div style="font-weight: 700; font-size: 13px;">مشاهده سایت</div>
            <div style="font-size: 11px; color: #64748b;">صفحه اصلی وبسایت</div>
        </a>
        <a href="/sitemap.xml" target="_blank" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-decoration: none; color: #1e293b; transition: 0.2s;" onmouseover="this.style.borderColor='#4f46e5'" onmouseout="this.style.borderColor='#e2e8f0'">
            <div style="font-size: 24px; margin-bottom: 6px;">🗺️</div>
            <div style="font-weight: 700; font-size: 13px;">نقشه سایت</div>
            <div style="font-size: 11px; color: #64748b;">XML Sitemap</div>
        </a>
    </div>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'پنل مدیریت', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>