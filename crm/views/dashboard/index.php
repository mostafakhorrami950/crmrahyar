<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #eef0ff; color: #4361ee;">💼</div>
        <div class="stat-label">کل معاملات</div>
        <div class="stat-value"><?php echo number_format($totalDeals ?? 0); ?></div>
        <div class="stat-sub"><?php echo $activeDeals ?? 0; ?> معامله فعال</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #d4edda; color: #06d6a0;">💰</div>
        <div class="stat-label">فروش تکمیل شده</div>
        <div class="stat-value"><?php echo number_format($completedDeals ?? 0); ?></div>
        <div class="stat-sub"><?php echo number_format($totalRevenue ?? 0); ?> تومان</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fff3cd; color: #ffd166;">👥</div>
        <div class="stat-label">مخاطبان</div>
        <div class="stat-value"><?php echo number_format($totalContacts ?? 0); ?></div>
        <div class="stat-sub"><?php echo $newContactsThisMonth ?? 0; ?> جدید این ماه</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #d1ecf1; color: #118ab2;">📊</div>
        <div class="stat-label">پایپ لاین‌ها</div>
        <div class="stat-value"><?php echo number_format($totalPipelines ?? 0); ?></div>
        <div class="stat-sub"><?php echo $totalStages ?? 0; ?> مرحله تعریف شده</div>
    </div>
</div>

<div class="card">
    <div class="card-header">آخرین معاملات</div>
    <?php if (!empty($recentDeals)): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>مبلغ</th>
                    <th>مرحله</th>
                    <th>مخاطب</th>
                    <th>تاریخ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentDeals as $deal): ?>
                <tr>
                    <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" style="color: var(--primary);"><?php echo htmlspecialchars($deal->title); ?></a></td>
                    <td class="amount-value"><?php echo number_format($deal->amount); ?> تومان</td>
                    <td><span class="badge badge-primary"><?php echo htmlspecialchars($deal->stage_name ?? ''); ?></span></td>
                    <td><?php echo htmlspecialchars($deal->contact_name ?? ''); ?></td>
                    <td class="text-muted"><?php echo $deal->created_at; ?></td>
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
        <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-primary">ایجاد معامله جدید</a>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">فعالیت‌های اخیر</div>
    <?php if (!empty($recentActivities)): ?>
    <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php foreach ($recentActivities as $activity): ?>
        <div style="display: flex; align-items: flex-start; gap: 12px; padding: 8px 0; border-bottom: 1px solid var(--gray-100);">
            <span style="font-size: 18px;"><?php 
                $icons = ['created'=>'➕', 'updated'=>'✏️', 'deleted'=>'🗑️', 'payment'=>'💳', 'sms'=>'✉️', 'stage_changed'=>'🔄'];
                echo $icons[$activity->action] ?? '📌';
            ?></span>
            <div style="flex: 1;">
                <div style="font-size: 14px; color: var(--gray-800);"><?php echo htmlspecialchars($activity->description); ?></div>
                <div style="font-size: 12px; color: var(--gray-500); margin-top: 2px;">
                    <?php echo $activity->created_at; ?> - <?php echo htmlspecialchars($activity->user_name ?? ''); ?>
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