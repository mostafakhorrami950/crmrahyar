<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-1"></i>ویرایش نقش: <?php echo htmlspecialchars($role->name); ?></h5>
    <a href="<?php echo $config['url']; ?>/roles" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="card" style="padding:24px;">
    <form method="POST" action="<?php echo $config['url']; ?>/roles/update/<?php echo $role->id; ?>">
        <h5 class="fw-bold mb-0"><i class="bi bi-list-task me-1"></i> اطلاعات نقش</h5>
        
        <div class="form-row">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">نام نقش *</label>
                <input type="text" name="name" class="form-input" required value="<?php echo htmlspecialchars($role->name); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">توضیحات</label>
                <input type="text" name="description" class="form-input" value="<?php echo htmlspecialchars($role->description ?? ''); ?>">
            </div>
        </div>

        <?php if ($role->is_system): ?>
        <div style="background:#fef3c7;color:#92400e;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
            ⚠️ این نقش سیستمی است. نام آن قابل تغییر نیست اما دسترسی‌ها قابل ویرایش هستند.
        </div>
        <?php endif; ?>

        <div style="margin-top:8px;">
            <h5 class="fw-bold mb-0"><i class="bi bi-key me-1"></i> دسترسی‌ها</h5>
            <div style="background:var(--gray-50);padding:12px 16px;border-radius:10px;margin-bottom:16px;">
                <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--gray-600);">
                    <span>🌐 <strong>همه:</strong> دسترسی به تمام داده‌ها</span>
                    <span style="margin:0 8px;">|</span>
                    <span><i class="bi bi-person me-1"></i> <strong>فقط خودش:</strong> دسترسی فقط به داده‌های خودش</span>
                </div>
            </div>
            
            <?php include __DIR__ . '/_permissions.php'; ?>
        </div>

        <div style="display:flex;gap:8px;margin-top:24px;padding-top:16px;border-top:1px solid var(--gray-200);">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره تغییرات</button>
            <a href="<?php echo $config['url']; ?>/roles" class="btn btn-outline-secondary">انصراف</a>
        </div>
    </form>
</div>