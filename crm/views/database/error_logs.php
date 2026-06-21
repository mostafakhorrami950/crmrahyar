<div class="page-header">
    <h5>⚠️ گزارش خطاها</h5>
    <a href="<?php echo $config['url']; ?>/database/repair" class="btn btn-secondary">🔧 تعمیر دیتابیس</a>
</div>

<!-- Bulk Actions Bar -->
<div id="bulkBar" style="display:none;position:sticky;top:0;z-index:100;background:#1e293b;color:#fff;padding:12px 16px;border-radius:12px;margin-bottom:12px;align-items:center;justify-content:space-between;">
    <span id="bulkCount">۰ مورد انتخاب شده</span>
    <div style="display:flex;gap:8px;">
        <button onclick="bulkDelete('activity_logs')" class="btn btn-danger btn-sm">🗑️ حذف انتخاب شده‌ها</button>
        <button onclick="clearSelection()" class="btn btn-secondary btn-sm" style="background:#475569;">✕ لغو انتخاب</button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">خطاهای سیستم</div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                    <th>#</th>
                    <th>کاربر</th>
                    <th>عملیات</th>
                    <th>نوع</th>
                    <th>توضیحات</th>
                    <th>IP</th>
                    <th>تاریخ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($errorLogs)): ?>
                <tr><td colspan="7" class="text-center py-4" style="color:var(--gray-500);">✅ هیچ خطایی ثبت نشده است.</td></tr>
                <?php else: ?>
                <?php foreach ($errorLogs as $log): ?>
                <tr data-id="<?php echo $log->id; ?>">
                    <td><input type="checkbox" class="row-check" value="<?php echo $log->id; ?>" onchange="updateBulkBar()"></td>
                    <td><?php echo $log->id; ?></td>
                    <td><?php echo htmlspecialchars($log->user_name ?? 'سیستم'); ?></td>
                    <td><span class="badge badge-danger"><?php echo htmlspecialchars($log->action); ?></span></td>
                    <td><?php echo htmlspecialchars($log->entity_type ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars(mb_substr($log->description ?? '', 0, 100)); ?></td>
                    <td><?php echo htmlspecialchars($log->ip_address ?? '-'); ?></td>
                    <td style="font-size:12px;"><?php echo $log->created_at; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">📊 آمار خطاها</div>
    <div class="stats-grid mt-8">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff3cd;color:#856404;">⚠️</div>
            <div class="stat-label">کل خطاها</div>
            <div class="stat-value"><?php echo $totalErrors ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f8d7da;color:#721c24;">❌</div>
            <div class="stat-label">خطاهای امروز</div>
            <div class="stat-value"><?php echo $todayErrors ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1ecf1;color:#0c5460;">👤</div>
            <div class="stat-label">کاربران خطا</div>
            <div class="stat-value"><?php echo $uniqueUsers ?? 0; ?></div>
        </div>
    </div>
</div>