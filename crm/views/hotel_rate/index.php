<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-cash-coin me-2" style="color:#e67e22;"></i>نرخنامه هتل‌ها</h5>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-rates/display" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-eye me-1"></i>نمایش عمومی</a>
        <button class="btn btn-success btn-sm fw-bold" onclick="openHotelModal()"><i class="bi bi-building me-1"></i>افزودن هتل</button>
        <button class="btn btn-warning btn-sm fw-bold" onclick="openAddModal()"><i class="bi bi-plus-circle me-1"></i>افزودن نرخ</button>
    </div>
</div>

<?php if (empty($hotels)): ?>
<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>ابتدا یک هتل ثبت کنید، سپس می‌توانید برای آن نرخ‌نامه ایجاد کنید.</div>
<?php endif; ?>

<!-- Hotels List -->
<?php if (!empty($hotels)): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white py-2"><h6 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>هتل‌های ثبت شده</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr><th>هتل</th><th>شهر</th><th>ستاره</th><th>توضیحات</th><th>امکانات</th><th>عملیات</th></tr>
                </thead>
                <tbody>
                <?php foreach ($hotels as $h): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($h->hotel_name); ?></strong></td>
                    <td><?php echo htmlspecialchars($h->city ?? '-'); ?></td>
                    <td><?php echo $h->star_rating ? str_repeat('⭐', $h->star_rating) : '-'; ?></td>
                    <td><small><?php echo htmlspecialchars(mb_substr(strip_tags(\Controllers\HotelRateController::md($h->description ?? '')), 0, 50)); ?></small></td>
                    <td><small><?php echo htmlspecialchars(mb_substr(strip_tags(\Controllers\HotelRateController::md($h->facilities ?? '')), 0, 50)); ?></small></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="editHotel(<?php echo $h->id; ?>)" title="ویرایش"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-danger" onclick="deleteHotel(<?php echo $h->id; ?>)" title="حذف"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="hotel" class="form-select form-select-sm">
                    <option value="">همه هتل‌ها</option>
                    <?php foreach ($hotels as $h): ?>
                    <option value="<?php echo htmlspecialchars($h->hotel_name); ?>" <?php echo $hotelFilter === $h->hotel_name ? 'selected' : ''; ?>><?php echo htmlspecialchars($h->hotel_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto"><input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateFrom); ?>"></div>
            <div class="col-auto"><input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateTo); ?>"></div>
            <div class="col-auto"><button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>فیلتر</button></div>
            <div class="col-auto"><a href="<?php echo $config['url']; ?>/hotel-rates" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>پاک</a></div>
        </form>
    </div>
</div>

<!-- Rates Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th>#</th><th>هتل</th><th>نوع اتاق</th><th>از تاریخ</th><th>تا تاریخ</th><th>فصل</th>
                    <th class="text-center">اقامت</th><th class="text-center">اقامت+صبحانه</th><th class="text-center">هافبرد</th>
                    <th class="text-center">فولبرد انتخابی</th><th class="text-center">فولبرد بوفه</th><th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rates)): ?>
                <tr><td colspan="12" class="text-center text-muted py-4">نرخنامه‌ای ثبت نشده</td></tr>
                <?php else: foreach ($rates as $i => $r): ?>
                <tr>
                    <td class="text-muted"><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($r->hotel_name); ?></strong></td>
                    <td><?php echo htmlspecialchars($r->room_type); ?></td>
                    <td dir="ltr"><?php echo \Core\JDate::displayDate($r->date_from); ?></td>
                    <td dir="ltr"><?php echo \Core\JDate::displayDate($r->date_to); ?></td>
                    <td><small><?php echo htmlspecialchars($r->season_label ?? '-'); ?></small></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_ekht > 0 ? number_format($r->price_ekht) : '-'; ?></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_sobhaneh > 0 ? number_format($r->price_sobhaneh) : '-'; ?></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_nahar > 0 ? number_format($r->price_nahar) : '-'; ?></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_entekhabifulboard > 0 ? number_format($r->price_entekhabifulboard) : '-'; ?></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_fulboard_boufeh > 0 ? number_format($r->price_fulboard_boufeh) : '-'; ?></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="editRate(<?php echo $r->id; ?>)" title="ویرایش"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-danger" onclick="deleteRate(<?php echo $r->id; ?>)" title="حذف"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Hotel Modal -->
<div class="modal fade" id="hotelModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header bg-success text-white"><h6 class="modal-title fw-bold" id="hotelModalTitle"><i class="bi bi-building me-1"></i>افزودن هتل</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <form id="hotelForm" method="post"><div class="modal-body">
        <input type="hidden" id="hotel_id">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">نام هتل <span class="text-danger">*</span></label><input type="text" name="hotel_name" id="h_hotel_name" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label fw-bold">شهر</label><input type="text" name="city" id="h_city" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">ستاره</label><select name="star_rating" id="h_star_rating" class="form-select"><option value="">-</option><?php for($s=1;$s<=5;$s++): ?><option value="<?php echo $s; ?>"><?php echo str_repeat('⭐',$s); ?></option><?php endfor; ?></select></div>
            <div class="col-12"><label class="form-label fw-bold">توضیحات هتل</label><textarea name="description" id="h_description" class="form-control" rows="3" placeholder="درباره هتل، موقعیت مکانی، ویژگی‌ها..."></textarea></div>
            <div class="col-12"><label class="form-label fw-bold">امکانات هتل</label><textarea name="facilities" id="h_facilities" class="form-control" rows="2" placeholder="استخر، رستوران، پارکینگ، اینترنت رایگان..."></textarea></div>
        </div>
    </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-success fw-bold"><i class="bi bi-check-circle me-1"></i>ذخیره</button></div></form>
