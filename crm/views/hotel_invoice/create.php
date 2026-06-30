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
        <a href="<?php echo $config['url']; ?>/settings/invoice" class="btn btn-outline-info btn-sm" target="_blank"><i class="bi bi-gear me-1"></i>تنظیمات</a>
        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

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

<form method="POST" action="<?php echo $config['url']; ?>/hotel-invoice/store" id="invoiceForm">
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
                        <input type="text" name="guest_name" class="form-control" value="<?php echo htmlspecialchars($deal->contact_name ?? ''); ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-phone me-1"></i>تلفن میهمان</label>
                        <input type="text" name="guest_phone" class="form-control" value="<?php echo htmlspecialchars($deal->contact_phone ?? ''); ?>" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-plus me-1"></i>تاریخ ورود <span class="text-danger">*</span></label>
                        <input type="date" name="check_in_date" class="form-control" id="checkInDate" required onchange="recalc()">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-minus me-1"></i>تاریخ خروج <span class="text-danger">*</span></label>
                        <input type="date" name="check_out_date" class="form-control" id="checkOutDate" required onchange="recalc()">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">نوع فاکتور</label>
                        <select name="invoice_type" class="form-select" id="invoiceType">
                            <option value="proforma">پیش فاکتور</option>
                            <option value="confirmed">فاکتور تایید شده</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium">وضعیت فاکتور</label>
                        <select name="invoice_status" class="form-select">
                            <option value="pending">مانده دارد</option>
                            <option value="settled">تسویه شده</option>
                            <option value="prepaid">پیش پرداخت</option>
                        </select>
                    </div>
                    <div class="col-12"><hr class="my-1"><small class="text-muted fw-bold">خدمات</small></div>
                    <div class="col-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="transfer_included" id="transferIncluded" value="1"><label class="form-check-label small" for="transferIncluded">ترانسفر</label></div></div>
                    <div class="col-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="visa_included" id="visaIncluded" value="1"><label class="form-check-label small" for="visaIncluded">ویزا</label></div></div>
                    <div class="col-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="insurance_included" id="insuranceIncluded" value="1"><label class="form-check-label small" for="insuranceIncluded">بیمه</label></div></div>
                    <div class="col-12"><label class="form-label text-muted small fw-medium">خدمات اضافی</label><textarea name="extra_services" class="form-control" rows="2"></textarea></div>
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
                    <div class="item-row row g-2 mb-2 pb-2 border-bottom">
                        <div class="col-5">
                            <label class="form-label text-muted small">شرح <span class="text-danger">*</span></label>
                            <div class="item-search-wrapper position-relative">
                                <input type="text" class="form-control form-control-sm item-search-input" placeholder="🔍 جستجوی آیتم ..." oninput="filterItems(this)" autocomplete="off">
                                <select name="item_description[]" class="form-select form-select-sm item-select d-none" onchange="onItemSelect(this)">
                                    <option value="">انتخاب آیتم...</option>
                                </select>
                                <div class="item-dropdown dropdown-menu w-100 py-0" style="max-height:200px;overflow-y:auto;display:none;position:absolute;z-index:1000;"></div>
                            </div>
                            <input type="hidden" name="item_category[]" class="item-category" value="">
                            <input type="text" name="item_description_custom[]" class="form-control form-control-sm mt-1 item-custom-desc" placeholder="یا شرح دلخواه را بنویسید..." oninput="onCustomDesc(this)">
                        </div>
                        <div class="col-2">
                            <label class="form-label text-muted small">تعداد</label>
                            <input type="number" name="item_quantity[]" class="form-control form-control-sm item-qty" value="1" min="1" onchange="recalc()">
                        </div>
                        <div class="col-3">
                            <label class="form-label text-muted small">قیمت واحد (تومان)</label>
                            <input type="number" name="item_unit_price[]" class="form-control form-control-sm item-price" value="0" min="0" onchange="recalc()" dir="ltr" style="text-align:left;">
                        </div>
                        <div class="col-2 d-flex align-items-end">
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
                    <div class="col-6"><label class="form-label text-muted small fw-medium">درصد مالیات</label><input type="number" name="tax_percent" class="form-control" id="taxPercent" value="0" min="0" max="100" onchange="recalc()" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">هزینه خدمات (تومان)</label><input type="number" name="service_fee" class="form-control" id="serviceFee" value="0" min="0" onchange="recalc()" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">تخفیف (تومان)</label><input type="number" name="discount_amount" class="form-control" id="discountAmount" value="0" min="0" onchange="recalc()" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">مبلغ بیعانه (تومان)</label><input type="number" name="deposit_amount" class="form-control" id="depositAmount" value="0" min="0" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">تاریخ اعتبار فاکتور</label><input type="date" name="valid_until" class="form-control"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">واحد پول</label><select name="currency" class="form-select"><option value="IRR">تومان</option><option value="USD">دلار</option><option value="EUR">یورو</option><option value="AED">درهم</option><option value="TRY">لیر</option></select></div>
                </div>
            </div>
        </div>

        <!-- Notes & Footer -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2" style="color:<?php echo $primaryColor; ?>;"></i>توضیحات و شرایط</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label text-muted small fw-medium">توضیحات</label><textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($invSet['invoice_notes'] ?? ''); ?></textarea></div>
                    <div class="col-12"><label class="form-label text-muted small fw-medium">شرایط پرداخت</label><textarea name="payment_terms" class="form-control" rows="2"><?php echo htmlspecialchars($invSet['invoice_terms'] ?? ''); ?></textarea></div>
                    <div class="col-12"><label class="form-label text-muted small fw-medium">متن فوتر فاکتور</label><textarea name="footer_text" class="form-control" rows="2"><?php echo htmlspecialchars($invSet['invoice_footer_text'] ?? ''); ?></textarea></div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn w-100 mt-3 fw-bold" style="background:<?php echo $primaryColor; ?>;color:#fff;">
            <i class="bi bi-check-circle me-1"></i>صدور فاکتور
        </button>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-calculator me-2" style="color:<?php echo $primaryColor; ?>;"></i>خلاصه محاسبات</h6></div>
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
                    <div class="bg-light rounded p-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div><strong class="small"><?php echo htmlspecialchars($inv->hotel_name); ?></strong><br><small class="text-muted"><?php echo \Core\JDate::displayDate($inv->check_in_date); ?> تا <?php echo \Core\JDate::displayDate($inv->check_out_date); ?> | <?php echo $inv->nights; ?> شب</small></div>
                            <div class="text-end"><strong class="text-success"><?php echo number_format($inv->final_amount); ?> ت</strong><br><span class="badge <?php echo $inv->invoice_status=='settled'?'bg-success':($inv->invoice_status=='prepaid'?'bg-info':'bg-warning text-dark'); ?>" style="font-size:9px;"><?php echo $inv->invoice_status=='settled'?'تسویه':($inv->invoice_status=='prepaid'?'پیش':'مانده'); ?></span></div>
                        </div>
                        <div class="d-flex gap-1 mt-1"><a href="<?php echo $config['url']; ?>/hotel-invoice/view/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-primary py-0" style="font-size:10px;">مشاهده</a><a href="<?php echo $config['url']; ?>/hotel-invoice/edit/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-warning py-0" style="font-size:10px;">ویرایش</a><button type="button" class="btn btn-sm btn-outline-danger py-0" style="font-size:10px;" onclick="deleteInvoice(<?php echo $inv->id; ?>)">حذف</button></div>
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
var catalogItems = [];

