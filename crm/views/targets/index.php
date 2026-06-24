<?php $config = $GLOBALS['app_config'];
$monthNames = ['','فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-bullseye me-2 text-primary"></i>هدف‌گذاری فروش</h5>
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/targets?month=<?php echo $month - 1 ?: 12; ?>&year=<?php echo $month == 1 ? $year - 1 : $year; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></a>
        <span class="fw-bold px-3 py-1 rounded bg-primary bg-opacity-10 text-primary"><?php echo $monthNames[$month] ?? $month; ?> <?php echo $year; ?></span>
        <a href="<?php echo $config['url']; ?>/targets?month=<?php echo $month >= 12 ? 1 : $month + 1; ?>&year=<?php echo $month >= 12 ? $year + 1 : $year; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></a>
    </div>
</div>

<!-- Targets Cards -->
<?php if (!empty($targets)): ?>
<div class="row g-3 mb-4">
<?php foreach ($targets as $t): 
    $pct = $t->target_amount > 0 ? round(($t->achieved_amount / $t->target_amount) * 100, 1) : 0;
    $dealPct = $t->target_deals > 0 ? round(($t->achieved_deals / $t->target_deals) * 100, 1) : 0;
    $pctColor = $pct >= 100 ? 'success' : ($pct >= 50 ? 'primary' : 'warning');
    $dealColor = $dealPct >= 100 ? 'success' : ($dealPct >= 50 ? 'info' : 'warning');
?>
<div class="col-12 col-md-6 col-lg-4">
    <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                        <i class="bi <?php echo $t->target_type === 'user' ? 'bi-person' : 'bi-people'; ?> text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($t->target_name); ?></h6>
                        <small class="text-muted"><?php echo $t->target_type === 'user' ? 'کاربر' : 'تیم'; ?></small>
                    </div>
                </div>
                <?php if (\Core\Auth::hasPermission('settings.manage')): ?>
                <form method="POST" action="<?php echo $config['url']; ?>/targets/delete/<?php echo $t->id; ?>" onsubmit="return confirm('حذف شود؟')">
                    <button class="btn btn-outline-danger btn-sm" style="padding:4px 8px;font-size:11px;"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </div>
            
            <!-- Amount Progress -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted small"><i class="bi bi-cash me-1"></i>مبلغ</span>
                    <span class="fw-bold small text-<?php echo $pctColor; ?>"><?php echo $pct; ?>%</span>
                </div>
                <div class="progress" style="height:8px;border-radius:4px;">
                    <div class="progress-bar bg-<?php echo $pctColor; ?>" style="width:<?php echo min($pct, 100); ?>%;border-radius:4px;transition:width 0.5s;"></div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted"><?php echo number_format($t->achieved_amount); ?></small>
                    <small class="text-muted"><?php echo number_format($t->target_amount); ?> ریال</small>
                </div>
            </div>
            
            <!-- Deals Progress -->
            <div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted small"><i class="bi bi-briefcase me-1"></i>تعداد معاملات</span>
                    <span class="fw-bold small text-<?php echo $dealColor; ?>"><?php echo $dealPct; ?>%</span>
                </div>
                <div class="progress" style="height:8px;border-radius:4px;">
                    <div class="progress-bar bg-<?php echo $dealColor; ?>" style="width:<?php echo min($dealPct, 100); ?>%;border-radius:4px;transition:width 0.5s;"></div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted"><?php echo $t->achieved_deals; ?> عدد</small>
                    <small class="text-muted"><?php echo $t->target_deals; ?> عدد</small>
                </div>
            </div>
            
            <?php if ($pct >= 100): ?>
            <div class="text-center mt-3">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2"><i class="bi bi-check-circle me-1"></i>هدف محقق شده!</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center py-5">
        <i class="bi bi-bullseye text-muted" style="font-size:48px;opacity:0.3;"></i>
        <h6 class="fw-bold mt-3 text-muted">هنوز هدفی تعریف نشده</h6>
        <p class="text-muted small">از فرم زیر برای تعریف هدف جدید استفاده کنید</p>
    </div>
</div>
<?php endif; ?>

<!-- Add New Target -->
<?php if (\Core\Auth::hasPermission('settings.manage')): ?>
<div class="card border-0 shadow-sm" style="max-width:700px;">
    <div class="card-header bg-white border-bottom">
        <h6 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>تعریف هدف جدید</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo $config['url']; ?>/targets/store">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium">نوع هدف</label>
                    <select name="target_type" class="form-select" id="targetType" onchange="toggleTargetOptions()">
                        <option value="user"><i class="bi bi-person me-1"></i> کاربر</option>
                        <option value="team"><i class="bi bi-people me-1"></i> تیم</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium">هدف</label>
                    <select name="target_id" class="form-select" id="targetId">
                        <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u->id; ?>" class="opt-user"><i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($u->full_name); ?></option>
                        <?php endforeach; ?>
                        <?php foreach ($teams as $t): ?>
                        <option value="<?php echo $t->id; ?>" class="opt-team" style="display:none;"><i class="bi bi-people me-1"></i> <?php echo htmlspecialchars($t->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-cash me-1"></i>هدف مبلغ (ریال)</label>
                    <input type="number" name="target_amount" class="form-control" min="0" placeholder="مثال: 100000000">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-briefcase me-1"></i>هدف تعداد معامله</label>
                    <input type="number" name="target_deals" class="form-control" min="0" placeholder="مثال: 10">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-event me-1"></i>تاریخ شروع بازه (اختیاری)</label>
                    <input type="date" name="date_from" class="form-control">
                    <small class="text-muted">برای بازه سفارشی به جای ماه</small>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-check me-1"></i>تاریخ پایان بازه (اختیاری)</label>
                    <input type="date" name="date_to" class="form-control">
                    <small class="text-muted">خالی = کل ماه انتخاب شده</small>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره هدف</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleTargetOptions() {
    var type = document.getElementById('targetType').value;
    document.querySelectorAll('#targetId .opt-user').forEach(function(o){ o.style.display = type === 'user' ? '' : 'none'; });
    document.querySelectorAll('#targetId .opt-team').forEach(function(o){ o.style.display = type === 'team' ? '' : 'none'; });
}
</script>
<?php endif; ?>