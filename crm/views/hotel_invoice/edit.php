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
        <a href="<?php echo $config['url']; ?>/hotel-invoice/view/<?php echo $invoice->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="card mb-3"><div class="card-body">
    <div class="d-flex gap-3 p-3 bg-light rounded-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width:48px;height:48px;background:<?php echo $primaryColor; ?>;"><i class="bi bi-briefcase fs-4"></i></div>
        <div><strong class="d-block"><?php echo htmlspecialchars($deal->title); ?></strong><small class="text-muted"><?php echo htmlspecialchars($deal->contact_name ?? ''); ?> | <?php echo htmlspecialchars($deal->contact_phone ?? ''); ?></small></div>
    </div>
</div></div>

<form method="POST" action="<?php echo $config['url']; ?>/hotel-invoice/update/<?php echo $invoice->id; ?>" id="invoiceForm">
<input type="hidden" name="deal_id" value="<?php echo $invoice->deal_id; ?>">

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <!-- Hotel & Guest Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-building me-2" style="color:<?php echo $primaryColor; ?>;"></i>اطلاعات هتل و میهمان</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label text-muted small fw-medium">نام هتل <span class="text-danger">*</span></label><input type="text" name="hotel_name" class="form-control" value="<?php echo htmlspecialchars($invoice->hotel_name); ?>" required></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">نام میهمان</label><input type="text" name="guest_name" class="form-control" value="<?php echo htmlspecialchars($invoice->guest_name ?? ''); ?>"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">تلفن میهمان</label><input type="text" name="guest_phone" class="form-control" value="<?php echo htmlspecialchars($invoice->guest_phone ?? ''); ?>" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">تاریخ ورود <span class="text-danger">*</span></label><input type="date" name="check_in_date" class="form-control" id="checkInDate" value="<?php echo $invoice->check_in_date; ?>" required onchange="recalc()"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">تاریخ خروج <span class="text-danger">*</span></label><input type="date" name="check_out_date" class="form-control" id="checkOutDate" value="<?php echo $invoice->check_out_date; ?>" required onchange="recalc()"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">نوع فاکتور</label><select name="invoice_type" class="form-select" id="invoiceType"><option value="proforma" <?php echo $invoice->invoice_type=='proforma'?'selected':''; ?>>پیش فاکتور</option><option value="confirmed" <?php echo $invoice->invoice_type=='confirmed'?'selected':''; ?>>فاکتور تایید شده</option></select></div>
<div class="col-6"><label class="form-label text-muted small fw-medium">وضعیت فاکتور</label><select name="invoice_status" class="form-select"><option value="pending" <?php echo $invoice->invoice_status=='pending'?'selected':''; ?>>مانده دارد</option><option value="prepaid" <?php echo $invoice->invoice_status=='prepaid'?'selected':''; ?>>پیش پرداخت</option><option value="paid" <?php echo $invoice->invoice_status=='paid'?'selected':''; ?>>پرداخت شده</option><option value="settled" <?php echo $invoice->invoice_status=='settled'?'selected':''; ?>>تسویه شده</option></select></div>
                    <div class="col-12"><hr class="my-1"><small class="text-muted fw-bold">خدمات</small></div>
                    <div class="col-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="transfer_included" id="ti" value="1" <?php echo $invoice->transfer_included?'checked':''; ?>><label class="form-check-label small" for="ti">ترانسفر</label></div></div>
                    <div class="col-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="visa_included" id="vi" value="1" <?php echo $invoice->visa_included?'checked':''; ?>><label class="form-check-label small" for="vi">ویزا</label></div></div>
                    <div class="col-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="insurance_included" id="ii" value="1" <?php echo $invoice->insurance_included?'checked':''; ?>><label class="form-check-label small" for="ii">بیمه</label></div></div>
                    <div class="col-12"><label class="form-label text-muted small fw-medium">خدمات اضافی</label><textarea name="extra_services" class="form-control" rows="2"><?php echo htmlspecialchars($invoice->extra_services ?? ''); ?></textarea></div>
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
                    <div class="item-row row g-2 mb-2 pb-2 border-bottom">
                        <div class="col-5">
                            <label class="form-label text-muted small">شرح <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm item-search-input" placeholder="🔍 جستجو یا تایپ نام آیتم..." value="<?php echo htmlspecialchars($item->description); ?>" oninput="filterItems(this)" autocomplete="off">
                            <input type="hidden" name="item_description[]" class="item-description-hidden" value="<?php echo htmlspecialchars($item->description); ?>">
                            <input type="hidden" name="item_category[]" class="item-category" value="<?php echo htmlspecialchars($item->category ?? 'general'); ?>">
                            <div class="item-dropdown mt-1" style="display:none;position:absolute;z-index:1000;background:#fff;border:1px solid #ddd;border-radius:4px;max-height:200px;overflow-y:auto;width:calc(100% - 10px);box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>
                        </div>
                        <div class="col-2">
                            <label class="form-label text-muted small">تعداد</label>
                            <input type="number" name="item_quantity[]" class="form-control form-control-sm item-qty" value="<?php echo (int)$item->quantity; ?>" min="1" onchange="recalc()">
                        </div>
                        <div class="col-3">
                            <label class="form-label text-muted small">قیمت واحد (تومان)</label>
                            <input type="number" name="item_unit_price[]" class="form-control form-control-sm item-price" value="<?php echo $item->unit_price; ?>" min="0" onchange="recalc()" dir="ltr" style="text-align:left;">
                        </div>
                        <div class="col-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="item-row row g-2 mb-2 pb-2 border-bottom">
                        <div class="col-5">
                            <label class="form-label text-muted small">شرح <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm item-search-input" placeholder="🔍 جستجو یا تایپ نام آیتم..." oninput="filterItems(this)" autocomplete="off">
                            <input type="hidden" name="item_description[]" class="item-description-hidden" value="">
                            <input type="hidden" name="item_category[]" class="item-category" value="">
                            <div class="item-dropdown mt-1" style="display:none;position:absolute;z-index:1000;background:#fff;border:1px solid #ddd;border-radius:4px;max-height:200px;overflow-y:auto;width:calc(100% - 10px);box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>
                        </div>
                        <div class="col-2"><label class="form-label text-muted small">تعداد</label><input type="number" name="item_quantity[]" class="form-control form-control-sm item-qty" value="1" min="1" onchange="recalc()"></div>
                        <div class="col-3"><label class="form-label text-muted small">قیمت واحد (تومان)</label><input type="number" name="item_unit_price[]" class="form-control form-control-sm item-price" value="0" min="0" onchange="recalc()" dir="ltr" style="text-align:left;"></div>
                        <div class="col-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Financial Details -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-cash me-2" style="color:<?php echo $primaryColor; ?>;"></i>جزییات مالی</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6"><label class="form-label text-muted small fw-medium">درصد مالیات</label><input type="number" name="tax_percent" class="form-control" id="taxPercent" value="<?php echo $invoice->tax_percent ?? 0; ?>" min="0" max="100" onchange="recalc()" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">هزینه خدمات (تومان)</label><input type="number" name="service_fee" class="form-control" id="serviceFee" value="<?php echo $invoice->service_fee ?? 0; ?>" min="0" onchange="recalc()" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">تخفیف (تومان)</label><input type="number" name="discount_amount" class="form-control" id="discountAmount" value="<?php echo $invoice->discount_amount ?? 0; ?>" min="0" onchange="recalc()" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">مبلغ بیعانه (تومان)</label><input type="number" name="deposit_amount" class="form-control" id="depositAmount" value="<?php echo $invoice->deposit_amount ?? 0; ?>" min="0" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">تاریخ اعتبار</label><input type="date" name="valid_until" class="form-control" value="<?php echo $invoice->valid_until ?? ''; ?>"></div>
                    <div class="col-6"><label class="form-label text-muted small fw-medium">واحد پول</label><select name="currency" class="form-select"><option value="IRR" <?php echo ($invoice->currency ?? 'IRR')=='IRR'?'selected':''; ?>>تومان</option><option value="USD" <?php echo ($invoice->currency ?? '')=='USD'?'selected':''; ?>>دلار</option><option value="EUR" <?php echo ($invoice->currency ?? '')=='EUR'?'selected':''; ?>>یورو</option><option value="AED" <?php echo ($invoice->currency ?? '')=='AED'?'selected':''; ?>>درهم</option><option value="TRY" <?php echo ($invoice->currency ?? '')=='TRY'?'selected':''; ?>>لیر</option></select></div>
                </div>
            </div>
        </div>

        <!-- Notes & Footer -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2" style="color:<?php echo $primaryColor; ?>;"></i>توضیحات و شرایط</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label text-muted small fw-medium">توضیحات</label><textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($invoice->notes ?? ''); ?></textarea></div>
                    <div class="col-12"><label class="form-label text-muted small fw-medium">شرایط پرداخت</label><textarea name="payment_terms" class="form-control" rows="2"><?php echo htmlspecialchars($invoice->payment_terms ?? $invSet['invoice_terms'] ?? ''); ?></textarea></div>
                    <div class="col-12"><label class="form-label text-muted small fw-medium">متن فوتر فاکتور</label><textarea name="footer_text" class="form-control" rows="2"><?php echo htmlspecialchars($invoice->footer_text ?? $invSet['invoice_footer_text'] ?? ''); ?></textarea></div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn w-100 mt-3 fw-bold" style="background:<?php echo $primaryColor; ?>;color:#fff;"><i class="bi bi-check-circle me-1"></i>بروزرسانی فاکتور</button>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-calculator me-2" style="color:<?php echo $primaryColor; ?>;"></i>خلاصه محاسبات</h6></div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">شب‌ها</small><strong class="fs-5" style="color:<?php echo $primaryColor; ?>;" id="calcNights"><?php echo $invoice->nights; ?></strong></div></div>
                    <div class="col-6"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block" style="font-size:11px;">آیتم‌ها</small><strong class="fs-5" style="color:<?php echo $primaryColor; ?>;" id="calcItems"><?php echo count($items); ?></strong></div></div>
                </div>
                <div class="bg-light rounded p-3">
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">جمع کل</small><strong id="calcSubtotal"><?php echo number_format($invoice->subtotal ?? $invoice->total_amount ?? 0); ?> تومان</strong></div>
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">مالیات (<span id="calcTaxPct"><?php echo $invoice->tax_percent ?? 0; ?></span>%)</small><strong id="calcTaxAmount"><?php echo number_format($invoice->tax_amount ?? 0); ?> تومان</strong></div>
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">خدمات</small><strong id="calcServiceFee"><?php echo number_format($invoice->service_fee ?? 0); ?> تومان</strong></div>
                    <div class="d-flex justify-content-between mb-1"><small class="text-muted">تخفیف</small><strong class="text-danger" id="calcDiscount"><?php echo number_format($invoice->discount_amount ?? 0); ?> تومان</strong></div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between"><strong>مبلغ نهایی</strong><strong style="color:<?php echo $successColor; ?>;" class="fs-5" id="calcFinalAmount"><?php echo number_format($invoice->final_amount); ?> تومان</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>

