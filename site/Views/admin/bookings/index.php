<h2 style="font-size: 18px; font-weight: 800; margin-bottom: 20px;">📋 مدیریت رزروها</h2>
<?php if (!empty($bookings)): ?>
<div class="table-wrap">
<table>
    <thead><tr><th>کد</th><th>مهمان</th><th>تلفن</th><th>وضعیت</th><th>تاریخ</th><th>عملیات</th></tr></thead>
    <tbody>
    <?php foreach ($bookings as $b): ?>
    <tr>
        <td style="font-weight: 700; font-size: 12px;"><?php echo htmlspecialchars($b->booking_code ?? $b->id); ?></td>
        <td><?php echo htmlspecialchars($b->guest_name ?? '-'); ?></td>
        <td style="direction: ltr;"><?php echo htmlspecialchars($b->guest_phone ?? '-'); ?></td>
        <td><?php
            $statusColors = ['pending' => '#f59e0b', 'paid' => '#059669', 'cancelled' => '#dc2626', 'confirmed' => '#4f46e5'];
            $color = $statusColors[$b->booking_status ?? 'pending'] ?? '#94a3b8';
            echo '<span style="color:' . $color . ';font-weight:700;">' . htmlspecialchars($b->booking_status ?? 'pending') . '</span>';
        ?></td>
        <td style="font-size: 11px;"><?php echo $b->created_at ?? '-'; ?></td>
        <td><a href="/booking/<?php echo htmlspecialchars($b->token ?? ''); ?>" target="_blank" class="btn btn-sm btn-secondary">مشاهده</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php else: ?>
<p style="text-align: center; color: #94a3b8; padding: 40px;">رزرویی ثبت نشده.</p>
<?php endif; ?>