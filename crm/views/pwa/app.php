<?php
$config = $GLOBALS['app_config'];
$user = \Core\Auth::user();
$db = \Core\Database::getInstance();

// Fetch dashboard data
$myDealsCount = $db->fetch("SELECT COUNT(*) as c FROM deals WHERE assigned_to = ? AND (is_won IS NULL OR is_won = 0) AND (is_lost IS NULL OR is_lost = 0)", [\Core\Auth::id()])->c ?? 0;
$myDealsValue = $db->fetch("SELECT COALESCE(SUM(amount),0) as t FROM deals WHERE assigned_to = ? AND (is_won IS NULL OR is_won = 0) AND (is_lost IS NULL OR is_lost = 0)", [\Core\Auth::id()])->t ?? 0;
$todayActivities = $db->fetch("SELECT COUNT(*) as c FROM deal_activities WHERE user_id = ? AND DATE(activity_date) = CURDATE() AND is_done = 0", [\Core\Auth::id()])->c ?? 0;
$overdueActivities = $db->fetch("SELECT COUNT(*) as c FROM deal_activities WHERE user_id = ? AND activity_date < NOW() AND is_done = 0", [\Core\Auth::id()])->c ?? 0;
$wonDeals = $db->fetch("SELECT COUNT(*) as c FROM deals WHERE assigned_to = ? AND is_won = 1", [\Core\Auth::id()])->c ?? 0;

// Recent deals
$recentDeals = $db->fetchAll("SELECT d.id, d.title, d.amount, d.created_at, s.name as stage_name, s.color as stage_color FROM deals d LEFT JOIN stages s ON d.stage_id = s.id WHERE d.assigned_to = ? ORDER BY d.created_at DESC LIMIT 5", [\Core\Auth::id()]);

// Today activities
$myActivities = $db->fetchAll("SELECT al.*, d.title as deal_title FROM deal_activities al LEFT JOIN deals d ON al.deal_id = d.id WHERE al.user_id = ? AND al.is_done = 0 ORDER BY al.activity_date ASC LIMIT 5", [\Core\Auth::id()]);

ob_start();
?>
<!-- Header -->
<div class="pwa-header">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h5>✈️ علاءالدین سفیر اسمان</h5>
            <span class="brand-sm"><?php echo htmlspecialchars($user->full_name ?? ''); ?></span>
        </div>
        <a href="<?php echo $config['url']; ?>/pwa/logout" style="color:var(--pwa-muted);text-decoration:none;font-size:13px;">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div>

