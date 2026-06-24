<?php $config = $GLOBALS['app_config']; ?>

<!-- Welcome + Notifications -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold mb-1"><i class="bi bi-speedometer2 me-2 text-primary"></i><?php echo $isAdmin ? 'داشبورد مدیریت' : 'داشبورد من'; ?></h5>
        <p class="text-muted small mb-0">خوش آمدید، <?php echo htmlspecialchars(\Core\Auth::user()->full_name); ?></p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm position-relative" data-bs-toggle="dropdown" id="notifBtn">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" id="notifBadge" style="font-size:10px;<?php echo ($unreadNotifs->count ?? 0) > 0 ? '' : 'display:none'; ?>"><?php echo $unreadNotifs->count ?? 0; ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0" style="width:350px;max-height:400px;overflow-y:auto;" id="notifDropdown">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                    <strong class="small">اعلان‌ها</strong>
                    <button class="btn btn-link text-decoration-none p-0 small" onclick="markAllNotifsRead()">خواندن همه</button>
                </div>
                <div id="notifList" class="text-center py-3">
                    <span class="spinner-border spinner-border-sm text-muted"></span>
                </div>
            </div>
        </div>
        <?php if ($isAdmin): ?>
        <button class="btn btn-primary btn-sm" onclick="new bootstrap.Modal(document.getElementById('addNoteModal')).show()"><i class="bi bi-sticky me-1"></i>یادداشت</button>
        <?php endif; ?>
    </div>
</div>

<!-- Admin Notes for User -->
<?php if (!empty($adminNotes)): ?>
<div class="alert alert-warning border-0 shadow-sm mb-4 d-flex align-items-start gap-3">
    <i class="bi bi-pin-angle fs-4 text-warning flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong class="d-block mb-2">یادداشت‌های مدیر</strong>
        <?php foreach ($adminNotes as $note): ?>
        <div class="bg-white rounded-3 p-2 mb-2 d-flex justify-content-between align-items-start">
            <div>
                <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($note->note)); ?></p>
                <small class="text-muted"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($note->author_name ?? 'مدیر'); ?> - <?php echo \Core\JDate::displayDate($note->created_at); ?></small>
            </div>
            <?php if ($isAdmin): ?>
            <button class="btn btn-sm text-danger p-0 ms-2" onclick="deleteNote(<?php echo $note->id; ?>)" title="حذف"><i class="bi bi-x-lg"></i></button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-right:4px solid #3B82F6!important;">
            <i class="bi bi-briefcase text-primary fs-4"></i>
            <div class="fw-bold fs-5 mt-1"><?php echo $totalDeals->count; ?></div>
            <small class="text-muted"><?php echo $isAdmin ? 'کل معاملات' : 'معاملات من'; ?></small>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-right:4px solid #8B5CF6!important;">
            <i class="bi bi-cash-stack text-purple fs-4" style="color:#8B5CF6;"></i>
            <div class="fw-bold mt-1" style="font-size:14px;color:#8B5CF6;"><?php echo number_format($totalDeals->total); ?></div>
            <small class="text-muted">ارزش کل</small>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-right:4px solid #10B981!important;">
            <i class="bi bi-trophy text-success fs-4"></i>
            <div class="fw-bold fs-5 mt-1 text-success"><?php echo $wonDeals->count; ?></div>
            <small class="text-muted">موفق</small>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-right:4px solid #EF4444!important;">
            <i class="bi bi-x-circle text-danger fs-4"></i>
            <div class="fw-bold fs-5 mt-1 text-danger"><?php echo $lostDeals->count; ?></div>
            <small class="text-muted">ناموفق</small>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-right:4px solid #F59E0B!important;">
            <i class="bi bi-people text-warning fs-4"></i>
            <div class="fw-bold fs-5 mt-1"><?php echo $totalContacts->count; ?></div>
            <small class="text-muted"><?php echo $isAdmin ? 'مخاطبین' : 'مخاطبین من'; ?></small>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-right:4px solid #6366F1!important;">
            <i class="bi bi-check-circle text-primary fs-4"></i>
            <div class="fw-bold mt-1" style="font-size:14px;color:#10B981;"><?php echo number_format($wonDeals->total); ?></div>
            <small class="text-muted">درآمد موفق</small>
        </div>
    </div>
