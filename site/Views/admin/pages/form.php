<div style="max-width: 800px;">
    <a href="/admin/pages" style="font-size: 13px; color: #64748b;">← بازگشت</a>
    <h2 style="font-size: 18px; font-weight: 800; margin: 12px 0 20px;">ویرایش صفحه: /<?php echo htmlspecialchars($slug); ?></h2>
    <form method="POST" class="card">
        <div class="form-group"><label>عنوان</label><input type="text" name="title" value="<?php echo htmlspecialchars($page->title ?? ''); ?>" required></div>
        <div class="form-group"><label>محتوا (HTML)</label><textarea name="content" rows="12"><?php echo htmlspecialchars($page->content ?? ''); ?></textarea></div>
        <div class="form-row">
            <div class="form-group"><label>متا عنوان SEO</label><input type="text" name="meta_title" value="<?php echo htmlspecialchars($page->meta_title ?? ''); ?>"></div>
            <div class="form-group"><label>متا توضیح SEO</label><textarea name="meta_description" rows="2"><?php echo htmlspecialchars($page->meta_description ?? ''); ?></textarea></div>
        </div>
        <div class="form-group"><label><input type="checkbox" name="is_active" value="1" <?php echo ($page->is_active ?? 1) ? 'checked' : ''; ?>> ✅ فعال</label></div>
        <button type="submit" class="btn btn-primary">💾 ذخیره</button>
    </form>
</div>