<script>
var catalogItems = [];

fetch(CRM_BASE_URL + '/hotel-invoice/items-catalog/api')
.then(function(r) { return r.json(); })
.then(function(data) {
    if (data.success && data.items) { catalogItems = data.items; }
    recalc();
})
.catch(function() { console.log('Failed to load catalog'); });

function filterItems(input) {
    var row = input.closest('.item-row');
    var dd = row.querySelector('.item-dropdown');
    var hidden = row.querySelector('.item-description-hidden');
    var catInput = row.querySelector('.item-category');
    var priceInput = row.querySelector('.item-price');
    var q = input.value.trim();

    if (q.length === 0) {
        // Don't clear hidden field - keep existing value
        dd.style.display = 'none';
        recalc();
        return;
    }

    var ql = q.toLowerCase();
    var filtered = catalogItems.filter(function(item) {
        return item.name.toLowerCase().indexOf(ql) !== -1 || (item.description && item.description.toLowerCase().indexOf(ql) !== -1);
    });

    if (filtered.length === 0) {
        hidden.value = q;
        catInput.value = 'general';
        dd.innerHTML = '<div style="padding:6px 10px;color:#999;font-size:12px;">آیتمی یافت نشد.</div>';
        dd.style.display = 'block';
        recalc();
        return;
    }

    var html = '';
    filtered.forEach(function(item) {
        var price = parseFloat(item.default_price) || 0;
        html += '<div class="item-option" data-value="' + item.name.replace(/"/g,'"') + '" data-price="' + price + '" data-category="' + (item.category||'general') + '" style="padding:6px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:13px;" onmouseover="this.style.background=\'#f0f4ff\'" onmouseout="this.style.background=\'\'" onclick="selectItem(this)">' + item.name + ' <small style="color:#999;">(' + formatNumber(price) + ' ت)</small> <small style="color:#0d6efd;">[' + (item.category||'عمومی') + ']</small></div>';
    });
    dd.innerHTML = html;
    dd.style.display = 'block';
}

function selectItem(el) {
    var row = el.closest('.item-row');
    var input = row.querySelector('.item-search-input');
    var hidden = row.querySelector('.item-description-hidden');
    var catInput = row.querySelector('.item-category');
    var priceInput = row.querySelector('.item-price');
    var dd = row.querySelector('.item-dropdown');

    input.value = el.getAttribute('data-value');
    hidden.value = el.getAttribute('data-value');
    catInput.value = el.getAttribute('data-category') || 'general';
    priceInput.value = el.getAttribute('data-price') || '0';
    dd.style.display = 'none';
    recalc();
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.item-row')) {
        document.querySelectorAll('.item-dropdown').forEach(function(d) { d.style.display = 'none'; });
    }
});