</div></div></div>

<!-- Rate Modal -->
<div class="modal fade" id="rateModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header bg-warning text-dark"><h6 class="modal-title fw-bold" id="rateModalTitle"><i class="bi bi-plus-circle me-1"></i>افزودن نرخ</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="rateForm" method="post"><div class="modal-body">
        <input type="hidden" id="rate_id">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">هتل <span class="text-danger">*</span></label>
                <select name="hotel_id" id="f_hotel_id" class="form-select" required>
                    <option value="">انتخاب هتل...</option>
                    <?php foreach ($hotels as $h): ?><option value="<?php echo $h->id; ?>"><?php echo htmlspecialchars($h->hotel_name); ?><?php echo $h->city ? ' - ' . htmlspecialchars($h->city) : ''; ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label fw-bold">نوع اتاق <span class="text-danger">*</span></label><input type="text" name="room_type" id="f_room_type" class="form-control" required placeholder="یک تخته، دو تخته..."></div>
            <div class="col-md-3"><label class="form-label fw-bold">فصل/دوره</label><input type="text" name="season_label" id="f_season_label" class="form-control" placeholder="لو سیزن"></div>
            <div class="col-md-3"><label class="form-label fw-bold">از تاریخ</label><input type="text" name="date_from" id="f_date_from" class="form-control" placeholder="2025-01-01" value="<?php echo date('Y-m-d'); ?>" autocomplete="off"></div>
            <div class="col-md-3"><label class="form-label fw-bold">تا تاریخ</label><input type="text" name="date_to" id="f_date_to" class="form-control" placeholder="2025-01-30" value="<?php echo date('Y-m-d'); ?>" autocomplete="off"></div>
        </div>
        <hr><h6 class="fw-bold mb-3"><i class="bi bi-cash me-1"></i>قیمت‌ها (تومان)</h6>
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">اقامت</label><input type="number" name="price_ekht" id="f_price_ekht" class="form-control" min="0" value="0"></div>
            <div class="col-md-4"><label class="form-label">اقامت + صبحانه</label><input type="number" name="price_sobhaneh" id="f_price_sobhaneh" class="form-control" min="0" value="0"></div>
            <div class="col-md-4"><label class="form-label">هافبرد</label><input type="number" name="price_nahar" id="f_price_nahar" class="form-control" min="0" value="0"></div>
            <div class="col-md-4"><label class="form-label">فولبرد انتخابی</label><input type="number" name="price_entekhabifulboard" id="f_price_entekhabifulboard" class="form-control" min="0" value="0"></div>
            <div class="col-md-4"><label class="form-label">فولبرد بوفه</label><input type="number" name="price_fulboard_boufeh" id="f_price_fulboard_boufeh" class="form-control" min="0" value="0"></div>
        </div>
        <div class="row mt-3"><div class="col-12"><label class="form-label">توضیحات</label><textarea name="notes" id="f_notes" class="form-control" rows="2"></textarea></div></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-check-circle me-1"></i>ذخیره</button></div></form>
</div></div></div>

<form id="deleteForm" method="post" style="display:none;"></form>

<script>
var hotelModal, rateModal;
document.addEventListener('DOMContentLoaded', function() {
    hotelModal = new bootstrap.Modal(document.getElementById('hotelModal'));
    rateModal = new bootstrap.Modal(document.getElementById('rateModal'));

    // Initialize Jalali datepicker on modal date fields
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.pDatepicker !== 'undefined') {
        var $df = jQuery('#f_date_from');
        var $dt = jQuery('#f_date_to');
        // Convert current Gregorian value to Jalali for display
        function toJalaliStr(gDate) {
            try {
                if (!gDate) return '';
                var parts = gDate.split('-');
                if (parts.length !== 3) return gDate;
                var pd = new persianDate();
                pd.toCalendar('persian');
                var j = pd.convert(new Date(parseInt(parts[0]), parseInt(parts[1])-1, parseInt(parts[2])));
                return j.year() + '/' + String(j.month()).padStart(2,'0') + '/' + String(j.date()).padStart(2,'0');
            } catch(e) { return gDate; }
        }
        function toGregorianStr(unix) {
            var d = new Date(unix);
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        }
        // Set initial Jalali display values
        $df.val(toJalaliStr($df.val()));
        $dt.val(toJalaliStr($dt.val()));
        // Init datepicker on from
        $df.pDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: false,
            autoClose: true,
            calendar: { persian: { locale: 'fa' } },
            onSelect: function(unix) { $df.attr('data-gregorian', toGregorianStr(unix)); }
        });
        // Init datepicker on to
        $dt.pDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: false,
            autoClose: true,
            calendar: { persian: { locale: 'fa' } },
            onSelect: function(unix) { $dt.attr('data-gregorian', toGregorianStr(unix)); }
        });
        // Before form submit, set Gregorian values in hidden fields
        var form = document.getElementById('rateForm');
        if (form) {
            form.addEventListener('submit', function() {
                var dg = $df.attr('data-gregorian');
                var tg = $dt.attr('data-gregorian');
                if (dg) $df.val(dg);
                if (tg) $dt.val(tg);
            });
        }
    }
});

