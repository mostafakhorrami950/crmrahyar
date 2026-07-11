<?php
$currency = new \Shared\Services\CurrencyService(\Shared\Core\Config::getInstance());
ob_start();
?>

<div class="container" style="padding-top: 30px; padding-bottom: 40px;">
    <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 20px;">
        🔍 <?php echo !empty($filters['q']) ? 'نتایج جستجو: ' . htmlspecialchars($filters['q']) : 'جستجوی هتل'; ?>
    </h1>

    <!-- Search Form -->
    <form method="get" style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; background: #fff; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>" placeholder="نام هتل، شهر، منطقه..." style="flex: 1; min-width: 200px; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 13px;">
        <select name="city" style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 13px;">
            <option value="">همه شهرها</option>
            <?php foreach ($cities as $c): ?>
            <option value="<?php echo $c->slug; ?>" <?php echo (($filters['city'] ?? '') === $c->slug) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->name); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="checkin" value="<?php echo htmlspecialchars($filters['checkin'] ?? ''); ?>" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 13px;">
        <input type="date" name="checkout" value="<?php echo htmlspecialchars($filters['checkout'] ?? ''); ?>" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 13px;">
        <button type="submit" style="background: #4f46e5; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font: inherit; font-weight: 700; cursor: pointer;">جستجو</button>
    </form>

    <!-- Results -->
    <?php if (!empty($results)): ?>
    <div style="font-size: 13px; color: #64748b; margin-bottom: 16px;"><?php echo $pagination->total ?? 0; ?> نتیجه یافت شد</div>
    <div class="hotel-grid">
        <?php foreach ($results as $r): ?>
        <?php if ($r->entity_type === 'hotel'): ?>
        <a href="/hotel/<?php echo htmlspecialchars($r->title); ?>" class="hotel-card">
            <div class="hotel-img">🏨</div>
            <div class="hotel-body">
                <h3><?php echo htmlspecialchars($r->title); ?></h3>
                <?php if (!empty($r->star_rating)): ?>
                <div class="stars"><?php echo str_repeat('★', $r->star_rating); echo str_repeat('☆', 5 - $r->star_rating); ?></div>
                <?php endif; ?>
                <?php if (!empty($r->min_price)): ?>
                <div style="margin-top: 8px;">
                    <span style="font-size: 18px; font-weight: 900; color: #059669;"><?php echo $currency->format($r->min_price); ?></span>
                    <span style="font-size: 11px; color: #94a3b8;"> / شب</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($r->description)): ?>
                <p style="font-size: 12px; color: #64748b; margin-top: 6px;"><?php echo mb_substr(strip_tags($r->description), 0, 80); ?>...</p>
                <?php endif; ?>
            </div>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if (($pagination->total_pages ?? 1) > 1): ?>
    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 24px;">
        <?php for ($p = 1; $p <= $pagination->total_pages; $p++): ?>
        <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $p])); ?>" style="padding: 8px 14px; border-radius: 8px; font-weight: 700; font-size: 13px; <?php echo $p == $pagination->page ? 'background: #4f46e5; color: #fff;' : 'background: #fff; color: #475569; border: 1px solid #e2e8f0;'; ?>"><?php echo $p; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div style="text-align: center; padding: 60px;">
        <div style="font-size: 60px; margin-bottom: 16px;">🔍</div>
        <p style="font-size: 16px; color: #94a3b8;">نتیجه‌ای یافت نشد</p>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'جستجو', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>