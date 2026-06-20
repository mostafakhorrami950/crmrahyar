<?php $config = $GLOBALS['app_config'];
$monthNames = ['','فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
$daysInMonth = date('t', mktime(0,0,0,$month,1,$year));
$firstDay = date('w', mktime(0,0,0,$month,1,$year)); // 0=Sat for Iranian
// Adjust for Persian week starting Saturday
$firstDayAdj = ($firstDay + 1) % 7;
$jalali = \Core\JDate::toJalali($year, $month, 1);
$jMonth = $monthNames[$jalali[1]] ?? $month;
$jYear = $jalali[0];
?>

<div class="page-header">
    <h5>🗓️ تقویم فعالیت‌ها</h5>
    <div class="d-flex gap-8 align-center">
        <a href="<?php echo $config['url']; ?>/calendar?month=<?php echo $month - 1; ?>&year=<?php echo $year; ?>" class="btn btn-sm btn-secondary">◀</a>
        <span class="fw-bold"><?php echo $jMonth . ' ' . $jYear; ?></span>
        <a href="<?php echo $config['url']; ?>/calendar?month=<?php echo $month + 1; ?>&year=<?php echo $year; ?>" class="btn btn-sm btn-secondary">▶</a>
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
        <?php $isToday = ($d == date('j') && $month == date('n') && $year == date('Y')); ?>
        <div style="padding:6px;background:<?php echo $isToday ? '#eef0ff' : '#fff'; ?>;border-radius:6px;min-height:70px;border:1px solid <?php echo $isToday ? 'var(--primary)' : 'var(--gray-200)'; ?>;text-align:right;">
            <div style="font-weight:700;font-size:13px;color:<?php echo $isToday ? 'var(--primary)' : 'var(--gray-700)'; ?>;margin-bottom:4px;">
                <?php 
                $jDate = \Core\JDate::toJalali($year, $month, $d);
                echo $jDate[2]; 
                ?>
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
    <div class="card-header">📋 فعالیت‌های <?php echo $jMonth; ?></div>
    <?php if (empty($activities)): ?>
    <p class="text-muted" style="padding:20px;text-align:center;">فعالیتی در این ماه ثبت نشده</p>
    <?php else: ?>
    <div class="table-wrapper"><table>
        <thead><tr><th>تاریخ</th><th>موضوع</th><th>نوع</th><th>معامله</th><th>وضعیت</th></tr></thead>
        <tbody>
        <?php foreach ($activities as $a): ?>
        <tr>
            <td><?php echo \Core\JDate::displayDate($a->activity_date); ?></td>
            <td><?php echo htmlspecialchars($a->subject); ?></td>
            <td><span class="badge badge-info"><?php echo $a->type; ?></span></td>
            <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $a->deal_id; ?>"><?php echo htmlspecialchars($a->deal_title); ?></a></td>
            <td><?php echo $a->is_done ? '<span class="badge badge-success">✅ انجام شده</span>' : '<span class="badge badge-warning">⏳ در انتظار</span>'; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>