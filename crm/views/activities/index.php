<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>مدیریت فعالیت‌ها</h5>
    <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>فعالیت جدید</a>
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="border-right:4px solid #EF4444!important;">
            <div class="fw-bold fs-4 text-danger"><?php echo $overdueCount; ?></div>
            <small class="text-muted"><i class="bi bi-exclamation-triangle me-1"></i>سررسید گذشته</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="border-right:4px solid #F59E0B!important;">
            <div class="fw-bold fs-4 text-warning"><?php echo $todayCount; ?></div>
            <small class="text-muted"><i class="bi bi-pin me-1"></i>امروز</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="border-right:4px solid #10B981!important;">
            <div class="fw-bold fs-4 text-success"><?php echo $doneTodayCount; ?></div>
            <small class="text-muted"><i class="bi bi-check-circle me-1"></i>انجام شده امروز</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="border-right:4px solid #3B82F6!important;">
            <div class="fw-bold fs-4 text-primary"><?php echo $upcomingCount; ?></div>
            <small class="text-muted"><i class="bi bi-calendar me-1"></i>۷ روز آینده</small>
        </div>
    </div>
</div>

<!-- Summary by type -->
<?php if (!empty($activitySummary)): ?>
<div class="d-flex gap-2 flex-wrap mb-3">
    <?php foreach ($activitySummary as $sum): 
        $typeIcons = ['call'=>'bi-telephone','meeting'=>'bi-people','sms'=>'bi-envelope','email'=>'bi-envelope-at','follow_up'=>'bi-pin','note'=>'bi-journal-text'];
        $typeNames = ['call'=>'تماس','meeting'=>'جلسه','sms'=>'پیامک','email'=>'ایمیل','follow_up'=>'پیگیری','note'=>'یادداشت'];
    ?>
    <span class="badge bg-light text-dark border px-3 py-2">
        <i class="bi <?php echo $typeIcons[$sum->type] ?? 'bi-circle'; ?> me-1"></i>
        <?php echo $typeNames[$sum->type] ?? $sum->type; ?>
        <strong>(<?php echo $sum->count; ?>)</strong>
    </span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?php echo $config['url']; ?>/activities">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small">از تاریخ</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $dateFrom; ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small">تا تاریخ</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $dateTo; ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small">کاربر</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">همه</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u->id; ?>" <?php echo $selectedUser == $u->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small">نوع</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">همه</option>
                        <option value="call" <?php echo $selectedType === 'call' ? 'selected' : ''; ?>>تماس</option>
                        <option value="meeting" <?php echo $selectedType === 'meeting' ? 'selected' : ''; ?>>جلسه</option>
                        <option value="follow_up" <?php echo $selectedType === 'follow_up' ? 'selected' : ''; ?>>پیگیری</option>
                        <option value="email" <?php echo $selectedType === 'email' ? 'selected' : ''; ?>>ایمیل</option>
                        <option value="sms" <?php echo $selectedType === 'sms' ? 'selected' : ''; ?>>پیامک</option>
                        <option value="note" <?php echo $selectedType === 'note' ? 'selected' : ''; ?>>یادداشت</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small">وضعیت</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">همه</option>
                        <option value="pending" <?php echo $selectedStatus === 'pending' ? 'selected' : ''; ?>>انجام نشده</option>
                        <option value="done" <?php echo $selectedStatus === 'done' ? 'selected' : ''; ?>>انجام شده</option>
                        <option value="overdue" <?php echo $selectedStatus === 'overdue' ? 'selected' : ''; ?>>سررسید گذشته</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="bi bi-search me-1"></i>فیلتر</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div id="bulkBar" class="alert alert-dark d-none align-items-center justify-content-between py-2 mb-2">
    <span id="bulkCount" class="text-white">۰ مورد انتخاب شده</span>
    <div class="d-flex gap-2">
        <button onclick="bulkDelete('activity_logs')" class="btn btn-danger btn-sm"><i class="bi bi-trash me-1"></i>حذف</button>
        <button onclick="clearSelection()" class="btn btn-secondary btn-sm"><i class="bi bi-x-lg me-1"></i>لغو</button>
    </div>
</div>

