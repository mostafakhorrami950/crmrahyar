<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0">😞 مدیریت دلایل شکست معاملات</h5>
    <button class="btn btn-primary" onclick="openModal('addModal')"><i class="bi bi-plus-circle me-1"></i> افزودن دلیل جدید</button>
</div>

<div class="card">
    <?php if (empty($reasons)): ?>
        <div class="empty-state">
            <div class="empty-icon">😞</div>
            <h5 class="fw-bold mb-0">هنوز دلیلی ثبت نشده</h5>
            <p>دلایل شکست معاملات را تعریف کنید تا در آمار و گزارشات استفاده شوند.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th class="text-nowrap">آیکون</th>
                        <th class="text-nowrap">نام دلیل</th>
                        <th class="text-nowrap">ترتیب</th>
                        <th class="text-nowrap">وضعیت</th>
                        <th class="text-nowrap">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reasons as $reason): ?>
                    <tr>
                        <td style="font-size:20px"><?php echo $reason->icon; ?></td>
                        <td><?php echo htmlspecialchars($reason->name); ?></td>
                        <td><?php echo $reason->sort_order; ?></td>
                        <td>
                            <?php if ($reason->is_active): ?>
                                <span class="badge badge-success">فعال</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">غیرفعال</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" onclick="editReason(<?php echo htmlspecialchars(json_encode($reason)); ?>)"><i class="bi bi-pencil me-1"></i></button>
                            <form method="POST" action="<?php echo $config['url']; ?>/settings/loss-reasons/delete" data-ajax="true" style="display:inline" onsubmit="return confirm('آیا مطمئن هستید؟')">
                                <input type="hidden" name="id" value="<?php echo $reason->id; ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5 class="fw-bold mb-0">افزودن دلیل شکست جدید</h5>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/loss-reasons/store" data-ajax="true">
            <div class="modal-body">
                <div class="ajax-error" style="display:none;color:var(--danger);margin-bottom:12px"></div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">نام دلیل شکست *</label>
                    <input type="text" name="name" class="form-input" required placeholder="مثال: قیمت بالا">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">آیکون</label>
                    <input type="text" name="icon" class="form-input" value="😞" placeholder="😞" style="width:80px">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره</button>
                <button type="button" class="btn btn-outline-secondary" onclick="closeModal('addModal')">انصراف</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5 class="fw-bold mb-0">ویرایش دلیل شکست</h5>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/loss-reasons/update" data-ajax="true">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="ajax-error" style="display:none;color:var(--danger);margin-bottom:12px"></div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">نام دلیل شکست *</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">آیکون</label>
                    <input type="text" name="icon" id="edit_icon" class="form-input" style="width:80px">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">ترتیب نمایش</label>
                    <input type="number" name="sort_order" id="edit_sort_order" class="form-input" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">
                        <input type="checkbox" name="is_active" id="edit_is_active" checked> فعال
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره تغییرات</button>
                <button type="button" class="btn btn-outline-secondary" onclick="closeModal('editModal')">انصراف</button>
            </div>
        </form>
    </div>
</div>

<script>
function editReason(reason) {
    document.getElementById('edit_id').value = reason.id;
    document.getElementById('edit_name').value = reason.name;
    document.getElementById('edit_icon').value = reason.icon;
    document.getElementById('edit_sort_order').value = reason.sort_order || 0;
    document.getElementById('edit_is_active').checked = reason.is_active == 1;
    openModal('editModal');
}
</script>