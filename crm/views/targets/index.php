<?php $config = $GLOBALS['app_config'];
$monthNames = ['','فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-crosshair me-1"></i> هدف‌گذاری فروش - <?php echo $monthNames[$month] ?? $month; ?> <?php echo $year; ?></h5>
    <div class="d-flex gap-8 align-center">
        <a href="<?php echo $config['url']; ?>/targets?month=<?php echo $month - 1 ?: 12; ?>&year=<?php echo $month == 1 ? $year - 1 : $year; ?>" class="btn btn-sm btn-outline-secondary">◀</a>
        <span class="fw-bold"><?php echo $monthNames[$month] . ' ' . $year; ?></span>
        <a href="<?php echo $config['url']; ?>/targets?month=<?php echo $month > 12 ? 1 : $month + 1; ?>&year=<?php echo $month > 12 ? $year + 1 : $year; ?>" class="btn btn-sm btn-outline-secondary">▶</a>
    </div>
</div>

<!-- Existing targets -->
<?php if (!empty($targets)): ?>
<div class="stats-grid">
<?php foreach ($targets as $t): 
    $pct = $t->target_amount > 0 ? round(($t->achieved_amount / $t->target_amount) * 100, 1) : 0;
    $dealPct = $t->target_deals > 0 ? round(($t->achieved_deals / $t->target_deals) * 100, 1) : 0;
?>
<div class="stat-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <span class="fw-bold fs-13"><?php echo $t->target_type === 'user' ? '<i class="bi bi-person me-1"></i>' : '<i class="bi bi-people me-1"></i>'; ?> <?php echo htmlspecialchars($t->target_name); ?></span>
    <?php if (\Core\Auth::hasPermission('settings.manage')): ?>
    <form method="POST" action="<?php echo $config['url']; ?>/targets/delete/<?php echo $t->id; ?>" style="display:inline;" onsubmit="return confirm('حذف شود؟')">
            <button class="btn btn-sm btn-danger" style="padding:2px 6px;font-size:10px;"><i class="bi bi-trash me-1"></i></button>
        </form>
    <?php endif; ?>
    </div>
    <div style="margin-bottom:8px;">
        <div class="d-flex justify-between fs-12 mb-4"><span>مبلغ: <?php echo number_format($t->achieved_amount); ?> / <?php echo number_format($t->target_amount); ?> ریال</span><span class="fw-bold <?php echo $pct >= 100 ? 'text-success' : 'text-primary'; ?>"><?php echo $pct; ?>%</span></div>
        <div style="background:var(--gray-200);border-radius:4px;height:8px;overflow:hidden;"><div style="background:<?php echo $pct >= 100 ? 'var(--success)' : 'var(--primary)'; ?>;width:<?php echo min($pct, 100); ?>%;height:100%;border-radius:4px;transition:width 0.5s;"></div></div>
    </div>
    <div>
        <div class="d-flex justify-between fs-12 mb-4"><span>تعداد: <?php echo $t->achieved_deals; ?> / <?php echo $t->target_deals; ?> معامله</span><span class="fw-bold"><?php echo $dealPct; ?>%</span></div>
        <div style="background:var(--gray-200);border-radius:4px;height:8px;overflow:hidden;"><div style="background:var(--info);width:<?php echo min($dealPct, 100); ?>%;height:100%;border-radius:4px;transition:width 0.5s;"></div></div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (\Core\Auth::hasPermission('settings.manage')): ?>
<!-- Add new target (only for admins) -->
<div class="card" style="max-width:600px;">
    <div class="card-header"><i class="bi bi-plus-circle me-1"></i> تعریف هدف جدید</div>
    <form method="POST" action="<?php echo $config['url']; ?>/targets/store">
        <input type="hidden" name="year" value="<?php echo $year; ?>">
        <input type="hidden" name="month" value="<?php echo $month; ?>">
        <div class="form-row">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">نوع هدف</label>
                <select name="target_type" class="form-select" id="targetType" onchange="toggleTargetOptions()">
                    <option value="user"><i class="bi bi-person me-1"></i> کاربر</option>
                    <option value="team"><i class="bi bi-people me-1"></i> تیم</option>
                </select>
            </div>
            <div class="mb-3">
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
        </div>
        <div class="form-row">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">هدف مبلغ (ریال)</label>
                <input type="number" name="target_amount" class="form-input" min="0" placeholder="مثال: 100000000">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">هدف تعداد معامله</label>
                <input type="number" name="target_deals" class="form-input" min="0" placeholder="مثال: 10">
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره هدف</button>
    </form>
</div>

<script>
function toggleTargetOptions() {
    var type = document.getElementById('targetType').value;
    document.querySelectorAll('#targetId .opt-user').forEach(function(o){ o.style.display = type === 'user' ? '' : 'none'; });
    document.querySelectorAll('#targetId .opt-team').forEach(function(o){ o.style.display = type === 'team' ? '' : 'none'; });
}
<?php endif; // settings.manage ?>
</script>