function addItem() {
    var c = document.getElementById('itemsContainer');
    var r = document.createElement('div');
    r.className = 'item-row row g-2 mb-2 pb-2 border-bottom';
    r.innerHTML = '<div class="col-5"><input type="text" class="form-control form-control-sm item-search-input" placeholder="🔍 جستجو یا تایپ نام آیتم..." oninput="filterItems(this)" autocomplete="off"><input type="hidden" name="item_description[]" class="item-description-hidden" value=""><input type="hidden" name="item_category[]" class="item-category" value=""><div class="item-dropdown mt-1" style="display:none;position:absolute;z-index:1000;background:#fff;border:1px solid #ddd;border-radius:4px;max-height:200px;overflow-y:auto;width:calc(100% - 10px);box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div></div><div class="col-2"><input type="number" name="item_quantity[]" class="form-control form-control-sm item-qty" value="1" min="1" onchange="recalc()"></div><div class="col-3"><input type="number" name="item_unit_price[]" class="form-control form-control-sm item-price" value="0" min="0" onchange="recalc()" dir="ltr" style="text-align:left;"></div><div class="col-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button></div>';
    c.appendChild(r);
    recalc();
}

function removeItem(btn) {
    var c = document.getElementById('itemsContainer');
    if (c.children.length > 1) { btn.closest('.item-row').remove(); recalc(); }
}

