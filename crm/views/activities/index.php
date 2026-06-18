<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>📅 مدیریت فعالیت‌ها</h5>
    <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-primary btn-sm">➕ فعالیت جدید</a>
</div>

<!-- Stats -->
<div class="stats-row" style="margin-bottom:16px;">
    <div class="stat-box" style="background:linear-gradient(135deg,#EF4444,#DC2626);">
        <div class="stat-value"><?php echo $overdueCount; ?></div>
        <div class="stat-label">⚠️ سررسید گذشته</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
        <div class="stat-value"><?php echo $todayCount; ?></div>
        <div class="stat-label">📌 امروز</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#10B981,#059669);">
        <div class="stat-value"><?php echo $doneTodayCount; ?></div>
        <div class="stat-label">✅ انجام شده امروز</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#3B82F6,#2563EB);">
        <div class="stat-value"><?php echo $upcomingCount; ?></div>
        <div class="stat-label">📅 ۷ روز آینده</div>
    </div>
</div>

<!-- Summary by type -->
<?php if (!empty($activitySummary)): ?>
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;">
    <?php foreach ($activitySummary as $sum): ?>
    <span style="background:var(--gray-100);padding:6px 14px;border-radius:20px;font-size:13px;">
        <?php echo $sum->type == 'call' ? '📞' : ($sum->type == 'meeting' ? '🤝' : ($sum->type == 'sms' ? '✉️' : ($sum->type == 'email' ? '📧' : ($sum->type == 'follow_up' ? '📌' : '📝')))); ?>
        <?php echo $sum->type == 'call' ? 'تماس' : ($sum->type == 'meeting' ? 'جلسه' : ($sum->type == 'sms' ? 'پیامک' : ($sum->type == 'email' ? 'ایمیل' : ($sum->type == 'follow_up' ? 'پیگیری' : 'یادداشت')))); ?>
        <strong>(<?php echo $sum->count; ?>)</strong>
    </span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card" style="padding:16px;margin-bottom:16px;">
    <form method="GET" action="<?php echo $config['url']; ?>/activities" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="date" name="date_from" class="form-input" value="<?php echo $dateFrom; ?>" style="flex:1;min-width:140px;" title="از تاریخ">
        <input type="date" name="date_to" class="form-input" value="<?php echo $dateTo; ?>" style="flex:1;min-width:140px;" title="تا تاریخ">
        <select name="user_id" class="form-input" style="flex:1;min-width:140px;">
            <option value="">همه کاربران</option>
            <?php foreach ($users as $u): ?>
            <option value="<?php echo $u->id; ?>" <?php echo $selectedUser == $u->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="type" class="form-input" style="flex:1;min-width:140px;">
            <option value="">همه انواع</option>
            <option value="call" <?php echo $selectedType === 'call' ? 'selected' : ''; ?>>📞 تماس</option>
            <option value="meeting" <?php echo $selectedType === 'meeting' ? 'selected' : ''; ?>>🤝 جلسه</option>
            <option value="follow_up" <?php echo $selectedType === 'follow_up' ? 'selected' : ''; ?>>📌 پیگیری</option>
            <option value="email" <?php echo $selectedType === 'email' ? 'selected' : ''; ?>>📧 ایمیل</option>
            <option value="sms" <?php echo $selectedType === 'sms' ? 'selected' : ''; ?>>✉️ پیامک</option>
            <option value="note" <?php echo $selectedType === 'note' ? 'selected' : ''; ?>>📝 یادداشت</option>
        </select>
        <select name="status" class="form-input" style="flex:1;min-width:140px;">
            <option value="">همه وضعیت‌ها</option>
            <option value="pending" <?php echo $selectedStatus === 'pending' ? 'selected' : ''; ?>>⏳ انجام نشده</option>
            <option value="done" <?php echo $selectedStatus === 'done' ? 'selected' : ''; ?>>✅ انجام شده</option>
            <option value="overdue" <?php echo $selectedStatus === 'overdue' ? 'selected' : ''; ?>>⚠️ سررسید گذشته</option>
        </select>
        <button type="submit" class="btn btn-primary">🔍 فیلتر</button>
    </form>
</div>

