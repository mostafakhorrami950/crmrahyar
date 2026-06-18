<?php
        $db = \Core\Database::getInstance();
        $sources = $db->fetchAll("SELECT id, name, icon FROM deal_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        $categories = $db->fetchAll("SELECT id, name, color FROM contact_categories ORDER BY sort_order ASC, name ASC");
?>
<div class="page-header">
    <h5>✏️ ویرایش مخاطب: <?php echo htmlspecialchars($contact->full_name); ?></h5>
    <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $contact->id; ?>" class="btn btn-secondary">← بازگشت به مشخصات</a>
</div>

<div class="card" style="padding:24px;max-width:700px;margin:0 auto;">
    <div class="ajax-error alert alert-danger" style="display:none;"></div>
    <form method="POST" action="<?php echo $config['url']; ?>/contacts/update/<?php echo $contact->id; ?>" data-ajax="true">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">نام کامل *</label>
                <input type="text" name="full_name" class="form-input" required value="<?php echo htmlspecialchars($contact->full_name); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">📞 شماره تماس</label>
                <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($contact->phone ?? ''); ?>" dir="ltr" style="text-align:left;">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">🏢 شماره تماس شرکت (اختیاری)</label>
                <input type="text" name="company_phone" class="form-input" value="<?php echo htmlspecialchars($contact->company_phone ?? ''); ?>" dir="ltr" style="text-align:left;" placeholder="021xxxxxxxx">
            </div>
            <div class="form-group">
                <label class="form-label">✉️ ایمیل</label>
                <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($contact->email ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">🪪 کد ملی</label>
                <input type="text" name="national_code" class="form-input" value="<?php echo htmlspecialchars($contact->national_code ?? ''); ?>" maxlength="10">
            </div>
            <div class="form-group">
                <label class="form-label">🛂 شماره پاسپورت</label>
                <input type="text" name="passport_number" class="form-input" value="<?php echo htmlspecialchars($contact->passport_number ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">🏢 شرکت</label>
                <input type="text" name="company" class="form-input" value="<?php echo htmlspecialchars($contact->company ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">📂 دسته‌بندی</label>
                <select name="category_id" class="form-input">
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat->id; ?>" <?php echo ($cat->id == ($contact->category_id ?? 0)) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">🎯 نحوه آشنایی</label>
                <select name="source" class="form-input">
                    <option value="">انتخاب کنید</option>
                    <?php foreach ($sources as $s): ?>
                    <option value="<?php echo htmlspecialchars($s->name); ?>" <?php echo $s->name == $contact->source ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">📍 آدرس</label>
            <textarea name="address" class="form-textarea" rows="2"><?php echo htmlspecialchars($contact->address ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">🏷️ برچسب‌ها</label>
            <input type="text" name="tags" class="form-input" value="<?php echo htmlspecialchars($contact->tags ?? ''); ?>" placeholder="مثلاً VIP, مسافرت, تور">
        </div>

        <div class="form-group">
            <label class="form-label">📝 یادداشت</label>
            <textarea name="notes" class="form-textarea" rows="3"><?php echo htmlspecialchars($contact->notes ?? ''); ?></textarea>
        </div>

        <div style="display:flex;gap:8px;margin-top:20px;">
            <button type="submit" class="btn btn-primary">💾 ذخیره تغییرات</button>
            <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $contact->id; ?>" class="btn btn-secondary">انصراف</a>
        </div>
    </form>
</div>