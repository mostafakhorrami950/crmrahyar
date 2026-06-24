<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-success"></i>مدیریت دلایل موفقیت معاملات</h5>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/settings" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت به تنظیمات</a>
        <a href="<?php echo $config['url']; ?>/settings/loss-reasons" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle me-1"></i>دلایل شکست</a>
        <button class="btn btn-success btn-sm" onclick="showAddWinReasonModal()"><i class="bi bi-plus-lg me-1"></i>افزودن دلیل موفقیت</button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="50">آیکون</th>
                        <th>نام دلیل موفقیت</th>
                        <th width="80">ترتیب</th>
                        <th width="80">وضعیت</th>
                        <th width="150">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reasons)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-trophy fs-1 d-block mb-2 opacity-25"></i>هنوز دلیلی ثبت نشده</td></tr>
                <?php else: ?>
                    <?php foreach ($reasons as $r): ?>
                    <tr>
                        <td class="text-center fs-4"><?php echo htmlspecialchars($r->icon ?? '✅'); ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($r->name); ?></td>
                        <td><?php echo $r->sort_order; ?></td>
                        <td>
                            <?php if ($r->is_active): ?>
                                <span class="badge bg-success">فعال</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">غیرفعال</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="editWinReason(<?php echo htmlspecialchars(json_encode($r)); ?>)"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteWinReason(<?php echo $r->id; ?>, '<?php echo htmlspecialchars($r->name); ?>')"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Win Reason Modal -->
<div class="modal fade" id="winReasonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white border-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-trophy me-2"></i><span id="winReasonModalTitle">افزودن دلیل موفقیت</span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="winReasonError" class="alert alert-danger" style="display:none;"></div>
                <form id="winReasonForm" onsubmit="return saveWinReason(event)">
                    <input type="hidden" name="id" id="winReasonId" value="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">نام دلیل موفقیت <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="winReasonName" required placeholder="مثلاً: قیمت مناسب">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">آیکون</label>
                        <input type="text" class="form-control" name="icon" id="winReasonIcon" value="✅" style="font-size:1.5rem;" maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ترتیب نمایش</label>
                        <input type="number" class="form-control" name="sort_order" id="winReasonSort" value="0">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="winReasonActive" checked>
                        <label class="form-check-label" for="winReasonActive">فعال</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-success" onclick="document.getElementById('winReasonForm').requestSubmit()"><i class="bi bi-check-lg me-1"></i>ذخیره</button>
            </div>
        </div>
    </div>
</div>

<script>
function showAddWinReasonModal() {
    document.getElementById('winReasonModalTitle').textContent = 'افزودن دلیل موفقیت';
    document.getElementById('winReasonForm').reset();
    document.getElementById('winReasonId').value = '';
    document.getElementById('winReasonIcon').value = '✅';
    document.getElementById('winReasonActive').checked = true;
    document.getElementById('winReasonError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('winReasonModal')).show();
}

function editWinReason(r) {
    document.getElementById('winReasonModalTitle').textContent = 'ویرایش دلیل موفقیت';
    document.getElementById('winReasonId').value = r.id;
    document.getElementById('winReasonName').value = r.name;
    document.getElementById('winReasonIcon').value = r.icon || '✅';
    document.getElementById('winReasonSort').value = r.sort_order || 0;
    document.getElementById('winReasonActive').checked = r.is_active == 1;
    document.getElementById('winReasonError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('winReasonModal')).show();
}

function saveWinReason(e) {
    e.preventDefault();
    var form = document.getElementById('winReasonForm');
    var fd = new FormData(form);
    var isEdit = !!document.getElementById('winReasonId').value;
    var url = '<?php echo $config['url']; ?>/settings/win-reasons/' + (isEdit ? 'update' : 'store');
    
    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.href = '<?php echo $config['url']; ?>' + data.redirect;
        } else {
            document.getElementById('winReasonError').textContent = data.message;
            document.getElementById('winReasonError').style.display = 'block';
        }
    })
    .catch(function() {
        document.getElementById('winReasonError').textContent = 'خطا در ارتباط با سرور';
        document.getElementById('winReasonError').style.display = 'block';
    });
    return false;
}

function deleteWinReason(id, name) {
    if (!confirm('آیا از حذف «' + name + '» مطمئن هستید؟')) return;
    fetch('<?php echo $config['url']; ?>/settings/win-reasons/delete', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'id=' + id
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.href = '<?php echo $config['url']; ?>' + data.redirect;
        } else {
            alert(data.message);
        }
    });
}
</script>