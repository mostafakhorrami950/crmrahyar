<?php $config = $GLOBALS['app_config']; ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h5 class="fw-bold mb-1"><i class="bi bi-kanban me-2 text-primary"></i>مدیریت پایپ لاین‌ها</h5>
        <p class="text-muted small mb-0">مراحل فروش و معاملات خود را مدیریت کنید</p>
    </div>
    <?php if (\Core\Auth::hasPermission('pipelines.create')): ?>
    <a href="<?php echo $config['url']; ?>/pipelines/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>پایپ لاین جدید
    </a>
    <?php endif; ?>
</div>

<?php if (empty($pipelines)): ?>
<!-- Empty State -->
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
            <i class="bi bi-kanban text-primary" style="font-size:36px;"></i>
        </div>
        <h5 class="fw-bold mb-2">هنوز پایپ لاینی ایجاد نشده</h5>
        <p class="text-muted mb-4">با ایجاد پایپ لاین، مراحل فروش خود را مدیریت کنید</p>
        <?php if (\Core\Auth::hasPermission('pipelines.create')): ?>
        <a href="<?php echo $config['url']; ?>/pipelines/create" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle me-2"></i>ایجاد اولین پایپ لاین
        </a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<!-- Pipelines Grid -->
<div class="row g-3">
    <?php foreach ($pipelines as $p): 
        $isActive = !empty($p->is_active);
        $isDefault = !empty($p->is_default);
    ?>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden">
            <!-- Top Color Bar -->
            <div style="height:4px;background:linear-gradient(90deg, var(--primary), var(--secondary));"></div>
            
            <div class="card-body">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-kanban text-primary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($p->name); ?></h6>
                            <div class="d-flex gap-1 mt-1">
                                <?php if ($isDefault): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:10px;padding:2px 8px;">پیش‌فرض</span>
                                <?php endif; ?>
                                <?php if ($isActive): ?>
                                <span class="badge bg-success bg-opacity-10 text-success" style="font-size:10px;padding:2px 8px;"><i class="bi bi-circle-fill" style="font-size:6px;"></i> فعال</span>
                                <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:10px;padding:2px 8px;">غیرفعال</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <p class="text-muted small mb-3" style="min-height:36px;">
                    <?php echo htmlspecialchars(mb_substr($p->description ?? 'توضیحی ثبت نشده', 0, 100)); ?>
                </p>
                
                <!-- Stats -->
                <div class="d-flex gap-3 mb-3">
                    <div class="d-flex align-items-center gap-2 bg-light rounded-3 px-3 py-2 flex-fill">
                        <i class="bi bi-layers text-primary"></i>
                        <div>
                            <div class="fw-bold"><?php echo $p->stages_count ?? 0; ?></div>
                            <small class="text-muted" style="font-size:11px;">مرحله</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 bg-light rounded-3 px-3 py-2 flex-fill">
                        <i class="bi bi-briefcase text-success"></i>
                        <div>
                            <div class="fw-bold"><?php echo $p->deals_count ?? 0; ?></div>
                            <small class="text-muted" style="font-size:11px;">معامله</small>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-flex gap-2 pt-3 border-top">
                    <?php if (\Core\Auth::hasPermission('pipelines.view')): ?>
                    <a href="<?php echo $config['url']; ?>/pipelines/kanban/<?php echo $p->id; ?>" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-kanban me-1"></i>نمای کانبان
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Auth::hasPermission('pipelines.edit')): ?>
                    <a href="<?php echo $config['url']; ?>/pipelines/edit/<?php echo $p->id; ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (\Core\Auth::hasPermission('pipelines.delete')): ?>
                    <form method="POST" action="<?php echo $config['url']; ?>/pipelines/delete/<?php echo $p->id; ?>" onsubmit="return confirm('آیا از حذف پایپ لاین «<?php echo htmlspecialchars($p->name, ENT_QUOTES); ?>» اطمینان دارید؟')" class="d-inline">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>