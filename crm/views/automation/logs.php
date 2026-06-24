<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-journal-text me-1"></i> لاگ اتوماسیون</h5>
    <div style="display:flex;gap:8px;">
        <a href="<?php echo $config['url']; ?>/automation/logs" class="btn btn-sm btn-primary"><i class="bi bi-arrow-repeat me-1"></i> بروزرسانی</a>
        <a href="<?php echo $config['url']; ?>/automation" class="btn btn-outline-secondary">بازگشت</a>
    </div>
</div>

<!-- Stats -->
<div class="stats-row" style="margin-bottom:16px;">
    <?php 
    $db2 = \Core\Database::getInstance();
    $successCount = $db2->fetch("SELECT COUNT(*) as cnt FROM automation_logs WHERE status = 'success'")->cnt ?? 0;
    $failedCount = $db2->fetch("SELECT COUNT(*) as cnt FROM automation_logs WHERE status = 'failed'")->cnt ?? 0;
    $skippedCount = $db2->fetch("SELECT COUNT(*) as cnt FROM automation_logs WHERE status = 'skipped'")->cnt ?? 0;
    ?>
    <div class="stat-card" style="background:linear-gradient(135deg,#10B981,#059669);">
        <div class="stat-value"><?php echo $successCount; ?></div>
        <div class="stat-label"><i class="bi bi-check-circle text-success me-1"></i> موفق</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#EF4444,#DC2626);">
        <div class="stat-value"><?php echo $failedCount; ?></div>
        <div class="stat-label"><i class="bi bi-x-circle text-danger me-1"></i> خطا</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
        <div class="stat-value"><?php echo $skippedCount; ?></div>
        <div class="stat-label">⏭️ رد شده</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#3B82F6,#2563EB);">
        <div class="stat-value"><?php echo $successCount + $failedCount + $skippedCount; ?></div>
        <div class="stat-label"><i class="bi bi-bar-chart me-1"></i> کل</div>
    </div>
</div>

<div class="card">
    <?php if (empty($logs)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--gray-400);">
        <div style="font-size:64px;margin-bottom:16px;"><i class="bi bi-journal-text me-1"></i></div>
        <h3 style="color:var(--gray-500);margin-bottom:8px;">لاگ اتوماسیون خالی است</h3>
        <p>وقتی قوانین اتوماسیون فعال شوند، نتیجه اجرا اینجا ثبت می‌شود</p>
    </div>
    <?php else: ?>
    <div class="table-responsive"><table>
        <thead><tr>
            <th class="text-nowrap">وضعیت</th>
            <th class="text-nowrap">قانون</th>
            <th class="text-nowrap">مرجع</th>
            <th class="text-nowrap">نتیجه</th>
            <th class="text-nowrap">تاریخ</th>
        </tr></thead>
        <tbody>
        <?php foreach ($logs as $l): ?>
        <tr style="<?php echo $l->status === 'failed' ? 'background:#fff5f5;' : ($l->status === 'skipped' ? 'background:#fffbeb;' : ''); ?>">
            <td><?php
                $statusBadge = ['success'=>'badge-success','failed'=>'badge-danger','skipped'=>'badge-warning'];
                $statusLabel = ['success'=>'<i class="bi bi-check-circle text-success me-1"></i> موفق','failed'=>'<i class="bi bi-x-circle text-danger me-1"></i> خطا','skipped'=>'⏭️ رد شده'];
                $statusIcon = ['success'=>'<i class="bi bi-check-circle text-success me-1"></i>','failed'=>'<i class="bi bi-x-circle text-danger me-1"></i>','skipped'=>'⏭️'];
                echo '<span class="badge '.($statusBadge[$l->status]??'badge-secondary').'">'.($statusLabel[$l->status]??$l->status).'</span>';
            ?></td>
            <td class="fw-bold">
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="font-size:16px;">🤖</span>
                    <?php echo htmlspecialchars($l->rule_name ?? '—'); ?>
                </div>
            </td>
            <td>
                <?php if ($l->entity_type === 'deal' && $l->entity_id): ?>
                <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $l->entity_id; ?>" style="display:flex;align-items:center;gap:4px;font-size:12px;color:var(--primary);background:#eef2ff;padding:4px 10px;border-radius:8px;text-decoration:none;font-weight:600;">
                    💼 معامله #<?php echo $l->entity_id; ?> 🔗
                </a>
                <?php elseif ($l->entity_type === 'contact' && $l->entity_id): ?>
                <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $l->entity_id; ?>" style="display:flex;align-items:center;gap:4px;font-size:12px;color:var(--primary);background:#eef2ff;padding:4px 10px;border-radius:8px;text-decoration:none;font-weight:600;">
                    <i class="bi bi-person me-1"></i> مخاطب #<?php echo $l->entity_id; ?> 🔗
                </a>
                <?php else: ?>
                <span style="font-size:12px;color:var(--gray-500);"><?php echo $l->entity_type; ?>#<?php echo $l->entity_id; ?></span>
                <?php endif; ?>
            </td>
            <td class="fs-12" style="max-width:300px;"><?php echo htmlspecialchars($l->result_message ?? ''); ?></td>
            <td class="fs-12"><?php echo \Core\JDate::displayDateTime($l->created_at); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<style>
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; }
.stat-box { color:white; padding:16px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.stat-value { font-weight:800; font-size:28px; }
.stat-label { font-size:12px; opacity:0.9; }
</style>