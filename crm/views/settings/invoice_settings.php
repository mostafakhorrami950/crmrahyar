<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-gear me-2 text-primary"></i>تنظیمات فاکتور</h5>
    <a href="<?php echo $config['url']; ?>/settings" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>تنظیمات سربرگ فاکتور</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo $config['url']; ?>/settings/invoice/update">
                    <div class="row g-3">
                        <!-- Invoice Title -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-type me-1"></i>عنوان فاکتور</label>
                            <input type="text" name="invoice_title" class="form-control" value="<?php echo htmlspecialchars($settings['invoice_title'] ?? 'فاکتور هتل'); ?>">
                        </div>

                        <!-- Company Name -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-building me-1"></i>نام شرکت / آژانس</label>
                            <input type="text" name="invoice_company_name" class="form-control" value="<?php echo htmlspecialchars($settings['invoice_company_name'] ?? 'علاءالدین سفیر اسمان'); ?>">
                        </div>

                        <!-- Subtitle -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-text-left me-1"></i>زیرعنوان (توضیحات سربرگ)</label>
                            <input type="text" name="invoice_subtitle" class="form-control" value="<?php echo htmlspecialchars($settings['invoice_subtitle'] ?? 'آژانس مسافرتی'); ?>">
                        </div>

                        <!-- Logo URL -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-image me-1"></i>آدرس لوگو (URL)</label>
                            <input type="url" name="invoice_logo_url" class="form-control" placeholder="https://example.com/logo.png" value="<?php echo htmlspecialchars($settings['invoice_logo_url'] ?? ''); ?>">
                            <small class="text-muted">آدرس تصویر لوگوی سربرگ فاکتور (اختیاری)</small>
                        </div>

                        <!-- Default PS Note -->
                        <div class="col-12">
<label class="form-label text-muted small fw-medium"><i class="bi bi-pencil-square me-1"></i>متن پیش‌فرض «پینوشت»</label>
                            <textarea name="invoice_ps_note" class="form-control" rows="3"><?php echo htmlspecialchars($settings['invoice_ps_note'] ?? ''); ?></textarea>
                            <small class="text-muted">این متن به صورت پیش‌فرض در فیلد پینوشت فاکتور نمایش داده می‌شود.</small>
                        </div>

                        <!-- Default Notes -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-journal-text me-1"></i>متن پیش‌فرض «توضیحات»</label>
                            <textarea name="invoice_notes" class="form-control" rows="3"><?php echo htmlspecialchars($settings['invoice_notes'] ?? ''); ?></textarea>
                            <small class="text-muted">این متن به صورت پیش‌فرض در فرم ایجاد فاکتور نمایش داده می‌شود.</small>
                        </div>

                        <!-- Default Payment Terms -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-shield-check me-1"></i>متن پیش‌فرض «شرایط پرداخت»</label>
                            <textarea name="invoice_terms" class="form-control" rows="3"><?php echo htmlspecialchars($settings['invoice_terms'] ?? 'شرایط پرداخت: پرداخت نقدی یا انتقال بانکی.'); ?></textarea>
                            <small class="text-muted">این متن به صورت پیش‌فرض در فرم ایجاد فاکتور نمایش داده می‌شود.</small>
                        </div>

                        <!-- Default Footer Text -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-file-text me-1"></i>متن پیش‌فرض «متن فوتر فاکتور»</label>
                            <textarea name="invoice_footer_text" class="form-control" rows="3"><?php echo htmlspecialchars($settings['invoice_footer_text'] ?? 'این فاکتور به صورت الکترونیکی صادر شده است.'); ?></textarea>
                            <small class="text-muted">این متن در پایین فاکتور چاپی و صفحه پرداخت نمایش داده می‌شود.</small>
                        </div>

                        <!-- Colors -->
                        <div class="col-4">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-palette me-1"></i>رنگ اصلی</label>
                            <input type="color" name="invoice_primary_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($settings['invoice_primary_color'] ?? '#0d6efd'); ?>">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-palette me-1"></i>رنگ فرعی</label>
                            <input type="color" name="invoice_secondary_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($settings['invoice_secondary_color'] ?? '#6c757d'); ?>">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-palette me-1"></i>رنگ موفقیت</label>
                            <input type="color" name="invoice_success_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($settings['invoice_success_color'] ?? '#198754'); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-3 fw-bold">
                        <i class="bi bi-check-circle me-1"></i>ذخیره تنظیمات
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="fw-bold mb-3"><i class="bi bi-eye me-2 text-primary"></i>پیش‌نمایش</h6>
                <div class="border rounded p-3" style="background:#f8f9fa;">
                    <?php if (!empty($settings['invoice_logo_url'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['invoice_logo_url']); ?>" alt="لوگو" style="max-height:60px;margin-bottom:10px;">
                    <?php endif; ?>
                    <h5 style="color:<?php echo htmlspecialchars($settings['invoice_primary_color'] ?? '#0d6efd'); ?>;" class="fw-bold">
                        <?php echo htmlspecialchars($settings['invoice_title'] ?? 'فاکتور هتل'); ?>
                    </h5>
                    <div class="fw-bold" style="color:<?php echo htmlspecialchars($settings['invoice_primary_color'] ?? '#0d6efd'); ?>;">
                        <?php echo htmlspecialchars($settings['invoice_company_name'] ?? 'علاءالدین سفیر اسمان'); ?>
                    </div>
                    <small class="text-muted"><?php echo htmlspecialchars($settings['invoice_subtitle'] ?? 'آژانس مسافرتی'); ?></small>
                </div>
            </div>
        </div>
    </div>
</div>