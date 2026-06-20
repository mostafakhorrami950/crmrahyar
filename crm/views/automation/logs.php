<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>📝 لاگ اتوماسیون</h5>
    <a href="<?php echo $config['url']; ?>/automation" class="btn btn-secondary">بازگشت</a>
</div>

<div class="card">
    <?php if (empty($logs)): ?>
    <p class="text-muted" style="padding:30px;text-align:center;">لاگی ثبت نشده</p>
    <?php else: ?>
    <div class="table-wrapper"><table>
        <thead><tr><th>قانون</th><th>موجودیت</th><th>وضعیت</th><th>نتیجه</th><th>تاریخ</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $l): ?>
        <tr>
            <td class="fw-bold"><?php echo htmlspecialchars($l->rule_name ?? '—'); ?></td>
            <td><?php echo $l->entity_type . '#' . $l->entity_id; ?></td>
            <td><?php
                $statusBadge = ['success'=>'badge-success','failed'=>'badge-danger','skipped'=>'badge-warning'];
                $statusLabel = ['success'=>'✅ موفق','failed'=>'❌ خطا','skipped'=>'⏭️ رد شده'];
                echo '<span class="badge '.($statusBadge[$l->status]??'badge-secondary').'">'.($statusLabel[$l->status]??$l->status).'</span>';
            ?></td>
            <td class="fs-12"><?php echo htmlspecialchars($l->result_message ?? ''); ?></td>
            <td class="fs-12"><?php echo \Core\JDate::displayDate($l->created_at); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>