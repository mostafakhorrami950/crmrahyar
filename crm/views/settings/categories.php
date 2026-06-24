<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0">📂 مدیریت دسته‌بندی مخاطبین</h5>
    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addCategoryModal')"><i class="bi bi-plus-circle me-1"></i> دسته‌بندی جدید</button>
</div>

<div class="card">
    <div class="ajax-error alert alert-danger" style="display:none;"></div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">رنگ</th>
                    <th class="text-nowrap">نام دسته‌بندی</th>
                    <th class="text-nowrap">توضیحات</th>
                    <th class="text-nowrap">تعداد مخاطبین</th>
                    <th class="text-nowrap">پیش‌فرض</th>
                    <th class="text-nowrap">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                <tr><td colspan="6" class="text-center py-4">هیچ دسته‌بندی تعریف نشده است.</td></tr>
                <?php endif; ?>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:<?php echo htmlspecialchars($cat->color ?? '#6B7280'); ?>;border:2px solid #ddd;"></span></td>
                    <td><strong><?php echo htmlspecialchars($cat->name); ?></strong></td>
                    <td><small><?php echo htmlspecialchars($cat->description ?? '-'); ?></small></td>
                    <td><span class="badge bg-info"><?php echo (int)($countMap[$cat->id] ?? 0); ?></span></td>
                    <td><?php echo $cat->is_default ? '<i class="bi bi-check-circle text-success me-1"></i>' : '-'; ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editCategory(<?php echo $cat->id; ?>, '<?php echo htmlspecialchars($cat->name, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($cat->description ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($cat->color ?? '#6B7280'); ?>')"><i class="bi bi-pencil me-1"></i></button>
                        <?php if (!$cat->is_default): ?>
                        <form method="POST" action="<?php echo $config['url']; ?>/settings/categories/delete/<?php echo $cat->id; ?>" style="display:inline;" onsubmit="return confirm('آیا از حذف دسته‌بندی «<?php echo htmlspecialchars($cat->name, ENT_QUOTES); ?>» اطمینان دارید؟ مخاطبین این دسته به دسته پیش‌فرض منتقل می‌شوند.')">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addCategoryModal">
    <div class="modal-box" style="max-width:450px;">
        <div class="modal-header">
            <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-1"></i> دسته‌بندی جدید</h5>
            <button type="button" class="modal-close" onclick="closeModal('addCategoryModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/categories/store" data-ajax="true">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">نام دسته‌بندی *</label>
                    <input type="text" name="name" class="form-input" required placeholder="مثال: مشتری وفادار">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">توضیحات</label>
                    <textarea name="description" class="form-textarea" rows="2" placeholder="توضیح کوتاه درباره این دسته‌بندی"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">رنگ</label>
                    <input type="color" name="color" class="form-input" value="#6B7280" style="height:40px;padding:4px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle text-success me-1"></i> ذخیره</button>
                <button type="button" class="btn btn-outline-secondary" onclick="closeModal('addCategoryModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editCategoryModal">
    <div class="modal-box" style="max-width:450px;">
        <div class="modal-header">
            <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-1"></i>ویرایش دسته‌بندی</h5>
            <button type="button" class="modal-close" onclick="closeModal('editCategoryModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/categories/update" data-ajax="true">
            <input type="hidden" name="id" id="editCategoryId">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">نام دسته‌بندی *</label>
                    <input type="text" name="name" id="editCategoryName" class="form-input" required placeholder="مثال: مشتری وفادار">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">توضیحات</label>
                    <textarea name="description" id="editCategoryDesc" class="form-textarea" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">رنگ</label>
                    <input type="color" name="color" id="editCategoryColor" class="form-input" style="height:40px;padding:4px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle text-success me-1"></i> ویرایش</button>
                <button type="button" class="btn btn-outline-secondary" onclick="closeModal('editCategoryModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<style>
.badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.badge.bg-info { background:#dbeafe; color:#1e40af; }
</style>

<script>
function editCategory(id, name, desc, color) {
    document.getElementById('editCategoryId').value = id;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategoryDesc').value = desc;
    document.getElementById('editCategoryColor').value = color || '#6B7280';
    openModal('editCategoryModal');
}

function deleteCategory(id, name) {
    if (!confirm('آیا از حذف دسته‌بندی "' + name + '" اطمینان دارید؟ مخاطبین این دسته به "مشتری جدید" منتقل می‌شوند.')) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo $config['url']; ?>/settings/categories/delete/' + id;
    form.style.display = 'none';
    document.body.appendChild(form);
    form.submit();
}
</script>