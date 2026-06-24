<?php $config = $GLOBALS['app_config'];
$jYear = $year;
$jMonth = $month;
$monthName = $monthName ?? \Core\JDate::monthName($jMonth);
$daysInMonth = $daysInMonth ?? \Core\JDate::daysInMonth($jYear, $jMonth);

list($gYear, $gMonth, $gDay) = \Core\JDate::toGregorian($jYear, $jMonth, 1);
$gregorianDow = (int)date('w', mktime(0, 0, 0, $gMonth, $gDay, $gYear));
$firstDayAdj = ($gregorianDow + 1) % 7;

list($todayJY, $todayJM, $todayJD) = \Core\JDate::now();

$typeIcons = ['call'=>'bi-telephone','meeting'=>'bi-people','sms'=>'bi-envelope','email'=>'bi-envelope-at','follow_up'=>'bi-pin','note'=>'bi-journal-text'];
$typeNames = ['call'=>'تماس','meeting'=>'جلسه','sms'=>'پیامک','email'=>'ایمیل','follow_up'=>'پیگیری','note'=>'یادداشت'];

$prevMonth = $jMonth - 1; $prevYear = $jYear;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $jMonth + 1; $nextYear = $jYear;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2 text-primary"></i>تقویم فعالیت‌ها</h5>
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/calendar?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></a>
        <span class="fw-bold px-3"><?php echo $monthName . ' ' . $jYear; ?></span>
        <a href="<?php echo $config['url']; ?>/calendar?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></a>
    </div>
</div>

