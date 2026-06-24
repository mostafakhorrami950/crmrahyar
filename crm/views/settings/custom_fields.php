<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-list-task me-1"></i> فیلدهای اختصاصی</h5>
</div>

<div class="card" style="margin-bottom:16px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
            <label class="form-label text-muted small fw-medium">نوع موجودیت</label>
            <select class="form-select" onchange="location.href='?entity='+this.value">
                <option value="deals" <?php echo $entityType === 'deals' ? 'selected' : ''; ?>>معاملات</option>
                <option value="contacts" <?php echo $entityType === 'contacts' ? 'selected' : ''; ?>>اشخاص</option>
            </select>
        </div>
        <div style="flex:2;min-width:200px;">
            <label class="form-label text-muted small fw-medium">&nbsp;</label>
            <button type="button" class="btn btn-primary" style="width:100%;" onclick="showAddFieldModal()"><i class="bi bi-plus-circle me-1"></i> افزودن فیلد جدید</button>
        </div>
    </div>
</div>

<div class="card" style="padding:0;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">عنوان فیلد</th>
                    <th class="text-nowrap">نوع</th>
                    <th class="text-nowrap">اجباری</th>
                    <th class="text-nowrap">فعال</th>
                    <th class="text-nowrap">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($fields)): ?>
                <tr><td colspan="5" style="text-align:center;padding:30px;">هیچ فیلد اختصاصی تعریف نشده است.</td></tr>
                <?php else: ?>
                <?php foreach ($fields as $f): ?>
                <tr>
                    <td data-label="عنوان"><?php echo htmlspecialchars($f->field_label); ?></td>
                    <td data-label="نوع"><?php echo $f->field_type; ?></td>
                    <td data-label="اجباری"><?php echo $f->is_required ? '<i class="bi bi-check-circle text-success me-1"></i>' : '<i class="bi bi-x-circle text-danger me-1"></i>'; ?></td>
                    <td data-label="فعال"><?php echo $f->is_active ? '<i class="bi bi-check-circle text-success me-1"></i>' : '<i class="bi bi-x-circle text-danger me-1"></i>'; ?></td>
                    <td data-label="عملیات">
                        <div style="display:flex;gap:4px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editField(<?php echo $f->id; ?>, '<?php echo htmlspecialchars($f->field_label, ENT_QUOTES); ?>', '<?php echo $f->field_type; ?>', '<?php echo htmlspecialchars($f->field_options ?? '', ENT_QUOTES); ?>', <?php echo $f->is_required; ?>, <?php echo $f->is_active; ?>)"><i class="bi bi-pencil me-1"></i></button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteField(<?php echo $f->id; ?>)"><i class="bi bi-trash me-1"></i></button>
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
<div class="modal-overlay" id="fieldModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5 class="fw-bold mb-0">فیلد جدید</h5>
            <button type="button" class="modal-close" onclick="closeModal('fieldModal')">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editFieldId" value="">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">عنوان فیلد *</label>
                <input type="text" id="fieldLabel" class="form-control" placeholder="مثال: شماره پاسپورت">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">نوع فیلد</label>
                <select id="fieldType" class="form-select">
                    <option value="text">متن</option>
                    <option value="number">عدد</option>
                    <option value="textarea">متن بلند</option>
                    <option value="date">تاریخ</option>
                    <option value="select">انتخاب از لیست</option>
                    <option value="checkbox">چک‌باکس</option>
                </select>
            </div>
            <div class="mb-3" id="optionsDiv" style="display:none;">
                <label class="form-label text-muted small fw-medium">گزینه‌ها (هر خط یک گزینه)</label>
                <textarea id="fieldOptions" class="form-textarea" rows="3" placeholder="گزینه 1&#10;گزینه 2&#10;گزینه 3"></textarea>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" id="isRequired" class="form-check-input">
                    <label class="form-check-label">فیلد اجباری</label>
                </div>
            </div>
            <div class="mb-3" id="activeDiv" style="display:none;">
                <div class="form-check">
                    <input type="checkbox" id="isActive" class="form-check-input" checked>
                    <label class="form-check-label">فعال</label>
                </div>
            </div>
            <div id="fieldError" class="alert alert-danger" style="display:none;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="saveField()">ذخیره</button>
            <button type="button" class="btn btn-outline-secondary" onclick="closeModal('fieldModal')">انصراف</button>
        </div>
    </div>
