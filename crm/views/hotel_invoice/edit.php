<?php $config = $GLOBALS['app_config']; ?>
<?php $invSet = $invoiceSettings ?? []; ?>
<?php $primaryColor = $invSet['invoice_primary_color'] ?? '#0d6efd'; ?>
<?php $successColor = $invSet['invoice_success_color'] ?? '#198754'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice/view/<?php echo $invoice->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
        <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2" style="color:<?php echo $primaryColor; ?>;"></i>ویرایش فاکتور هتل</h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list me-1"></i>لیست فاکتورها</a>
        <a href="<?php echo $config['url']; ?>/hotel-invoice/view/<?php echo $invoice->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<!-- Deal Info -->
<div class="card mb-3"><div class="card-body">
    <div class="d-flex gap-3 p-3 bg-light rounded-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width:48px;height:48px;background:<?php echo $primaryColor; ?>;">
            <i class="bi bi-briefcase fs-4"></i>
        </div>
        <div>
            <strong class="d-block"><?php echo htmlspecialchars($deal->title); ?></strong>
            <small class="text-muted"><?php echo htmlspecialchars($deal->contact_name ?? ''); ?> | <?php echo htmlspecialchars($deal->contact_phone ?? ''); ?></small>
        </div>
    </div>
</div></div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-receipt me-2" style="color:<?php echo $primaryColor; ?>;"></i>ویرایش فاکتور #<?php echo $invoice->id; ?></h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo $config['url']; ?>/hotel-invoice/update/<?php echo $invoice->id; ?>" id="invoiceForm" data-ajax="true">
                    <input type="hidden" name="deal_id" value="<?php echo $invoice->deal_id; ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-building me-1"></i>نام هتل <span class="text-danger">*</span></label>
                            <input type="text" name="hotel_name" class="form-control" value="<?php echo htmlspecialchars($invoice->hotel_name); ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-plus me-1"></i>تاریخ ورود <span class="text-danger">*</span></label>
                            <input type="date" name="check_in_date" class="form-control" id="checkInDate" value="<?php echo $invoice->check_in_date; ?>" required onchange="calculateInvoice()">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-minus me-1"></i>تاریخ خروج <span class="text-danger">*</span></label>
                            <input type="date" name="check_out_date" class="form-control" id="checkOutDate" value="<?php echo $invoice->check_out_date; ?>" required onchange="calculateInvoice()">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-file-earmark me-1"></i>نوع فاکتور</label>
                            <select name="invoice_type" class="form-select" id="invoiceType">
                                <option value="proforma" <?php echo $invoice->invoice_type=='proforma'?'selected':''; ?>>پیش فاکتور</option>
                                <option value="confirmed" <?php echo $invoice->invoice_type=='confirmed'?'selected':''; ?>>فاکتور تایید شده</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-tag me-1"></i>وضعیت فاکتور</label>
                            <select name="invoice_status" class="form-select" id="invoiceStatus">
                                <option value="draft" <?php echo $invoice->invoice_status=='draft'?'selected':''; ?>>پیش‌نویس</option>
                                <option value="final" <?php echo $invoice->invoice_status=='final'?'selected':''; ?>>نهایی</option>
                                <option value="paid" <?php echo $invoice->invoice_status=='paid'?'selected':''; ?>>پرداخت شده</option>
                                <option value="cancelled" <?php echo $invoice->invoice_status=='cancelled'?'selected':''; ?>>لغو شده</option>
                            </select>
                        </div>
                        <div class="col-12"><hr class="my-1"><small class="text-muted fw-bold">تعداد نفرات</small></div>
                        <div class="col-4">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-person me-1"></i>بزرگسال (کامل)</label>
                            <input type="number" name="adults_count" class="form-control" id="adultsCount" value="<?php echo $invoice->adults_count ?? 0; ?>" min="0" onchange="calculateInvoice()">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-person me-1"></i>کودک 3-5 سال (نیم بها)</label>
                            <input type="number" name="children_3to5_count" class="form-control" id="children3to5Count" value="<?php echo $invoice->children_3to5_count ?? 0; ?>" min="0" onchange="calculateInvoice()">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-person me-1"></i>کودک زیر 3 سال (رایگان)</label>
                            <input type="number" name="children_under3_count" class="form-control" id="childrenUnder3Count" value="<?php echo $invoice->children_under3_count ?? 0; ?>" min="0" onchange="calculateInvoice()">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-currency-dollar me-1"></i>قیمت هر نفر هر شب (تومان) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_person_night" class="form-control" id="pricePerPersonNight" value="<?php echo $invoice->price_per_person_night; ?>" min="0" required onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-tag me-1"></i>قیمت جدید هر نفر هر شب (تومان)</label>
                            <input type="number" name="new_price_per_person_night" class="form-control" id="newPricePerPersonNight" placeholder="اختیاری" min="0" value="<?php echo $invoice->new_price_per_person_night ?? ''; ?>" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                            <small class="text-muted">درصد تخفیف خودکار محاسبه می‌شود</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-wallet2 me-1"></i>مبلغ بیعانه (تومان)</label>
                            <input type="number" name="deposit_amount" class="form-control" id="depositAmount" value="<?php echo $invoice->deposit_amount ?? 0; ?>" min="0" dir="ltr" style="text-align:left;">
                            <small class="text-muted">در صورت پر بودن، لینک پرداخت فقط به مبلغ بیعانه ایجاد می‌شود</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-journal-text me-1"></i>توضیحات</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="توضیحات اختیاری"><?php echo htmlspecialchars($invoice->notes ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="ajax-error alert alert-danger d-none mt-2 small p-2"></div>
                    <button type="submit" class="btn w-100 mt-3 fw-bold" style="background:<?php echo $primaryColor; ?>;color:#fff;">
                        <i class="bi bi-check-circle me-1"></i>بروزرسانی فاکتور
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-calculator me-2" style="color:<?php echo $primaryColor; ?>;"></i>خلاصه محاسبات</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">تعداد شب‌ها</small><strong class="fs-5" style="color:<?php echo $primaryColor; ?>;" id="calcNights"><?php echo $invoice->nights; ?></strong></div></div>
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">نفر-شب</small><strong class="fs-5" style="color:<?php echo $primaryColor; ?>;" id="calcPersonNights"><?php echo $invoice->person_night_count; ?></strong></div></div>
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">بزرگسال</small><strong style="color:<?php echo $primaryColor; ?>;" id="calcAdults"><?php echo $invoice->adults_count ?? 0; ?></strong></div></div>
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">کودک 3-5</small><strong style="color:<?php echo $primaryColor; ?>;" id="calcChildren3to5"><?php echo $invoice->children_3to5_count ?? 0; ?></strong></div></div>
                    <div class="col-12">
                        <div class="bg-light rounded p-3">
                            <div class="d-flex justify-content-between mb-1"><small class="text-muted">قیمت هر نفر هر شب</small><strong id="calcPricePerPersonNight"><?php echo number_format($invoice->price_per_person_night); ?></strong></div>
                            <div class="d-flex justify-content-between mb-1"><small class="text-muted">قیمت جدید هر نفر هر شب</small><strong id="calcNewPrice" class="text-warning"><?php echo $invoice->new_price_per_person_night ? number_format($invoice->new_price_per_person_night) : '-'; ?></strong></div>
                            <div class="d-flex justify-content-between mb-1"><small class="text-muted">مبلغ کل</small><strong id="calcOriginalTotal" style="color:<?php echo $primaryColor; ?>;"><?php echo number_format($invoice->total_amount + ($invoice->discount_amount ?? 0)); ?> تومان</strong></div>
                            <div class="d-flex justify-content-between mb-1"><small class="text-muted">تخفیف (<span id="calcDiscountPct"><?php echo $invoice->discount_percent ?? 0; ?></span>%)</small><strong class="text-danger" id="calcDiscountAmount"><?php echo number_format($invoice->discount_amount ?? 0); ?> تومان</strong></div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between"><strong>مبلغ نهایی</strong><strong style="color:<?php echo $successColor; ?>;" class="fs-5" id="calcFinalAmount"><?php echo number_format($invoice->final_amount); ?> تومان</strong></div>
                            <div class="d-flex justify-content-between mt-1"><small class="text-muted">نوع فاکتور</small><strong id="calcInvoiceType"><?php echo $invoice->invoice_type=='confirmed'?'فاکتور تایید شده':'پیش فاکتور'; ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateInvoice() {
    var checkIn = document.getElementById('checkInDate').value;
    var checkOut = document.getElementById('checkOutDate').value;
    var adults = parseInt(document.getElementById('adultsCount').value) || 0;
    var children3to5 = parseInt(document.getElementById('children3to5Count').value) || 0;
    var childrenUnder3 = parseInt(document.getElementById('childrenUnder3Count').value) || 0;
    var price = parseFloat(document.getElementById('pricePerPersonNight').value) || 0;
    var newPrice = parseFloat(document.getElementById('newPricePerPersonNight').value) || 0;

    var nights = 0;
    if (checkIn && checkOut) {
        var d1 = new Date(checkIn);
        var d2 = new Date(checkOut);
        nights = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
        if (isNaN(nights) || nights < 0) nights = 0;
    }

    var totalPersons = adults + children3to5 + childrenUnder3;
    var personNights = totalPersons * nights;

    var originalTotal = (adults * nights * price) + (children3to5 * nights * price * 0.5);
    var discountAmount = 0;
    var discountPct = 0;
    var finalAmount = originalTotal;

    if (newPrice > 0 && originalTotal > 0) {
        var newTotal = (adults * nights * newPrice) + (children3to5 * nights * newPrice * 0.5);
        discountAmount = originalTotal - newTotal;
        if (discountAmount < 0) discountAmount = 0;
        discountPct = originalTotal > 0 ? Math.round((discountAmount / originalTotal) * 100 * 100) / 100 : 0;
        finalAmount = newTotal;
    }

    document.getElementById('calcNights').textContent = nights;
    document.getElementById('calcPersonNights').textContent = personNights;
    document.getElementById('calcAdults').textContent = adults;
    document.getElementById('calcChildren3to5').textContent = children3to5;
    document.getElementById('calcPricePerPersonNight').textContent = formatNumber(price) + ' تومان';
    document.getElementById('calcNewPrice').textContent = newPrice > 0 ? formatNumber(newPrice) + ' تومان' : '-';
    document.getElementById('calcOriginalTotal').textContent = formatNumber(originalTotal) + ' تومان';
    document.getElementById('calcDiscountPct').textContent = discountPct;
    document.getElementById('calcDiscountAmount').textContent = formatNumber(discountAmount) + ' تومان';
    document.getElementById('calcFinalAmount').textContent = formatNumber(finalAmount) + ' تومان';
    
    var invoiceType = document.getElementById('invoiceType');
    var calcInvoiceType = document.getElementById('calcInvoiceType');
    if (invoiceType && calcInvoiceType) {
        calcInvoiceType.textContent = invoiceType.value === 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور';
    }
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

document.addEventListener('DOMContentLoaded', function() { 
    calculateInvoice();
    var invoiceType = document.getElementById('invoiceType');
    if (invoiceType) {
        invoiceType.addEventListener('change', function() {
            var calcInvoiceType = document.getElementById('calcInvoiceType');
            if (calcInvoiceType) {
                calcInvoiceType.textContent = this.value === 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور';
            }
        });
    }
});
</script>