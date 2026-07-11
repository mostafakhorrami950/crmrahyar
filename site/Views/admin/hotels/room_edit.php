<?php
ob_start();
?>
<div class="container" style="max-width: 700px; margin: 0 auto; padding: 30px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 900;">✏️ ویرایش اتاق: <?php echo htmlspecialchars($room->room_type_key); ?></h1>
        <a href="/admin/hotels/<?php echo $hotel->id ?? 0; ?>/rooms" style="background: #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none;">بازگشت</a>
    </div>

    <form method="POST" action="/admin/rooms/<?php echo $room->id; ?>/update" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">شناسه URL (slug)</label>
                <input type="text" name="slug" value="<?php echo htmlspecialchars($room->slug ?? ''); ?>" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">نوع تخت</label>
                <input type="text" name="bed_type" value="<?php echo htmlspecialchars($room->bed_type ?? ''); ?>" placeholder="دوبل، توئین، سوئیت" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 12px;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">ظرفیت بزرگسال</label>
                <input type="number" name="capacity_adults" value="<?php echo $room->capacity_adults ?? 2; ?>" min="1" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">ظرفیت کودک</label>
                <input type="number" name="capacity_children" value="<?php echo $room->capacity_children ?? 0; ?>" min="0" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">مساحت (m²)</label>
                <input type="number" name="size_sqm" value="<?php echo $room->size_sqm ?? ''; ?>" min="0" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">حداکثر موجودی</label>
                <input type="number" name="max_inventory" value="<?php echo $room->max_inventory ?? 10; ?>" min="0" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">ترتیب نمایش</label>
                <input type="number" name="sort_order" value="<?php echo $room->sort_order ?? 0; ?>" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
        </div>

        <div style="margin-bottom: 12px;">
            <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">توضیحات</label>
            <textarea name="description" rows="4" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;"><?php echo htmlspecialchars($room->description ?? ''); ?></textarea>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="font-size: 13px;"><input type="checkbox" name="is_active" value="1" <?php echo $room->is_active ? 'checked' : ''; ?>> ✅ فعال</label>
        </div>

        <button type="submit" style="background: #4f46e5; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 800; cursor: pointer; font-size: 14px;">💾 ذخیره</button>
    </form>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'ویرایش اتاق', 'description' => ''];
require __DIR__ . '/../../layouts/main.php';
?>