<?php $config = $GLOBALS['app_config'];
$jYear = $year;
$jMonth = $month;
$monthName = $monthName ?? \Core\JDate::monthName($jMonth);
$daysInMonth = $daysInMonth ?? \Core\JDate::daysInMonth($jYear, $jMonth);

// Convert 1st day of Jalali month to Gregorian to find day of week
list($gYear, $gMonth, $gDay) = \Core\JDate::toGregorian($jYear, $jMonth, 1);
$gregorianDow = (int)date('w', mktime(0, 0, 0, $gMonth, $gDay, $gYear));
$firstDayAdj = ($gregorianDow + 1) % 7;

// Today
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
                        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $act->deal_id; ?>" class="d-block text-decoration-none mb-1 px-1 py-0 rounded small" style="font-size:10px;background:var(--bs-primary);color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo htmlspecialchars($act->subject); ?>">
                            <?php echo mb_substr($act->subject, 0, 12); ?>
                        </a>
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
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="small fw-bold">تاریخ</th>
                        <th class="small fw-bold">موضوع</th>
                        <th class="small fw-bold d-none d-md-table-cell">نوع</th>
                        <th class="small fw-bold d-none d-md-table-cell">معامله</th>
                        <th class="small fw-bold">وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($activities as $a): ?>
                <tr>
                    <td class="small"><?php echo \Core\JDate::displayDate($a->activity_date); ?></td>
                    <td class="small fw-medium"><?php echo htmlspecialchars($a->subject); ?></td>
                    <td class="d-none d-md-table-cell">
                        <span class="badge bg-light text-dark small">
                            <i class="bi <?php echo $typeIcons[$a->type] ?? 'bi-circle'; ?> me-1"></i><?php echo $typeNames[$a->type] ?? $a->type; ?>
                        </span>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $a->deal_id; ?>" class="text-decoration-none small"><?php echo htmlspecialchars($a->deal_title); ?></a>
                    </td>
                    <td>
                        <?php if ($a->is_done): ?>
                        <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle me-1"></i>انجام شده</span>
                        <?php else: ?>
                        <span class="badge bg-warning bg-opacity-10 text-warning"><i class="bi bi-clock me-1"></i>در انتظار</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>