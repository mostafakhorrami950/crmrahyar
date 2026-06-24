<?php $config = $GLOBALS['app_config'];
$jYear = $year;
$jMonth = $month;
$monthName = $monthName ?? \Core\JDate::monthName($jMonth);
$daysInMonth = $daysInMonth ?? \Core\JDate::daysInMonth($jYear, $jMonth);

// Convert 1st day of Jalali month to Gregorian to find day of week
list($gYear, $gMonth, $gDay) = \Core\JDate::toGregorian($jYear, $jMonth, 1);
// 0=Sunday ... 6=Saturday in Gregorian, but Persian week starts Saturday
$gregorianDow = (int)date('w', mktime(0, 0, 0, $gMonth, $gDay, $gYear));
// Adjust: Saturday=0, Sunday=1, ..., Friday=6
$firstDayAdj = ($gregorianDow + 1) % 7;

// Current Jalali date for "today" highlight
list($todayJY, $todayJM, $todayJD) = \Core\JDate::now();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0">🗓️ تقویم فعالیت‌ها</h5>
    <div class="d-flex gap-8 align-center">
        <?php
        $prevMonth = $jMonth - 1;
        $prevYear = $jYear;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
        $nextMonth = $jMonth + 1;
        $nextYear = $jYear;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
        ?>
        <a href="<?php echo $config['url']; ?>/calendar?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-sm btn-outline-secondary">◀</a>
        <span class="fw-bold"><?php echo $monthName . ' ' . $jYear; ?></span>
        <a href="<?php echo $config['url']; ?>/calendar?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-sm btn-outline-secondary">▶</a>
    </div>
</div>

<div class="card">
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center;">
        <div style="padding:8px;font-weight:700;font-size:12px;color:var(--gray-500);">شنبه</div>
        <div style="padding:8px;font-weight:700;font-size:12px;color:var(--gray-500);">یکشنبه</div>
        <div style="padding:8px;font-weight:700;font-size:12px;color:var(--gray-500);">دوشنبه</div>
        <div style="padding:8px;font-weight:700;font-size:12px;color:var(--gray-500);">سه‌شنبه</div>
        <div style="padding:8px;font-weight:700;font-size:12px;color:var(--gray-500);">چهارشنبه</div>
        <div style="padding:8px;font-weight:700;font-size:12px;color:var(--gray-500);">پنجشنبه</div>
        <div style="padding:8px;font-weight:700;font-size:12px;color:var(--gray-500);">جمعه</div>
        
        <?php for ($i = 0; $i < $firstDayAdj; $i++): ?>
        <div style="padding:8px;background:var(--gray-50);border-radius:6px;min-height:70px;"></div>
        <?php endfor; ?>
        
        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
        <?php $isToday = ($d == $todayJD && $jMonth == $todayJM && $jYear == $todayJY); ?>
        <div style="padding:6px;background:<?php echo $isToday ? '#eef0ff' : '#fff'; ?>;border-radius:6px;min-height:70px;border:1px solid <?php echo $isToday ? 'var(--primary)' : 'var(--gray-200)'; ?>;text-align:right;">
            <div style="font-weight:700;font-size:13px;color:<?php echo $isToday ? 'var(--primary)' : 'var(--gray-700)'; ?>;margin-bottom:4px;">
                <?php echo $d; ?>
            </div>
            <?php if (isset($days[$d])): ?>
            <?php foreach (array_slice($days[$d], 0, 3) as $act): ?>
            <div style="font-size:10px;padding:2px 4px;background:var(--primary-light);border-radius:4px;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $act->deal_id; ?>" style="color:var(--primary);">
                <?php echo mb_substr($act->subject, 0, 15); ?>
                </a>
            </div>
            <?php endforeach; ?>
            <?php if (count($days[$d]) > 3): ?>
            <div style="font-size:9px;color:var(--gray-500);">+<?php echo count($days[$d]) - 3; ?> بیشتر</div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-list-task me-1"></i> فعالیت‌های <?php echo $monthName; ?></div>
    <?php if (empty($activities)): ?>
    <p class="text-muted" style="padding:20px;text-align:center;">فعالیتی در این ماه ثبت نشده</p>
    <?php else: ?>
    <div class="table-responsive"><table>
        <thead><tr><th class="text-nowrap">تاریخ شمسی</th><th class="text-nowrap">موضوع</th><th class="text-nowrap">نوع</th><th class="text-nowrap">معامله</th><th class="text-nowrap">وضعیت</th></tr></thead>
        <tbody>
        <?php foreach ($activities as $a): ?>
        <tr>
            <td><?php echo \Core\JDate::displayDate($a->activity_date); ?></td>
            <td><?php echo htmlspecialchars($a->subject); ?></td>
            <td><span class="badge badge-info"><?php echo $a->type; ?></span></td>
            <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $a->deal_id; ?>"><?php echo htmlspecialchars($a->deal_title); ?></a></td>
            <td><?php echo $a->is_done ? '<span class="badge badge-success"><i class="bi bi-check-circle text-success me-1"></i> انجام شده</span>' : '<span class="badge badge-warning"><i class="bi bi-clock text-warning me-1"></i> در انتظار</span>'; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>