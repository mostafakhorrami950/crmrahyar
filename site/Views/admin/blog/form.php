<div style="max-width: 900px;">
    <a href="/admin/blog" style="font-size: 13px; color: #64748b;">← بازگشت به لیست</a>
    <h2 style="font-size: 18px; font-weight: 800; margin: 12px 0 20px;"><?php echo $post ? 'ویرایش مقاله' : 'مقاله جدید'; ?></h2>

    <form method="POST" id="blogForm">
        <!-- Main Content -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">📝 محتوای مقاله</h3>
            <div class="form-group"><label>عنوان مقاله *</label><input type="text" name="title" id="postTitle" value="<?php echo htmlspecialchars($post->title ?? ''); ?>" required oninput="autoSlug(this.value)"></div>
            <div class="form-group">
                <label>آدرس URL (slug) - فقط انگلیسی و خط تیره</label>
                <input type="text" name="slug" id="postSlug" value="<?php echo htmlspecialchars($post->slug ?? ''); ?>" dir="ltr" style="text-align: left; font-family: monospace;" placeholder="my-article-title">
                <small style="color: #94a3b8; font-size: 11px;">پیش‌نمایش: <span id="slugPreview">https://crm.mobixai.ir/blog/...</span></small>
            </div>
            <div class="form-group"><label>خلاصه (_excerpt) - برای نمایش در لیست و شبکه‌های اجتماعی</label><textarea name="excerpt" rows="3" style="max-width: 100%;"><?php echo htmlspecialchars($post->excerpt ?? ''); ?></textarea></div>
            <div class="form-group">
                <label>محتوای اصلی مقاله *</label>
                <div id="quillEditor" style="height: 350px; direction: rtl;"></div>
                <textarea name="content" id="editor" style="display: none;"><?php echo htmlspecialchars($post->content ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Image Upload -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🖼️ تصویر شاخص</h3>
            <div id="dropZone" style="border: 2px dashed #c7d2fe; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; background: #f8fafc; transition: 0.2s;" onclick="document.getElementById('imageInput').click()">
                <div style="font-size: 32px; margin-bottom: 8px;">📤</div>
                <div style="font-weight: 700; color: #4f46e5;">تصویر را اینجا بکشید یا کلیک کنید</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">JPG, PNG, WebP - حداکثر 5MB</div>
            </div>
            <input type="file" id="imageInput" accept="image/*" style="display: none;" onchange="uploadImage(this)">
            <div id="imagePreview" style="margin-top: 12px; display: none;">
                <img id="previewImg" src="" style="max-width: 100%; max-height: 250px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="margin-top: 6px;"><button type="button" class="btn btn-sm btn-danger" onclick="removeImage()">حذف تصویر</button></div>
            </div>
            <input type="hidden" name="featured_image" id="featuredImage" value="<?php echo htmlspecialchars($post->featured_image ?? ''); ?>">
            <div class="form-group" style="margin-top: 10px;">
                <label>Alt Text تصویر (سئو)</label>
                <input type="text" name="image_alt" id="imageAlt" value="<?php echo htmlspecialchars($post->image_alt ?? ''); ?>" placeholder="توضیح تصویر برای موتورهای جستجو">
            </div>
        </div>

        <!-- SEO Settings -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🔍 تنظیمات سئو (SEO 2026)</h3>
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

            <div class="form-group" style="margin-top: 12px;">
                <label>کلمه کلیدی اصلی (Focus Keyword)</label>
                <input type="text" name="focus_keyword" value="<?php echo htmlspecialchars($post->focus_keyword ?? ''); ?>" placeholder="مثال: هتل مشهد، رزرو هتل">
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

<!-- Quill Editor (Free, no API key) -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
// Image upload
var dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', function(e) { e.preventDefault(); this.style.borderColor = '#4f46e5'; this.style.background = '#eef2ff'; });
dropZone.addEventListener('dragleave', function(e) { this.style.borderColor = '#c7d2fe'; this.style.background = '#f8fafc'; });
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.borderColor = '#c7d2fe'; this.style.background = '#f8fafc';
    if (e.dataTransfer.files.length) { document.getElementById('imageInput').files = e.dataTransfer.files; uploadImage(document.getElementById('imageInput')); }
});

function uploadImage(input) {
    if (!input.files || !input.files[0]) return;
    var formData = new FormData();
    formData.append('image', input.files[0]);
    fetch('/admin/blog/image-upload', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('featuredImage').value = data.path;
                document.getElementById('previewImg').src = data.path;
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('dropZone').style.display = 'none';
            } else { alert(data.message || 'خطا در آپلود'); }
        })
        .catch(() => alert('خطا در آپلود'));
}

function removeImage() {
    document.getElementById('featuredImage').value = '';
    document.getElementById('previewImg').src = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('dropZone').style.display = 'block';
}

// Show existing image
if (document.getElementById('featuredImage').value) {
    document.getElementById('previewImg').src = document.getElementById('featuredImage').value;
    document.getElementById('imagePreview').style.display = 'block';
    document.getElementById('dropZone').style.display = 'none';
}

// Quill Editor
var quill = new Quill('#quillEditor', {
    theme: 'snow',
    direction: 'rtl',
    placeholder: 'محتوای مقاله را بنویسید...',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'direction': 'rtl' }],
            [{ 'align': ['right', 'center', 'left', 'justify'] }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['link', 'image', 'video'],
            ['blockquote', 'code-block'],
            [{ 'color': [] }, { 'background': [] }],
            ['clean']
        ]
    }
});
// Sync quill content to textarea
quill.on('text-change', function() {
    document.getElementById('editor').value = quill.root.innerHTML;
});
// Load existing content
if (document.getElementById('editor').value) {
    quill.root.innerHTML = document.getElementById('editor').value;
}

function autoSlug(val) {
    if (!document.getElementById('postSlug').value) {
        // Auto-generate slug from title
        fetch('/admin/blog/create').catch(() => {});
    }
    if (!document.getElementById('metaTitle').value) {
        document.getElementById('metaTitle').value = val;
        updateSeoPreview();
    }
}

function updateSeoPreview() {
    var title = document.getElementById('metaTitle').value || document.getElementById('postTitle').value || 'عنوان مقاله';
    var desc = document.getElementById('metaDesc').value || 'توضیحات مقاله...';
    var slug = document.getElementById('postSlug').value || 'slug';
    document.getElementById('seoPreviewTitle').textContent = title.substring(0, 60);
    document.getElementById('seoPreviewDesc').textContent = desc.substring(0, 160);
    document.getElementById('seoPreviewUrl').textContent = 'https://crm.mobixai.ir/blog/' + slug;
    document.getElementById('slugPreview').textContent = 'https://crm.mobixai.ir/blog/' + slug;
    document.getElementById('metaTitleCount').textContent = (document.getElementById('metaTitle').value || '').length;
    document.getElementById('metaDescCount').textContent = (document.getElementById('metaDesc').value || '').length;
}
updateSeoPreview();
</script>