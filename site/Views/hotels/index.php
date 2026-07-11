<?php
$company = \Shared\Core\Config::getInstance()->get('invoice_company_name', '');
ob_start();
?>

<div class="container" style="padding-top: 30px; padding-bottom: 40px;">
    <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 20px;">🏨 هتل‌ها</h1>

    <!-- Filters -->
    <div style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap;">
        <select onchange="filterByCity(this.value)" style="padding: 8px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 13px;">
            <option value="">همه شهرها</option>
            <?php foreach ($cities as $city): ?>
            <option value="<?php echo $city->id; ?>" <?php echo (($filters['city_id'] ?? '') == $city->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($city->name); ?></option>
            <?php endforeach; ?>
        </select>
        <select onchange="sortHotels(this.value)" style="padding: 8px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 13px;">
            <option value="featured" <?php echo (($filters['sort'] ?? '') === 'featured') ? 'selected' : ''; ?>>ویژه</option>
            <option value="name" <?php echo (($filters['sort'] ?? '') === 'name') ? 'selected' : ''; ?>>نام</option>
            <option value="rating" <?php echo (($filters['sort'] ?? '') === 'rating') ? 'selected' : ''; ?>>ستاره</option>
        </select>
    </div>

    <!-- Hotel Grid -->
    <div class="hotel-grid">
        <?php if (!empty($hotels)): ?>
            <?php foreach ($hotels as $hotel): ?>
            <a href="/hotel/<?php echo htmlspecialchars($hotel->slug); ?>" class="hotel-card">
                <div class="hotel-img">🏨</div>
                <div class="hotel-body">
                    <h3><?php echo htmlspecialchars($hotel->hotel_name ?? $hotel->slug); ?></h3>
                    <div class="city">📍 <?php echo htmlspecialchars($hotel->address ?? ''); ?></div>
                    <?php if (!empty($hotel->star_rating)): ?>
                    <div class="stars"><?php echo str_repeat('★', $hotel->star_rating); echo str_repeat('☆', 5 - $hotel->star_rating); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($hotel->description_short)): ?>
                    <p style="font-size: 12px; color: #64748b; margin-top: 6px; line-height: 1.4;"><?php echo mb_substr($hotel->description_short, 0, 100); ?>...</p>
                    <?php endif; ?>
                    <div class="hotel-tags" style="margin-top: 8px;">
                        <?php if ($hotel->family_friendly): ?><span>👨‍👩‍👧‍👦 خانوادگی</span><?php endif; ?>
                        <?php if ($hotel->couple_friendly): ?><span>💑 زوجین</span><?php endif; ?>
                        <?php if ($hotel->luxury): ?><span>✨ لوکس</span><?php endif; ?>
                        <?php if ($hotel->budget_friendly): ?><span>💰 اقتصادی</span><?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 60px;">هتلی یافت نشد.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function filterByCity(cityId) {
    var url = new URL(window.location);
    if (cityId) url.searchParams.set('city_id', cityId);
    else url.searchParams.delete('city_id');
    window.location.href = url.toString();
}
function sortHotels(sort) {
    var url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location.href = url.toString();
}
</script>

<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'هتل‌ها', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>