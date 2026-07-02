<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i>مدیریت آیتم‌های فاکتور</h5>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/hotel-invoice" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<!-- Add New Item -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>افزودن آیتم جدید</h6></div>
    <div class="card-body">
        <form method="POST" action="<?php echo $config['url']; ?>/hotel-invoice/items-catalog/store" data-ajax="true">
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small">نام آیتم <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="نام آیتم" required>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small">توضیحات</label>
                    <input type="text" name="description" class="form-control" placeholder="توضیحات اختیاری">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label text-muted small">قیمت پیش‌فرض (تومان)</label>
                    <input type="number" name="default_price" class="form-control" value="0" min="0" dir="ltr" style="text-align:left;">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label text-muted small">دسته‌بندی</label>
                    <select name="category" class="form-select">
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
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus me-1"></i>افزودن</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Items List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2"></i>لیست آیتم‌ها</h6></div>
    <div class="card-body">
        <?php if (empty($items)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
            <p>هنوز آیتمی تعریف نشده.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام آیتم</th>
                        <th>توضیحات</th>
                        <th>قیمت پیش‌فرض</th>
                        <th>دسته‌بندی</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($item->name); ?></strong></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars($item->description ?? '-'); ?></small></td>
                        <td><strong class="text-success"><?php echo number_format($item->default_price); ?> تومان</strong></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item->category); ?></span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?php echo $item->id; ?>)"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteItem(id) {
    if (!confirm('آیا از حذف این آیتم مطمئن هستید؟')) return;
    fetch(CRM_BASE_URL + '/hotel-invoice/items-catalog/delete/' + id, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { if (data.success) { location.reload(); } else { alert(data.message || 'خطا'); } })
    .catch(function() { alert('خطای شبکه'); });
}
</script>