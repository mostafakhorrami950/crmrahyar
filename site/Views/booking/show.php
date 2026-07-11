<?php
$currency = new \Shared\Services\CurrencyService(\Shared\Core\Config::getInstance());
ob_start();
?>
<div class="container" style="max-width: 700px; margin: 0 auto; padding: 40px 20px;">
    <div style="background: #fff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">

        <!-- Timer -->
        <div id="timer-bar" style="background: linear-gradient(135deg, #f59e0b, #ef4444); color: #fff; padding: 12px 20px; border-radius: 10px; text-align: center; margin-bottom: 20px;">
            ⏰ <strong>زمان باقیمانده:</strong> <span id="timer">--:--</span>
            <div style="font-size: 11px; margin-top: 4px; opacity: 0.8;">رزرو شما موقت است. لطفاً قبل از اتمام زمان، پرداخت را انجام دهید.</div>
        </div>

        <h1 style="font-size: 22px; font-weight: 900; margin-bottom: 20px;">رزرو شما</h1>
        <div style="font-size: 12px; color: #64748b; margin-bottom: 16px;">کد رزرو: <strong style="color: #1e293b;"><?php echo htmlspecialchars($reservation->booking_code); ?></strong></div>

        <!-- Hotel & Room Info -->
        <div style="display: flex; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <div style="font-size: 18px; font-weight: 800;"><?php echo htmlspecialchars($hotel->hotel_name ?? ''); ?></div>
                <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><?php echo htmlspecialchars($room->room_type_key ?? ''); ?></div>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                    👥 <?php echo $reservation->guests_adults; ?> نفر ·
                    📅 ورود: <?php echo $reservation->checkin_date; ?> · خروج: <?php echo $reservation->checkout_date; ?>
                    · <?php echo $reservation->nights; ?> شب
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div style="background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                <span style="color: #64748b;">قیمت هر شب</span>
                <span style="font-weight: 700;"><?php echo $currency->format($pricing->price_per_night); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                <span style="color: #64748b;">تعداد شب</span>
                <span style="font-weight: 700;"><?php echo $reservation->nights; ?> شب</span>
            </div>
            <?php if ($pricing->markup_amount > 0): ?>
            <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                <span style="color: #64748b;">هزینه خدمات</span>
                <span style="font-weight: 700;"><?php echo $currency->format($pricing->markup_amount); ?></span>
            </div>
            <?php endif; ?>
            <div style="border-top: 2px solid #e2e8f0; margin-top: 8px; padding-top: 10px; display: flex; justify-content: space-between;">
                <span style="font-weight: 900; font-size: 16px;">مبلغ قابل پرداخت</span>
                <span style="font-weight: 900; font-size: 20px; color: #059669;"><?php echo $currency->format($reservation->final_price); ?></span>
            </div>
        </div>

        <!-- Pay Button -->
        <a href="/booking/<?php echo htmlspecialchars($reservation->reservation_token); ?>/pay" id="pay-btn" style="display: block; text-align: center; background: linear-gradient(135deg, #059669, #10b981); color: #fff; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 16px; text-decoration: none; transition: 0.2s;">
            💳 پرداخت و تأیید رزرو
        </a>
    </div>
</div>

<script>
var expiresAt = new Date('<?php echo $reservation->expires_at; ?>').getTime();
function updateTimer() {
    var now = new Date().getTime();
    var diff = Math.max(0, Math.floor((expiresAt - now) / 1000));
    var min = Math.floor(diff / 60);
    var sec = diff % 60;
    document.getElementById('timer').textContent = String(min).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
    if (diff <= 0) {
        document.getElementById('timer-bar').style.background = '#dc2626';
        document.getElementById('timer').textContent = 'منقضی شد';
        document.getElementById('pay-btn').style.opacity = '0.5';
        document.getElementById('pay-btn').style.pointerEvents = 'none';
    }
}
updateTimer();
setInterval(updateTimer, 1000);

// Heartbeat every 15 seconds
setInterval(function() {
    fetch('/api/v1/reservations/<?php echo $reservation->reservation_token; ?>/heartbeat')
        .then(r => r.json())
        .then(data => {
            if (!data.valid) {
                document.getElementById('timer-bar').style.background = '#dc2626';
                document.getElementById('timer').textContent = 'منقضی شد';
            }
        }).catch(function(){});
}, 15000);
</script>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'رزرو', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>