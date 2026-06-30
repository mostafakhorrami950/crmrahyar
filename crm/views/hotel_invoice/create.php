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
        <button type="button" class="btn btn-outline-success btn-sm" onclick="showAddItemModal()"><i class="bi bi-plus-circle me-1"></i>افزودن آیتم جدید</button>
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
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-person me-1"></i>نام میهمان (خودکار از معامله)</label>
                        <input type="text" name="guest_name" class="form-control" value="<?php echo htmlspecialchars($deal->contact_name ?? ''); ?>" placeholder="نام میهمان">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-phone me-1"></i>تلفن میهمان (خودکار از معامله)</label>
                        <input type="text" name="guest_phone" class="form-control" value="<?php echo htmlspecialchars($deal->contact_phone ?? ''); ?>" placeholder="تلفن میهمان" dir="ltr" style="text-align:left;">
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
                            <option value="pending">مانده دارد</option>
                            <option value="settled">تسویه شده</option>
                            <option value="prepaid">پیش پرداخت</option>
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
                            <select name="item_description[]" class="form-select form-select-sm item-select" onchange="onItemSelect(this)">
                                <option value="">انتخاب آیتم...</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label text-muted small">تعداد</label>
                            <input type="number" name="item_quantity[]" class="form-control form-control-sm" value="1" min="1" onchange="calculateInvoice()">
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
                        <textarea name="notes" class="form-control" rows="2" placeholder="توضیحات اختیاری"><?php echo htmlspecialchars($invSet['invoice_notes'] ?? ''); ?></textarea>
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
                                <br><span class="badge <?php echo $inv->invoice_status=='settled'?'bg-success':($inv->invoice_status=='prepaid'?'bg-info':'bg-warning text-dark'); ?>" style="font-size:10px;">
                                    <?php echo $inv->invoice_status=='settled'?'تسویه شده':($inv->invoice_status=='prepaid'?'پیش پرداخت':'مانده دارد'); ?>
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

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>افزودن آیتم جدید به کاتالوگ</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label text-muted small">نام آیتم <span class="text-danger">*</span></label>
                <input type="text" id="newItemName" class="form-control" placeholder="نام آیتم" required>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small">توضیحات</label>
                <input type="text" id="newItemDesc" class="form-control" placeholder="توضیحات اختیاری">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">قیمت پیش‌فرض (تومان)</label>
                <input type="number" id="newItemPrice" class="form-control" value="0" min="0" dir="ltr" style="text-align:left;">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">دسته‌بندی</label>
                <select id="newItemCategory" class="form-select">
                    <option value="hotel">هتل</option>
                    <option value="transfer">ترانسفر</option>
                    <option value="visa">ویزا</option>
                    <option value="insurance">بیمه</option>
                    <option value="flight">بلیط</option>
                    <option value="tour">گشت</option>
                    <option value="guide">راهنما</option>
                    <option value="meal">غذا</option>
                    <option value="general">عمومی</option>
                    <option value="other">سایر</option>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="saveNewItem()"><i class="bi bi-check-circle me-1"></i>ذخیره و افزودن</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button>
    </div>
</div></div></div>

<script>
var catalogItems = [];

// Load items catalog
fetch(CRM_BASE_URL + '/hotel-invoice/items-catalog/api')
.then(function(r) { return r.json(); })
.then(function(data) {
    if (data.success && data.items) {
        catalogItems = data.items;
        populateItemSelects();
    }
})
.catch(function() {});

function populateItemSelects() {
    var selects = document.querySelectorAll('.item-select');
    selects.forEach(function(sel) {
        var currentVal = sel.value;
        sel.innerHTML = '<option value="">انتخاب آیتم...</option>';
        catalogItems.forEach(function(item) {
            var opt = document.createElement('option');
            opt.value = item.name;
            opt.textContent = item.name + (item.default_price > 0 ? ' (' + formatNumber(item.default_price) + ' ت)' : '');
            opt.setAttribute('data-price', item.default_price);
            opt.setAttribute('data-search', (item.name + ' ' + (item.description || '') + ' ' + item.category).toLowerCase());
            sel.appendChild(opt);
        });
        if (currentVal) sel.value = currentVal;
    });
}

function onItemSelect(sel) {
    var row = sel.closest('.item-row');
    var priceInput = row.querySelector('input[name="item_unit_price[]"]');
    var qtyInput = row.querySelector('input[name="item_quantity[]"]');
    var selectedOption = sel.options[sel.selectedIndex];
    
    if (sel.value === '') {
        priceInput.value = '0';
        qtyInput.value = '1';
    } else {
        var price = selectedOption.getAttribute('data-price') || '0';
        priceInput.value = price;
        // Auto-set quantity to number of nights for hotel items
        var checkIn = document.getElementById('checkInDate').value;
        var checkOut = document.getElementById('checkOutDate').value;
        if (checkIn && checkOut) {
            var d1 = new Date(checkIn);
            var d2 = new Date(checkOut);
            var nights = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
            if (nights > 0) {
                qtyInput.value = nights;
            }
        }
    }
    calculateInvoice();
}

function addItem() {
    var container = document.getElementById('itemsContainer');
    var row = document.createElement('div');
    row.className = 'item-row row g-2 mb-2 align-items-end';
    row.innerHTML = '<div class="col-5"><select name="item_description[]" class="form-select form-select-sm item-select" onchange="onItemSelect(this)"><option value="">انتخاب آیتم...</option></select></div>' +
        '<div class="col-2"><input type="number" name="item_quantity[]" class="form-control form-control-sm" value="1" min="1" onchange="calculateInvoice()"></div>' +
        '<div class="col-3"><input type="number" name="item_unit_price[]" class="form-control form-control-sm" value="0" min="0" onchange="calculateInvoice()" dir="ltr" style="text-align:left;"></div>' +
        '<div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button></div>';
    container.appendChild(row);
    populateItemSelects();
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
    var selects = document.querySelectorAll('select[name="item_description[]"]');
    var qtys = document.querySelectorAll('input[name="item_quantity[]"]');
    var prices = document.querySelectorAll('input[name="item_unit_price[]"]');
    for (var i = 0; i < selects.length; i++) {
        if (selects[i].value) {
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

function showAddItemModal() {
    document.getElementById('newItemName').value = '';
    document.getElementById('newItemDesc').value = '';
    document.getElementById('newItemPrice').value = '0';
    document.getElementById('newItemCategory').value = 'general';
    new bootstrap.Modal(document.getElementById('addItemModal')).show();
}

function saveNewItem() {
    var name = document.getElementById('newItemName').value.trim();
    var desc = document.getElementById('newItemDesc').value.trim();
    var price = document.getElementById('newItemPrice').value;
    var category = document.getElementById('newItemCategory').value;
    
    if (!name) { alert('نام آیتم الزامی است.'); return; }
    
    var fd = new FormData();
    fd.append('name', name);
    fd.append('description', desc);
    fd.append('default_price', price);
    fd.append('category', category);
    
    fetch(CRM_BASE_URL + '/hotel-invoice/items-catalog/store', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            fetch(CRM_BASE_URL + '/hotel-invoice/items-catalog/api')
            .then(function(r) { return r.json(); })
            .then(function(catalogData) {
                if (catalogData.success && catalogData.items) {
                    catalogItems = catalogData.items;
                    populateItemSelects();
                    var selects = document.querySelectorAll('.item-select');
                    if (selects.length > 0) {
                        var lastSelect = selects[selects.length - 1];
                        lastSelect.value = name;
                        onItemSelect(lastSelect);
                    }
                }
            });
            bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
        } else {
            alert(data.message || 'خطا');
        }
    })
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