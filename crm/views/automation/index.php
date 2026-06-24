<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-robot me-2"></i>اتوماسیون</h5>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/automation/logs" class="btn btn-sm btn-outline-secondary"><i class="bi bi-journal-text me-1"></i><span class="d-none d-sm-inline">لاگ اتوماسیون</span><span class="d-sm-none">لاگ</span></a>
        <a href="<?php echo $config['url']; ?>/automation/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i><span class="d-none d-sm-inline">قانون جدید</span><span class="d-sm-none">جدید</span></a>
    </div>
</div>

<?php if (empty($rules)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <div style="font-size:56px;opacity:0.4;" class="mb-3">🤖</div>
        <h5 class="fw-bold text-secondary mb-2">هنوز قانون اتوماسیونی تعریف نشده</h5>
        <p class="text-muted mb-3">با اتوماسیون، کارهای تکراری را خودکار کنید</p>
        <a href="<?php echo $config['url']; ?>/automation/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>ایجاد قانون</a>
    </div>
</div>
<?php else: ?>

<?php
    $triggerTypes = \Controllers\AutomationController::getTriggerTypes();
    $actionTypes = \Controllers\AutomationController::getActionTypes();
?>

<!-- Desktop Table (md and up) -->
<div class="card d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>نام قانون</th>
                    <th>ماشه</th>
                    <th>اقدام</th>
                    <th class="text-center">اجراها</th>
                    <th class="text-center">وضعیت</th>
                    <th class="text-center">عملیات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rules as $r): ?>
            <tr>
                <td>
                    <div class="fw-semibold"><?php echo htmlspecialchars($r->name); ?></div>
                    <?php if (!empty($r->description)): ?>
                    <small class="text-muted"><?php echo htmlspecialchars(mb_substr($r->description, 0, 80)); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php $tInfo = $triggerTypes[$r->trigger_type] ?? null; ?>
                    <span class="badge bg-info bg-opacity-10 text-info"><?php echo $tInfo ? $tInfo['label'] : $r->trigger_type; ?></span>
                </td>
                <td>
                    <?php $aInfo = $actionTypes[$r->action_type] ?? null; ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary"><?php echo $aInfo ? $aInfo['label'] : $r->action_type; ?></span>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary"><?php echo $r->execution_count; ?></span>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch d-inline-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" role="switch" <?php echo $r->is_active ? 'checked' : ''; ?> onchange="toggleRule(<?php echo $r->id; ?>, this)">
                    </div>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <a href="<?php echo $config['url']; ?>/automation/edit/<?php echo $r->id; ?>" class="btn btn-outline-secondary" title="ویرایش"><i class="bi bi-pencil"></i></a>
                        <button type="button" class="btn btn-outline-danger" title="حذف" onclick="if(confirm('حذف شود؟')){document.getElementById('del-form-<?php echo $r->id; ?>').submit();}"><i class="bi bi-trash"></i></button>
                    </div>
                    <form id="del-form-<?php echo $r->id; ?>" method="POST" action="<?php echo $config['url']; ?>/automation/delete/<?php echo $r->id; ?>" class="d-none"></form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Cards (below md) -->
<div class="d-md-none">
    <?php foreach ($rules as $r): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <!-- Header: name + toggle -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="me-2">
                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($r->name); ?></h6>
                    <?php if (!empty($r->description)): ?>
                    <small class="text-muted"><?php echo htmlspecialchars(mb_substr($r->description, 0, 80)); ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-check form-switch flex-shrink-0">
                    <input class="form-check-input" type="checkbox" role="switch" <?php echo $r->is_active ? 'checked' : ''; ?> onchange="toggleRule(<?php echo $r->id; ?>, this)">
                </div>
            </div>

            <!-- Trigger & Action badges -->
            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php $tInfo = $triggerTypes[$r->trigger_type] ?? null; ?>
                <span class="badge bg-info bg-opacity-10 text-info"><?php echo $tInfo ? $tInfo['label'] : $r->trigger_type; ?></span>
                <i class="bi bi-arrow-left text-muted align-self-center"></i>
                <?php $aInfo = $actionTypes[$r->action_type] ?? null; ?>
                <span class="badge bg-primary bg-opacity-10 text-primary"><?php echo $aInfo ? $aInfo['label'] : $r->action_type; ?></span>
            </div>

            <!-- Footer: executions + actions -->
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-bar-chart me-1"></i><?php echo $r->execution_count; ?> بار اجرا</small>
                <div class="btn-group btn-group-sm">
                    <a href="<?php echo $config['url']; ?>/automation/edit/<?php echo $r->id; ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    <button type="button" class="btn btn-outline-danger" onclick="if(confirm('حذف شود؟')){document.getElementById('del-form-m-<?php echo $r->id; ?>').submit();}"><i class="bi bi-trash"></i></button>
                </div>
                <form id="del-form-m-<?php echo $r->id; ?>" method="POST" action="<?php echo $config['url']; ?>/automation/delete/<?php echo $r->id; ?>" class="d-none"></form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<script>
function toggleRule(id, el) {
    fetch('<?php echo $config['url']; ?>/automation/toggle/' + id, {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(d){ if(!d.success) el.checked = !el.checked; })
        .catch(function(){ el.checked = !el.checked; });
}
</script>