<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-1"></i>ایجاد تیم جدید</h5>
    <a href="<?php echo $config['url']; ?>/teams" class="btn btn-outline-secondary">بازگشت</a>
</div>

<div class="card" style="max-width:600px;">
    <form method="POST" action="<?php echo $config['url']; ?>/teams/store">
        <div class="mb-3">
            <label class="form-label text-muted small fw-medium">نام تیم *</label>
            <input type="text" name="name" class="form-input" required>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted small fw-medium">توضیحات</label>
            <textarea name="description" class="form-textarea" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted small fw-medium">رهبر تیم</label>
            <select name="leader_id" class="form-select">
                <option value="">انتخاب کنید...</option>
                <?php foreach ($users as $u): ?>
                <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->full_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted small fw-medium">اعضای تیم (چند انتخابی)</label>
            <select name="members[]" class="form-select" multiple size="6">
                <?php foreach ($users as $u): ?>
                <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->full_name); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="form-hint">با نگه‌داشتن Ctrl چند نفر را انتخاب کنید</p>
        </div>
        <div class="d-flex gap-8">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره</button>
            <a href="<?php echo $config['url']; ?>/teams" class="btn btn-outline-secondary">انصراف</a>
        </div>
    </form>
</div>