</div>

<!-- Pipeline Chart + Follow-ups -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2 text-primary"></i>وضعیت مراحل</h6>
                <?php if (!empty($dealsByStage)): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($dealsByStage as $stage): ?>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted" style="width:80px;font-size:11px;"><?php echo htmlspecialchars($stage->name); ?></small>
                        <div class="flex-grow-1 bg-light rounded-pill" style="height:24px;overflow:hidden;">
                            <div class="rounded-pill d-flex align-items-center px-2" style="width:<?php echo max(5, min(100, ($stage->count / max($totalDeals->count, 1)) * 100)); ?>%;height:100%;background:linear-gradient(90deg,<?php echo $stage->color; ?>,<?php echo $stage->color; ?>88);min-width:<?php echo $stage->count > 0 ? '40' : '0'; ?>px;">
                                <small class="text-white fw-bold" style="font-size:11px;"><?php echo $stage->count; ?></small>
                            </div>
                        </div>
                        <small class="text-muted" style="font-size:11px;width:70px;text-align:left;"><?php echo number_format($stage->total); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-3">هنوز معامله‌ای ثبت نشده</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-alarm me-2 text-danger"></i>پیگیری‌های آتی</h6>
                <?php if (!empty($upcomingFollowUps)): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($upcomingFollowUps as $fu): ?>
                    <div class="bg-light rounded-3 p-2 d-flex align-items-start gap-2">
                        <i class="bi bi-clock text-warning mt-1"></i>
                        <div>
                            <small class="fw-bold d-block"><?php echo htmlspecialchars(mb_substr($fu->deal_title, 0, 25)); ?></small>
                            <small class="text-muted"><?php echo \Core\JDate::displayDateTime($fu->reminder_at); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-3">پیگیری فعالی وجود ندارد</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Deals + Activities -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-briefcase me-2 text-primary"></i><?php echo $isAdmin ? 'آخرین معاملات' : 'معاملات من'; ?></h6>
                    <a href="<?php echo $config['url']; ?>/deals" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left me-1"></i>همه</a>
                </div>
                <?php if (!empty($recentDeals)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr><th class="small fw-bold">عنوان</th><th class="small fw-bold d-none d-md-table-cell">مخاطب</th><th class="small fw-bold">مبلغ</th><th class="small fw-bold">وضعیت</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentDeals as $deal): ?>
                        <tr>
                            <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="text-decoration-none fw-medium"><?php echo htmlspecialchars(mb_substr($deal->title, 0, 25)); ?></a></td>
                            <td class="d-none d-md-table-cell"><small class="text-muted"><?php echo htmlspecialchars($deal->contact_name ?? '-'); ?></small></td>
                            <td class="amount-value" style="font-size:13px;"><?php echo number_format($deal->amount); ?></td>
                            <td>
                                <?php if ($deal->is_won): ?><span class="badge bg-success bg-opacity-10 text-success small">✅ موفق</span>
                                <?php elseif ($deal->is_lost): ?><span class="badge bg-danger bg-opacity-10 text-danger small">❌ ناموفق</span>
                                <?php else: ?><span class="badge bg-primary bg-opacity-10 text-primary small"><?php echo htmlspecialchars($deal->stage_name); ?></span><?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                    <p>معامله‌ای ثبت نشده</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-activity me-2 text-success"></i>فعالیت‌های اخیر</h6>
                <?php if (!empty($recentActivities)): ?>
                <div class="d-flex flex-column gap-2" style="max-height:300px;overflow-y:auto;">
                    <?php foreach ($recentActivities as $act): ?>
                    <div class="d-flex align-items-start gap-2 py-2 border-bottom">
                        <i class="bi bi-flag text-primary mt-1"></i>
                        <div>
                            <div class="small"><?php echo htmlspecialchars($act->description); ?></div>
                            <small class="text-muted"><?php echo \Core\JDate::displayDate($act->created_at); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-calendar-check fs-1 d-block mb-2 opacity-25"></i>
                    <p>فعالیتی ثبت نشده</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Admin: Add Note Modal -->