<!-- Content -->
<div class="pwa-content pwa-fade">
    <!-- Stats Grid -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <div class="pwa-stat">
                <div class="pwa-stat-value" style="color:var(--pwa-primary);"><?php echo $myDealsCount; ?></div>
                <div class="pwa-stat-label">معاملات باز</div>
            </div>
        </div>
        <div class="col-6">
            <div class="pwa-stat">
                <div class="pwa-stat-value" style="color:var(--pwa-success);font-size:16px;"><?php echo number_format($myDealsValue); ?></div>
                <div class="pwa-stat-label">ارزش معاملات (تومان)</div>
            </div>
        </div>
        <div class="col-6">
            <div class="pwa-stat">
                <div class="pwa-stat-value" style="color:var(--pwa-warning);"><?php echo $todayActivities; ?></div>
                <div class="pwa-stat-label">فعالیت‌های امروز</div>
            </div>
        </div>
        <div class="col-6">
            <div class="pwa-stat">
                <div class="pwa-stat-value" style="color:var(--pwa-accent);"><?php echo $wonDeals; ?></div>
                <div class="pwa-stat-label">معاملات موفق</div>
            </div>
        </div>
    </div>

    <?php if ($overdueActivities > 0): ?>
    <div class="pwa-alert pwa-alert-danger">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?php echo $overdueActivities; ?> فعالیت سررسید گذشته دارید
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="pwa-section-title">دسترسی سریع</div>
    <div class="pwa-quick-grid">
        <a href="<?php echo $config['url']; ?>/pwa/deals" class="pwa-quick-item">
            <i class="bi bi-briefcase" style="color:var(--pwa-primary);"></i>
            <span>معاملات</span>
        </a>
        <a href="<?php echo $config['url']; ?>/pwa/contacts" class="pwa-quick-item">
            <i class="bi bi-people" style="color:var(--pwa-success);"></i>
            <span>مخاطبان</span>
        </a>
        <a href="<?php echo $config['url']; ?>/pwa/activities" class="pwa-quick-item">
            <i class="bi bi-calendar-check" style="color:var(--pwa-warning);"></i>
            <span>فعالیت‌ها</span>
        </a>
        <a href="<?php echo $config['url']; ?>/pwa/calendar" class="pwa-quick-item">
            <i class="bi bi-calendar3" style="color:var(--pwa-accent);"></i>
            <span>تقویم</span>
        </a>
    </div>

    <!-- Today Activities -->
    <?php if (!empty($myActivities)): ?>
    <div class="pwa-card">
        <div class="pwa-card-header">
            <span class="pwa-card-title"><i class="bi bi-clock me-1" style="color:var(--pwa-warning);"></i>فعالیت‌های امروز</span>
            <a href="<?php echo $config['url']; ?>/pwa/activities" style="color:var(--pwa-primary);font-size:12px;text-decoration:none;">همه ←</a>
        </div>
        <?php foreach ($myActivities as $act): ?>
        <div class="pwa-list-item">
            <div class="pwa-list-icon" style="background:rgba(255,209,102,0.1);color:var(--pwa-warning);">
                <i class="bi bi-<?php echo $act->type === 'call' ? 'telephone' : ($act->type === 'meeting' ? 'people' : 'pin'); ?>"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="pwa-list-title"><?php echo htmlspecialchars(mb_substr($act->subject ?? '-', 0, 30)); ?></div>
                <div class="pwa-list-sub"><?php echo $act->deal_title ? htmlspecialchars(mb_substr($act->deal_title, 0, 25)) : ''; ?></div>
            </div>
            <div class="pwa-list-right">
                <small style="color:var(--pwa-muted);font-size:10px;"><?php echo $act->activity_date ? \Core\JDate::displayDate($act->activity_date) : ''; ?></small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Recent Deals -->
    <?php if (!empty($recentDeals)): ?>
    <div class="pwa-card">
        <div class="pwa-card-header">
            <span class="pwa-card-title"><i class="bi bi-briefcase me-1" style="color:var(--pwa-primary);"></i>آخرین معاملات</span>
            <a href="<?php echo $config['url']; ?>/pwa/deals" style="color:var(--pwa-primary);font-size:12px;text-decoration:none;">همه ←</a>
        </div>
        <?php foreach ($recentDeals as $deal): ?>
        <div class="pwa-list-item">
            <div class="pwa-list-icon" style="background:rgba(67,97,238,0.1);color:var(--pwa-primary);">
                <i class="bi bi-briefcase"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="pwa-list-title"><?php echo htmlspecialchars(mb_substr($deal->title, 0, 30)); ?></div>
                <div class="pwa-list-sub">
                    <?php if ($deal->stage_name): ?>
                    <span class="pwa-badge pwa-badge-primary" style="font-size:9px;"><?php echo htmlspecialchars($deal->stage_name); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="pwa-list-right">
                <div style="font-size:13px;font-weight:700;color:var(--pwa-text);"><?php echo number_format($deal->amount); ?></div>
                <small style="color:var(--pwa-muted);font-size:10px;">تومان</small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Open in Web -->
    <div style="text-align:center;margin-top:16px;">
        <a href="<?php echo $config['url']; ?>/dashboard" class="pwa-btn pwa-btn-ghost" style="font-size:12px;">
            <i class="bi bi-globe"></i> نسخه وب کامل
        </a>
    </div>
</div>

<!-- Bottom Navigation -->
<nav class="pwa-bottom-nav">
    <a href="<?php echo $config['url']; ?>/pwa/app" class="active">
        <i class="bi bi-house-door-fill"></i>
        <span>خانه</span>
    </a>
    <a href="<?php echo $config['url']; ?>/pwa/deals">
        <i class="bi bi-briefcase"></i>
        <span>معاملات</span>
    </a>
    <a href="<?php echo $config['url']; ?>/pwa/contacts">
        <i class="bi bi-people"></i>
        <span>مخاطبان</span>
    </a>
    <a href="<?php echo $config['url']; ?>/pwa/activities">
        <i class="bi bi-calendar-check"></i>
        <span>فعالیت‌ها</span>
    </a>
    <a href="<?php echo $config['url']; ?>/pwa/more">
        <i class="bi bi-three-dots"></i>
        <span>بیشتر</span>
    </a>
</nav>
<?php
$pwaContent = ob_get_clean();
$pageTitle = 'داشبورد';
include __DIR__ . '/layout.php';