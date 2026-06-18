<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>📂 مدیریت دسته‌بندی مخاطبین</h5>
    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addCategoryModal')">➕ دسته‌بندی جدید</button>
</div>

<div class="card">
    <div class="ajax-error alert alert-danger" style="display:none;"></div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>رنگ</th>
                    <th>نام دسته‌بندی</th>
                    <th>توضیحات</th>
                    <th>تعداد مخاطبین</th>
                    <th>پیش‌فرض</th>
                    <th>عملیات</th>
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
                    <td><?php echo $cat->is_default ? '✅' : '-'; ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="editCategory(<?php echo $cat->id; ?>, '<?php echo htmlspecialchars($cat->name, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($cat->description ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($cat->color ?? '#6B7280'); ?>')">✏️</button>
                        <?php if (!$cat->is_default): ?>
                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $cat->id; ?>, '<?php echo htmlspecialchars($cat->name, ENT_QUOTES); ?>')">🗑️</button>
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
            <h5>➕ دسته‌بندی جدید</h5>
            <button type="button" class="modal-close" onclick="closeModal('addCategoryModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/categories/store" data-ajax="true">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">نام دسته‌بندی *</label>
                    <input type="text" name="name" class="form-input" required placeholder="مثال: مشتری وفادار">
                </div>
                <div class="form-group">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-textarea" rows="2" placeholder="توضیح کوتاه درباره این دسته‌بندی"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">رنگ</label>
                    <input type="color" name="color" class="form-input" value="#6B7280" style="height:40px;padding:4px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">✅ ذخیره</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCategoryModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editCategoryModal">
    <div class="modal-box" style="max-width:450px;">
        <div class="modal-header">
            <h5>✏️ ویرایش دسته‌بندی</h5>
            <button type="button" class="modal-close" onclick="closeModal('editCategoryModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/categories/update" data-ajax="true">
            <input type="hidden" name="id" id="editCategoryId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">نام دسته‌بندی *</label>
                    <input type="text" name="name" id="editCategoryName" class="form-input" required placeholder="مثال: مشتری وفادار">
                </div>
                <div class="form-group">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" id="editCategoryDesc" class="form-textarea" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">رنگ</label>
                    <input type="color" name="color" id="editCategoryColor" class="form-input" style="height:40px;padding:4px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">✅ ویرایش</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editCategoryModal')">لغو</button>
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