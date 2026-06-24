<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>مدیریت تیم‌ها</h5>
    <a href="<?php echo $config['url']; ?>/teams/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>تیم جدید</a>
</div>

<?php if (empty($teams)): ?>
<div class="card border-0 shadow-sm">
    <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-people"></i></div>
        <h5>هنوز تیمی ایجاد نشده</h5>
        <p>اولین تیم خود را ایجاد کنید</p>
        <a href="<?php echo $config['url']; ?>/teams/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>ایجاد اولین تیم</a>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
<?php foreach ($teams as $t): ?>
<div class="col-12 col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-people text-primary fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($t->name); ?></h6>
                        <small class="text-muted"><?php echo $t->member_count; ?> نفر عضو</small>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <a href="<?php echo $config['url']; ?>/teams/edit/<?php echo $t->id; ?>" class="btn btn-outline-primary btn-sm" style="padding:4px 8px;" title="ویرایش"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="<?php echo $config['url']; ?>/teams/delete/<?php echo $t->id; ?>" class="d-inline" onsubmit="return confirm('آیا مطمئنید؟')">
                        <button class="btn btn-outline-danger btn-sm" style="padding:4px 8px;" title="حذف"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
            
            <?php if (!empty($t->description)): ?>
            <p class="text-muted small mb-2"><?php echo htmlspecialchars(mb_substr($t->description, 0, 100)); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($t->leader_name)): ?>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-warning bg-opacity-10 text-warning"><i class="bi bi-star me-1"></i>رهبر:</span>
                <span class="small fw-semibold"><?php echo htmlspecialchars($t->leader_name); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>