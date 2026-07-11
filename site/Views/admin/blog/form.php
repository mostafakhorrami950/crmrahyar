<div style="max-width: 800px;">
    <a href="/admin/blog" style="font-size: 13px; color: #64748b;">← بازگشت به لیست</a>
    <h2 style="font-size: 18px; font-weight: 800; margin: 12px 0 20px;"><?php echo $post ? 'ویرایش مقاله' : 'مقاله جدید'; ?></h2>
    <form method="POST" class="card">
        <div class="form-group"><label>عنوان</label><input type="text" name="title" value="<?php echo htmlspecialchars($post->title ?? ''); ?>" required></div>
        <?php if ($post): ?><div class="form-group"><label>slug</label><input type="text" name="slug" value="<?php echo htmlspecialchars($post->slug ?? ''); ?>"></div><?php endif; ?>
        <div class="form-group"><label>خلاصه</label><textarea name="excerpt" rows="2"><?php echo htmlspecialchars($post->excerpt ?? ''); ?></textarea></div>
        <div class="form-group"><label>محتوا (HTML)</label><textarea name="content" rows="12" style="direction: ltr; text-align: left;"><?php echo htmlspecialchars($post->content ?? ''); ?></textarea></div>
        <div class="form-row">
            <div class="form-group"><label>متا عنوان SEO</label><input type="text" name="meta_title" value="<?php echo htmlspecialchars($post->meta_title ?? ''); ?>"></div>
            <div class="form-group"><label>متا توضیح SEO</label><textarea name="meta_description" rows="2"><?php echo htmlspecialchars($post->meta_description ?? ''); ?></textarea></div>
        </div>
        <div class="form-group"><label><input type="checkbox" name="is_published" value="1" <?php echo ($post->is_published ?? 0) ? 'checked' : ''; ?>> ✅ منتشر شده</label></div>
        <button type="submit" class="btn btn-primary">💾 ذخیره</button>
    </form>
</div>