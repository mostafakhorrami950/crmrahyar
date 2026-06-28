<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
        <h5 class="fw-bold mb-0"><i class="bi bi-building me-2 text-primary"></i>فاکتور هتل</h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت به معامله</a>
    </div>
</div>

<!-- Deal Info -->
<div class="card mb-3"><div class="card-body">
    <div class="d-flex gap-3 p-3 bg-light rounded-3">
        <div class="rounded-3 bg-primary d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width:48px;height:48px;">
            <i class="bi bi-briefcase fs-4"></i>
        </div>
        <div>
            <strong class="d-block"><?php echo htmlspecialchars($deal->title); ?></strong>
            <small class="text-muted"><?php echo htmlspecialchars($deal->contact_name ?? ''); ?> | <?php echo htmlspecialchars($deal->contact_phone ?? ''); ?></small>
            <?php if ($deal->travel_date_from && $deal->travel_date_to): ?>
            <br><small class="text-primary"><i class="bi bi-calendar me-1"></i><?php echo \Core\JDate::displayDate($deal->travel_date_from); ?> تا <?php echo \Core\JDate::displayDate($deal->travel_date_to); ?></small>
            <?php endif; ?>
            <?php if ($deal->passengers_count): ?>
            <small class="text-muted me-2"><i class="bi bi-people me-1"></i><?php echo $deal->passengers_count; ?> نفر</small>
            <?php endif; ?>
        </div>
    </div>
