<?php
$company = \Shared\Core\Config::getInstance()->get('invoice_company_name', 'آژانس مسافرتی');
$companyName = \Shared\Core\Config::getInstance()->get('site_title', 'رزرو هتل');

// Start output buffering for layout
ob_start();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>🏨 بهترین هتل‌ها را رزرو کنید</h1>
        <p>قیمت مناسب، رزرو آسان، پشتیبانی ۲۴ ساعته</p>

        <div class="search-box">
            <select id="searchCity">
                <option value="">انتخاب شهر</option>
                <option value="mashhad" selected>مشهد مقدس</option>
            </select>
            <input type="date" id="searchCheckin" placeholder="تاریخ ورود">
            <input type="date" id="searchCheckout" placeholder="تاریخ خروج">
            <select id="searchGuests">
                <option value="1">۱ نفر</option>
                <option value="2" selected>۲ نفر</option>
                <option value="3">۳ نفر</option>
                <option value="4">۴ نفر</option>
                <option value="5">۵+ نفر</option>
            </select>
            <button onclick="doSearch()"><i class="bi bi-search"></i> جستجو</button>
        </div>
    </div>
</section>

<!-- Featured Hotels -->
<div class="container">
    <h2 class="section-title">🏨 هتل‌های ویژه</h2>
    <div class="hotel-grid">
        <?php if (!empty($featuredHotels)): ?>
            <?php foreach ($featuredHotels as $hotel): ?>
            <a href="/hotel/<?php echo htmlspecialchars($hotel->slug); ?>" class="hotel-card">
                <div class="hotel-img">🏨</div>
                <div class="hotel-body">
                    <h3><?php echo htmlspecialchars($hotel->hotel_name ?? $hotel->slug); ?></h3>
                    <div class="city">📍 <?php echo htmlspecialchars($hotel->city_name ?? ''); ?></div>
                    <?php if (!empty($hotel->star_rating)): ?>
                    <div class="stars"><?php echo str_repeat('★', $hotel->star_rating); echo str_repeat('☆', 5 - $hotel->star_rating); ?></div>
                    <?php endif; ?>
                    <div class="hotel-tags">
                        <?php if ($hotel->family_friendly): ?><span>👨‍👩‍👧‍👦 خانوادگی</span><?php endif; ?>
                        <?php if ($hotel->couple_friendly): ?><span>💑 زوجین</span><?php endif; ?>
                        <?php if ($hotel->luxury): ?><span>✨ لوکس</span><?php endif; ?>
                        <?php if ($hotel->budget_friendly): ?><span>💰 اقتصادی</span><?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px;">هتلی یافت نشد.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function doSearch() {
    var city = document.getElementById('searchCity').value;
    var checkin = document.getElementById('searchCheckin').value;
    var checkout = document.getElementById('searchCheckout').value;
    var guests = document.getElementById('searchGuests').value;
    var url = '/search?';
    if (city) url += 'city=' + encodeURIComponent(city) + '&';
    if (checkin) url += 'checkin=' + checkin + '&';
    if (checkout) url += 'checkout=' + checkout + '&';
    if (guests) url += 'guests=' + guests;
    window.location.href = url;
}
// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    var today = new Date();
    var tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    var dayAfter = new Date(today);
    dayAfter.setDate(dayAfter.getDate() + 2);
    document.getElementById('searchCheckin').value = tomorrow.toISOString().split('T')[0];
    document.getElementById('searchCheckout').value = dayAfter.toISOString().split('T')[0];
});
</script>

<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => $companyName, 'description' => 'رزرو آنلاین هتل'];
require __DIR__ . '/../layouts/main.php';
?>