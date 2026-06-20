<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>🔍 نتایج جستجو: «<?php echo htmlspecialchars($query); ?>»</h5>
</div>

<?php $totalResults = count($results['deals']) + count($results['contacts']) + count($results['activities']) + count($results['payments']); ?>

<?php if ($totalResults === 0): ?>
<div class="empty-state">
    <div class="empty-icon">🔍</div>
    <h5>نتیجه‌ای یافت نشد</h5>
    <p>برای «<?php echo htmlspecialchars($query); ?>» نتیجه‌ای پیدا نشد.</p>
</div>
<?php else: ?>

<?php if (!empty($results['deals'])): ?>
<div class="card">
    <div class="card-header">💼 معاملات (<?php echo count($results['deals']); ?>)</div>
    <div class="table-wrapper"><table>
        <thead><tr><th>عنوان</th><th>مبلغ</th><th>مرحله</th><th>مخاطب</th><th>وضعیت</th></tr></thead>
        <tbody>
        <?php foreach ($results['deals'] as $d): ?>
        <tr>
            <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $d->id; ?>" style="color:var(--primary);font-weight:600;"><?php echo htmlspecialchars($d->title); ?></a></td>
            <td class="amount-value"><?php echo number_format($d->amount); ?> ریال</td>
            <td><?php echo htmlspecialchars($d->stage_name); ?></td>
            <td><?php echo htmlspecialchars($d->contact_name ?? '-'); ?></td>
            <td><?php echo $d->is_won ? '<span class="badge badge-success">موفق</span>' : ($d->is_lost ? '<span class="badge badge-danger">ناموفق</span>' : '<span class="badge badge-info">در جریان</span>'); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php if (!empty($results['contacts'])): ?>
<div class="card">
    <div class="card-header">👤 مخاطبان (<?php echo count($results['contacts']); ?>)</div>
    <div class="table-wrapper"><table>
        <thead><tr><th>نام</th><th>تلفن</th><th>ایمیل</th><th>شرکت</th></tr></thead>
        <tbody>
        <?php foreach ($results['contacts'] as $c): ?>
        <tr>
            <td><a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $c->id; ?>" style="color:var(--primary);font-weight:600;"><?php echo htmlspecialchars($c->full_name); ?></a></td>
            <td><?php echo htmlspecialchars($c->phone); ?></td>
            <td><?php echo htmlspecialchars($c->email ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($c->company ?? '-'); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php if (!empty($results['activities'])): ?>
<div class="card">
    <div class="card-header">📅 فعالیت‌ها (<?php echo count($results['activities']); ?>)</div>
    <div class="table-wrapper"><table>
        <thead><tr><th>موضوع</th><th>نوع</th><th>تاریخ</th><th>معامله</th></tr></thead>
        <tbody>
        <?php foreach ($results['activities'] as $a): ?>
        <tr>
            <td><?php echo htmlspecialchars($a->subject); ?></td>
            <td><span class="badge badge-info"><?php echo $a->type; ?></span></td>
            <td><?php echo \Core\JDate::displayDate($a->activity_date); ?></td>
            <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $a->deal_id; ?>"><?php echo htmlspecialchars($a->deal_title); ?></a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php if (!empty($results['payments'])): ?>
<div class="card">
    <div class="card-header">💳 پرداخت‌ها (<?php echo count($results['payments']); ?>)</div>
    <div class="table-wrapper"><table>
        <thead><tr><th>معامله</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
        <tbody>
        <?php foreach ($results['payments'] as $p): ?>
        <tr>
            <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $p->deal_id; ?>"><?php echo htmlspecialchars($p->deal_title); ?></a></td>
            <td class="amount-value"><?php echo number_format($p->amount); ?> ریال</td>
            <td><?php echo $p->status === 'paid' ? '<span class="badge badge-success">پرداخت شده</span>' : '<span class="badge badge-secondary">'.$p->status.'</span>'; ?></td>
            <td><?php echo \Core\JDate::displayDate($p->created_at); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php endif; ?>