// Load catalog
fetch(CRM_BASE_URL + '/hotel-invoice/items-catalog/api')
.then(function(r) { return r.json(); })
.then(function(data) {
    if (data.success && data.items) {
        catalogItems = data.items;
        initDropdowns();
        recalc();
    }
}).catch(function() {});

function initDropdowns() {
    document.querySelectorAll('.item-search-wrapper').forEach(function(wrapper) {
        var input = wrapper.querySelector('.item-search-input');
        if (input) filterItems({target: input});
    });
}

function filterItems(e) {
    var input = e.target;
    var wrapper = input.closest('.item-search-wrapper');
    var dropdown = wrapper.querySelector('.item-dropdown');
    var select = wrapper.querySelector('.item-select');
    var q = input.value.trim().toLowerCase();

    if (q.length === 0) {
        // Show all items grouped by category
        var html = '';
        var categories = {};
        catalogItems.forEach(function(item) {
            var cat = item.category || 'general';
            if (!categories[cat]) categories[cat] = [];
            categories[cat].push(item);
        });
        var catLabels = {'hotel':'🏨 هتل','transfer':'🚗 ترانسفر','visa':'🛂 ویزا','insurance':'🛡 بیمه','flight':'✈ بلیط','tour':'🗺 گشت','guide':'🧑 راهنما','meal':'🍽 غذا','general':'📦 عمومی','other':'📌 سایر'};
        Object.keys(categories).forEach(function(cat) {
            html += '<div class="dropdown-item disabled small text-muted border-bottom" style="font-size:10px;background:#f8f9fa;cursor:default;">' + (catLabels[cat] || cat) + '</div>';
            categories[cat].forEach(function(item) {
                html += '<a class="dropdown-item small item-dropdown-option" href="#" data-value="' + item.name + '" data-price="' + item.default_price + '" data-category="' + item.category + '" onclick="selectItem(this);return false;">' + item.name + ' <small class="text-muted">(' + formatNumber(item.default_price) + ' ت)</small></a>';
            });
        });
        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
        return;
    }

    // Filter items
    var filtered = catalogItems.filter(function(item) {
        return item.name.toLowerCase().indexOf(q) !== -1 || (item.description && item.description.toLowerCase().indexOf(q) !== -1);
    });

    if (filtered.length === 0) {
        dropdown.innerHTML = '<div class="dropdown-item small text-muted">هیچ آیتمی یافت نشد. می‌توانید شرح دلخواه بنویسید.</div>';
        dropdown.style.display = 'block';
        return;
    }

    var html = '';
    filtered.forEach(function(item) {
        html += '<a class="dropdown-item small item-dropdown-option" href="#" data-value="' + item.name + '" data-price="' + item.default_price + '" data-category="' + item.category + '" onclick="selectItem(this);return false;">' + item.name + ' <small class="text-muted">(' + formatNumber(item.default_price) + ' ت)</small> <small class="text-primary">[' + (item.category || 'عمومی') + ']</small></a>';
    });
    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
}

