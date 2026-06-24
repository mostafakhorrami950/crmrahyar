<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0">🤖 اتوماسیون</h5>
    <div class="d-flex gap-8">
        <a href="<?php echo $config['url']; ?>/automation/logs" class="btn btn-sm btn-outline-secondary"><i class="bi bi-journal-text me-1"></i> لاگ اتوماسیون</a>
        <a href="<?php echo $config['url']; ?>/automation/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> قانون جدید</a>
    </div>
</div>

<?php if (empty($rules)): ?>
<div class="empty-state">
    <div class="empty-icon">🤖</div>
    <h5 class="fw-bold mb-0">هنوز قانون اتوماسیونی تعریف نشده</h5>
    <p>با اتوماسیون، کارهای تکراری را خودکار کنید</p>
    <a href="<?php echo $config['url']; ?>/automation/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>ایجاد قانون</a>
</div>
<?php else: ?>
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr><th class="text-nowrap">نام</th><th class="text-nowrap">ماشه</th><th class="text-nowrap">اقدام</th><th class="text-nowrap">اجراها</th><th class="text-nowrap">وضعیت</th><th class="text-nowrap">عملیات</th></tr></thead>
        <tbody>
        <?php
            $triggerTypes = \Controllers\AutomationController::getTriggerTypes();
            $actionTypes = \Controllers\AutomationController::getActionTypes();
        ?>
        <?php foreach ($rules as $r): ?>
        <tr>
            <td class="fw-bold">
                <div><?php echo htmlspecialchars($r->name); ?></div>
                <?php if (!empty($r->description)): ?>
                <div style="font-size:11px;color:var(--gray-400);font-weight:400;margin-top:2px;"><?php echo htmlspecialchars(mb_substr($r->description, 0, 60)); ?></div>
                <?php endif; ?>
            </td>
            <td>
                <?php $tInfo = $triggerTypes[$r->trigger_type] ?? null; ?>
                <span class="badge badge-info"><?php echo $tInfo ? $tInfo['label'] : $r->trigger_type; ?></span>
            </td>
            <td>
                <?php $aInfo = $actionTypes[$r->action_type] ?? null; ?>
                <span class="badge badge-primary"><?php echo $aInfo ? $aInfo['label'] : $r->action_type; ?></span>
            </td>
            <td><span style="font-weight:700;"><?php echo $r->execution_count; ?></span> بار</td>
            <td>
                <label class="toggle-switch">
                    <input type="checkbox" <?php echo $r->is_active ? 'checked' : ''; ?> onchange="toggleRule(<?php echo $r->id; ?>, this)">
                    <span class="toggle-slider"></span>
                </label>
            </td>
            <td>
                <div class="d-flex gap-4">
                    <a href="<?php echo $config['url']; ?>/automation/edit/<?php echo $r->id; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i></a>
                    <form method="POST" action="<?php echo $config['url']; ?>/automation/delete/<?php echo $r->id; ?>" style="display:inline;" onsubmit="return confirm('حذف شود؟')">
                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i></button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<script>
function toggleRule(id, el) {
    fetch('<?php echo $config['url']; ?>/automation/toggle/' + id, {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(d){ if(!d.success) el.checked = !el.checked; });
}
</script>