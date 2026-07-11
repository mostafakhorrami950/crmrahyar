<div style="max-width: 900px;">
    <a href="/admin/blog" style="font-size: 13px; color: #64748b;">← بازگشت به لیست</a>
    <h2 style="font-size: 18px; font-weight: 800; margin: 12px 0 20px;"><?php echo $post ? 'ویرایش مقاله' : 'مقاله جدید'; ?></h2>

    <form method="POST" id="blogForm">
        <!-- Main Content -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">📝 محتوای مقاله</h3>
            <div class="form-group"><label>عنوان مقاله *</label><input type="text" name="title" id="postTitle" value="<?php echo htmlspecialchars($post->title ?? ''); ?>" required oninput="autoSlug(this.value)"></div>
            <?php if ($post): ?>
            <div class="form-group"><label>slug (آدرس URL)</label><input type="text" name="slug" value="<?php echo htmlspecialchars($post->slug ?? ''); ?>" dir="ltr" style="text-align: left;"></div>
            <?php endif; ?>
            <div class="form-group"><label>خلاصه (_excerpt) - برای نمایش در لیست و شبکه‌های اجتماعی</label><textarea name="excerpt" rows="3" style="max-width: 100%;"><?php echo htmlspecialchars($post->excerpt ?? ''); ?></textarea></div>
            <div class="form-group">
                <label>محتوای اصلی مقاله *</label>
                <textarea name="content" id="editor" rows="15" style="width: 100%; direction: rtl;"><?php echo htmlspecialchars($post->content ?? ''); ?></textarea>
            </div>
        </div>

        <!-- SEO Settings -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🔍 تنظیمات سئو (SEO)</h3>
            <div class="form-group">
                <label>عنوان SEO (Meta Title) - حداکثر ۶۰ کاراکتر</label>
                <input type="text" name="meta_title" id="metaTitle" value="<?php echo htmlspecialchars($post->meta_title ?? ''); ?>" maxlength="60" oninput="updateSeoPreview()">
                <small style="color: #94a3b8; font-size: 11px;"><span id="metaTitleCount">0</span>/60 کاراکتر</small>
            </div>
            <div class="form-group">
                <label>توضیحات SEO (Meta Description) - حداکثر ۱۶۰ کاراکتر</label>
                <textarea name="meta_description" id="metaDesc" rows="3" maxlength="160" oninput="updateSeoPreview()" style="max-width: 100%;"><?php echo htmlspecialchars($post->meta_description ?? ''); ?></textarea>
                <small style="color: #94a3b8; font-size: 11px;"><span id="metaDescCount">0</span>/160 کاراکتر</small>
            </div>

            <!-- SEO Preview -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-top: 10px;">
                <div style="font-size: 10px; color: #64748b; margin-bottom: 4px;">پیش‌نمایش گوگل:</div>
                <div id="seoPreviewTitle" style="color: #1a0dab; font-size: 16px; font-weight: 600; margin-bottom: 2px; direction: ltr; text-align: left;">عنوان مقاله</div>
                <div id="seoPreviewUrl" style="color: #006621; font-size: 12px; direction: ltr; text-align: left; margin-bottom: 2px;">https://crm.mobixai.ir/blog/slug</div>
                <div id="seoPreviewDesc" style="color: #545454; font-size: 13px; direction: ltr; text-align: left;">توضیحات مقاله...</div>
            </div>

            <!-- Focus Keyword -->
            <div class="form-group" style="margin-top: 12px;">
                <label>کلمه کلیدی اصلی (Focus Keyword)</label>
                <input type="text" name="focus_keyword" value="<?php echo htmlspecialchars($post->focus_keyword ?? ''); ?>" placeholder="مثال: هتل مشهد">
            </div>
        </div>

        <!-- Social Media -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">📱 شبکه‌های اجتماعی</h3>
            <div class="form-group">
                <label>تصویر شاخص (URL)</label>
                <input type="text" name="featured_image" value="<?php echo htmlspecialchars($post->featured_image ?? ''); ?>" dir="ltr" placeholder="/uploads/blog/image.jpg">
            </div>
            <div class="form-group">
                <label>Alt Text تصویر</label>
                <input type="text" name="image_alt" value="<?php echo htmlspecialchars($post->image_alt ?? ''); ?>" placeholder="توضیح تصویر برای سئو">
            </div>
        </div>

        <!-- Publish Settings -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">📤 انتشار</h3>
            <div class="form-group"><label><input type="checkbox" name="is_published" value="1" <?php echo ($post->is_published ?? 0) ? 'checked' : ''; ?>> ✅ منتشر شده</label></div>
        </div>

        <button type="submit" class="btn btn-primary" style="font-size: 15px; padding: 12px 32px;">💾 ذخیره مقاله</button>
    </form>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
// Fallback: if TinyMCE fails to load, use plain textarea
if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#editor',
        directionality: 'rtl',
        language: 'fa',
        height: 400,
        plugins: 'lists link image table code fullscreen preview searchreplace wordcount',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | link image table | code preview fullscreen',
        menubar: false,
        branding: false,
        content_style: 'body { font-family: Vazirmatn, sans-serif; font-size: 14px; line-height: 1.8; direction: rtl; }',
        setup: function(editor) {
            editor.on('change', function() { editor.save(); });
        }
    });
}

function autoSlug(val) {
    // Auto-generate SEO title from main title
    var metaTitle = document.getElementById('metaTitle');
    if (!metaTitle.value) {
        metaTitle.value = val;
        updateSeoPreview();
    }
}

function updateSeoPreview() {
    var title = document.getElementById('metaTitle').value || document.getElementById('postTitle').value || 'عنوان مقاله';
    var desc = document.getElementById('metaDesc').value || 'توضیحات مقاله...';
    document.getElementById('seoPreviewTitle').textContent = title.substring(0, 60);
    document.getElementById('seoPreviewDesc').textContent = desc.substring(0, 160);
    document.getElementById('metaTitleCount').textContent = (document.getElementById('metaTitle').value || '').length;
    document.getElementById('metaDescCount').textContent = (document.getElementById('metaDesc').value || '').length;
}
updateSeoPreview();
</script>