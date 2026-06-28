<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice/create/<?php echo $invoice->deal_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
        <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>فاکتور هتل: <?php echo htmlspecialchars($invoice->hotel_name); ?></h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice/print/<?php echo $invoice->id; ?>" class="btn btn-outline-success btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>چاپ</a>
        <a href="<?php echo $config['url']; ?>/hotel-invoice/create/<?php echo $invoice->deal_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <!-- Invoice Header -->
                <div class="text-center mb-4 pb-3 border-bottom">
                    <h4 class="fw-bold text-primary mb-1"><i class="bi bi-building me-2"></i>فاکتور هتل</h4>
                    <small class="text-muted">شماره فاکتور: #<?php echo $invoice->id; ?></small>
                    <br><span class="badge <?php echo $invoice->invoice_status=='final'?'bg-success':($invoice->invoice_status=='cancelled'?'bg-danger':'bg-warning text-dark'); ?>">
                        <?php echo $invoice->invoice_status=='final'?'نهایی':($invoice->invoice_status=='cancelled'?'لغو شده':'پیش‌نویس'); ?>
                    </span>
                </div>

                <!-- Deal & Contact Info -->
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-briefcase me-1"></i>معامله</small>
                            <strong class="small"><?php echo htmlspecialchars($invoice->deal_title); ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-person me-1"></i>مخاطب</small>
                            <strong class="small"><?php echo htmlspecialchars($invoice->contact_name ?? '-'); ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-building me-1"></i>هتل</small>
                            <strong class="small"><?php echo htmlspecialchars($invoice->hotel_name); ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-telephone me-1"></i>تلفن</small>
                            <strong class="small" dir="ltr"><?php echo htmlspecialchars($invoice->contact_phone ?? '-'); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Dates & Calculation Details -->
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-calendar-plus me-1"></i>تاریخ ورود</small>
                            <strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-calendar-minus me-1"></i>تاریخ خروج</small>
                            <strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-moon me-1"></i>تعداد شب‌ها</small>
                            <strong class="text-primary"><?php echo $invoice->nights; ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-people me-1"></i>تعداد نفرات</small>
                            <strong class="text-primary"><?php echo $invoice->persons_count; ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-calculator me-1"></i>نفر-شب</small>
                            <strong class="text-primary"><?php echo $invoice->person_night_count; ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Financial Details -->
                <div class="bg-light rounded p-3 mb-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-cash me-2"></i>جزییات مالی</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">قیمت هر نفر هر شب</td>
                            <td class="text-start fw-bold"><?php echo number_format($invoice->price_per_person_night); ?> تومان</td>
                        </tr>
                        <?php if ($invoice->new_price_per_person_night): ?>
                        <tr>
                            <td class="text-muted">قیمت جدید هر نفر هر شب</td>
                            <td class="text-start fw-bold text-warning"><?php echo number_format($invoice->new_price_per_person_night); ?> تومان</td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="text-muted">مبلغ کل</td>
                            <td class="text-start fw-bold"><?php echo number_format($invoice->total_amount); ?> تومان</td>
                        </tr>
                        <tr>
                            <td class="text-muted">تخفیف (<?php echo $invoice->discount_percent; ?>%)</td>
                            <td class="text-start fw-bold text-danger">- <?php echo number_format($invoice->discount_amount); ?> تومان</td>
                        </tr>
                        <tr class="border-top border-2">
                            <td class="fw-bold fs-6">مبلغ نهایی</td>
                            <td class="text-start fw-bold fs-5 text-success"><?php echo number_format($invoice->final_amount); ?> تومان</td>
                        </tr>
                    </table>
                </div>

                <?php if ($invoice->notes): ?>
                <div class="mb-3">
                    <small class="text-muted d-block mb-1"><i class="bi bi-journal-text me-1"></i>توضیحات</small>
                    <p class="small mb-0"><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></p>
                </div>
                <?php endif; ?>

                <!-- Status Actions -->
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($invoice->invoice_status !== 'final'): ?>
                    <button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $invoice->id; ?>, 'final')"><i class="bi bi-check-circle me-1"></i>نهایی کردن</button>
                    <?php endif; ?>
                    <?php if ($invoice->invoice_status !== 'cancelled'): ?>
                    <button class="btn btn-sm btn-outline-danger" onclick="updateStatus(<?php echo $invoice->id; ?>, 'cancelled')"><i class="bi bi-x-circle me-1"></i>لغو</button>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(<?php echo $invoice->id; ?>)"><i class="bi bi-trash me-1"></i>حذف</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded-3 p-4 mb-3">
                    <small class="text-muted d-block">مبلغ نهایی</small>
                    <strong class="text-success" style="font-size:28px;"><?php echo number_format($invoice->final_amount); ?></strong>
                    <br><small class="text-muted">تومان</small>
                </div>
                <a href="<?php echo $config['url']; ?>/hotel-invoice/print/<?php echo $invoice->id; ?>" class="btn btn-success w-100 fw-bold" target="_blank">
                    <i class="bi bi-printer me-1"></i>چاپ فاکتور
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(id, status) {
    var msg = status === 'final' ? 'آیا فاکتور را نهایی می‌کنید؟' : 'آیا فاکتور را لغو می‌کنید؟';
    if (!confirm(msg)) return;
    var formData = new FormData();
    formData.append('status', status);
    fetch(CRM_BASE_URL + '/hotel-invoice/status/' + id, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.message || 'خطا'); }
    })
    .catch(function() { alert('خطای شبکه'); });
}

function deleteInvoice(id) {
    if (!confirm('آیا از حذف فاکتور مطمئن هستید؟')) return;
    fetch(CRM_BASE_URL + '/hotel-invoice/delete/' + id, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.href = CRM_BASE_URL + '/hotel-invoice/create/' + '<?php echo $invoice->deal_id; ?>';
        }
        else { alert(data.message || 'خطا در حذف فاکتور'); }
    })
    .catch(function() { alert('خطای شبکه'); });
}
</script>