function selectItem(el) {
    var wrapper = el.closest('.item-search-wrapper');
    var input = wrapper.querySelector('.item-search-input');
    var select = wrapper.querySelector('.item-select');
    var catInput = wrapper.closest('.item-row').querySelector('.item-category');
    var priceInput = wrapper.closest('.item-row').querySelector('.item-price');

    var name = el.getAttribute('data-value');
    var price = el.getAttribute('data-price');
    var category = el.getAttribute('data-category');

    input.value = name + ' (' + formatNumber(parseFloat(price)) + ' تومان)';
    select.value = name;
    catInput.value = category || 'general';
    priceInput.value = price || '0';
    wrapper.querySelector('.item-dropdown').style.display = 'none';
    recalc();
}

function onCustomDesc(input) {
    var row = input.closest('.item-row');
    var select = row.querySelector('.item-select');
    var catInput = row.querySelector('.item-category');
    var priceInput = row.querySelector('.item-price');
    var wrapper = row.querySelector('.item-search-wrapper');
    var searchInput = wrapper.querySelector('.item-search-input');

    if (input.value.trim()) {
        // Custom description: clear select, keep user's typed desc
        select.value = '';
        catInput.value = 'general';
        searchInput.value = input.value.trim();
        var dropdown = wrapper.querySelector('.item-dropdown');
        dropdown.style.display = 'none';
    }
    recalc();
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.item-search-wrapper')) {
        document.querySelectorAll('.item-dropdown').forEach(function(d) { d.style.display = 'none'; });
    }
});

function onItemSelect(sel) {
    // This is kept for compatibility, but the search-input approach is primary
    var row = sel.closest('.item-row');
    var priceInput = row.querySelector('.item-price');
    var catInput = row.querySelector('.item-category');
    if (sel.value) {
        var opt = sel.options[sel.selectedIndex];
        priceInput.value = opt.getAttribute('data-price') || '0';
        catInput.value = opt.getAttribute('data-category') || 'general';
    }
    recalc();
}