</div>

<script>
document.getElementById('fieldType')?.addEventListener('change', function() {
    document.getElementById('optionsDiv').style.display = this.value === 'select' ? 'block' : 'none';
});

function showAddFieldModal() {
    document.getElementById('modalTitle').textContent = '<i class="bi bi-plus-circle me-1"></i> فیلد جدید';
    document.getElementById('editFieldId').value = '';
    document.getElementById('fieldLabel').value = '';
    document.getElementById('fieldType').value = 'text';
    document.getElementById('fieldOptions').value = '';
    document.getElementById('isRequired').checked = false;
    document.getElementById('activeDiv').style.display = 'none';
    document.getElementById('optionsDiv').style.display = 'none';
    document.getElementById('fieldError').style.display = 'none';
    openModal('fieldModal');
}

function editField(id, label, type, options, required, active) {
    document.getElementById('modalTitle').textContent = '<i class="bi bi-pencil me-1"></i>ویرایش فیلد';
    document.getElementById('editFieldId').value = id;
    document.getElementById('fieldLabel').value = label;
    document.getElementById('fieldType').value = type;
    document.getElementById('fieldOptions').value = options;
    document.getElementById('isRequired').checked = required == 1;
    document.getElementById('isActive').checked = active == 1;
    document.getElementById('activeDiv').style.display = 'block';
    document.getElementById('optionsDiv').style.display = type === 'select' ? 'block' : 'none';
    document.getElementById('fieldError').style.display = 'none';
    openModal('fieldModal');
}

function saveField() {
    var id = document.getElementById('editFieldId').value;
    var label = document.getElementById('fieldLabel').value;
    var type = document.getElementById('fieldType').value;
    var options = document.getElementById('fieldOptions').value;
    var required = document.getElementById('isRequired').checked ? 1 : 0;
    var active = document.getElementById('isActive').checked ? 1 : 0;
    var errorDiv = document.getElementById('fieldError');
    
    if (!label) {
        errorDiv.textContent = 'عنوان فیلد الزامی است';
        errorDiv.style.display = 'block';
        return;
    }
    
    var url = id ? '<?php echo $config['url']; ?>/custom-fields/update/' + id : '<?php echo $config['url']; ?>/custom-fields/store';
    var data = 'field_label=' + encodeURIComponent(label) + '&field_type=' + encodeURIComponent(type) + '&field_options=' + encodeURIComponent(options) + '&is_required=' + required + '&is_active=' + active + '&entity_type=<?php echo $entityType; ?>';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        try {
            var res = JSON.parse(xhr.responseText);
            if (res.success) {
                closeModal('fieldModal');
                location.reload();
            } else {
                errorDiv.textContent = res.message;
                errorDiv.style.display = 'block';
            }
        } catch(e) {
            errorDiv.textContent = 'خطا: ' + xhr.responseText;
            errorDiv.style.display = 'block';
        }
    };
    xhr.onerror = function() {
        errorDiv.textContent = 'خطا در ارتباط';
        errorDiv.style.display = 'block';
    };
    xhr.send(data);
}

function deleteField(id) {
    if (!confirm('آیا از حذف این فیلد اطمینان دارید؟')) return;
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo $config['url']; ?>/custom-fields/delete/' + id, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        try {
            var res = JSON.parse(xhr.responseText);
            if (res.success) location.reload();
            else alert(res.message);
        } catch(e) {
            alert('خطا');
        }
    };
    xhr.send('id=' + id);
}
</script>