</div></div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <!-- Invoice Form -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>ایجاد فاکتور هتل</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo $config['url']; ?>/hotel-invoice/store" id="invoiceForm" data-ajax="true">
                    <input type="hidden" name="deal_id" value="<?php echo $deal->id; ?>">

                    <div class="row g-3">
                        <!-- Hotel Name -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-building me-1"></i>نام هتل <span class="text-danger">*</span></label>
                            <input type="text" name="hotel_name" class="form-control" placeholder="نام هتل را وارد کنید" required>
                        </div>

                        <!-- Check-in / Check-out -->
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-plus me-1"></i>تاریخ ورود <span class="text-danger">*</span></label>
                            <input type="date" name="check_in_date" class="form-control" id="checkInDate" required onchange="calculateInvoice()">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-minus me-1"></i>تاریخ خروج <span class="text-danger">*</span></label>
                            <input type="date" name="check_out_date" class="form-control" id="checkOutDate" required onchange="calculateInvoice()">
                        </div>

                        <!-- Persons Count -->
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-people me-1"></i>تعداد نفرات <span class="text-danger">*</span></label>
                            <input type="number" name="persons_count" class="form-control" id="personsCount" value="<?php echo $deal->passengers_count ?? 1; ?>" min="1" required onchange="calculateInvoice()">
                        </div>

                        <!-- Price per person per night -->
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-currency-dollar me-1"></i>قیمت هر نفر هر شب (تومان) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_person_night" class="form-control" id="pricePerPersonNight" value="0" min="0" required onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                        </div>

                        <!-- New Price (discounted) -->
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-tag me-1"></i>قیمت جدید هر نفر هر شب (تومان)</label>
                            <input type="number" name="new_price_per_person_night" class="form-control" id="newPricePerPersonNight" placeholder="اختیاری - قیمت جدید" min="0" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                        </div>

                        <!-- Discount Percent -->
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-percent me-1"></i>درصد تخفیف</label>
                            <input type="number" name="discount_percent" class="form-control" id="discountPercent" value="20" min="0" max="100" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-journal-text me-1"></i>توضیحات</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="توضیحات اختیاری"></textarea>
                        </div>
                    </div>

                    <div class="ajax-error alert alert-danger d-none mt-2 small p-2"></div>

                    <button type="submit" class="btn btn-primary w-100 mt-3 fw-bold" id="submitInvoiceBtn">
                        <i class="bi bi-check-circle me-1"></i>صدور فاکتور
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <!-- Calculation Summary -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-calculator me-2 text-primary"></i>خلاصه محاسبات</h6>
            </div>
            <div class="card-body">
                <div id="calcSummary">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block" style="font-size:11px;">تعداد شب‌ها</small>
                                <strong class="fs-5 text-primary" id="calcNights">0</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block" style="font-size:11px;">نفر-شب</small>
                                <strong class="fs-5 text-primary" id="calcPersonNights">0</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-light rounded p-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">قیمت هر نفر هر شب</small>
                                    <strong id="calcPricePerPersonNight">0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">قیمت جدید هر نفر هر شب</small>
                                    <strong id="calcNewPrice" class="text-warning">-</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">مبلغ کل</small>
                                    <strong id="calcTotalAmount" class="text-primary">0 تومان</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">تخفیف (<span id="calcDiscountPct">20</span>%)</small>
                                    <strong class="text-danger" id="calcDiscountAmount">0 تومان</strong>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <strong class="text-dark">مبلغ نهایی</strong>
                                    <strong class="text-success fs-5" id="calcFinalAmount">0 تومان</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Existing Invoices -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>فاکتورهای قبلی</h6>
            </div>
            <div class="card-body">
                <?php if (empty($invoices)): ?>
                <div class="text-center text-muted py-3">
                    <i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i>
                    <p class="small">هنوز فاکتوری ثبت نشده.</p>
                </div>
                <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($invoices as $inv): ?>
                    <div class="bg-light rounded p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="small"><?php echo htmlspecialchars($inv->hotel_name); ?></strong>
                                <br><small class="text-muted"><?php echo \Core\JDate::displayDate($inv->check_in_date); ?> تا <?php echo \Core\JDate::displayDate($inv->check_out_date); ?></small>
                                <br><small class="text-muted"><?php echo $inv->persons_count; ?> نفر | <?php echo $inv->nights; ?> شب | <?php echo $inv->person_night_count; ?> نفر-شب</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success"><?php echo number_format($inv->final_amount); ?> تومان</strong>
                                <br><span class="badge <?php echo $inv->invoice_status=='final'?'bg-success':($inv->invoice_status=='cancelled'?'bg-danger':'bg-warning text-dark'); ?>" style="font-size:10px;">
                                    <?php echo $inv->invoice_status=='final'?'نهایی':($inv->invoice_status=='cancelled'?'لغو شده':'پیش‌نویس'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex gap-1 mt-2">
                            <a href="<?php echo $config['url']; ?>/hotel-invoice/view/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 8px;"><i class="bi bi-eye me-1"></i>مشاهده</a>
                            <a href="<?php echo $config['url']; ?>/hotel-invoice/print/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-success" style="font-size:11px;padding:2px 8px;" target="_blank"><i class="bi bi-printer me-1"></i>چاپ</a>
                            <button type="button" class="btn btn-sm btn-outline-danger" style="font-size:11px;padding:2px 8px;" onclick="deleteInvoice(<?php echo $inv->id; ?>)"><i class="bi bi-trash me-1"></i>حذف</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function calculateInvoice() {
    var checkIn = document.getElementById('checkInDate').value;
    var checkOut = document.getElementById('checkOutDate').value;
    var persons = parseInt(document.getElementById('personsCount').value) || 0;
    var price = parseFloat(document.getElementById('pricePerPersonNight').value) || 0;
    var newPrice = parseFloat(document.getElementById('newPricePerPersonNight').value) || 0;
    var discountPct = parseFloat(document.getElementById('discountPercent').value) || 0;

    var nights = 0;
    if (checkIn && checkOut) {
        var d1 = new Date(checkIn);
        var d2 = new Date(checkOut);
        var diff = d2 - d1;
        nights = Math.ceil(diff / (1000 * 60 * 60 * 24));
        if (nights < 0) nights = 0;
    }

    var personNights = persons * nights;
    var totalAmount = personNights * (newPrice > 0 ? newPrice : price);

    var discountAmount = totalAmount * (discountPct / 100);
    var finalAmount = totalAmount - discountAmount;

    document.getElementById('calcNights').textContent = nights;
    document.getElementById('calcPersonNights').textContent = personNights;
    document.getElementById('calcPricePerPersonNight').textContent = formatNumber(price) + ' تومان';
    document.getElementById('calcNewPrice').textContent = newPrice > 0 ? formatNumber(newPrice) + ' تومان' : '-';
    document.getElementById('calcTotalAmount').textContent = formatNumber(totalAmount) + ' تومان';
    document.getElementById('calcDiscountPct').textContent = discountPct;
    document.getElementById('calcDiscountAmount').textContent = formatNumber(discountAmount) + ' تومان';
    document.getElementById('calcFinalAmount').textContent = formatNumber(finalAmount) + ' تومان';
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function deleteInvoice(id) {
    if (!confirm('آیا از حذف فاکتور مطمئن هستید؟')) return;
    fetch(CRM_BASE_URL + '/hotel-invoice/delete/' + id, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.message || 'خطا در حذف فاکتور'); }
    })
    .catch(function() { alert('خطای شبکه'); });
}

// Initial calculation on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateInvoice();
});
</script>