<?php if ($isAdmin): ?>
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-sticky me-2"></i>افزودن یادداشت برای کاربر</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm" onsubmit="submitNote(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">کاربر</label>
                        <select name="target_user_id" class="form-select" required>
                            <option value="">انتخاب کاربر</option>
                            <?php $allUsers = \Core\Database::getInstance()->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name"); ?>
                            <?php foreach ($allUsers as $u): ?>
                            <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">یادداشت</label>
                        <textarea name="note" class="form-control" rows="3" required placeholder="متن یادداشت..."></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_pinned" value="1" class="form-check-input" id="pinNote">
                        <label class="form-check-label" for="pinNote">پین شده (همیشه نمایش داده شود)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
var baseUrl = '<?php echo $config['url']; ?>';

// Load notifications on dropdown open
document.getElementById('notifBtn')?.addEventListener('click', function() {
    loadNotifications();
});

function loadNotifications() {
    fetch(baseUrl + '/dashboard/notifications')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var list = document.getElementById('notifList');
        if (data.success && data.notifications && data.notifications.length > 0) {
            var html = '';
            data.notifications.forEach(function(n) {
                html += '<div class="px-3 py-2 border-bottom notif-item ' + (n.is_read == 0 ? 'bg-light' : '') + '" data-id="' + n.id + '">';
                html += '<div class="d-flex justify-content-between align-items-start">';
                html += '<div><strong class="small d-block">' + (n.title||'') + '</strong>';
                html += '<small class="text-muted">' + (n.message||'') + '</small></div>';
                if (n.is_read == 0) html += '<span class="badge bg-primary rounded-pill" style="width:8px;height:8px;padding:0;"></span>';
                html += '</div>';
                if (n.link) html += '<a href="' + n.link + '" class="small text-primary" onclick="markNotifRead(' + n.id + ')">مشاهده</a>';
                html += '</div>';
            });
            list.innerHTML = html;
        } else {
            list.innerHTML = '<p class="text-muted small py-3 mb-0">اعلانی وجود ندارد</p>';
        }
        // Update badge
        var badge = document.getElementById('notifBadge');
        if (data.unread_count > 0) {
            badge.textContent = data.unread_count;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    })
    .catch(function() {
        document.getElementById('notifList').innerHTML = '<p class="text-danger small py-3 mb-0">خطا در بارگذاری</p>';
    });
}

function markNotifRead(id) {
    fetch(baseUrl + '/dashboard/notification/read', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    }).then(function() {
        var badge = document.getElementById('notifBadge');
        var count = parseInt(badge.textContent) - 1;
        if (count > 0) { badge.textContent = count; } else { badge.style.display = 'none'; }
    });
}

function markAllNotifsRead() {
    fetch(baseUrl + '/dashboard/notification/read-all', { method: 'POST' })
    .then(function() {
        document.getElementById('notifBadge').style.display = 'none';
        document.getElementById('notifBadge').textContent = '0';
        loadNotifications();
    });
}

function submitNote(e) {
    e.preventDefault();
    var form = document.getElementById('addNoteForm');
    var formData = new FormData(form);
    fetch(baseUrl + '/dashboard/add-note', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'خطا');
        }
    });
}

function deleteNote(id) {
    if (!confirm('آیا از حذف این یادداشت اطمینان دارید؟')) return;
    fetch(baseUrl + '/dashboard/delete-note', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'note_id=' + id
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) location.reload();
    });
}

// Auto-check notifications every 60 seconds
setInterval(function() {
    fetch(baseUrl + '/dashboard/notifications')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var badge = document.getElementById('notifBadge');
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count;
                badge.style.display = '';
                // Flash effect
                badge.classList.add('bg-warning');
                setTimeout(function() { badge.classList.remove('bg-warning'); badge.classList.add('bg-danger'); }, 2000);
            }
        }
    });
}, 60000);
</script>