<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-list-task me-1"></i> مدیریت پایپ لاین‌ها</h5>
    <?php if (\Core\Auth::hasPermission('pipelines.create')): ?>
    <a href="<?php echo $config['url']; ?>/pipelines/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> پایپ لاین جدید</a>
    <?php endif; ?>
</div>

<?php if (empty($pipelines)): ?>
<div class="empty-state">
    <div class="empty-icon"><i class="bi bi-list-task me-1"></i></div>
    <h5 class="fw-bold mb-0">هیچ پایپ لاینی وجود ندارد</h5>
    <p>اولین پایپ لاین خود را ایجاد کنید</p>
    <?php if (\Core\Auth::hasPermission('pipelines.create')): ?>
    <a href="<?php echo $config['url']; ?>/pipelines/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>ایجاد پایپ لاین</a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($pipelines as $p): ?>
    <div class="col-md-6 mb-3">
        <div class="card" style="border-right: 4px solid var(--primary); transition: all 0.2s;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="fw-bold mb-0">
                        <?php echo htmlspecialchars($p->name); ?>
                        <?php if (!empty($p->is_default)): ?>
                        <span class="badge badge-primary" style="font-size:10px;">پیش‌فرض</span>
                        <?php endif; ?>
                        <?php if (!empty($p->is_active)): ?>
                        <span class="badge badge-success" style="font-size:10px;">فعال</span>
                        <?php else: ?>
                        <span class="badge badge-secondary" style="font-size:10px;">غیرفعال</span>
                        <?php endif; ?>
                    </h5>
                    <p style="color:var(--gray-500);font-size:13px;margin:6px 0;">
                        <?php echo htmlspecialchars(mb_substr($p->description ?? 'توضیحی ثبت نشده', 0, 80)); ?>
                    </p>
                    <div style="display:flex;gap:12px;font-size:12px;color:var(--gray-500);">
                        <span><i class="bi bi-pin me-1"></i> <?php echo $p->stages_count ?? 0; ?> مرحله</span>
                        <span>💼 <?php echo $p->deals_count ?? 0; ?> معامله</span>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:8px;margin-top:14px;padding-top:12px;border-top:1px solid var(--gray-100);">
                <?php if (\Core\Auth::hasPermission('pipelines.view')): ?>
                <a href="<?php echo $config['url']; ?>/pipelines/kanban/<?php echo $p->id; ?>" class="btn btn-primary btn-sm" style="flex:1;">
                    <i class="bi bi-bar-chart me-1"></i> مشاهده کانبان
                </a>
                <?php endif; ?>
                <?php if (\Core\Auth::hasPermission('pipelines.edit')): ?>
                <a href="<?php echo $config['url']; ?>/pipelines/edit/<?php echo $p->id; ?>" class="btn btn-outline-secondary btn-sm" style="flex:1;">
                    <i class="bi bi-pencil me-1"></i>ویرایش
                </a>
                <?php endif; ?>
                <?php if (\Core\Auth::hasPermission('pipelines.delete')): ?>
                <form method="POST" action="<?php echo $config['url']; ?>/pipelines/delete/<?php echo $p->id; ?>" style="flex:1;" onsubmit="return confirm('آیا از حذف پایپ لاین «<?php echo htmlspecialchars($p->name, ENT_QUOTES); ?>» اطمینان دارید؟')">
                    <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">
                        <i class="bi bi-trash me-1"></i>حذف
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>