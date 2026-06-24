<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-pin me-1"></i> مدیریت نحوه آشنایی</h5>
    <button class="btn btn-primary" onclick="openModal('addSourceModal')"><i class="bi bi-plus-circle me-1"></i> افزودن منبع</button>
</div>

<div style="margin-top:16px;">
    <div class="row">
        <div class="col-md-8">
            <div class="card" style="padding:20px;">
                <h5 class="fw-bold mb-0"><i class="bi bi-list-task me-1"></i> لیست منابع</h5>
                
                <?php if (empty($sources)): ?>
                <p style="text-align:center;padding:40px;color:var(--gray-400);">هیچ منبعی ثبت نشده است.</p>
                <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($sources as $s): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:<?php echo $s->is_active ? 'var(--gray-50)' : '#f8d7da'; ?>;border-radius:12px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <span style="font-size:24px;"><?php echo htmlspecialchars($s->icon); ?></span>
                            <div>
                                <strong style="font-size:14px;"><?php echo htmlspecialchars($s->name); ?></strong>
                                <br><small style="color:var(--gray-500);font-size:12px;">ترتیب: <?php echo $s->sort_order; ?></small>
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <span style="padding:4px 10px;border-radius:12px;font-size:11px;font-weight:600;<?php echo $s->is_active ? 'background:#d4edda;color:#155724;' : 'background:#f8d7da;color:#721c24;'; ?>">
                                <?php echo $s->is_active ? 'فعال' : 'غیرفعال'; ?>
                            </span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="editSource(<?php echo $s->id; ?>, '<?php echo htmlspecialchars($s->name); ?>', '<?php echo htmlspecialchars($s->icon); ?>', <?php echo $s->sort_order; ?>, <?php echo $s->is_active; ?>)" title="ویرایش"><i class="bi bi-pencil me-1"></i></button>
                            <form method="POST" action="<?php echo $config['url']; ?>/settings/sources/delete" style="display:inline;" data-ajax="true" onsubmit="return confirm('آیا از حذف "<?php echo htmlspecialchars($s->name); ?>" اطمینان دارید؟')">
                                <input type="hidden" name="id" value="<?php echo $s->id; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف"><i class="bi bi-trash me-1"></i></button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="padding:20px;">
                <h5 class="fw-bold mb-0"><i class="bi bi-lightbulb me-1"></i> راهنما</h5>
                <p style="font-size:13px;color:var(--gray-600);line-height:1.8;">
                    در این بخش می‌توانید منابع و نحوه آشنایی مشتریان با کسب و کار خود را مدیریت کنید.
                </p>
                <ul style="font-size:12px;color:var(--gray-500);line-height:2;">
                    <li><i class="bi bi-plus-circle me-1"></i> برای افزودن منبع جدید از دکمه بالا استفاده کنید</li>
                    <li><i class="bi bi-pencil me-1"></i> برای ویرایش نام، آیکون و ترتیب روی دکمه ویرایش کلیک کنید</li>
                    <li><i class="bi bi-trash me-1"></i> منابع غیرضروری را حذف کنید</li>
                    <li><i class="bi bi-check-circle text-success me-1"></i> منابع فعال در فرم ایجاد و ویرایش معامله نمایش داده می‌شوند</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Add Source Modal -->
<div class="modal-overlay" id="addSourceModal">
    <div class="modal-box" style="max-width:450px;">
        <div class="modal-header">
            <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-1"></i> افزودن منبع جدید</h5>
            <button type="button" class="modal-close" onclick="closeModal('addSourceModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/sources/store" data-ajax="true">
            <div class="modal-body">
                <div class="mb-3" style="margin-bottom:12px;">
                    <label class="form-label text-muted small fw-medium">نام منبع *</label>
                    <input type="text" name="name" class="form-input" required placeholder="مثال: اینستاگرام, وبسایت, معرفی دوستان">
                </div>
                <div class="mb-3" style="margin-bottom:12px;">
                    <label class="form-label text-muted small fw-medium">آیکون (اموجی)</label>
                    <input type="text" name="icon" class="form-input" value="<i class="bi bi-pin me-1"></i>" placeholder="<i class="bi bi-pin me-1"></i>" style="max-width:80px;">
                    <small style="color:var(--gray-400);font-size:11px;">یک اموجی انتخاب کنید. مثال: 📸 🌐 💬 <i class="bi bi-telephone me-1"></i></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle text-success me-1"></i> ذخیره</button>
                <button type="button" class="btn btn-outline-secondary" onclick="closeModal('addSourceModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Source Modal -->
<div class="modal-overlay" id="editSourceModal">
    <div class="modal-box" style="max-width:450px;">
        <div class="modal-header">
            <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-1"></i>ویرایش منبع</h5>
            <button type="button" class="modal-close" onclick="closeModal('editSourceModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/settings/sources/update" data-ajax="true">
            <input type="hidden" name="id" id="editSourceId">
            <div class="modal-body">
                <div class="mb-3" style="margin-bottom:12px;">
                    <label class="form-label text-muted small fw-medium">نام منبع *</label>
                    <input type="text" name="name" class="form-input" required id="editSourceName">
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="mb-3" style="margin-bottom:12px;">
                            <label class="form-label text-muted small fw-medium">آیکون (اموجی)</label>
                            <input type="text" name="icon" class="form-input" id="editSourceIcon" style="max-width:80px;">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3" style="margin-bottom:12px;">
                            <label class="form-label text-muted small fw-medium">ترتیب نمایش</label>
                            <input type="number" name="sort_order" class="form-input" id="editSourceOrder" min="0" value="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-check-label" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" id="editSourceActive" value="1" checked style="width:18px;height:18px;">
                        <span>فعال</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle text-success me-1"></i> ذخیره تغییرات</button>
                <button type="button" class="btn btn-outline-secondary" onclick="closeModal('editSourceModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSource(id, name, icon, sortOrder, isActive) {
    document.getElementById('editSourceId').value = id;
    document.getElementById('editSourceName').value = name;
    document.getElementById('editSourceIcon').value = icon;
    document.getElementById('editSourceOrder').value = sortOrder;
    document.getElementById('editSourceActive').checked = isActive == 1;
    openModal('editSourceModal');
}
</script>