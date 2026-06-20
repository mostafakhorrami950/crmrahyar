<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>➕ ایجاد تیم جدید</h5>
    <a href="<?php echo $config['url']; ?>/teams" class="btn btn-secondary">بازگشت</a>
</div>

<div class="card" style="max-width:600px;">
    <form method="POST" action="<?php echo $config['url']; ?>/teams/store">
        <div class="form-group">
            <label class="form-label">نام تیم *</label>
            <input type="text" name="name" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">توضیحات</label>
            <textarea name="description" class="form-textarea" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">رهبر تیم</label>
            <select name="leader_id" class="form-select">
                <option value="">انتخاب کنید...</option>
                <?php foreach ($users as $u): ?>
                <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->full_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">اعضای تیم (چند انتخابی)</label>
            <select name="members[]" class="form-select" multiple size="6">
                <?php foreach ($users as $u): ?>
                <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->full_name); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="form-hint">با نگه‌داشتن Ctrl چند نفر را انتخاب کنید</p>
        </div>
        <div class="d-flex gap-8">
            <button type="submit" class="btn btn-primary">💾 ذخیره</button>
            <a href="<?php echo $config['url']; ?>/teams" class="btn btn-secondary">انصراف</a>
        </div>
    </form>
</div>