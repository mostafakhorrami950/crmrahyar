<div style="max-width: 900px;">
    <a href="/admin/hotels" style="font-size: 13px; color: #64748b;">← بازگشت به لیست</a>
    <h2 style="font-size: 18px; font-weight: 800; margin: 12px 0 20px;">🏨 ویرایش هتل: <?php echo htmlspecialchars($crmHotel->hotel_name ?? ''); ?></h2>

    <form method="POST">
        <!-- Hotel Info (Read-only from CRM) -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">📋 اطلاعات پایه (از CRM)</h3>
            <div class="form-row">
                <div class="form-group"><label>نام هتل</label><input type="text" value="<?php echo htmlspecialchars($crmHotel->hotel_name ?? ''); ?>" disabled></div>
                <div class="form-group"><label>ستاره</label><input type="text" value="<?php echo $crmHotel->star_rating ?? 0; ?> ستاره" disabled></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>شهر</label><input type="text" value="<?php echo htmlspecialchars($crmHotel->city ?? ''); ?>" disabled></div>
                <div class="form-group"><label>slug</label><input type="text" name="slug" value="<?php echo htmlspecialchars($hotel->slug ?? ''); ?>" dir="ltr" style="text-align: left; font-family: monospace;"></div>
            </div>
        </div>

        <!-- Location -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">📍 موقعیت مکانی</h3>
            <div class="form-row-3">
                <div class="form-group"><label>شهر</label><select name="city_id"><option value="">انتخاب...</option><?php foreach ($cities as $c): ?><option value="<?php echo $c->id; ?>" <?php echo ($hotel->city_id ?? 0) == $c->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->name); ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>محله</label><select name="neighborhood_id"><option value="">انتخاب...</option><?php foreach ($neighborhoods as $n): ?><option value="<?php echo $n->id; ?>" <?php echo ($hotel->neighborhood_id ?? 0) == $n->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($n->name); ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>فاصله تا حرم (km)</label><input type="number" step="0.1" name="distance_to_haram_km" value="<?php echo $hotel->distance_to_haram_km ?? ''; ?>"></div>
            </div>
            <div class="form-group"><label>آدرس کامل</label><input type="text" name="address" value="<?php echo htmlspecialchars($hotel->address ?? ''); ?>"></div>
            <div class="form-row">
                <div class="form-group"><label>عرض جغرافیایی</label><input type="text" name="latitude" value="<?php echo $hotel->latitude ?? ''; ?>" dir="ltr"></div>
                <div class="form-group"><label>طول جغرافیایی</label><input type="text" name="longitude" value="<?php echo $hotel->longitude ?? ''; ?>" dir="ltr"></div>
            </div>
        </div>

        <!-- Description -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">📝 توضیحات هتل</h3>
            <div class="form-group"><label>توضیح کوتاه (برای لیست و SEO)</label><textarea name="description_short" rows="3" style="max-width: 100%;"><?php echo htmlspecialchars($hotel->description_short ?? ''); ?></textarea></div>
            <div class="form-group"><label>توضیحات کامل (با ویرایشگر حرفه‌ای)</label><textarea name="description_long" id="editor" rows="12" style="width: 100%; direction: rtl;"><?php echo htmlspecialchars($hotel->description_long ?? ''); ?></textarea></div>
        </div>

        <!-- SEO -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🔍 تنظیمات سئو (SEO 2026)</h3>
            <div class="form-group">
                <label>عنوان SEO (Meta Title) - حداکثر ۶۰ کاراکتر</label>
                <input type="text" name="meta_title" id="metaTitle" value="<?php echo htmlspecialchars($hotel->meta_title ?? ''); ?>" maxlength="60" oninput="document.getElementById('metaTitleCount').textContent=this.value.length">
                <small style="color: #94a3b8; font-size: 11px;"><span id="metaTitleCount"><?php echo strlen($hotel->meta_title ?? '0'); ?></span>/60</small>
            </div>
            <div class="form-group">
                <label>توضیحات SEO (Meta Description) - حداکثر ۱۶۰ کاراکتر</label>
                <textarea name="meta_description" id="metaDesc" rows="3" maxlength="160" style="max-width: 100%;" oninput="document.getElementById('metaDescCount').textContent=this.value.length"><?php echo htmlspecialchars($hotel->meta_description ?? ''); ?></textarea>
                <small style="color: #94a3b8; font-size: 11px;"><span id="metaDescCount"><?php echo strlen($hotel->meta_description ?? '0'); ?></span>/160</small>
            </div>
        </div>

        <!-- Gallery -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🖼️ گالری تصاویر</h3>
            <div id="galleryDropZone" style="border: 2px dashed #c7d2fe; border-radius: 12px; padding: 24px; text-align: center; cursor: pointer; background: #f8fafc;" onclick="document.getElementById('galleryInput').click()">
                <div style="font-size: 28px; margin-bottom: 6px;">📤</div>
                <div style="font-weight: 700; color: #4f46e5;">تصاویر را اینجا بکشید یا کلیک کنید</div>
                <div style="font-size: 11px; color: #94a3b8;">JPG, PNG, WebP</div>
            </div>
            <input type="file" id="galleryInput" accept="image/*" multiple style="display: none;" onchange="uploadGallery(this)">
            <div id="galleryPreview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; margin-top: 12px;"></div>
        </div>

        <!-- Tags -->
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🏷️ برچسب‌ها</h3>
            <div class="form-row">
                <div class="form-group"><label><input type="checkbox" name="family_friendly" value="1" <?php echo ($hotel->family_friendly ?? 0) ? 'checked' : ''; ?>> 👨‍👩‍👧‍👦 مناسب خانواده</label></div>
                <div class="form-group"><label><input type="checkbox" name="couple_friendly" value="1" <?php echo ($hotel->couple_friendly ?? 0) ? 'checked' : ''; ?>> 💑 مناسب زوجین</label></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label><input type="checkbox" name="budget_friendly" value="1" <?php echo ($hotel->budget_friendly ?? 0) ? 'checked' : ''; ?>> 💰 اقتصادی</label></div>
                <div class="form-group"><label><input type="checkbox" name="luxury" value="1" <?php echo ($hotel->luxury ?? 0) ? 'checked' : ''; ?>> 👑 لوکس</label></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label><input type="checkbox" name="featured" value="1" <?php echo ($hotel->featured ?? 0) ? 'checked' : ''; ?>> ⭐ ویژه</label></div>
                <div class="form-group"><label><input type="checkbox" name="is_active" value="1" <?php echo ($hotel->is_active ?? 1) ? 'checked' : ''; ?>> ✅ فعال</label></div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="font-size: 15px; padding: 12px 32px;">💾 ذخیره تغییرات</button>
    </form>
