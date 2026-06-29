<?php $config = $GLOBALS['app_config']; ?>
<?php $invSet = $invoiceSettings ?? []; ?>
<?php $primaryColor = $invSet['invoice_primary_color'] ?? '#0d6efd'; ?>
<?php $successColor = $invSet['invoice_success_color'] ?? '#198754'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice/create/<?php echo $invoice->deal_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
        <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2" style="color:<?php echo $primaryColor; ?>;"></i><?php echo htmlspecialchars($invoice->hotel_name); ?></h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice/print/<?php echo $invoice->id; ?>" class="btn btn-outline-success btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>چاپ</a>
        <a href="<?php echo $config['url']; ?>/hotel-invoice/create/<?php echo $invoice->deal_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <div class="text-center mb-4 pb-3 border-bottom">
                <?php if (!empty($invSet['invoice_logo_url'])): ?><img src="<?php echo htmlspecialchars($invSet['invoice_logo_url']); ?>" alt="لوگو" style="max-height:60px;margin-bottom:10px;"><?php endif; ?>
                <h4 class="fw-bold mb-1" style="color:<?php echo $primaryColor; ?>;"><?php echo htmlspecialchars($invSet['invoice_title'] ?? 'فاکتور هتل'); ?></h4>
                <small class="text-muted">شماره فاکتور: #<?php echo $invoice->id; ?></small>
                <br>
                <?php
                $statusLabels = ['draft'=>'پیش‌نویس','final'=>'نهایی','paid'=>'پرداخت شده','cancelled'=>'لغو شده'];
                $statusColors = ['draft'=>'bg-warning text-dark','final'=>'bg-success','paid'=>'bg-info','cancelled'=>'bg-danger'];
                $st = $invoice->invoice_status;
                ?>
                <span class="badge <?php echo $statusColors[$st] ?? 'bg-secondary'; ?>"><?php echo $statusLabels[$st] ?? $st; ?></span>
                <?php if (!empty($invoice->invoice_type)): ?>
                <span class="badge <?php echo $invoice->invoice_type=='confirmed'?'bg-primary':'bg-secondary'; ?>"><?php echo $invoice->invoice_type=='confirmed'?'فاکتور تایید شده':'پیش فاکتور'; ?></span>
                <?php endif; ?>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">معامله</small><strong class="small"><?php echo htmlspecialchars($invoice->deal_title); ?></strong></div></div>
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">مخاطب</small><strong class="small"><?php echo htmlspecialchars($invoice->contact_name ?? '-'); ?></strong></div></div>
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">هتل</small><strong class="small"><?php echo htmlspecialchars($invoice->hotel_name); ?></strong></div></div>
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">تلفن</small><strong class="small" dir="ltr"><?php echo htmlspecialchars($invoice->contact_phone ?? '-'); ?></strong></div></div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">تاریخ ورود</small><strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></strong></div></div>
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">تاریخ خروج</small><strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></strong></div></div>
                <div class="col-4"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">شب‌ها</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->nights; ?></strong></div></div>
                <div class="col-4"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">بزرگسال</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->adults_count ?? 0; ?></strong></div></div>
                <div class="col-4"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">کودک 3-5</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->children_3to5_count ?? 0; ?></strong></div></div>
            </div>

            <div class="bg-light rounded p-3 mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-cash me-2"></i>جزییات مالی</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">قیمت هر نفر هر شب</td><td class="text-start fw-bold"><?php echo number_format($invoice->price_per_person_night); ?> تومان</td></tr>
                    <?php if ($invoice->new_price_per_person_night): ?>
                    <tr><td class="text-muted">قیمت جدید</td><td class="text-start fw-bold text-warning"><?php echo number_format($invoice->new_price_per_person_night); ?> تومان</td></tr>
                    <?php endif; ?>
                    <tr><td class="text-muted">مبلغ کل</td><td class="text-start fw-bold"><?php echo number_format($invoice->total_amount); ?> تومان</td></tr>
                    <tr><td class="text-muted">تخفیف (<?php echo $invoice->discount_percent; ?>%)</td><td class="text-start fw-bold text-danger">- <?php echo number_format($invoice->discount_amount); ?> تومان</td></tr>
                    <tr class="border-top border-2"><td class="fw-bold fs-6">مبلغ نهایی</td><td class="text-start fw-bold fs-5" style="color:<?php echo $successColor; ?>;"><?php echo number_format($invoice->final_amount); ?> تومان</td></tr>
                    <?php if (($invoice->deposit_amount ?? 0) > 0): ?>
                    <tr><td class="text-muted"><i class="bi bi-wallet2 me-1"></i>بیعانه</td><td class="text-start fw-bold"><?php echo number_format($invoice->deposit_amount); ?> تومان</td></tr>
                    <?php endif; ?>
                </table>
            </div>

            <?php if ($invoice->notes): ?>
            <div class="mb-3"><small class="text-muted d-block mb-1"><i class="bi bi-journal-text me-1"></i>توضیحات</small><p class="small mb-0"><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></p></div>
            <?php endif; ?>

            <div class="d-flex gap-2 flex-wrap">
                <?php if ($invoice->invoice_status !== 'final' && $invoice->invoice_status !== 'paid'): ?><button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $invoice->id; ?>, 'final')"><i class="bi bi-check-circle me-1"></i>نهایی کردن</button><?php endif; ?>
                <?php if ($invoice->invoice_status !== 'cancelled' && $invoice->invoice_status !== 'paid'): ?><button class="btn btn-sm btn-outline-danger" onclick="updateStatus(<?php echo $invoice->id; ?>, 'cancelled')"><i class="bi bi-x-circle me-1"></i>لغو</button><?php endif; ?>
                <a href="<?php echo $config['url']; ?>/hotel-invoice/edit/<?php echo $invoice->id; ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>ویرایش</a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(<?php echo $invoice->id; ?>)"><i class="bi bi-trash me-1"></i>حذف</button>
            </div>
        </div></div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="rounded-3 p-4 mb-3" style="background:<?php echo $successColor; ?>18;">
                    <small class="text-muted d-block">مبلغ نهایی</small>
                    <strong style="color:<?php echo $successColor; ?>;font-size:28px;"><?php echo number_format($invoice->final_amount); ?></strong>
                    <br><small class="text-muted">تومان</small>
                    <?php if (!empty($invoice->invoice_type)): ?>
                    <br><small class="text-muted"><?php echo $invoice->invoice_type=='confirmed'?'<span class="text-primary">فاکتور تایید شده</span>':'<span class="text-secondary">پیش فاکتور</span>'; ?></small>
                    <?php endif; ?>
                    <?php if (($invoice->deposit_amount ?? 0) > 0): ?>
                    <br><small class="text-warning">بیعانه: <?php echo number_format($invoice->deposit_amount); ?> تومان</small>
                    <?php endif; ?>
                </div>
                <?php if ($invoice->invoice_status !== 'paid' && $invoice->invoice_status !== 'cancelled' && !empty($invoice->payment_token)): ?>
                <a href="<?php echo $config['url']; ?>/hotel-pay/<?php echo htmlspecialchars($invoice->payment_token); ?>" class="btn w-100 fw-bold mt-2" style="background:<?php echo $successColor; ?>;color:#fff;" target="_blank"><i class="bi bi-credit-card me-1"></i>لینک پرداخت</a>
                <?php endif; ?>
                <a href="<?php echo $config['url']; ?>/hotel-invoice/print/<?php echo $invoice->id; ?>" class="btn w-100 fw-bold" style="background:<?php echo $primaryColor; ?>;color:#fff;" target="_blank"><i class="bi bi-printer me-1"></i>چاپ فاکتور</a>
            </div>
        </div>

        <?php if (!empty($payments)): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-credit-card me-2" style="color:<?php echo $primaryColor; ?>;"></i>لینک‌های پرداخت</h6></div>
            <div class="card-body">
                <?php foreach($payments as $p): ?>
                <div class="bg-light rounded p-2 mb-2">
                    <strong class="small"><?php echo number_format($p->amount); ?> تومان</strong>
                    <br><small class="text-muted"><?php echo \Core\JDate::displayDate($p->created_at); ?></small>
                    <span class="badge <?php echo $p->status=='success'?'bg-success':($p->status=='pending'?'bg-warning text-dark':'bg-danger'); ?>" style="font-size:10px;"><?php echo $p->status=='success'?'موفق':($p->status=='pending'?'در انتظار':'ناموفق'); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateStatus(id, status) {
    var msg = status === 'final' ? 'آیا فاکتور را نهایی می‌کنید؟' : 'آیا فاکتور را لغو می‌کنید؟';
    if (!confirm(msg)) return;
    var fd = new FormData(); fd.append('status', status);
    fetch(CRM_BASE_URL + '/hotel-invoice/status/' + id, {method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd})
    .then(function(r){return r.json();}).then(function(d){if(d.success){location.reload();}else{alert(d.message||'خطا');}}).catch(function(){alert('خطای شبکه');});
}
function deleteInvoice(id) {
    if (!confirm('آیا از حذف فاکتور مطمئن هستید؟')) return;
    fetch(CRM_BASE_URL + '/hotel-invoice/delete/' + id, {method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.json();}).then(function(d){if(d.success){window.location.href=CRM_BASE_URL+'/hotel-invoice/create/<?php echo $invoice->deal_id; ?>';}else{alert(d.message||'خطا');}}).catch(function(){alert('خطای شبکه');});
}
</script>