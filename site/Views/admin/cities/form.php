<div style="max-width: 600px;">
    <a href="/admin/cities" style="font-size: 13px; color: #64748b;">← بازگشت</a>
    <h2 style="font-size: 18px; font-weight: 800; margin: 12px 0 20px;"><?php echo $city ? 'ویرایش شهر' : 'شهر جدید'; ?></h2>
    <form method="POST" class="card">
        <div class="form-row">
            <div class="form-group"><label>نام شهر</label><input type="text" name="name" value="<?php echo htmlspecialchars($city->name ?? ''); ?>" required></div>
            <div class="form-group"><label>slug</label><input type="text" name="slug" value="<?php echo htmlspecialchars($city->slug ?? ''); ?>" dir="ltr" placeholder="auto-generate"></div>
        </div>
        <div class="form-group"><label>توضیحات</label><textarea name="description" rows="3"><?php echo htmlspecialchars($city->description ?? ''); ?></textarea></div>
        <div class="form-group"><label><input type="checkbox" name="is_active" value="1" <?php echo ($city->is_active ?? 1) ? 'checked' : ''; ?>> ✅ فعال</label></div>
        <button type="submit" class="btn btn-primary">💾 ذخیره</button>
    </form>
</div>