<!-- Calendar Grid -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-2 p-md-3">
        <div class="table-responsive">
            <table class="table table-bordered text-center mb-0" style="table-layout:fixed;">
                <thead>
                    <tr class="bg-light">
                        <th class="text-muted small fw-bold py-2">شنبه</th>
                        <th class="text-muted small fw-bold py-2">یکشنبه</th>
                        <th class="text-muted small fw-bold py-2">دوشنبه</th>
                        <th class="text-muted small fw-bold py-2">سه‌شنبه</th>
                        <th class="text-muted small fw-bold py-2">چهارشنبه</th>
                        <th class="text-muted small fw-bold py-2">پنجشنبه</th>
                        <th class="text-muted small fw-bold py-2 text-danger">جمعه</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                    <?php for ($i = 0; $i < $firstDayAdj; $i++): ?>
                        <td class="bg-light" style="min-height:80px;"></td>
                    <?php endfor; ?>
                    
                    <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                    <?php 
                    $isToday = ($d == $todayJD && $jMonth == $todayJM && $jYear == $todayJY);
                    $isFriday = (($firstDayAdj + $d - 1) % 7 == 6);
                    $dayActs = $days[$d] ?? [];
                    ?>
                    <td class="p-1 p-md-2" style="min-height:80px;vertical-align:top;background:<?php echo $isToday ? '#eef0ff' : '#fff'; ?>;<?php echo $isFriday ? 'color:#9ca3af;' : ''; ?>">
                        <div class="fw-bold small mb-1 <?php echo $isToday ? 'text-primary' : ($isFriday ? 'text-muted' : 'text-dark'); ?>" style="<?php echo $isToday ? 'background:var(--bs-primary);color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;' : ''; ?>">
                            <?php echo $d; ?>
                        </div>
                        <?php foreach (array_slice($dayActs, 0, 2) as $act): ?>
                        <div class="mb-1 d-flex align-items-center gap-1" style="font-size:10px;">
                            <button type="button" class="btn btn-sm rounded-circle cal-toggle-btn <?php echo $act->is_done ? 'btn-success' : 'btn-outline-secondary'; ?>" style="width:18px;height:18px;padding:0;font-size:8px;" data-id="<?php echo $act->id; ?>" title="<?php echo $act->is_done ? 'انجام شده' : 'انجام نشده'; ?>">
                                <i class="bi <?php echo $act->is_done ? 'bi-check' : 'bi-circle'; ?>" style="font-size:8px;"></i>
                            </button>
                            <a href="javascript:void(0)" class="text-decoration-none flex-grow-1 quick-view-deal" data-id="<?php echo $act->deal_id; ?>" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:<?php echo $act->is_done ? '#9ca3af' : 'var(--bs-primary)'; ?>;text-decoration:<?php echo $act->is_done ? 'line-through' : 'none' ?>;" title="<?php echo htmlspecialchars($act->subject); ?>">
                                <?php echo mb_substr($act->subject, 0, 10); ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($dayActs) > 2): ?>
                        <small class="text-muted" style="font-size:9px;">+<?php echo count($dayActs) - 2; ?></small>
                        <?php endif; ?>
                    </td>
                    <?php if (($firstDayAdj + $d) % 7 == 0 && $d < $daysInMonth): ?>
                    </tr><tr>
                    <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php
                    $remaining = ($firstDayAdj + $daysInMonth) % 7;
                    if ($remaining > 0):
                        for ($i = $remaining; $i < 7; $i++): ?>
                        <td class="bg-light"></td>
                    <?php endfor; endif; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Activities List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="fw-bold mb-0"><i class="bi bi-list-task me-2 text-primary"></i>فعالیت‌های <?php echo $monthName; ?></h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($activities)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-calendar-check fs-1 d-block mb-2 opacity-25"></i>
            <p>فعالیتی در این ماه ثبت نشده</p>
        </div>
        <?php else: ?>
        <?php foreach ($activities as $a): ?>
        <?php $isOverdue = !$a->is_done && $a->activity_date && strtotime($a->activity_date) < time(); ?>
        <div class="d-flex align-items-start gap-3 px-3 py-3 border-bottom activity-row <?php echo $a->is_done ? 'opacity-50' : ''; ?> <?php echo $isOverdue ? 'bg-danger bg-opacity-5' : ''; ?>">
            <button type="button" class="btn btn-sm rounded-circle toggle-done-btn flex-shrink-0 <?php echo $a->is_done ? 'btn-success' : ($isOverdue ? 'btn-danger' : 'btn-outline-secondary'); ?>" style="width:36px;height:36px;padding:0;" data-id="<?php echo $a->id; ?>">
                <i class="bi <?php echo $a->is_done ? 'bi-check-lg' : ($isOverdue ? 'bi-exclamation' : 'bi-circle'); ?>"></i>
            </button>
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;background:<?php echo $a->type == 'call' ? '#e3f2fd' : '#f3e5f5'; ?>;">
                <i class="bi <?php echo $typeIcons[$a->type] ?? 'bi-journal-text'; ?>"></i>
            </div>
            <div class="flex-grow-1">
                <strong class="<?php echo $a->is_done ? 'text-decoration-line-through' : ''; ?>" style="font-size:14px;"><?php echo htmlspecialchars($a->subject); ?></strong>
                <div class="d-flex gap-2 align-items-center flex-wrap mt-1">
                    <span class="badge bg-light text-dark small"><?php echo $typeNames[$a->type] ?? $a->type; ?></span>
                    <small class="text-muted"><i class="bi bi-calendar me-1"></i><?php echo \Core\JDate::displayDate($a->activity_date); ?></small>
                    <?php if ($a->deal_id): ?>
                    <a href="javascript:void(0)" class="badge bg-primary bg-opacity-10 text-primary text-decoration-none quick-view-deal" data-id="<?php echo $a->deal_id; ?>"><i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars(mb_substr($a->deal_title ?? '-', 0, 25)); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold" id="qvTitle"><i class="bi bi-eye me-2"></i>مشاهده سریع</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="qvBody">
                <div class="text-center py-4"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer">
                <a href="#" id="qvLink" class="btn btn-primary"><i class="bi bi-box-arrow-up-right me-1"></i>مشاهده کامل</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var baseUrl = '<?php echo $config['url']; ?>';
    
    // Toggle Done (both calendar grid and list)
    document.querySelectorAll('.toggle-done-btn, .cal-toggle-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var btnEl = this;
            fetch(baseUrl + '/activities/toggle-done/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                // Toggle visual state
                var isNowDone = btnEl.classList.contains('btn-outline-secondary') || btnEl.classList.contains('btn-danger');
                if (isNowDone) {
                    btnEl.className = btnEl.classList.contains('cal-toggle-btn') ? 'btn btn-sm rounded-circle cal-toggle-btn btn-success' : 'btn btn-sm rounded-circle toggle-done-btn btn-success';
                    btnEl.innerHTML = '<i class="bi ' + (btnEl.classList.contains('cal-toggle-btn') ? 'bi-check' : 'bi-check-lg') + '"></i>';
                    var row = btnEl.closest('.activity-row');
                    if (row) { row.classList.add('opacity-50'); row.classList.remove('bg-danger','bg-opacity-5'); }
                } else {
                    btnEl.className = btnEl.classList.contains('cal-toggle-btn') ? 'btn btn-sm rounded-circle cal-toggle-btn btn-outline-secondary' : 'btn btn-sm rounded-circle toggle-done-btn btn-outline-secondary';
                    btnEl.innerHTML = '<i class="bi ' + (btnEl.classList.contains('cal-toggle-btn') ? 'bi-circle' : 'bi-circle') + '"></i>';
                    var row = btnEl.closest('.activity-row');
                    if (row) row.classList.remove('opacity-50');
                }
            })
            .catch(function() {});
        });
    });
    
    // Quick View Deal
    document.querySelectorAll('.quick-view-deal').forEach(function(el) {
        el.addEventListener('click', function() {
            var id = this.dataset.id;
            var modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
            document.getElementById('qvTitle').innerHTML = '<i class="bi bi-briefcase me-2"></i>اطلاعات معامله';
            document.getElementById('qvBody').innerHTML = '<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>';
            document.getElementById('qvLink').href = baseUrl + '/deals/view/' + id;
            modal.show();
            fetch(baseUrl + '/deals/get-data/' + id)
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success && d.deal) {
                    var dl = d.deal;
                    document.getElementById('qvBody').innerHTML = 
                        '<div class="row g-3">' +
                        '<div class="col-12"><div class="d-flex gap-3 p-3 bg-light rounded-3"><div class="rounded-3 bg-primary d-flex align-items-center justify-content-center text-white" style="width:48px;height:48px;"><i class="bi bi-briefcase fs-4"></i></div><div><strong class="d-block fs-6">' + (dl.title||'-') + '</strong><small class="text-muted">' + (dl.contact_name||'') + '</small></div></div></div>' +
                        '<div class="col-6"><small class="text-muted d-block">مبلغ</small><strong class="text-primary">' + (dl.amount ? parseInt(dl.amount).toLocaleString('en-US') + ' تومان' : '-') + '</strong></div>' +
                        '<div class="col-6"><small class="text-muted d-block">وضعیت</small>' + (dl.is_won ? '<span class="badge bg-success">موفق</span>' : (dl.is_lost ? '<span class="badge bg-danger">ناموفق</span>' : '<span class="badge bg-warning text-dark">در جریان</span>')) + '</div>' +
                        '</div>';
                }
            })
            .catch(function() { document.getElementById('qvBody').innerHTML = '<p class="text-danger">خطا</p>'; });
        });
    });
});
</script>