<?php
ob_start();
?>
<div class="container" style="max-width: 500px; margin: 0 auto; padding: 60px 20px;">
    <div style="background: #fff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
        <h1 style="font-size: 24px; font-weight: 900; text-align: center; margin-bottom: 24px;">🔍 پیگیری رزرو</h1>

        <form method="GET" action="/booking/track">
            <div style="margin-bottom: 16px;">
                <label style="font-size: 13px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">کد رزرو</label>
                <input type="text" name="code" value="<?php echo htmlspecialchars($_GET['code'] ?? ''); ?>" placeholder="مثال: BK250711A1B2C" style="width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font: inherit; font-size: 14px;" required>
            </div>
            <button type="submit" style="width: 100%; padding: 12px; background: #4f46e5; color: #fff; border: none; border-radius: 10px; font: inherit; font-size: 15px; font-weight: 800; cursor: pointer;">جستجو</button>
        </form>

        <?php if (!empty($error)): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; font-size: 13px; margin-top: 16px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($booking)): ?>
        <div style="margin-top: 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px;">
            <div style="font-size: 14px; font-weight: 800; margin-bottom: 8px;">✅ رزرو یافت شد</div>
            <div style="font-size: 13px; color: #475569; line-height: 1.8;">
                <div>کد رزرو: <strong><?php echo htmlspecialchars($booking->booking_code); ?></strong></div>
                <div>وضعیت: <strong><?php echo htmlspecialchars($booking->booking_status); ?></strong></div>
                <div>تاریخ ورود: <strong><?php echo $booking->checkin_date; ?></strong></div>
                <div>تاریخ خروج: <strong><?php echo $booking->checkout_date; ?></strong></div>
                <div>مبلغ: <strong><?php echo number_format($booking->final_price); ?> تومان</strong></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'پیگیری رزرو', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>