<div class="page-header">
    <h5>📅 مدیریت فعالیت‌ها</h5>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="<?php echo $config['url']; ?>/activities" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="date" name="date_from" class="form-input" value="<?php echo $dateFrom; ?>" style="flex:1;">
        <input type="date" name="date_to" class="form-input" value="<?php echo $dateTo; ?>" style="flex:1;">
        <select name="user_id" class="form-input" style="flex:1;">
            <option value="">همه کاربران</option>
            <?php foreach ($users as $u): ?>
            <option value="<?php echo $u->id; ?>" <?php echo $selectedUser == $u->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">🔍 فیلتر</button>
    </form>
</div>

<!-- Activity Summary -->
<div class="stats-grid" style="margin-bottom:16px;">
    <?php foreach ($activitySummary as $sum): ?>
    <div class="stat-card" style="text-align:center;">
        <div class="stat-label" style="font-size:14px;">
            <?php echo $sum->type == 'call' ? '📞 تماس' : ($sum->type == 'meeting' ? '🤝 جلسه' : ($sum->type == 'sms' ? '✉️ پیامک' : ($sum->type == 'email' ? '📧 ایمیل' : ($sum->type == 'follow_up' ? '📌 پیگیری' : '📝 یادداشت')))); ?>
        </div>
        <div class="stat-value"><?php echo $sum->count; ?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card" style="padding:0;">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width:60px;">وضعیت</th>
                    <th>نوع</th>
                    <th>موضوع</th>
                    <th>معامله</th>
                    <th>کاربر</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                <tr><td colspan="7" class="text-center py-4" style="color:var(--gray-500);">هیچ فعالیتی یافت نشد.</td></tr>
                <?php else: ?>
                <?php foreach ($activities as $act): ?>
                <tr style="<?php echo $act->is_done ? 'opacity:0.6;' : ''; ?>">
                    <td>
                        <form method="POST" action="<?php echo $config['url']; ?>/activities/toggle-done/<?php echo $act->id; ?>" data-ajax="true">
                            <button type="submit" class="btn btn-sm <?php echo $act->is_done ? 'btn-success' : 'btn-secondary'; ?>" title="<?php echo $act->is_done ? 'انجام شده' : 'انجام نشده'; ?>">
                                <?php echo $act->is_done ? '✅' : '⬜'; ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <?php echo $act->type == 'call' ? '📞' : ($act->type == 'meeting' ? '🤝' : ($act->type == 'sms' ? '✉️' : ($act->type == 'email' ? '📧' : ($act->type == 'follow_up' ? '📌' : '📝')))); ?>
                        <?php echo $act->type == 'call' ? 'تماس' : ($act->type == 'meeting' ? 'جلسه' : ($act->type == 'sms' ? 'پیامک' : ($act->type == 'email' ? 'ایمیل' : ($act->type == 'follow_up' ? 'پیگیری' : 'یادداشت')))); ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($act->subject ?? '-'); ?></strong></td>
                    <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $act->deal_id; ?>" style="color:var(--primary);"><?php echo htmlspecialchars($act->deal_title ?? '-'); ?></a></td>
                    <td><?php echo htmlspecialchars($act->user_name ?? '-'); ?></td>
                    <td style="font-size:12px;color:var(--gray-500);"><?php echo date('Y/m/d H:i', strtotime($act->activity_date ?? $act->created_at)); ?></td>
                    <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $act->deal_id; ?>" class="btn btn-primary btn-sm">👁️</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>