<div class="page-header">
    <h5>😞 مدیریت دلایل شکست معاملات</h5>
    <button class="btn btn-primary" onclick="openModal('addModal')">➕ افزودن دلیل جدید</button>
</div>

<div class="card">
    <?php if (empty($reasons)): ?>
        <div class="empty-state">
            <div class="empty-icon">😞</div>
            <h5>هنوز دلیلی ثبت نشده</h5>
            <p>دلایل شکست معاملات را تعریف کنید تا در آمار و گزارشات استفاده شوند.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>آیکون</th>
                        <th>نام دلیل</th>
                        <th>ترتیب</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
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
                            <button class="btn btn-sm btn-secondary" onclick="editReason(<?php echo htmlspecialchars(json_encode($reason)); ?>)">✏️</button>
                            <form method="POST" action="<?php echo $config['url']; ?>/settings/loss-reasons/delete" data-ajax="true" style="display:inline" onsubmit="return confirm('آیا مطمئن هستید؟')">
                                <input type="hidden" name="id" value="<?php echo $reason->id; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
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
            <h5>افزودن دلیل شکست جدید</h5>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/loss-reasons/store" data-ajax="true">
            <div class="modal-body">
                <div class="ajax-error" style="display:none;color:var(--danger);margin-bottom:12px"></div>
                <div class="form-group">
                    <label class="form-label">نام دلیل شکست *</label>
                    <input type="text" name="name" class="form-input" required placeholder="مثال: قیمت بالا">
                </div>
                <div class="form-group">
                    <label class="form-label">آیکون</label>
                    <input type="text" name="icon" class="form-input" value="😞" placeholder="😞" style="width:80px">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">💾 ذخیره</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">انصراف</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5>ویرایش دلیل شکست</h5>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/loss-reasons/update" data-ajax="true">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="ajax-error" style="display:none;color:var(--danger);margin-bottom:12px"></div>
                <div class="form-group">
                    <label class="form-label">نام دلیل شکست *</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">آیکون</label>
                    <input type="text" name="icon" id="edit_icon" class="form-input" style="width:80px">
                </div>
                <div class="form-group">
                    <label class="form-label">ترتیب نمایش</label>
                    <input type="number" name="sort_order" id="edit_sort_order" class="form-input" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="is_active" id="edit_is_active" checked> فعال
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">💾 ذخیره تغییرات</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">انصراف</button>
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