<!-- Activities List -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($activities)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-calendar-check"></i></div>
            <h5>هیچ فعالیتی یافت نشد</h5>
            <p>فیلترها را تغییر دهید یا فعالیت جدید ایجاد کنید</p>
        </div>
        <?php else: ?>
        <?php 
        $currentDate = '';
        foreach ($activities as $act): 
            $actDate = \Core\JDate::displayDate($act->activity_date ?? $act->created_at);
            $isOverdue = !$act->is_done && $act->activity_date && strtotime($act->activity_date) < time();
            $isToday = date('Y-m-d', strtotime($act->activity_date ?? '')) === date('Y-m-d');
            
            if ($actDate !== $currentDate):
                $currentDate = $actDate;
        ?>
            <div class="px-3 py-2 bg-light fw-bold small text-dark border-bottom">
                <i class="bi bi-calendar3 me-1"></i><?php echo $actDate; ?>
                <?php if ($isToday): ?><span class="badge bg-primary ms-2">امروز</span><?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="d-flex align-items-start gap-3 px-3 py-3 border-bottom <?php echo $act->is_done ? 'opacity-50' : ''; ?> <?php echo $isOverdue ? 'bg-danger bg-opacity-5' : ''; ?>" data-id="<?php echo $act->id; ?>">
            <!-- Checkbox + Toggle -->
            <div class="d-flex flex-column align-items-center gap-1 flex-shrink-0">
                <input type="checkbox" class="row-check form-check-input" value="<?php echo $act->id; ?>" onchange="updateBulkBar()">
                <form method="POST" action="<?php echo $config['url']; ?>/activities/toggle-done/<?php echo $act->id; ?>" data-ajax="true">
                    <button type="submit" class="btn btn-sm rounded-circle <?php echo $act->is_done ? 'btn-success' : ($isOverdue ? 'btn-danger' : 'btn-outline-secondary'); ?>" style="width:32px;height:32px;padding:0;" title="<?php echo $act->is_done ? 'انجام شده' : 'انجام نشده'; ?>">
                        <i class="bi <?php echo $act->is_done ? 'bi-check-lg' : ($isOverdue ? 'bi-exclamation' : 'bi-circle'); ?>"></i>
                    </button>
                </form>
            </div>
            
            <!-- Type Icon -->
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:<?php echo $act->type == 'call' ? '#e3f2fd' : ($act->type == 'meeting' ? '#fce4ec' : ($act->type == 'follow_up' ? '#e8f5e9' : ($act->type == 'email' ? '#fff3e0' : ($act->type == 'sms' ? '#e0f7fa' : '#f3e5f5')))); ?>;">
                <i class="bi <?php echo ($typeIcons[$act->type] ?? 'bi-journal-text') . ' fs-5'; ?>"></i>
            </div>
            
            <!-- Content -->
            <div class="flex-grow-1 min-width-0">
                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                    <div>
                        <strong class="<?php echo $act->is_done ? 'text-decoration-line-through' : ''; ?>" style="font-size:14px;"><?php echo htmlspecialchars($act->subject ?? '-'); ?></strong>
                        <div class="d-flex gap-2 align-items-center flex-wrap mt-1">
                            <span class="badge bg-light text-dark small"><?php echo $typeNames[$act->type] ?? $act->type; ?></span>
                            <?php if ($act->user_name): ?>
                            <small class="text-muted"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($act->user_name); ?></small>
                            <?php endif; ?>
                            <?php if ($act->activity_date): ?>
                            <small class="<?php echo $isOverdue ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                <i class="bi bi-clock me-1"></i><?php echo \Core\JDate::displayDateTime($act->activity_date); ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    <?php if ($act->deal_id): ?>
                    <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $act->deal_id; ?>" class="badge bg-primary bg-opacity-10 text-primary text-decoration-none px-2 py-1">
                        <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars(mb_substr($act->deal_title ?? '-', 0, 30)); ?>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($act->stage_name)): ?>
                    <span class="badge px-2 py-1" style="background:<?php echo $act->stage_color; ?>20;color:<?php echo $act->stage_color; ?>;"><?php echo htmlspecialchars($act->stage_name); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($act->contact_name)): ?>
                    <small class="text-muted"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($act->contact_name); ?>
                    <?php if (!empty($act->contact_phone)): ?><span dir="ltr">(<?php echo htmlspecialchars($act->contact_phone); ?>)</span><?php endif; ?>
                    </small>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($act->description)): ?>
                <div class="mt-1 small text-muted"><?php echo nl2br(htmlspecialchars(mb_substr($act->description, 0, 200))); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>