<!-- Activities List -->
<div class="card" style="padding:0;">
    <?php if (empty($activities)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--gray-400);">
        <div style="font-size:64px;margin-bottom:16px;">📅</div>
        <h3 style="color:var(--gray-500);margin-bottom:8px;">هیچ فعالیتی یافت نشد</h3>
        <p>فیلترها را تغییر دهید یا فعالیت جدید ایجاد کنید</p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;">
        <?php 
        $currentDate = '';
        foreach ($activities as $act): 
            $actDate = \Core\JDate::displayDate($act->activity_date ?? $act->created_at);
            $isOverdue = !$act->is_done && $act->activity_date && strtotime($act->activity_date) < time();
            $isToday = date('Y-m-d', strtotime($act->activity_date ?? '')) === date('Y-m-d');
            
            if ($actDate !== $currentDate):
                $currentDate = $actDate;
        ?>
            <div style="padding:10px 20px;background:var(--gray-50);font-weight:700;font-size:14px;color:var(--gray-700);border-bottom:1px solid var(--gray-200);">
                📅 <?php echo $actDate; ?>
                <?php if ($isToday): ?><span style="color:var(--primary);font-size:12px;font-weight:600;">(امروز)</span><?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="activity-item" style="display:flex;align-items:flex-start;gap:14px;padding:14px 20px;border-bottom:1px solid var(--gray-100);<?php echo $act->is_done ? 'opacity:0.6;' : ''; ?><?php echo $isOverdue ? 'background:#fff5f5;' : ''; ?>">
            <!-- Toggle Done -->
            <form method="POST" action="<?php echo $config['url']; ?>/activities/toggle-done/<?php echo $act->id; ?>" data-ajax="true" style="flex-shrink:0;margin-top:4px;">
                <button type="submit" class="btn btn-sm <?php echo $act->is_done ? 'btn-success' : ($isOverdue ? 'btn-danger' : 'btn-secondary'); ?>" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;" title="<?php echo $act->is_done ? 'انجام شده' : 'انجام نشده'; ?>">
                    <?php echo $act->is_done ? '✅' : ($isOverdue ? '⚠️' : '⬜'); ?>
                </button>
            </form>
            
            <!-- Type Icon -->
            <div style="width:40px;height:40px;border-radius:10px;background:<?php echo $act->type == 'call' ? '#e3f2fd' : ($act->type == 'meeting' ? '#fce4ec' : ($act->type == 'follow_up' ? '#e8f5e9' : ($act->type == 'email' ? '#fff3e0' : '#f3e5f5'))); ?>;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                <?php echo $act->type == 'call' ? '📞' : ($act->type == 'meeting' ? '🤝' : ($act->type == 'follow_up' ? '📌' : ($act->type == 'email' ? '📧' : ($act->type == 'sms' ? '✉️' : '📝')))); ?>
            </div>
            
            <!-- Content -->
            <div style="flex:1;min-width:0;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;flex-wrap:wrap;">
                    <div>
                        <strong style="font-size:14px;<?php echo $act->is_done ? 'text-decoration:line-through;' : ''; ?>"><?php echo htmlspecialchars($act->subject ?? '-'); ?></strong>
                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:4px;">
                            <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:var(--gray-100);color:var(--gray-600);">
                                <?php echo $act->type == 'call' ? '📞 تماس' : ($act->type == 'meeting' ? '🤝 جلسه' : ($act->type == 'follow_up' ? '📌 پیگیری' : ($act->type == 'email' ? '📧 ایمیل' : ($act->type == 'sms' ? '✉️ پیامک' : '📝 یادداشت')))); ?>
                            </span>
                            <?php if ($act->user_name): ?>
                            <span style="font-size:11px;color:var(--gray-400);">👤 <?php echo htmlspecialchars($act->user_name); ?></span>
                            <?php endif; ?>
                            <?php if ($act->activity_date): ?>
                            <span style="font-size:11px;color:<?php echo $isOverdue ? '#EF4444' : 'var(--gray-400)'; ?>;">⏰ <?php echo \Core\JDate::displayDateTime($act->activity_date); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Deal + Contact info -->
                <div style="display:flex;gap:12px;margin-top:8px;flex-wrap:wrap;">
                    <?php if ($act->deal_id): ?>
                    <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $act->deal_id; ?>" style="display:flex;align-items:center;gap:4px;font-size:12px;color:var(--primary);background:#eef2ff;padding:4px 10px;border-radius:8px;text-decoration:none;font-weight:600;">
                        💼 <?php echo htmlspecialchars(mb_substr($act->deal_title ?? '-', 0, 30)); ?> 🔗
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($act->stage_name)): ?>
                    <span style="font-size:11px;background:<?php echo $act->stage_color; ?>20;color:<?php echo $act->stage_color; ?>;padding:4px 10px;border-radius:8px;">
                        <?php echo htmlspecialchars($act->stage_name); ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($act->contact_name)): ?>
                    <span style="font-size:12px;color:var(--gray-500);">
                        👤 <?php echo htmlspecialchars($act->contact_name); ?>
                        <?php if (!empty($act->contact_phone)): ?>
                        <span dir="ltr">(<?php echo htmlspecialchars($act->contact_phone); ?>)</span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($act->description)): ?>
                <div style="margin-top:6px;font-size:12px;color:var(--gray-500);line-height:1.6;"><?php echo nl2br(htmlspecialchars(mb_substr($act->description, 0, 200))); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:10px; }
.stat-box { color:white; padding:16px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.stat-value { font-weight:800; font-size:28px; }
.stat-label { font-size:12px; opacity:0.9; }
.activity-item:hover { background:var(--gray-50); }
.btn { border:none; cursor:pointer; font-weight:600; }
.btn-sm { padding:6px 10px; font-size:12px; border-radius:8px; }
.btn-success { background:#d1fae5; color:#065f46; }
.btn-secondary { background:var(--gray-200); color:var(--gray-700); }
.btn-danger { background:#fee2e2; color:#991b1b; }
</style>