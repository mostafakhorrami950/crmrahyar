<?php
$db = \Core\Database::getInstance();
$sources = $db->fetchAll("SELECT id, name, icon FROM deal_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
?>
<div class="page-header">
    <h5>➕ ایجاد مخاطب جدید</h5>
    <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-secondary">← بازگشت به لیست</a>
</div>

<div class="card" style="padding:24px;max-width:700px;margin:0 auto;">
    <div class="ajax-error alert alert-danger" style="display:none;"></div>
    <form method="POST" action="<?php echo $config['url']; ?>/contacts/store" data-ajax="true">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">نام کامل *</label>
                <input type="text" name="full_name" class="form-input" required placeholder="مثال: سعید محمدی">
            </div>
            <div class="form-group">
                <label class="form-label">📞 شماره تماس</label>
                <input type="text" name="phone" class="form-input" placeholder="09120000000" dir="ltr" style="text-align:left;">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">🏢 شماره تماس شرکت (اختیاری)</label>
                <input type="text" name="company_phone" class="form-input" placeholder="021xxxxxxxx" dir="ltr" style="text-align:left;">
            </div>
            <div class="form-group">
                <label class="form-label">✉️ ایمیل</label>
                <input type="email" name="email" class="form-input" placeholder="example@mail.com">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">🪪 کد ملی</label>
                <input type="text" name="national_code" class="form-input" placeholder="۱۰ رقمی" maxlength="10">
            </div>
            <div class="form-group">
                <label class="form-label">🛂 شماره پاسپورت</label>
                <input type="text" name="passport_number" class="form-input" placeholder="P12345678">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">🏢 شرکت</label>
                <input type="text" name="company" class="form-input" placeholder="نام شرکت">
            </div>
            <div class="form-group">
                <label class="form-label">🎯 نحوه آشنایی</label>
                <select name="source" class="form-input">
                    <option value="">انتخاب کنید</option>
                    <?php foreach ($sources as $s): ?>
                    <option value="<?php echo htmlspecialchars($s->name); ?>"><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">📍 آدرس</label>
            <textarea name="address" class="form-textarea" rows="2" placeholder="آدرس کامل"></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">🏷️ برچسب‌ها</label>
            <input type="text" name="tags" class="form-input" placeholder="مثلاً VIP, مسافرت, تور">
        </div>

        <div class="form-group">
            <label class="form-label">📝 یادداشت</label>
            <textarea name="notes" class="form-textarea" rows="3" placeholder="یادداشت‌های اضافی..."></textarea>
        </div>

        <div style="display:flex;gap:8px;margin-top:20px;">
            <button type="submit" class="btn btn-primary">💾 ذخیره مخاطب</button>
            <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-secondary">انصراف</a>
        </div>
    </form>
</div>