<?php $config = $GLOBALS['app_config']; ?>
<?php $invSet = $invoiceSettings ?? []; ?>
<?php $primaryColor = $invSet['invoice_primary_color'] ?? '#0d6efd'; ?>
<?php $successColor = $invSet['invoice_success_color'] ?? '#198754'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
        <h5 class="fw-bold mb-0"><i class="bi bi-building me-2" style="color:<?php echo $primaryColor; ?>;"></i><?php echo htmlspecialchars($invSet['invoice_title'] ?? 'فاکتور رزرو هتل'); ?></h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list me-1"></i>لیست فاکتورها</a>
        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
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

<form method="POST" action="<?php echo $config['url']; ?>/hotel-invoice/store" id="invoiceForm" data-ajax="true">
<input type="hidden" name="deal_id" value="<?php echo $deal->id; ?>">

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <!-- Hotel & Guest Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-building me-2" style="color:<?php echo $primaryColor; ?>;"></i>اطلاعات هتل و میهمان</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-building me-1"></i>نام هتل <span class="text-danger">*</span></label>
                        <input type="text" name="hotel_name" class="form-control" placeholder="نام هتل را وارد کنید" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-person me-1"></i>نام میهمان</label>
                        <input type="text" name="guest_name" class="form-control" placeholder="نام میهمان">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-phone me-1"></i>تلفن میهمان</label>
                        <input type="text" name="guest_phone" class="form-control" placeholder="تلفن میهمان" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-door-open me-1"></i>نوع اتاق</label>
                        <input type="text" name="room_type" class="form-control" placeholder="مثلاً: سوئیت دو نفره">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-hash me-1"></i>شماره اتاق</label>
                        <input type="text" name="room_number" class="form-control" placeholder="شماره اتاق">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-cup me-1"></i>نوع وعده غذایی</label>
                        <select name="meal_plan" class="form-select">
                            <option value="">انتخاب کنید</option>
                            <option value="BB">BB - صبحانه</option>
                            <option value="HB">HB - صبحانه و شام</option>
                            <option value="FB">FB - سه وعده</option>
                            <option value="AI">AI - همه‌چیز شامل</option>
                            <option value="UAI">UAI - بدون محدودیت</option>
                            <option value="RO">RO - بدون وعده</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-plus me-1"></i>تاریخ ورود <span class="text-danger">*</span></label>
                        <input type="date" name="check_in_date" class="form-control" id="checkInDate" required onchange="calculateInvoice()">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-minus me-1"></i>تاریخ خروج <span class="text-danger">*</span></label>
                        <input type="date" name="check_out_date" class="form-control" id="checkOutDate" required onchange="calculateInvoice()">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-file-earmark me-1"></i>نوع فاکتور</label>
                        <select name="invoice_type" class="form-select" id="invoiceType">
                            <option value="proforma">پیش فاکتور</option>
                            <option value="confirmed">فاکتور تایید شده</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-tag me-1"></i>وضعیت فاکتور</label>
                        <select name="invoice_status" class="form-select" id="invoiceStatus">
                            <option value="draft">پیش‌نویس</option>
                            <option value="final">نهایی</option>
                        </select>
                    </div>
                    <div class="col-12"><hr class="my-1"><small class="text-muted fw-bold">خدمات</small></div>
                    <div class="col-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="transfer_included" id="transferIncluded" value="1">
                            <label class="form-check-label small" for="transferIncluded"><i class="bi bi-car-front me-1"></i>ترانسفر</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="visa_included" id="visaIncluded" value="1">
                            <label class="form-check-label small" for="visaIncluded"><i class="bi bi-passport me-1"></i>ویزا</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="insurance_included" id="insuranceIncluded" value="1">
                            <label class="form-check-label small" for="insuranceIncluded"><i class="bi bi-shield-check me-1"></i>بیمه</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-list-check me-1"></i>خدمات اضافی</label>
                        <textarea name="extra_services" class="form-control" rows="2" placeholder="خدمات اضافی (اختیاری)"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-list-ol me-2" style="color:<?php echo $primaryColor; ?>;"></i>آیتم‌های فاکتور</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()"><i class="bi bi-plus me-1"></i>افزودن آیتم</button>
            </div>
            <div class="card-body">
                <div id="itemsContainer">
                    <div class="item-row row g-2 mb-2 align-items-end">
                        <div class="col-5">
                            <label class="form-label text-muted small">شرح</label>
                            <input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="شرح آیتم" required>
                        </div>
                        <div class="col-2">
                            <label class="form-label text-muted small">تعداد</label>
                            <input type="number" name="item_quantity[]" class="form-control form-control-sm" value="1" min="0" step="0.01" onchange="calculateInvoice()">
                        </div>
                        <div class="col-3">
                            <label class="form-label text-muted small">قیمت واحد (تومان)</label>
                            <input type="number" name="item_unit_price[]" class="form-control form-control-sm" value="0" min="0" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Details -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-cash me-2" style="color:<?php echo $primaryColor; ?>;"></i>جزییات مالی</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">درصد مالیات</label>
                        <input type="number" name="tax_percent" class="form-control" id="taxPercent" value="0" min="0" max="100" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">هزینه خدمات (تومان)</label>
                        <input type="number" name="service_fee" class="form-control" id="serviceFee" value="0" min="0" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">تخفیف (تومان)</label>
                        <input type="number" name="discount_amount" class="form-control" id="discountAmount" value="0" min="0" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">مبلغ بیعانه (تومان)</label>
                        <input type="number" name="deposit_amount" class="form-control" id="depositAmount" value="0" min="0" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">تاریخ اعتبار فاکتور</label>
                        <input type="date" name="valid_until" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">واحد پول</label>
                        <select name="currency" class="form-select">
                            <option value="IRR">تومان (IRR)</option>
                            <option value="USD">دلار (USD)</option>
                            <option value="EUR">یورو (EUR)</option>
                            <option value="AED">درهم (AED)</option>
                            <option value="TRY">لیر (TRY)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes & Footer -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2" style="color:<?php echo $primaryColor; ?>;"></i>توضیحات و شرایط</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small fw-medium">توضیحات</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="توضیحات اختیاری"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small fw-medium">شرایط پرداخت</label>
                        <textarea name="payment_terms" class="form-control" rows="2" placeholder="شرایط پرداخت"><?php echo htmlspecialchars($invSet['invoice_terms'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small fw-medium">متن فوتر فاکتور</label>
                        <textarea name="footer_text" class="form-control" rows="2" placeholder="متن فوتر فاکتور"><?php echo htmlspecialchars($invSet['invoice_footer_text'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="ajax-error alert alert-danger d-none mt-2 small p-2"></div>
        <button type="submit" class="btn w-100 mt-3 fw-bold" style="background:<?php echo $primaryColor; ?>;color:#fff;">
            <i class="bi bi-check-circle me-1"></i>صدور فاکتور
        </button>
    </div>

    <div class="col-12 col-lg-4">
        <!-- Summary -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-calculator me-2" style="color:<?php echo $primaryColor; ?>;"></i>خلاصه محاسبات</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">تعداد شب‌ها</small><strong class="fs-5" style="color:<?php echo $primaryColor; ?>;" id="calcNights">0</strong></div></div>
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">تعداد آیتم‌ها</small><strong class="fs-5" style="color:<?php echo $primaryColor; ?>;" id="calcItems">0</strong></div></div>
                </div>
                <div class="bg-light rounded p-3">
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">جمع کل</small><strong id="calcSubtotal">0 تومان</strong></div>
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">مالیات (<span id="calcTaxPct">0</span>%)</small><strong id="calcTaxAmount">0 تومان</strong></div>
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">هزینه خدمات</small><strong id="calcServiceFee">0 تومان</strong></div>
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">تخفیف</small><strong class="text-danger" id="calcDiscount">0 تومان</strong></div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between"><strong>مبلغ نهایی</strong><strong style="color:<?php echo $successColor; ?>;" class="fs-5" id="calcFinalAmount">0 تومان</strong></div>
                    <div class="d-flex justify-content-between mt-1"><small class="text-muted">نوع فاکتور</small><strong id="calcInvoiceType">پیش فاکتور</strong></div>
                </div>
            </div>
        </div>

        <!-- Previous Invoices -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2" style="color:<?php echo $primaryColor; ?>;"></i>فاکتورهای قبلی</h6></div>
            <div class="card-body">
                <?php if (empty($invoices)): ?>
                <div class="text-center text-muted py-3"><i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i><p class="small">هنوز فاکتوری ثبت نشده.</p></div>
                <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($invoices as $inv): ?>
                    <div class="bg-light rounded p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="small"><?php echo htmlspecialchars($inv->hotel_name); ?></strong>
                                <br><small class="text-muted"><?php echo \Core\JDate::displayDate($inv->check_in_date); ?> تا <?php echo \Core\JDate::displayDate($inv->check_out_date); ?></small>
                                <br><small class="text-muted"><?php echo $inv->nights; ?> شب</small>
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
                            <a href="<?php echo $config['url']; ?>/hotel-invoice/edit/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-warning" style="font-size:11px;padding:2px 8px;"><i class="bi bi-pencil me-1"></i>ویرایش</a>
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
</form>

<script>
function addItem() {
    var container = document.getElementById('itemsContainer');
    var row = document.createElement('div');
    row.className = 'item-row row g-2 mb-2 align-items-end';
    row.innerHTML = '<div class="col-5"><input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="شرح آیتم" required></div>' +
        '<div class="col-2"><input type="number" name="item_quantity[]" class="form-control form-control-sm" value="1" min="0" step="0.01" onchange="calculateInvoice()"></div>' +
        '<div class="col-3"><input type="number" name="item_unit_price[]" class="form-control form-control-sm" value="0" min="0" onchange="calculateInvoice()" dir="ltr" style="text-align:left;"></div>' +
        '<div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button></div>';
    container.appendChild(row);
    calculateInvoice();
}

function removeItem(btn) {
    var row = btn.closest('.item-row');
    var container = document.getElementById('itemsContainer');
    if (container.children.length > 1) {
        row.remove();
        calculateInvoice();
    }
}

function calculateInvoice() {
    var checkIn = document.getElementById('checkInDate').value;
    var checkOut = document.getElementById('checkOutDate').value;
    var nights = 0;
    if (checkIn && checkOut) {
        var d1 = new Date(checkIn);
        var d2 = new Date(checkOut);
        nights = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
        if (isNaN(nights) || nights < 0) nights = 0;
    }

    var subtotal = 0;
    var itemCount = 0;
    var descs = document.querySelectorAll('input[name="item_description[]"]');
    var qtys = document.querySelectorAll('input[name="item_quantity[]"]');
    var prices = document.querySelectorAll('input[name="item_unit_price[]"]');
    for (var i = 0; i < descs.length; i++) {
        if (descs[i].value.trim()) {
            var qty = parseFloat(qtys[i].value) || 0;
            var price = parseFloat(prices[i].value) || 0;
            subtotal += qty * price;
            itemCount++;
        }
    }

    var taxPct = parseFloat(document.getElementById('taxPercent').value) || 0;
    var taxAmount = subtotal * (taxPct / 100);
    var serviceFee = parseFloat(document.getElementById('serviceFee').value) || 0;
    var discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    var finalAmount = subtotal + taxAmount + serviceFee - discount;

    document.getElementById('calcNights').textContent = nights;
    document.getElementById('calcItems').textContent = itemCount;
    document.getElementById('calcSubtotal').textContent = formatNumber(subtotal) + ' تومان';
    document.getElementById('calcTaxPct').textContent = taxPct;
    document.getElementById('calcTaxAmount').textContent = formatNumber(taxAmount) + ' تومان';
    document.getElementById('calcServiceFee').textContent = formatNumber(serviceFee) + ' تومان';
    document.getElementById('calcDiscount').textContent = formatNumber(discount) + ' تومان';
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

function deleteInvoice(id) {
    if (!confirm('آیا از حذف فاکتور مطمئن هستید؟')) return;
    fetch(CRM_BASE_URL + '/hotel-invoice/delete/' + id, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { if (data.success) { location.reload(); } else { alert(data.message || 'خطا'); } })
    .catch(function() { alert('خطای شبکه'); });
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