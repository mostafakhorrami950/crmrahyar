<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>لاگ تغییرات</h5>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-auto">
                <label class="form-label text-muted small">نوع</label>
                <select name="entity_type" class="form-select form-select-sm">
                    <option value="">همه</option>
                    <option value="contact" <?php echo $selectedEntityType === 'contact' ? 'selected' : ''; ?>>مخاطب</option>
                    <option value="deal" <?php echo $selectedEntityType === 'deal' ? 'selected' : ''; ?>>معامله</option>
                </select>
            </div>
            <div class="col-12 col-sm-auto">
                <label class="form-label text-muted small">کاربر</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">همه کاربران</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>" <?php echo $selectedUserId == $u->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>فیلتر</button>
                <a href="<?php echo $config['url']; ?>/audit" class="btn btn-sm btn-outline-secondary">پاک کردن</a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>تاریخ</th>
                    <th>کاربر</th>
                    <th>نوع</th>
                    <th>عملیات</th>
                    <th>تغییرات</th>
                    <th class="text-center">بازگردانی</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">لاگی یافت نشد</td></tr>
                <?php endif; ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-nowrap small"><?php echo \Core\JDate::displayDateTime($log->created_at); ?></td>
                    <td><span class="fw-semibold"><?php echo htmlspecialchars($log->user_name ?? 'سیستم'); ?></span></td>
                    <td>
                        <?php
                        $typeLabels = ['contact' => 'مخاطب', 'deal' => 'معامله', 'pipeline' => 'پایپ لاین', 'user' => 'کاربر'];
                        $typeColors = ['contact' => 'info', 'deal' => 'primary', 'pipeline' => 'success', 'user' => 'warning'];
                        $typeIcons = ['contact' => 'person', 'deal' => 'briefcase', 'pipeline' => 'kanban', 'user' => 'person-gear'];
                        ?>
                        <span class="badge bg-<?php echo $typeColors[$log->entity_type] ?? 'secondary'; ?> bg-opacity-10 text-<?php echo $typeColors[$log->entity_type] ?? 'secondary'; ?>">
                            <i class="bi bi-<?php echo $typeIcons[$log->entity_type] ?? 'circle'; ?> me-1"></i>
                            <?php echo $typeLabels[$log->entity_type] ?? $log->entity_type; ?> #<?php echo $log->entity_id; ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $actionLabels = ['create' => 'ایجاد', 'update' => 'ویرایش', 'delete' => 'حذف', 'restore' => 'بازیابی'];
                        $actionColors = ['create' => 'success', 'update' => 'warning', 'delete' => 'danger', 'restore' => 'info'];
                        $actionIcons = ['create' => 'plus-circle', 'update' => 'pencil', 'delete' => 'trash', 'restore' => 'recycle'];
                        ?>
                        <span class="badge bg-<?php echo $actionColors[$log->action] ?? 'secondary'; ?>">
                            <i class="bi bi-<?php echo $actionIcons[$log->action] ?? 'circle'; ?> me-1"></i>
                            <?php echo $actionLabels[$log->action] ?? $log->action; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($log->action === 'update' && $log->changes): ?>
                        <?php $changes = json_decode($log->changes, true); ?>
                        <div class="small">
                            <?php foreach (array_slice($changes, 0, 3) as $field => $vals): ?>
                            <div>
                                <span class="text-muted"><?php echo \Core\AuditTrail::getFieldLabel($field); ?>:</span>
                                <del class="text-danger"><?php echo mb_substr((string)($vals['old'] ?? ''), 0, 30); ?></del>
                                → <span class="text-success"><?php echo mb_substr((string)($vals['new'] ?? ''), 0, 30); ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($changes) > 3): ?>
                            <small class="text-muted">+<?php echo count($changes) - 3; ?> مورد دیگر</small>
                            <?php endif; ?>
                        </div>
                        <?php elseif ($log->action === 'create'): ?>
                        <small class="text-muted">رکورد جدید ایجاد شد</small>
                        <?php elseif ($log->action === 'delete'): ?>
                        <small class="text-danger"><i class="bi bi-trash me-1"></i>رکورد حذف شد</small>
                        <?php if ($log->snapshot): ?>
                        <br><small class="text-success fw-bold"><i class="bi bi-recycle me-1"></i>قابل بازیابی</small>
                        <?php endif; ?>
                        <?php elseif ($log->action === 'restore'): ?>
                        <small class="text-info"><i class="bi bi-recycle me-1"></i>رکورد بازیابی شد</small>
                        <?php else: ?>
                        <small class="text-muted">—</small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($log->snapshot && in_array($log->action, ['delete', 'update'])): ?>
                        <form method="POST" action="<?php echo $config['url']; ?>/audit/rollback" style="display:inline;" onsubmit="return confirm('<?php echo $log->action === 'delete' ? 'آیا مطمئن هستید؟ این رکورد حذف شده بازیابی خواهد شد.' : 'آیا مطمئن هستید؟ داده‌ها به این نسخه بازمی‌گردند.'; ?>')">
                            <input type="hidden" name="log_id" value="<?php echo $log->id; ?>">
                            <input type="hidden" name="entity_type" value="<?php echo $log->entity_type; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $log->action === 'delete' ? 'btn-success' : 'btn-outline-warning'; ?>" title="<?php echo $log->action === 'delete' ? 'بازیابی رکورد حذف شده' : 'بازگردانی به این نسخه'; ?>">
                                <i class="bi bi-<?php echo $log->action === 'delete' ? 'recycle' : 'arrow-counterclockwise'; ?>"></i>
                                <?php if ($log->action === 'delete'): ?> بازیابی<?php endif; ?>
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>