function openHotelModal() {
    document.getElementById('hotelModalTitle').innerHTML = '<i class="bi bi-building me-1"></i>افزودن هتل';
    document.getElementById('hotelForm').reset();
    document.getElementById('hotel_id').value = '';
    document.getElementById('hotelForm').action = '<?php echo $config['url']; ?>/hotel-rates/hotel/store';
    hotelModal.show();
}

function editHotel(id) {
    fetch('<?php echo $config['url']; ?>/hotel-rates/hotel/data/' + id).then(function(r){return r.json();}).then(function(d){
        if(!d||!d.id) return alert('یافت نشد');
        document.getElementById('hotelModalTitle').innerHTML = '<i class="bi bi-pencil me-1"></i>ویرایش هتل';
        document.getElementById('hotel_id').value = d.id;
        document.getElementById('h_hotel_name').value = d.hotel_name;
        document.getElementById('h_city').value = d.city||'';
        document.getElementById('h_star_rating').value = d.star_rating||'';
        document.getElementById('h_description').value = d.description||'';
        document.getElementById('h_facilities').value = d.facilities||'';
        document.getElementById('hotelForm').action = '<?php echo $config['url']; ?>/hotel-rates/hotel/update/' + id;
        hotelModal.show();
    });
}

function deleteHotel(id) {
    if (confirm('آیا از حذف این هتل مطمئن هستید؟')) {
        var f = document.getElementById('deleteForm');
        f.action = '<?php echo $config['url']; ?>/hotel-rates/hotel/delete/' + id;
        f.submit();
    }
}

function openAddModal() {
    document.getElementById('rateModalTitle').innerHTML = '<i class="bi bi-plus-circle me-1"></i>افزودن نرخ';
    document.getElementById('rateForm').reset();
    document.getElementById('rate_id').value = '';
    document.getElementById('rateForm').action = '<?php echo $config['url']; ?>/hotel-rates/store';
    var today = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('f_date_from').value = today;
    document.getElementById('f_date_to').value = today;
    // Re-init Jalali display
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.pDatepicker !== 'undefined') {
        try {
            var $df = jQuery('#f_date_from');
            var $dt = jQuery('#f_date_to');
            var parts = today.split('-');
            var pd = new persianDate();
            pd.toCalendar('persian');
            var j = pd.convert(new Date(parseInt(parts[0]), parseInt(parts[1])-1, parseInt(parts[2])));
            var jStr = j.year() + '/' + String(j.month()).padStart(2,'0') + '/' + String(j.date()).padStart(2,'0');
            $df.val(jStr).attr('data-gregorian', today);
            $dt.val(jStr).attr('data-gregorian', today);
        } catch(e) {}
    }
    rateModal.show();
}

function editRate(id) {
    fetch('<?php echo $config['url']; ?>/hotel-rates/data/' + id).then(function(r){return r.json();}).then(function(d){
        if(!d||!d.id) return alert('یافت نشد');
        document.getElementById('rateModalTitle').innerHTML = '<i class="bi bi-pencil me-1"></i>ویرایش نرخ';
        document.getElementById('rate_id').value = d.id;
        document.getElementById('f_hotel_id').value = d.hotel_id;
        document.getElementById('f_room_type').value = d.room_type;
        document.getElementById('f_date_from').value = d.date_from;
        document.getElementById('f_date_to').value = d.date_to;
        document.getElementById('f_season_label').value = d.season_label||'';
        document.getElementById('f_price_ekht').value = d.price_ekht;
        document.getElementById('f_price_sobhaneh').value = d.price_sobhaneh;
        document.getElementById('f_price_nahar').value = d.price_nahar;
        document.getElementById('f_price_entekhabifulboard').value = d.price_entekhabifulboard;
        document.getElementById('f_price_fulboard_boufeh').value = d.price_fulboard_boufeh;
        document.getElementById('f_notes').value = d.notes||'';
        document.getElementById('rateForm').action = '<?php echo $config['url']; ?>/hotel-rates/update/' + id;
        rateModal.show();
    });
}

function deleteRate(id) {
    if (confirm('آیا از حذف این نرخ مطمئن هستید؟')) {
        var f = document.getElementById('deleteForm');
        f.action = '<?php echo $config['url']; ?>/hotel-rates/delete/' + id;
        f.submit();
    }
}
</script>