<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-cash-coin me-2" style="color:#e67e22;"></i>نرخنامه هتل‌ها</h5>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-rates/display" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-eye me-1"></i>مشاهده عمومی</a>
        <button class="btn btn-warning btn-sm fw-bold" onclick="openAddModal()"><i class="bi bi-plus-circle me-1"></i>افزودن نرخ</button>
    </div>
</div>

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
            <div class="col-auto"><input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateFrom); ?>" placeholder="از تاریخ"></div>
            <div class="col-auto"><input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateTo); ?>" placeholder="تا تاریخ"></div>
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
                    <th>#</th>
                    <th>هتل</th>
                    <th>نوع اتاق</th>
                    <th>تاریخ</th>
                    <th>فصل</th>
                    <th class="text-center">اقامت</th>
                    <th class="text-center">اقامت+صبحانه</th>
                    <th class="text-center">اقامت+صبحانه+ناهار</th>
                    <th class="text-center">فولبرد</th>
                    <th class="text-center">فولبرد انتخابی</th>
                    <th class="text-center">بوفه</th>
                    <th>ثبت کننده</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rates)): ?>
                <tr><td colspan="13" class="text-center text-muted py-4">نرخنامه‌ای ثبت نشده</td></tr>
                <?php else: ?>
                <?php foreach ($rates as $i => $r): ?>
                <tr>
                    <td class="text-muted"><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($r->hotel_name); ?></strong></td>
                    <td><?php echo htmlspecialchars($r->room_type); ?></td>
                    <td dir="ltr"><?php echo \Core\JDate::displayDate($r->rate_date); ?></td>
                    <td><small><?php echo htmlspecialchars($r->season_label ?? '-'); ?></small></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_ekht > 0 ? number_format($r->price_ekht) : '-'; ?></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_sobhaneh > 0 ? number_format($r->price_sobhaneh) : '-'; ?></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_nahar > 0 ? number_format($r->price_nahar) : '-'; ?></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_fulboard > 0 ? number_format($r->price_fulboard) : '-'; ?></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_entekhabifulboard > 0 ? number_format($r->price_entekhabifulboard) : '-'; ?></td>
                    <td class="text-center" dir="ltr"><?php echo $r->price_boufeh > 0 ? number_format($r->price_boufeh) : '-'; ?></td>
                    <td><small class="text-muted"><?php echo htmlspecialchars($r->creator_name ?? '-'); ?></small></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="editRate(<?php echo $r->id; ?>)" title="ویرایش"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-danger" onclick="deleteRate(<?php echo $r->id; ?>, '<?php echo htmlspecialchars($r->hotel_name); ?>')" title="حذف"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="rateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold" id="rateModalTitle"><i class="bi bi-plus-circle me-1"></i>افزودن نرخ</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rateForm" method="post">
                <div class="modal-body">
                    <input type="hidden" id="rate_id">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">نام هتل <span class="text-danger">*</span></label>
                            <input type="text" name="hotel_name" id="f_hotel_name" class="form-control" list="hotelList" required>
                            <datalist id="hotelList">
                                <?php foreach ($hotels as $h): ?>
                                <option value="<?php echo htmlspecialchars($h->hotel_name); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">نوع اتاق <span class="text-danger">*</span></label>
                            <input type="text" name="room_type" id="f_room_type" class="form-control" required placeholder="مثال: یک تخته، دو تخته، سوئیت">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">تاریخ</label>
                            <input type="date" name="rate_date" id="f_rate_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">فصل/دوره</label>
                            <input type="text" name="season_label" id="f_season_label" class="form-control" placeholder="لو سیزن">
                        </div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-3"><i class="bi bi-cash me-1"></i>قیمت‌ها (تومان)</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">اقامت</label>
                            <input type="number" name="price_ekht" id="f_price_ekht" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">اقامت + صبحانه</label>
                            <input type="number" name="price_sobhaneh" id="f_price_sobhaneh" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">اقامت + صبحانه + ناهار</label>
                            <input type="number" name="price_nahar" id="f_price_nahar" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">فولبرد</label>
                            <input type="number" name="price_fulboard" id="f_price_fulboard" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">فولبرد انتخابی</label>
                            <input type="number" name="price_entekhabifulboard" id="f_price_entekhabifulboard" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">بوفه</label>
                            <input type="number" name="price_boufeh" id="f_price_boufeh" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">توضیحات</label>
                            <textarea name="notes" id="f_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-check-circle me-1"></i>ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="post" style="display:none;"></form>

<script>
var modal;
document.addEventListener('DOMContentLoaded', function() {
    modal = new bootstrap.Modal(document.getElementById('rateModal'));
});

function openAddModal() {
    document.getElementById('rateModalTitle').innerHTML = '<i class="bi bi-plus-circle me-1"></i>افزودن نرخ';
    document.getElementById('rateForm').reset();
    document.getElementById('rate_id').value = '';
    document.getElementById('rateForm').action = '<?php echo $config['url']; ?>/hotel-rates/store';
    document.getElementById('f_rate_date').value = '<?php echo date('Y-m-d'); ?>';
    modal.show();
}

function editRate(id) {
    fetch('<?php echo $config['url']; ?>/hotel-rates/data/' + id)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d || !d.id) return alert('یافت نشد');
            document.getElementById('rateModalTitle').innerHTML = '<i class="bi bi-pencil me-1"></i>ویرایش نرخ';
            document.getElementById('rate_id').value = d.id;
            document.getElementById('f_hotel_name').value = d.hotel_name;
            document.getElementById('f_room_type').value = d.room_type;
            document.getElementById('f_rate_date').value = d.rate_date;
            document.getElementById('f_season_label').value = d.season_label || '';
            document.getElementById('f_price_ekht').value = d.price_ekht;
            document.getElementById('f_price_sobhaneh').value = d.price_sobhaneh;
            document.getElementById('f_price_nahar').value = d.price_nahar;
            document.getElementById('f_price_fulboard').value = d.price_fulboard;
            document.getElementById('f_price_entekhabifulboard').value = d.price_entekhabifulboard;
            document.getElementById('f_price_boufeh').value = d.price_boufeh;
            document.getElementById('f_notes').value = d.notes || '';
            document.getElementById('rateForm').action = '<?php echo $config['url']; ?>/hotel-rates/update/' + id;
            modal.show();
        });
}

function deleteRate(id, name) {
    if (confirm('آیا از حذف نرخ «' + name + '» مطمئن هستید؟')) {
        var f = document.getElementById('deleteForm');
        f.action = '<?php echo $config['url']; ?>/hotel-rates/delete/' + id;
        f.submit();
    }
}
</script>