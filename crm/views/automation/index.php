<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>🤖 اتوماسیون</h5>
    <div class="d-flex gap-8">
        <a href="<?php echo $config['url']; ?>/automation/logs" class="btn btn-sm btn-secondary">📝 لاگ اتوماسیون</a>
        <a href="<?php echo $config['url']; ?>/automation/create" class="btn btn-primary">➕ قانون جدید</a>
    </div>
</div>

<?php if (empty($rules)): ?>
<div class="empty-state">
    <div class="empty-icon">🤖</div>
    <h5>هنوز قانون اتوماسیونی تعریف نشده</h5>
    <p>با اتوماسیون، کارهای تکراری را خودکار کنید</p>
    <a href="<?php echo $config['url']; ?>/automation/create" class="btn btn-primary">➕ ایجاد قانون</a>
</div>
<?php else: ?>
<div class="card">
    <div class="table-wrapper"><table>
        <thead><tr><th>نام</th><th>ماشه</th><th>اقدام</th><th>اجراها</th><th>وضعیت</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php
            $triggerTypes = \Controllers\AutomationController::getTriggerTypes();
            $actionTypes = \Controllers\AutomationController::getActionTypes();
            $conditions = json_decode($r->trigger_conditions, true) ?: [];
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
                    <a href="<?php echo $config['url']; ?>/automation/edit/<?php echo $r->id; ?>" class="btn btn-sm btn-secondary">✏️</a>
                    <form method="POST" action="<?php echo $config['url']; ?>/automation/delete/<?php echo $r->id; ?>" style="display:inline;" onsubmit="return confirm('حذف شود؟')">
                        <button class="btn btn-sm btn-danger">🗑️</button>
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