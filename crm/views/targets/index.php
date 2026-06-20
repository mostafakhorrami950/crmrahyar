<?php $config = $GLOBALS['app_config'];
$monthNames = ['','فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
?>
<div class="page-header">
    <h5>🎯 هدف‌گذاری فروش - <?php echo $monthNames[$month] ?? $month; ?> <?php echo $year; ?></h5>
    <div class="d-flex gap-8 align-center">
        <a href="<?php echo $config['url']; ?>/targets?month=<?php echo $month - 1 ?: 12; ?>&year=<?php echo $month == 1 ? $year - 1 : $year; ?>" class="btn btn-sm btn-secondary">◀</a>
        <span class="fw-bold"><?php echo $monthNames[$month] . ' ' . $year; ?></span>
        <a href="<?php echo $config['url']; ?>/targets?month=<?php echo $month > 12 ? 1 : $month + 1; ?>&year=<?php echo $month > 12 ? $year + 1 : $year; ?>" class="btn btn-sm btn-secondary">▶</a>
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
        <span class="fw-bold fs-13"><?php echo $t->target_type === 'user' ? '👤' : '👥'; ?> <?php echo htmlspecialchars($t->target_name); ?></span>
        <form method="POST" action="<?php echo $config['url']; ?>/targets/delete/<?php echo $t->id; ?>" style="display:inline;" onsubmit="return confirm('حذف شود؟')">
            <button class="btn btn-sm btn-danger" style="padding:2px 6px;font-size:10px;">🗑️</button>
        </form>
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

<!-- Add new target -->
<div class="card" style="max-width:600px;">
    <div class="card-header">➕ تعریف هدف جدید</div>
    <form method="POST" action="<?php echo $config['url']; ?>/targets/store">
        <input type="hidden" name="year" value="<?php echo $year; ?>">
        <input type="hidden" name="month" value="<?php echo $month; ?>">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">نوع هدف</label>
                <select name="target_type" class="form-select" id="targetType" onchange="toggleTargetOptions()">
                    <option value="user">👤 کاربر</option>
                    <option value="team">👥 تیم</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">هدف</label>
                <select name="target_id" class="form-select" id="targetId">
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>" class="opt-user">👤 <?php echo htmlspecialchars($u->full_name); ?></option>
                    <?php endforeach; ?>
                    <?php foreach ($teams as $t): ?>
                    <option value="<?php echo $t->id; ?>" class="opt-team" style="display:none;">👥 <?php echo htmlspecialchars($t->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">هدف مبلغ (ریال)</label>
                <input type="number" name="target_amount" class="form-input" min="0" placeholder="مثال: 100000000">
            </div>
            <div class="form-group">
                <label class="form-label">هدف تعداد معامله</label>
                <input type="number" name="target_deals" class="form-input" min="0" placeholder="مثال: 10">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">💾 ذخیره هدف</button>
    </form>
</div>

<script>
function toggleTargetOptions() {
    var type = document.getElementById('targetType').value;
    document.querySelectorAll('#targetId .opt-user').forEach(function(o){ o.style.display = type === 'user' ? '' : 'none'; });
    document.querySelectorAll('#targetId .opt-team').forEach(function(o){ o.style.display = type === 'team' ? '' : 'none'; });
}
</script>