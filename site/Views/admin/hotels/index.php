<?php
ob_start();
?>
<div class="container" style="max-width: 1000px; margin: 0 auto; padding: 30px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 style="font-size: 22px; font-weight: 900;">🏨 مدیریت هتل‌ها</h1>
        <a href="/admin" style="background: #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none;">بازگشت</a>
    </div>

    <?php if (!empty($hotels)): ?>
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: #1e293b; color: #fff;">
                    <th style="padding: 10px 12px; text-align: right;">هتل</th>
                    <th style="padding: 10px 12px; text-align: center;">شهر</th>
                    <th style="padding: 10px 12px; text-align: center;">ستاره</th>
                    <th style="padding: 10px 12px; text-align: center;">وضعیت</th>
                    <th style="padding: 10px 12px; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hotels as $h): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 12px; font-weight: 700;"><?php echo htmlspecialchars($h->hotel_name ?? 'بدون نام'); ?></td>
                    <td style="padding: 10px 12px; text-align: center;"><?php echo htmlspecialchars($h->city_name ?? '-'); ?></td>
                    <td style="padding: 10px 12px; text-align: center; color: #f59e0b;"><?php echo $h->star_rating ? str_repeat('★', $h->star_rating) : '-'; ?></td>
                    <td style="padding: 10px 12px; text-align: center;">
                        <?php if ($h->is_active): ?><span style="color: #059669; font-weight: 700;">فعال</span>
                        <?php else: ?><span style="color: #dc2626; font-weight: 700;">غیرفعال</span><?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                        <a href="/admin/hotels/<?php echo $h->id; ?>/edit" style="background: #4f46e5; color: #fff; padding: 4px 12px; border-radius: 6px; font-size: 11px; text-decoration: none; font-weight: 700;">ویرایش</a>
                        <a href="/admin/hotels/<?php echo $h->id; ?>/rooms" style="background: #059669; color: #fff; padding: 4px 12px; border-radius: 6px; font-size: 11px; text-decoration: none; font-weight: 700;">اتاق‌ها</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 60px; color: #94a3b8;">
        <p>هتلی ثبت نشده. ابتدا از بخش <a href="/crm/hotel-rates">نرخ‌نامه هتل‌ها</a> هتل اضافه کنید.</p>
    </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'مدیریت هتل‌ها', 'description' => ''];
require __DIR__ . '/../../layouts/main.php';
?>