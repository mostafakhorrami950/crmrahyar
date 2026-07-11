<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-size: 18px; font-weight: 800;">🏨 مدیریت هتل‌ها</h2>
    <span style="font-size: 13px; color: #64748b;"><?php echo count($hotels ?? []); ?> هتل</span>
</div>

<?php if (!empty($hotels)): ?>
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>نام هتل</th>
            <th>ستاره</th>
            <th>شهر</th>
            <th>پروفایل</th>
            <th>SEO</th>
            <th>عملیات</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($hotels as $h): ?>
    <tr>
        <td style="font-weight: 700;"><?php echo htmlspecialchars($h->hotel_name ?? 'بدون نام'); ?></td>
        <td><?php echo $h->star_rating ? str_repeat('⭐', min($h->star_rating, 5)) : '-'; ?></td>
        <td><?php echo htmlspecialchars($h->city ?? '-'); ?></td>
        <td>
            <?php if ($h->profile_id): ?>
                <span style="color: #059669; font-weight: 600;">✅ فعال</span>
            <?php else: ?>
                <span style="color: #f59e0b; font-weight: 600;">⚠️ بدون پروفایل</span>
            <?php endif; ?>
        </td>
        <td style="font-size: 11px;">
            <?php if (!empty($h->meta_title)): ?>
                <span style="color: #059669;">✓ متا</span>
            <?php endif; ?>
            <?php if (!empty($h->description_short)): ?>
                <span style="color: #059669;">✓ متن</span>
            <?php endif; ?>
            <?php if (empty($h->meta_title) && empty($h->description_short)): ?>
                <span style="color: #dc2626;">✗ ناقص</span>
            <?php endif; ?>
        </td>
        <td>
            <a href="/admin/hotels/<?php echo $h->crm_hotel_id; ?>/edit" class="btn btn-sm btn-primary">ویرایش</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php else: ?>
<p style="text-align: center; color: #94a3b8; padding: 40px;">هتلی ثبت نشده. ابتدا از بخش <a href="/crm/hotel-rates" style="color: #4f46e5;">نرخ‌نامه CRM</a> هتل اضافه کنید.</p>
<?php endif; ?>