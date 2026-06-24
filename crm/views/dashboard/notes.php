<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-sticky me-2 text-warning"></i>مدیریت یادداشت‌ها</h5>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" onclick="new bootstrap.Modal(document.getElementById('addNoteModal')).show()"><i class="bi bi-plus me-1"></i>یادداشت جدید</button>
        <button class="btn btn-outline-warning btn-sm" onclick="new bootstrap.Modal(document.getElementById('addAllModal')).show()"><i class="bi bi-broadcast me-1"></i>ارسال به همه</button>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">کاربر</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">همه</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>" <?php echo $filterUser == $u->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">وضعیت</label>
                <select name="archived" class="form-select form-select-sm">
                    <option value="0" <?php echo !$filterArchived ? 'selected' : ''; ?>>فعال</option>
                    <option value="1" <?php echo $filterArchived ? 'selected' : ''; ?>>آرشیو شده</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="bi bi-search me-1"></i>فیلتر</button>
            </div>
        </form>
    </div>
</div>

<!-- Notes List -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($notes)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-sticky fs-1 d-block mb-2 opacity-25"></i>
            <p>یادداشتی یافت نشد</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="small fw-bold">#</th>
                        <th class="small fw-bold">گیرنده</th>
                        <th class="small fw-bold">متن</th>
                        <th class="small fw-bold">نویسنده</th>
                        <th class="small fw-bold">تاریخ</th>
                        <th class="small fw-bold">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($notes as $note): ?>
                <tr>
                    <td class="small text-muted"><?php echo $note->id; ?></td>
                    <td><strong class="small"><?php echo htmlspecialchars($note->target_name ?? '-'); ?></strong></td>
                    <td>
                        <?php if ($note->is_pinned): ?><span class="badge bg-warning text-dark me-1" title="پین شده"><i class="bi bi-pin-angle"></i></span><?php endif; ?>
                        <span class="small"><?php echo htmlspecialchars(mb_substr($note->note, 0, 100)); ?></span>
                    </td>
                    <td><small class="text-muted"><?php echo htmlspecialchars($note->author_name ?? '-'); ?></small></td>
                    <td><small class="text-muted"><?php echo \Core\JDate::displayDate($note->created_at); ?></small></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" onclick="editNote(<?php echo $note->id; ?>, '<?php echo htmlspecialchars(addslashes($note->note)); ?>', <?php echo $note->is_pinned; ?>)" title="ویرایش"><i class="bi bi-pencil"></i></button>
                            <?php if (!$note->is_archived): ?>
                            <button class="btn btn-sm btn-outline-warning" onclick="archiveNote(<?php echo $note->id; ?>)" title="آرشیو"><i class="bi bi-archive"></i></button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(<?php echo $note->id; ?>)" title="حذف"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-sticky me-2"></i>افزودن یادداشت</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm" onsubmit="submitNote(event, '<?php echo $config['url']; ?>/dashboard/add-note')">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">کاربر</label>
                        <select name="target_user_id" class="form-select" required>
                            <option value="">انتخاب کاربر</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">متن یادداشت</label>
                        <textarea name="note" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_pinned" value="1" class="form-check-input" id="pinNote">
                        <label class="form-check-label" for="pinNote">پین شده</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check me-1"></i>ذخیره</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send to All Modal -->
<div class="modal fade" id="addAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold"><i class="bi bi-broadcast me-2"></i>ارسال یادداشت به همه کاربران</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAllForm" onsubmit="submitNote(event, '<?php echo $config['url']; ?>/dashboard/add-note-all')">
                <div class="modal-body">
                    <div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>این یادداشت برای تمام کاربران فعال سیستم ارسال خواهد شد.</div>
                    <div class="mb-3">
                        <label class="form-label">متن یادداشت</label>
                        <textarea name="note" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_pinned" value="1" class="form-check-input" id="pinAllNote">
                        <label class="form-check-label" for="pinAllNote">پین شده</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-send me-1"></i>ارسال به همه</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>ویرایش یادداشت</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editNoteForm" onsubmit="submitEditNote(event)">
                <input type="hidden" name="note_id" id="editNoteId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">متن یادداشت</label>
                        <textarea name="note" id="editNoteText" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_pinned" value="1" class="form-check-input" id="editNotePinned">
                        <label class="form-check-label" for="editNotePinned">پین شده</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><i class="bi bi-check me-1"></i>ذخیره</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var baseUrl = '<?php echo $config['url']; ?>';

function submitNote(e, url) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);
    fetch(url, { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.message || 'خطا'); }
    });
}

function editNote(id, text, pinned) {
    document.getElementById('editNoteId').value = id;
    document.getElementById('editNoteText').value = text;
    document.getElementById('editNotePinned').checked = pinned == 1;
    new bootstrap.Modal(document.getElementById('editNoteModal')).show();
}

function submitEditNote(e) {
    e.preventDefault();
    var form = document.getElementById('editNoteForm');
    var formData = new FormData(form);
    fetch(baseUrl + '/dashboard/edit-note', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.message || 'خطا'); }
    });
}

function archiveNote(id) {
    if (!confirm('آیا از آرشیو این یادداشت اطمینان دارید؟')) return;
    fetch(baseUrl + '/dashboard/archive-note', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'note_id=' + id
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) location.reload();
    });
}

function deleteNote(id) {
    if (!confirm('آیا از حذف این یادداشت اطمینان دارید؟')) return;
    fetch(baseUrl + '/dashboard/delete-note', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'note_id=' + id
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) location.reload();
    });
}
</script>