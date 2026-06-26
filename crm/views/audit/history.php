<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>تاریخچه: <?php echo htmlspecialchars($entityName); ?></h5>
    <a href="<?php echo $config['url']; ?>/audit" class="btn btn-outline-secondary"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
</div>

<?php if (empty($logs)): ?>
<div class="card"><div class="card-body text-center text-muted py-5">تاریخچه‌ای یافت نشد</div></div>
<?php else: ?>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>تاریخ</th><th>کاربر</th><th>عملیات</th><th>تغییرات</th><th class="text-center">بازگردانی</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td class="text-nowrap small"><?php echo \Core\JDate::displayDateTime($log->created_at); ?></td>
                <td class="fw-semibold"><?php echo htmlspecialchars($log->user_name ?? 'سیستم'); ?></td>
                <td>
                    <?php
                    $ac = ['create'=>'success','update'=>'warning','delete'=>'danger'];
                    $al = ['create'=>'ایجاد','update'=>'ویرایش','delete'=>'حذف'];
                    $ai = ['create'=>'plus-circle','update'=>'pencil','delete'=>'trash'];
                    ?>
                    <span class="badge bg-<?php echo $ac[$log->action]??'secondary'; ?>"><i class="bi bi-<?php echo $ai[$log->action]??'circle'; ?> me-1"></i><?php echo $al[$log->action]??$log->action; ?></span>
                </td>
                <td>
                    <?php if ($log->action === 'update' && $log->changes): ?>
                    <?php $changes = json_decode($log->changes, true); ?>
                    <div class="small">
                        <?php foreach ($changes as $field => $vals): ?>
                        <div><span class="text-muted"><?php echo \Core\AuditTrail::getFieldLabel($field); ?>:</span> <del class="text-danger"><?php echo mb_substr((string)($vals['old']??''),0,50); ?></del> → <span class="text-success"><?php echo mb_substr((string)($vals['new']??''),0,50); ?></span></div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif($log->action==='create'): ?><small class="text-muted">رکورد جدید</small>
                    <?php elseif($log->action==='delete'): ?><small class="text-danger">حذف شد</small>
                    <?php else: ?><small class="text-muted">—</small><?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if ($log->snapshot && $log->action !== 'delete'): ?>
                    <form method="POST" action="<?php echo $config['url']; ?>/audit/rollback" style="display:inline;" onsubmit="return confirm('بازگردانی شود؟')">
                        <input type="hidden" name="log_id" value="<?php echo $log->id; ?>">
                        <input type="hidden" name="entity_type" value="<?php echo $log->entity_type; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-warning"><i class="bi bi-arrow-counterclockwise"></i></button>
                    </form>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>