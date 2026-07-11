<?php
ob_start();
?>
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 30px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 900;">✏️ ویرایش: <?php echo htmlspecialchars($crmHotel->hotel_name ?? ''); ?></h1>
        <a href="/admin/hotels" style="background: #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none;">بازگشت</a>
    </div>

    <form method="POST" action="/admin/hotels/<?php echo $hotel->id; ?>/update" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">شهر</label>
                <select name="city_id" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    <option value="">انتخاب شهر</option>
                    <?php foreach ($cities as $c): ?>
                    <option value="<?php echo $c->id; ?>" <?php echo ($hotel->city_id == $c->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">محله</label>
                <select name="neighborhood_id" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    <option value="">انتخاب محله</option>
                    <?php foreach ($neighborhoods as $n): ?>
                    <option value="<?php echo $n->id; ?>" <?php echo ($hotel->neighborhood_id == $n->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($n->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 12px;">
            <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">آدرس</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($hotel->address ?? ''); ?>" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">فاصله تا حرم (کیلومتر)</label>
                <input type="number" name="distance_to_haram_km" value="<?php echo $hotel->distance_to_haram_km ?? ''; ?>" step="0.1" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">شناسه URL (slug)</label>
                <input type="text" name="slug" value="<?php echo htmlspecialchars($hotel->slug ?? ''); ?>" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
        </div>

        <div style="margin-bottom: 12px;">
            <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">توضیحات کوتاه</label>
            <input type="text" name="description_short" value="<?php echo htmlspecialchars($hotel->description_short ?? ''); ?>" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
        </div>

        <div style="margin-bottom: 12px;">
            <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">توضیحات کامل</label>
            <textarea name="description_long" rows="5" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;"><?php echo htmlspecialchars($hotel->description_long ?? ''); ?></textarea>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">ویژگی‌ها:</label>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <label style="font-size: 13px;"><input type="checkbox" name="family_friendly" value="1" <?php echo $hotel->family_friendly ? 'checked' : ''; ?>> 👨‍👩‍👧‍👦 خانوادگی</label>
                <label style="font-size: 13px;"><input type="checkbox" name="couple_friendly" value="1" <?php echo $hotel->couple_friendly ? 'checked' : ''; ?>> 💑 زوجین</label>
                <label style="font-size: 13px;"><input type="checkbox" name="budget_friendly" value="1" <?php echo $hotel->budget_friendly ? 'checked' : ''; ?>> 💰 اقتصادی</label>
                <label style="font-size: 13px;"><input type="checkbox" name="luxury" value="1" <?php echo $hotel->luxury ? 'checked' : ''; ?>> ✨ لوکس</label>
                <label style="font-size: 13px;"><input type="checkbox" name="featured" value="1" <?php echo $hotel->featured ? 'checked' : ''; ?>> ⭐ ویژه</label>
                <label style="font-size: 13px;"><input type="checkbox" name="is_active" value="1" <?php echo $hotel->is_active ? 'checked' : ''; ?>> ✅ فعال</label>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">عنوان SEO</label>
                <input type="text" name="meta_title" value="<?php echo htmlspecialchars($hotel->meta_title ?? ''); ?>" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">توضیحات SEO</label>
                <input type="text" name="meta_description" value="<?php echo htmlspecialchars($hotel->meta_description ?? ''); ?>" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
            </div>
        </div>

        <button type="submit" style="background: #4f46e5; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 800; cursor: pointer; font-size: 14px;">💾 ذخیره</button>
    </form>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'ویرایش هتل', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>