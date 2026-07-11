<?php
$company = \Shared\Core\Config::getInstance()->get('invoice_company_name', '');
$currency = new \Shared\Services\CurrencyService(\Shared\Core\Config::getInstance());
ob_start();
?>

<!-- JSON-LD Schema -->
<?php if (!empty($schema)): ?>
<script type="application/ld+json"><?php echo json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<?php endif; ?>

<div class="container" style="padding-top: 30px; padding-bottom: 40px;">

    <!-- Breadcrumb -->
    <div style="font-size: 12px; color: #94a3b8; margin-bottom: 16px;">
        <a href="/" style="color: #4f46e5;">خانه</a>
        <span style="margin: 0 6px;">›</span>
        <a href="/hotels" style="color: #4f46e5;">هتل‌ها</a>
        <span style="margin: 0 6px;">›</span>
        <span><?php echo htmlspecialchars($hotel->hotel_name ?? $hotel->slug); ?></span>
    </div>

    <!-- Hotel Header -->
    <div style="display: flex; gap: 24px; margin-bottom: 30px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
            <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 6px;">
                <?php echo htmlspecialchars($hotel->hotel_name ?? $hotel->slug); ?>
            </h1>
            <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 10px; flex-wrap: wrap;">
                <?php if (!empty($hotel->star_rating)): ?>
                <span style="color: #f59e0b; font-size: 16px;"><?php echo str_repeat('★', $hotel->star_rating); echo str_repeat('☆', 5 - $hotel->star_rating); ?></span>
                <?php endif; ?>
                <?php if (!empty($hotel->city_name)): ?>
                <span style="font-size: 13px; color: #64748b;">📍 <?php echo htmlspecialchars($hotel->city_name); ?></span>
                <?php endif; ?>
                <?php if (!empty($hotel->avg_rating)): ?>
                <span style="font-size: 13px; color: #f59e0b;">⭐ <?php echo number_format($hotel->avg_rating, 1); ?> (<?php echo $hotel->review_count; ?> نظر)</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($hotel->address)): ?>
            <p style="font-size: 13px; color: #64748b;">📍 <?php echo htmlspecialchars($hotel->address); ?></p>
            <?php endif; ?>
            <?php if (!empty($hotel->distance_to_haram_km)): ?>
            <p style="font-size: 13px; color: #4f46e5; font-weight: 600;">🕋 فاصله تا حرم: <?php echo $hotel->distance_to_haram_km; ?> کیلومتر</p>
            <?php endif; ?>

            <!-- Tags -->
            <div style="display: flex; gap: 6px; margin-top: 12px; flex-wrap: wrap;">
                <?php if ($hotel->family_friendly): ?><span style="background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">👨‍👩‍👧‍👦 خانوادگی</span><?php endif; ?>
                <?php if ($hotel->couple_friendly): ?><span style="background: #fce7f3; color: #be185d; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">💑 زوجین</span><?php endif; ?>
                <?php if ($hotel->luxury): ?><span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">✨ لوکس</span><?php endif; ?>
                <?php if ($hotel->budget_friendly): ?><span style="background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">💰 اقتصادی</span><?php endif; ?>
            </div>
        </div>
        <div style="width: 300px; height: 200px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 60px;">
            🏨
        </div>
    </div>

    <!-- Description -->
    <?php if (!empty($hotel->description_long)): ?>
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 10px;">درباره هتل</h2>
        <p style="font-size: 14px; color: #475569; line-height: 1.8;"><?php echo nl2br(htmlspecialchars($hotel->description_long)); ?></p>
    </div>
    <?php endif; ?>

    <!-- Rooms -->
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">🛏️ اتاق‌ها و قیمت‌ها</h2>
        <?php if (!empty($roomsWithPricing)): ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($roomsWithPricing as $rp): ?>
            <?php $room = $rp['room']; $pricing = $rp['pricing']; ?>
            <div style="background: #fff; border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; transition: 0.3s;" onmouseover="this.style.borderColor='#c7d2fe'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div>
                    <div style="font-size: 15px; font-weight: 800;"><?php echo htmlspecialchars($room->room_type_key); ?></div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                        <?php if ($room->capacity_adults): ?>👥 <?php echo $room->capacity_adults; ?> نفر<?php endif; ?>
                        <?php if ($room->bed_type): ?> · 🛏️ <?php echo htmlspecialchars($room->bed_type); ?><?php endif; ?>
                        <?php if ($room->size_sqm): ?> · 📐 <?php echo $room->size_sqm; ?> متر<?php endif; ?>
                    </div>
                </div>
                <div style="text-align: left;">
                    <?php if ($pricing->total_price > 0): ?>
                    <div style="font-size: 20px; font-weight: 900; color: #1e293b;"><?php echo $currency->format($pricing->total_price); ?></div>
                    <div style="font-size: 11px; color: #94a3b8;">برای <?php echo $pricing->nights; ?> شب · هر شب <?php echo $currency->format($pricing->price_per_night); ?></div>
                    <?php else: ?>
                    <div style="font-size: 14px; color: #94a3b8;">قیمت موجود نیست</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color: #94a3b8; text-align: center; padding: 30px;">اتاقی ثبت نشده</p>
        <?php endif; ?>
    </div>

    <!-- Reviews -->
    <?php if (!empty($reviews)): ?>
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">💬 نظرات مهمانان</h2>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($reviews as $review): ?>
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-weight: 700; font-size: 13px;"><?php echo htmlspecialchars($review->user_name ?? 'کاربر'); ?></span>
                    <span style="color: #f59e0b;"><?php echo str_repeat('★', $review->rating); echo str_repeat('☆', 5 - $review->rating); ?></span>
                </div>
                <?php if (!empty($review->title)): ?><div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;"><?php echo htmlspecialchars($review->title); ?></div><?php endif; ?>
                <p style="font-size: 12px; color: #475569; margin: 0;"><?php echo nl2br(htmlspecialchars($review->comment ?? '')); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Related Hotels -->
    <?php if (!empty($related)): ?>
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">🏨 هتل‌های مشابه</h2>
        <div class="hotel-grid">
            <?php foreach ($related as $rel): ?>
            <a href="/hotel/<?php echo htmlspecialchars($rel->slug); ?>" class="hotel-card">
                <div class="hotel-img">🏨</div>
                <div class="hotel-body">
                    <h3><?php echo htmlspecialchars($rel->hotel_name ?? $rel->slug); ?></h3>
                    <?php if (!empty($rel->star_rating)): ?>
                    <div class="stars"><?php echo str_repeat('★', $rel->star_rating); echo str_repeat('☆', 5 - $rel->star_rating); ?></div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'هتل', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>