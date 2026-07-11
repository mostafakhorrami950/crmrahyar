<?php
ob_start();
?>
<div class="container" style="max-width: 900px; margin: 0 auto; padding: 30px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 900;">🛏️ اتاق‌ها: <?php echo htmlspecialchars($hotel->slug ?? ''); ?></h1>
        <a href="/admin/hotels" style="background: #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none;">بازگشت</a>
    </div>

    <!-- Add Room -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <form method="POST" action="/admin/hotels/<?php echo $hotel->id; ?>/rooms/add" style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">نوع اتاق (از CRM)</label>
                <select name="room_type_key" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    <?php foreach ($crmRooms as $cr): ?>
                    <option value="<?php echo htmlspecialchars($cr->room_type); ?>"><?php echo htmlspecialchars($cr->room_type); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" style="background: #059669; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; cursor: pointer;">➕ افزودن اتاق</button>
        </form>
    </div>

    <!-- Rooms List -->
    <?php if (!empty($rooms)): ?>
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach ($rooms as $r): ?>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-weight: 800; font-size: 14px;"><?php echo htmlspecialchars($r->room_type_key); ?></div>
                <div style="font-size: 12px; color: #64748b;">
                    👥 <?php echo $r->capacity_adults; ?> نفر
                    <?php if ($r->bed_type): ?> · 🛏️ <?php echo htmlspecialchars($r->bed_type); ?><?php endif; ?>
                    <?php if ($r->size_sqm): ?> · 📐 <?php echo $r->size_sqm; ?>m²<?php endif; ?>
                    <?php if (!$r->is_active): ?> · <span style="color: #dc2626;">غیرفعال</span><?php endif; ?>
                </div>
            </div>
            <a href="/admin/rooms/<?php echo $r->id; ?>/edit" style="background: #4f46e5; color: #fff; padding: 4px 12px; border-radius: 6px; font-size: 11px; text-decoration: none; font-weight: 700;">ویرایش</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; color: #94a3b8;">اتاقی ثبت نشده</div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'اتاق‌ها', 'description' => ''];
require __DIR__ . '/../../layouts/main.php';
?>