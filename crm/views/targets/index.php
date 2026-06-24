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
    // Amounts are stored in Toman
    $targetAmountToman = $t->target_amount;
    $achievedAmountToman = $t->achieved_amount;
    $pct = $targetAmountToman > 0 ? round(($achievedAmountToman / $targetAmountToman) * 100, 1) : 0;
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
                <div class="d-flex gap-1">
                    <button class="btn btn-outline-primary btn-sm" style="padding:4px 8px;font-size:11px;" onclick="editTarget(<?php echo $t->id; ?>, <?php echo $t->target_amount; ?>, <?php echo $t->target_deals; ?>, '<?php echo $t->date_from ?? ''; ?>', '<?php echo $t->date_to ?? ''; ?>')"><i class="bi bi-pencil"></i></button>
                    <form method="POST" action="<?php echo $config['url']; ?>/targets/delete/<?php echo $t->id; ?>" onsubmit="return confirm('حذف شود؟')" class="d-inline">
                        <button class="btn btn-outline-danger btn-sm" style="padding:4px 8px;font-size:11px;"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Date Range Info -->
            <?php if (!empty($t->date_from) && !empty($t->date_to)): ?>
            <div class="mb-2">
                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>بازه: <?php echo \Core\JDate::displayDate($t->date_from); ?> تا <?php echo \Core\JDate::displayDate($t->date_to); ?></small>
            </div>
            <?php endif; ?>
            
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
                    <small class="text-muted"><?php echo number_format($achievedAmountToman); ?> تومان</small>
                    <small class="text-muted"><?php echo number_format($targetAmountToman); ?> تومان</small>
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
        <h6 class="fw-bold mb-0" id="formTitle"><i class="bi bi-plus-circle me-2 text-primary"></i>تعریف هدف جدید</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo $config['url']; ?>/targets/store" id="targetForm">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <input type="hidden" name="edit_id" id="editId" value="">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium">نوع هدف</label>
                    <select name="target_type" class="form-select" id="targetType" onchange="toggleTargetOptions()">
                        <option value="user">کاربر</option>
                        <option value="team">تیم</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium">هدف</label>
                    <select name="target_id" class="form-select" id="targetId">
                        <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u->id; ?>" class="opt-user"><?php echo htmlspecialchars($u->full_name); ?></option>
                        <?php endforeach; ?>
                        <?php foreach ($teams as $t): ?>
                        <option value="<?php echo $t->id; ?>" class="opt-team" style="display:none;"><?php echo htmlspecialchars($t->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-cash me-1"></i>هدف مبلغ (تومان)</label>
                    <input type="number" name="target_amount" id="targetAmount" class="form-control" min="0" placeholder="مثال: 10000000">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-briefcase me-1"></i>هدف تعداد معامله</label>
                    <input type="number" name="target_deals" id="targetDeals" class="form-control" min="0" placeholder="مثال: 10">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-event me-1"></i>تاریخ شروع بازه (اختیاری)</label>
                    <input type="date" name="date_from" id="dateFrom" class="form-control">
                    <small class="text-muted">برای بازه سفارشی به جای کل ماه</small>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-calendar-check me-1"></i>تاریخ پایان بازه (اختیاری)</label>
                    <input type="date" name="date_to" id="dateTo" class="form-control">
                    <small class="text-muted">خالی = کل ماه انتخاب شده</small>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i><span id="submitBtnText">ذخیره هدف</span></button>
                    <button type="button" class="btn btn-outline-secondary d-none" id="cancelEditBtn" onclick="cancelEdit()">انصراف</button>
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

function editTarget(id, amount, deals, dateFrom, dateTo) {
    document.getElementById('editId').value = id;
    document.getElementById('targetAmount').value = amount;
    document.getElementById('targetDeals').value = deals;
    document.getElementById('dateFrom').value = dateFrom || '';
    document.getElementById('dateTo').value = dateTo || '';
    
    // Change form action to update
    document.getElementById('targetForm').action = '<?php echo $config['url']; ?>/targets/update/' + id;
    document.getElementById('formTitle').innerHTML = '<i class="bi bi-pencil me-2 text-primary"></i>ویرایش هدف';
    document.getElementById('submitBtnText').textContent = 'بروزرسانی هدف';
    document.getElementById('cancelEditBtn').classList.remove('d-none');
    
    // Scroll to form
    document.getElementById('targetForm').scrollIntoView({behavior: 'smooth'});
}

function cancelEdit() {
    document.getElementById('editId').value = '';
    document.getElementById('targetAmount').value = '';
    document.getElementById('targetDeals').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    
    document.getElementById('targetForm').action = '<?php echo $config['url']; ?>/targets/store';
    document.getElementById('formTitle').innerHTML = '<i class="bi bi-plus-circle me-2 text-primary"></i>تعریف هدف جدید';
    document.getElementById('submitBtnText').textContent = 'ذخیره هدف';
    document.getElementById('cancelEditBtn').classList.add('d-none');
}
</script>
<?php endif; ?>