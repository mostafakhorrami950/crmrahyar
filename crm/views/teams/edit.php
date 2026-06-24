<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>ویرایش تیم: <?php echo htmlspecialchars($team->name); ?></h5>
    <a href="<?php echo $config['url']; ?>/teams" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="card border-0 shadow-sm" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="<?php echo $config['url']; ?>/teams/update/<?php echo $team->id; ?>">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">نام تیم <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($team->name); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">توضیحات</label>
                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($team->description ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">رهبر تیم</label>
                <select name="leader_id" class="form-select">
                    <option value="">انتخاب کنید...</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>" <?php echo $team->leader_id == $u->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">اعضای تیم</label>
                <select name="members[]" class="form-select" multiple size="6">
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>" <?php echo in_array($u->id, $memberIds) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">برای انتخاب چندگانه Ctrl را نگه دارید</small>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>بروزرسانی</button>
                <a href="<?php echo $config['url']; ?>/teams" class="btn btn-outline-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>