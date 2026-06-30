<?php $config = $GLOBALS['app_config']; ?>
<?php $invSet = $invoiceSettings ?? []; ?>
<?php $primaryColor = $invSet['invoice_primary_color'] ?? '#0d6efd'; ?>
<?php $successColor = $invSet['invoice_success_color'] ?? '#198754'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice/view/<?php echo $invoice->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
        <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2" style="color:<?php echo $primaryColor; ?>;"></i>ویرایش فاکتور #<?php echo $invoice->invoice_number ?? $invoice->id; ?></h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list me-1"></i>لیست فاکتورها</a>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="showAddItemModal()"><i class="bi bi-plus-circle me-1"></i>افزودن آیتم جدید</button>
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

<form method="POST" action="<?php echo $config['url']; ?>/hotel-invoice/update/<?php echo $invoice->id; ?>" id="invoiceForm" data-ajax="true">
<input type="hidden" name="deal_id" value="<?php echo $invoice->deal_id; ?>">

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
                        <input type="text" name="hotel_name" class="form-control" value="<?php echo htmlspecialchars($invoice->hotel_name); ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-person me-1"></i>نام میهمان</label>
                        <input type="text" name="guest_name" class="form-control" value="<?php echo htmlspecialchars($invoice->guest_name ?? ''); ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-phone me-1"></i>تلفن میهمان</label>
                        <input type="text" name="guest_phone" class="form-control" value="<?php echo htmlspecialchars($invoice->guest_phone ?? ''); ?>" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-plus me-1"></i>تاریخ ورود <span class="text-danger">*</span></label>
                        <input type="date" name="check_in_date" class="form-control" id="checkInDate" value="<?php echo $invoice->check_in_date; ?>" required onchange="recalcHotelItems()">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-minus me-1"></i>تاریخ خروج <span class="text-danger">*</span></label>
                        <input type="date" name="check_out_date" class="form-control" id="checkOutDate" value="<?php echo $invoice->check_out_date; ?>" required onchange="recalcHotelItems()">
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
                            <option value="pending" <?php echo $invoice->invoice_status=='pending'?'selected':''; ?>>مانده دارد</option>
                            <option value="settled" <?php echo $invoice->invoice_status=='settled'?'selected':''; ?>>تسویه شده</option>
                            <option value="prepaid" <?php echo $invoice->invoice_status=='prepaid'?'selected':''; ?>>پیش پرداخت</option>
                        </select>
                    </div>
                    <div class="col-12"><hr class="my-1"><small class="text-muted fw-bold">خدمات</small></div>
                    <div class="col-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="transfer_included" id="transferIncluded" value="1" <?php echo $invoice->transfer_included?'checked':''; ?>>
                            <label class="form-check-label small" for="transferIncluded"><i class="bi bi-car-front me-1"></i>ترانسفر</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="visa_included" id="visaIncluded" value="1" <?php echo $invoice->visa_included?'checked':''; ?>>
                            <label class="form-check-label small" for="visaIncluded"><i class="bi bi-passport me-1"></i>ویزا</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="insurance_included" id="insuranceIncluded" value="1" <?php echo $invoice->insurance_included?'checked':''; ?>>
                            <label class="form-check-label small" for="insuranceIncluded"><i class="bi bi-shield-check me-1"></i>بیمه</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-list-check me-1"></i>خدمات اضافی</label>
                        <textarea name="extra_services" class="form-control" rows="2"><?php echo htmlspecialchars($invoice->extra_services ?? ''); ?></textarea>
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
                    <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                    <div class="item-row row g-2 mb-2 align-items-end">
                        <div class="col-5">
                            <label class="form-label text-muted small">شرح</label>
                            <select name="item_description[]" class="form-select form-select-sm item-select" onchange="onItemSelect(this)">
                                <option value="">انتخاب آیتم...</option>
                            </select>
                            <input type="text" name="item_description_custom[]" class="form-control form-control-sm mt-1" value="<?php echo htmlspecialchars($item->description); ?>" placeholder="شرح دلخواه">
                        </div>
                        <div class="col-2">
                            <label class="form-label text-muted small">تعداد</label>
                            <input type="number" name="item_quantity[]" class="form-control form-control-sm" value="<?php echo (int)$item->quantity; ?>" min="1" onchange="calculateInvoice()">
                        </div>
                        <div class="col-3">
                            <label class="form-label text-muted small">قیمت واحد (تومان)</label>
                            <input type="number" name="item_unit_price[]" class="form-control form-control-sm" value="<?php echo $item->unit_price; ?>" min="0" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
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
                    <?php endif; ?>
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
                        <input type="number" name="tax_percent" class="form-control" id="taxPercent" value="<?php echo $invoice->tax_percent ?? 0; ?>" min="0" max="100" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">هزینه خدمات (تومان)</label>
                        <input type="number" name="service_fee" class="form-control" id="serviceFee" value="<?php echo $invoice->service_fee ?? 0; ?>" min="0" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">تخفیف (تومان)</label>
                        <input type="number" name="discount_amount" class="form-control" id="discountAmount" value="<?php echo $invoice->discount_amount ?? 0; ?>" min="0" onchange="calculateInvoice()" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">مبلغ بیعانه (تومان)</label>
                        <input type="number" name="deposit_amount" class="form-control" id="depositAmount" value="<?php echo $invoice->deposit_amount ?? 0; ?>" min="0" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">تاریخ اعتبار فاکتور</label>
                        <input type="date" name="valid_until" class="form-control" value="<?php echo $invoice->valid_until ?? ''; ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">واحد پول</label>
                        <select name="currency" class="form-select">
                            <option value="IRR" <?php echo ($invoice->currency ?? 'IRR')=='IRR'?'selected':''; ?>>تومان (IRR)</option>
                            <option value="USD" <?php echo ($invoice->currency ?? '')=='USD'?'selected':''; ?>>دلار (USD)</option>
                            <option value="EUR" <?php echo ($invoice->currency ?? '')=='EUR'?'selected':''; ?>>یورو (EUR)</option>
                            <option value="AED" <?php echo ($invoice->currency ?? '')=='AED'?'selected':''; ?>>درهم (AED)</option>
                            <option value="TRY" <?php echo ($invoice->currency ?? '')=='TRY'?'selected':''; ?>>لیر (TRY)</option>
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
                        <textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($invoice->notes ?? ''); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small fw-medium">شرایط پرداخت</label>
                        <textarea name="payment_terms" class="form-control" rows="2"><?php echo htmlspecialchars($invoice->payment_terms ?? $invSet['invoice_terms'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small fw-medium">متن فوتر فاکتور</label>
                        <textarea name="footer_text" class="form-control" rows="2"><?php echo htmlspecialchars($invoice->footer_text ?? $invSet['invoice_footer_text'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="ajax-error alert alert-danger d-none mt-2 small p-2"></div>
        <button type="submit" class="btn w-100 mt-3 fw-bold" style="background:<?php echo $primaryColor; ?>;color:#fff;">
            <i class="bi bi-check-circle me-1"></i>بروزرسانی فاکتور
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
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">تعداد شب‌ها</small><strong class="fs-5" style="color:<?php echo $primaryColor; ?>;" id="calcNights"><?php echo $invoice->nights; ?></strong></div></div>
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">تعداد آیتم‌ها</small><strong class="fs-5" style="color:<?php echo $primaryColor; ?>;" id="calcItems"><?php echo count($items); ?></strong></div></div>
                </div>
                <div class="bg-light rounded p-3">
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">جمع کل</small><strong id="calcSubtotal"><?php echo number_format($invoice->subtotal ?? $invoice->total_amount ?? 0); ?> تومان</strong></div>
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">مالیات (<span id="calcTaxPct"><?php echo $invoice->tax_percent ?? 0; ?></span>%)</small><strong id="calcTaxAmount"><?php echo number_format($invoice->tax_amount ?? 0); ?> تومان</strong></div>
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">هزینه خدمات</small><strong id="calcServiceFee"><?php echo number_format($invoice->service_fee ?? 0); ?> تومان</strong></div>
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">تخفیف</small><strong class="text-danger" id="calcDiscount"><?php echo number_format($invoice->discount_amount ?? 0); ?> تومان</strong></div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between"><strong>مبلغ نهایی</strong><strong style="color:<?php echo $successColor; ?>;" class="fs-5" id="calcFinalAmount"><?php echo number_format($invoice->final_amount); ?> تومان</strong></div>
                    <div class="d-flex justify-content-between mt-1"><small class="text-muted">نوع فاکتور</small><strong id="calcInvoiceType"><?php echo $invoice->invoice_type=='confirmed'?'فاکتور تایید شده':'پیش فاکتور'; ?></strong></div>
                </div>
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
            <div class="col-12"><label class="form-label text-muted small">نام آیتم <span class="text-danger">*</span></label><input type="text" id="newItemName" class="form-control" placeholder="نام آیتم"></div>
            <div class="col-12"><label class="form-label text-muted small">توضیحات</label><input type="text" id="newItemDesc" class="form-control" placeholder="توضیحات اختیاری"></div>
            <div class="col-6"><label class="form-label text-muted small">قیمت پیش‌فرض (تومان)</label><input type="number" id="newItemPrice" class="form-control" value="0" min="0" dir="ltr" style="text-align:left;"></div>
            <div class="col-6"><label class="form-label text-muted small">دسته‌بندی</label><select id="newItemCategory" class="form-select"><option value="hotel">هتل</option><option value="transfer">ترانسفر</option><option value="visa">ویزا</option><option value="insurance">بیمه</option><option value="flight">بلیط</option><option value="tour">گشت</option><option value="guide">راهنما</option><option value="meal">غذا</option><option value="general">عمومی</option><option value="other">سایر</option></select></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="saveNewItem()"><i class="bi bi-check-circle me-1"></i>ذخیره و افزودن</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button>
    </div>
</div></div></div>

<script>
var catalogItems = [];
var existingItemDescs = <?php echo json_encode(array_column($items ?? [], 'description')); ?>;

fetch(CRM_BASE_URL + '/hotel-invoice/items-catalog/api')
.then(function(r) { return r.json(); })
.then(function(data) {
    if (data.success && data.items) {
        catalogItems = data.items;
        populateItemSelects();
    }
}).catch(function() {});

function populateItemSelects() {
    var selects = document.querySelectorAll('.item-select');
    selects.forEach(function(sel, idx) {
        var currentVal = sel.value;
        sel.innerHTML = '<option value="">انتخاب آیتم...</option>';
        catalogItems.forEach(function(item) {
            var opt = document.createElement('option');
            opt.value = item.name;
            opt.textContent = item.name + (item.default_price > 0 ? ' (' + formatNumber(item.default_price) + ' ت)' : '');
            opt.setAttribute('data-price', item.default_price);
            opt.setAttribute('data-category', item.category);
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
        priceInput.value = selectedOption.getAttribute('data-price') || '0';
        // Auto-set quantity to nights for hotel category items
        var category = selectedOption.getAttribute('data-category') || '';
        if (category === 'hotel') {
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
    }
    calculateInvoice();
}

function addItem() {
    var container = document.getElementById('itemsContainer');
    var row = document.createElement('div');
    row.className = 'item-row row g-2 mb-2 align-items-end';
    row.innerHTML = '<div class="col-5"><select name="item_description[]" class="form-select form-select-sm item-select" onchange="onItemSelect(this)"><option value="">انتخاب آیتم...</option></select><input type="text" name="item_description_custom[]" class="form-control form-control-sm mt-1" placeholder="شرح دلخواه"></div>' +
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
    if (container.children.length > 1) { row.remove(); calculateInvoice(); }
}

function calculateInvoice() {
    var checkIn = document.getElementById('checkInDate').value;
    var checkOut = document.getElementById('checkOutDate').value;
    var nights = 0;
    if (checkIn && checkOut) {
        var d1 = new Date(checkIn); var d2 = new Date(checkOut);
        nights = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
        if (isNaN(nights) || nights < 0) nights = 0;
    }
    var subtotal = 0, itemCount = 0;
    var selects = document.querySelectorAll('select[name="item_description[]"]');
    var qtys = document.querySelectorAll('input[name="item_quantity[]"]');
    var prices = document.querySelectorAll('input[name="item_unit_price[]"]');
    for (var i = 0; i < selects.length; i++) {
        if (selects[i].value) {
            subtotal += (parseInt(qtys[i].value) || 0) * (parseFloat(prices[i].value) || 0);
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
    if (invoiceType && calcInvoiceType) calcInvoiceType.textContent = invoiceType.value === 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور';
}

function formatNumber(num) { return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); }

function showAddItemModal() {
    document.getElementById('newItemName').value = '';
    document.getElementById('newItemDesc').value = '';
    document.getElementById('newItemPrice').value = '0';
    document.getElementById('newItemCategory').value = 'general';
    new bootstrap.Modal(document.getElementById('addItemModal')).show();
}

function saveNewItem() {
    var name = document.getElementById('newItemName').value.trim();
    if (!name) { alert('نام آیتم الزامی است.'); return; }
    var fd = new FormData();
    fd.append('name', name);
    fd.append('description', document.getElementById('newItemDesc').value.trim());
    fd.append('default_price', document.getElementById('newItemPrice').value);
    fd.append('category', document.getElementById('newItemCategory').value);
    fetch(CRM_BASE_URL + '/hotel-invoice/items-catalog/store', { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            fetch(CRM_BASE_URL + '/hotel-invoice/items-catalog/api').then(function(r) { return r.json(); })
            .then(function(cd) { if (cd.success && cd.items) { catalogItems = cd.items; populateItemSelects(); var s = document.querySelectorAll('.item-select'); if (s.length > 0) { s[s.length-1].value = name; onItemSelect(s[s.length-1]); } } });
            bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
        } else { alert(data.message || 'خطا'); }
    }).catch(function() { alert('خطای شبکه'); });
}

function recalcHotelItems() {
    var checkIn = document.getElementById('checkInDate').value;
    var checkOut = document.getElementById('checkOutDate').value;
    if (!checkIn || !checkOut) return;
    var d1 = new Date(checkIn);
    var d2 = new Date(checkOut);
    var nights = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
    if (nights <= 0) return;
    var selects = document.querySelectorAll('.item-select');
    selects.forEach(function(sel) {
        var selectedOption = sel.options[sel.selectedIndex];
        if (selectedOption && sel.value) {
            var category = selectedOption.getAttribute('data-category') || '';
            if (category === 'hotel') {
                var row = sel.closest('.item-row');
                var qtyInput = row.querySelector('input[name="item_quantity[]"]');
                if (qtyInput) qtyInput.value = nights;
            }
        }
    });
    calculateInvoice();
}

document.addEventListener('DOMContentLoaded', function() { 
    recalcHotelItems();
    calculateInvoice();
    document.getElementById('invoiceType').addEventListener('change', function() {
        document.getElementById('calcInvoiceType').textContent = this.value === 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور';
    });
    // Recalculate hotel items before form submission
    var form = document.getElementById('invoiceForm');
    if (form) {
        form.addEventListener('submit', function() {
            recalcHotelItems();
        });
    }
});
</script>