function getNights() {
    var ci=document.getElementById('checkInDate').value, co=document.getElementById('checkOutDate').value;
    if(!ci||!co) return 0; var n=Math.ceil((new Date(co)-new Date(ci))/(1000*60*60*24)); return (isNaN(n)||n<0)?0:n;
}

function recalc() {
    var nights=getNights(), subtotal=0, itemCount=0;
    document.querySelectorAll('.item-row').forEach(function(row) {
        var h=row.querySelector('.item-description-hidden');
        if(!h||!h.value.trim()) return;
        var qty=parseFloat(row.querySelector('.item-qty').value)||0, price=parseFloat(row.querySelector('.item-price').value)||0, cat=(row.querySelector('.item-category')||{}).value||'';
        subtotal += (cat==='hotel'&&nights>0) ? qty*price*nights : qty*price; itemCount++;
    });
    var tp=parseFloat(document.getElementById('taxPercent').value)||0, ta=subtotal*(tp/100), sf=parseFloat(document.getElementById('serviceFee').value)||0, disc=parseFloat(document.getElementById('discountAmount').value)||0, fa=subtotal+ta+sf-disc;
    document.getElementById('calcNights').textContent=nights; document.getElementById('calcItems').textContent=itemCount;
    document.getElementById('calcSubtotal').textContent=formatNumber(subtotal)+' تومان'; document.getElementById('calcTaxPct').textContent=tp; document.getElementById('calcTaxAmount').textContent=formatNumber(ta)+' تومان'; document.getElementById('calcServiceFee').textContent=formatNumber(sf)+' تومان'; document.getElementById('calcDiscount').textContent=formatNumber(disc)+' تومان'; document.getElementById('calcFinalAmount').textContent=formatNumber(fa)+' تومان';
}

function formatNumber(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,','); }

document.addEventListener('DOMContentLoaded',function(){recalc();});
</script>