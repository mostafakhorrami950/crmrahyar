<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #eef0ff; color: #4361ee;"><i class="bi bi-briefcase"></i></div>
        <div class="stat-label">کل معاملات</div>
        <div class="stat-value"><?php echo number_format((int)(($totalDeals->count ?? $totalDeals) ?: 0)); ?></div>
        <div class="stat-sub"><?php echo number_format((int)(($totalDeals->total ?? 0) ?: 0)); ?> تومان ارزش</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #d4edda; color: #06d6a0;"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-label">فروش موفق</div>
        <div class="stat-value"><?php echo number_format((int)(($wonDeals->count ?? $wonDeals) ?: 0)); ?></div>
        <div class="stat-sub"><?php echo number_format((int)(($wonDeals->total ?? 0) ?: 0)); ?> تومان</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fff3cd; color: #ffd166;"><i class="bi bi-people"></i></div>
        <div class="stat-label">مخاطبان</div>
        <div class="stat-value"><?php echo number_format((int)(($totalContacts->count ?? $totalContacts) ?: 0)); ?></div>
        <div class="stat-sub"><?php echo (int)(($lostDeals->count ?? $lostDeals) ?: 0); ?> معامله از دست رفته</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #d1ecf1; color: #118ab2;"><i class="bi bi-kanban"></i></div>
        <div class="stat-label">پایپ لاین‌ها</div>
        <div class="stat-value"><?php echo number_format((int)(($totalPipelines->count ?? $totalPipelines) ?: 0)); ?></div>
        <div class="stat-sub">سیستم فعال</div>
    </div>
</div>

<!-- Recent Deals -->
<div class="card mb-4">
    <div class="card-body">
        <h6 class="card-title fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>آخرین معاملات</h6>
        <?php if (!empty($recentDeals)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>مبلغ</th>
                        <th class="d-none d-md-table-cell">مرحله</th>
                        <th class="d-none d-md-table-cell">مخاطب</th>
                        <th class="d-none d-lg-table-cell">تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentDeals as $deal): ?>
                    <tr>
                        <td>
                            <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="text-primary fw-medium text-decoration-none">
                                <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars($deal->title); ?>
                            </a>
                        </td>
                        <td class="amount-value"><?php echo number_format($deal->amount); ?> تومان</td>
                        <td class="d-none d-md-table-cell"><span class="badge bg-primary bg-opacity-10 text-primary"><?php echo htmlspecialchars($deal->stage_name ?? ''); ?></span></td>
                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($deal->contact_name ?? ''); ?></td>
                        <td class="d-none d-lg-table-cell text-muted small"><?php echo $deal->created_at; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h5>هنوز معامله‌ای ثبت نشده</h5>
            <p>اولین معامله خود را ایجاد کنید</p>
            <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>ایجاد معامله جدید</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Activities -->
<div class="card">
    <div class="card-body">
        <h6 class="card-title fw-bold mb-3"><i class="bi bi-activity me-2"></i>فعالیت‌های اخیر</h6>
        <?php if (!empty($recentActivities)): ?>
        <div class="d-flex flex-column gap-2">
            <?php foreach ($recentActivities as $activity): ?>
            <div class="d-flex align-items-start gap-3 py-2 border-bottom">
                <span class="fs-5"><?php 
                    $icons = ['created'=>'bi-plus-circle', 'updated'=>'bi-pencil', 'deleted'=>'bi-trash', 'payment'=>'bi-credit-card', 'sms'=>'bi-envelope', 'stage_changed'=>'bi-arrow-repeat'];
                    $colors = ['created'=>'text-success', 'updated'=>'text-primary', 'deleted'=>'text-danger', 'payment'=>'text-warning', 'sms'=>'text-info', 'stage_changed'=>'text-secondary'];
                    $icon = $icons[$activity->action] ?? 'bi-flag';
                    $color = $colors[$activity->action] ?? 'text-muted';
                ?><i class="bi <?php echo $icon; ?> <?php echo $color; ?>"></i></span>
                <div class="flex-grow-1">
                    <div class="text-dark" style="font-size:14px;"><?php echo htmlspecialchars($activity->description); ?></div>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-clock me-1"></i><?php echo $activity->created_at; ?> 
                        <?php if ($activity->user_name): ?>
                        - <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($activity->user_name); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <h5>هنوز فعالیتی ثبت نشده</h5>
            <p>با ایجاد معاملات و مخاطبان، فعالیت‌ها ثبت می‌شوند</p>
        </div>
        <?php endif; ?>
    </div>
</div>