function addItem() {
    var container = document.getElementById('itemsContainer');
    var row = document.createElement('div');
    row.className = 'item-row row g-2 mb-2 pb-2 border-bottom';
    row.innerHTML =
        '<div class="col-5">' +
            '<div class="item-search-wrapper position-relative">' +
                '<input type="text" class="form-control form-control-sm item-search-input" placeholder="🔍 جستجوی آیتم ..." oninput="filterItems(this)" autocomplete="off">' +
                '<select name="item_description[]" class="form-select form-select-sm item-select d-none"><option value="">انتخاب آیتم...</option></select>' +
                '<div class="item-dropdown dropdown-menu w-100 py-0" style="max-height:200px;overflow-y:auto;display:none;position:absolute;z-index:1000;"></div>' +
            '</div>' +
            '<input type="hidden" name="item_category[]" class="item-category" value="">' +
            '<input type="text" name="item_description_custom[]" class="form-control form-control-sm mt-1 item-custom-desc" placeholder="یا شرح دلخواه را بنویسید..." oninput="onCustomDesc(this)">' +
        '</div>' +
        '<div class="col-2">' +
            '<input type="number" name="item_quantity[]" class="form-control form-control-sm item-qty" value="1" min="1" onchange="recalc()">' +
        '</div>' +
        '<div class="col-3">' +
            '<input type="number" name="item_unit_price[]" class="form-control form-control-sm item-price" value="0" min="0" onchange="recalc()" dir="ltr" style="text-align:left;">' +
        '</div>' +
        '<div class="col-2 d-flex align-items-end">' +
            '<button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button>' +
        '</div>';
    container.appendChild(row);
    var wrapper = row.querySelector('.item-search-wrapper');
    var input = wrapper.querySelector('.item-search-input');
    if (catalogItems.length > 0) filterItems({target: input});
    recalc();
}

function removeItem(btn) {
    var container = document.getElementById('itemsContainer');
    if (container.children.length > 1) {
        btn.closest('.item-row').remove();
        recalc();
    }
}

function getNights() {
    var ci = document.getElementById('checkInDate').value;
    var co = document.getElementById('checkOutDate').value;
    if (!ci || !co) return 0;
    var n = Math.ceil((new Date(co) - new Date(ci)) / (1000 * 60 * 60 * 24));
    return (isNaN(n) || n < 0) ? 0 : n;
}

function recalc() {
    var nights = getNights();
    var subtotal = 0, itemCount = 0;
    document.querySelectorAll('.item-row').forEach(function(row) {
        var select = row.querySelector('.item-select');
        var customDesc = row.querySelector('.item-custom-desc');
        var hasSelection = select && select.value;
        var hasCustom = customDesc && customDesc.value.trim();
        if (!hasSelection && !hasCustom) return;

        var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        var price = parseFloat(row.querySelector('.item-price').value) || 0;
        var cat = (row.querySelector('.item-category') || {}).value || '';
        var lineTotal = 0;
        if (cat === 'hotel' && nights > 0) lineTotal = qty * price * nights;
        else lineTotal = qty * price;
        subtotal += lineTotal;
        itemCount++;
    });

    var tp = parseFloat(document.getElementById('taxPercent').value) || 0;
    var ta = subtotal * (tp / 100);
    var sf = parseFloat(document.getElementById('serviceFee').value) || 0;
    var disc = parseFloat(document.getElementById('discountAmount').value) || 0;
    var fa = subtotal + ta + sf - disc;

    document.getElementById('calcNights').textContent = nights;
    document.getElementById('calcItems').textContent = itemCount;
    document.getElementById('calcSubtotal').textContent = formatNumber(subtotal) + ' تومان';
    document.getElementById('calcTaxPct').textContent = tp;
    document.getElementById('calcTaxAmount').textContent = formatNumber(ta) + ' تومان';
    document.getElementById('calcServiceFee').textContent = formatNumber(sf) + ' تومان';
    document.getElementById('calcDiscount').textContent = formatNumber(disc) + ' تومان';
    document.getElementById('calcFinalAmount').textContent = formatNumber(fa) + ' تومان';
}

function formatNumber(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

function deleteInvoice(id) {
    if (!confirm('آیا از حذف فاکتور مطمئن هستید؟')) return;
    fetch(CRM_BASE_URL + '/hotel-invoice/delete/' + id, {method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r) { return r.json(); })
    .then(function(d) { if (d.success) location.reload(); else alert(d.message || 'خطا'); })
    .catch(function() { alert('خطای شبکه'); });
}

document.addEventListener('DOMContentLoaded', function() { recalc(); });
</script>