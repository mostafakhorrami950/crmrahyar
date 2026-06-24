<?php
    $db = \Core\Database::getInstance();
    $sources = $db->fetchAll("SELECT id, name, icon FROM deal_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    $categories = $db->fetchAll("SELECT id, name, color FROM contact_categories ORDER BY sort_order ASC, name ASC");
    $defaultCategory = $db->fetch("SELECT id FROM contact_categories WHERE is_default = 1");
    $defaultCategoryId = $defaultCategory ? $defaultCategory->id : 0;
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-person-plus me-2"></i>ایجاد مخاطب جدید</h5>
    <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت به لیست</a>
</div>

<div class="card mx-auto" style="max-width:750px;">
    <div class="card-body p-3 p-md-4">
        <div class="ajax-error alert alert-danger d-none"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/contacts/store" data-ajax="true">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium">نام کامل <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" required placeholder="مثال: سعید محمدی">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-telephone me-1"></i>شماره تماس</label>
                    <input type="text" name="phone" class="form-control" placeholder="09120000000" dir="ltr" style="text-align:left;">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-building me-1"></i>شماره تماس شرکت</label>
                    <input type="text" name="company_phone" class="form-control" placeholder="021xxxxxxxx" dir="ltr" style="text-align:left;">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-envelope me-1"></i>ایمیل</label>
                    <input type="email" name="email" class="form-control" placeholder="example@mail.com">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-card-heading me-1"></i>کد ملی</label>
                    <input type="text" name="national_code" class="form-control" placeholder="۱۰ رقمی" maxlength="10">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-pass me-1"></i>شماره پاسپورت</label>
                    <input type="text" name="passport_number" class="form-control" placeholder="P12345678">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-building me-1"></i>شرکت</label>
                    <input type="text" name="company" class="form-control" placeholder="نام شرکت">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-folder me-1"></i>دسته‌بندی</label>
                    <select name="category_id" class="form-select">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>" <?php echo ($cat->id == $defaultCategoryId) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-crosshair me-1"></i>نحوه آشنایی</label>
                    <select name="source" class="form-select">
                        <option value="">انتخاب کنید</option>
                        <?php foreach ($sources as $s): ?>
                        <option value="<?php echo htmlspecialchars($s->name); ?>"><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-geo-alt me-1"></i>آدرس</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="آدرس کامل"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-tags me-1"></i>برچسب‌ها</label>
                    <input type="text" name="tags" class="form-control" placeholder="مثلاً VIP, مسافرت, تور">
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-journal-text me-1"></i>یادداشت</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="یادداشت‌های اضافی..."></textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره مخاطب</button>
                <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-outline-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>