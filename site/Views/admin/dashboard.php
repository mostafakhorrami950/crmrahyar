<div class="stat-grid">
    <div class="stat-card">
        <div class="num" style="color: #4f46e5;"><?php echo $stats['hotels'] ?? 0; ?></div>
        <div class="label">🏨 هتل فعال</div>
    </div>
    <div class="stat-card">
        <div class="num" style="color: #f59e0b;"><?php echo $stats['bookings'] ?? 0; ?></div>
        <div class="label">📋 کل رزروها</div>
    </div>
    <div class="stat-card">
        <div class="num" style="color: #059669;"><?php echo $stats['paid'] ?? 0; ?></div>
        <div class="label">💰 رزرو پرداخت شده</div>
    </div>
    <div class="stat-card">
        <div class="num" style="color: #ec4899;"><?php echo $stats['posts'] ?? 0; ?></div>
        <div class="label">📝 مقاله منتشر شده</div>
    </div>
    <div class="stat-card">
        <div class="num" style="color: #8b5cf6;"><?php echo $stats['cities'] ?? 0; ?></div>
        <div class="label">🏙️ شهر</div>
    </div>
    <div class="stat-card">
        <div class="num" style="color: #0ea5e9;"><?php echo $stats['rooms'] ?? 0; ?></div>
        <div class="label">🛏️ اتاق</div>
    </div>
</div>

<!-- Quick Links -->
<div class="card">
    <h3 style="margin-bottom: 16px; font-weight: 800;">🚀 دسترسی سریع</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px;">
        <a href="/admin/hotels" class="btn btn-primary" style="text-align: center;">🏨 مدیریت هتل‌ها</a>
        <a href="/admin/blog" class="btn btn-primary" style="text-align: center;">📝 مدیریت بلاگ</a>
        <a href="/admin/pages" class="btn btn-primary" style="text-align: center;">📄 مدیریت صفحات</a>
        <a href="/admin/settings" class="btn btn-secondary" style="text-align: center;">⚙️ تنظیمات</a>
        <a href="/admin/seo" class="btn btn-secondary" style="text-align: center;">🔍 سئو</a>
        <a href="/crm/hotel-rates" class="btn btn-success" style="text-align: center;">📊 نرخ‌نامه CRM</a>
    </div>
</div>