</div>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#editor', directionality: 'rtl', language: 'fa', height: 350,
        plugins: 'lists link image table code fullscreen preview searchreplace wordcount',
        toolbar: 'undo redo | blocks | bold italic underline | alignright aligncenter alignleft | bullist numlist | link image table | code preview',
        menubar: false, branding: false,
        content_style: 'body { font-family: Vazirmatn, sans-serif; font-size: 14px; line-height: 1.8; direction: rtl; }',
        setup: function(e) { e.on('change', function() { e.save(); }); }
    });
}

// Gallery upload
var dz = document.getElementById('galleryDropZone');
dz.addEventListener('dragover', function(e) { e.preventDefault(); this.style.borderColor = '#4f46e5'; });
dz.addEventListener('dragleave', function(e) { this.style.borderColor = '#c7d2fe'; });
dz.addEventListener('drop', function(e) {
    e.preventDefault(); this.style.borderColor = '#c7d2fe';
    if (e.dataTransfer.files.length) { document.getElementById('galleryInput').files = e.dataTransfer.files; uploadGallery(document.getElementById('galleryInput')); }
});

function uploadGallery(input) {
    if (!input.files) return;
    for (var i = 0; i < input.files.length; i++) {
        var formData = new FormData();
        formData.append('image', input.files[i]);
        fetch('/admin/blog/image-upload', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    var div = document.createElement('div');
                    div.style.cssText = 'position:relative;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;';
                    div.innerHTML = '<img src="' + data.path + '" style="width:100%;height:100px;object-fit:cover;display:block;"><div style="font-size:10px;padding:4px;text-align:center;word-break:break-all;">' + data.path.split('/').pop() + '</div>';
                    document.getElementById('galleryPreview').appendChild(div);
                }
            });
    }
}
</script>