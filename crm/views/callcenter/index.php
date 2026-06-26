<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-headset me-2"></i>مدیریت کال سنتر</h5>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/callcenter/lines" class="btn btn-sm btn-outline-secondary"><i class="bi bi-telephone me-1"></i>مدیریت خطوط</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal"><i class="bi bi-plus-circle me-1"></i>شیفت جدید</button>
    </div>
</div>

<!-- Current Active Assignments -->
<div class="card mb-4">
    <div class="card-header bg-success bg-opacity-10">
        <h6 class="mb-0 text-success"><i class="bi bi-broadcast me-1"></i>شیفت فعال (الان)</h6>
    </div>
    <div class="card-body">
        <?php if (empty($currentAssignments)): ?>
        <p class="text-muted mb-0">هیچ شیفت فعالی در حال حاضر وجود ندارد.</p>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($currentAssignments as $ca): ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="border rounded-3 p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="fw-bold"><i class="bi bi-telephone-fill text-primary me-1"></i><?php echo htmlspecialchars($ca->line_name); ?></span>
                        <span class="badge bg-success">فعال</span>
                    </div>
                    <div class="small text-muted mb-1"><?php echo htmlspecialchars($ca->phone_number); ?></div>
                    <div class="fw-semibold"><i class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($ca->user_name); ?></div>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-clock me-1"></i>
                        <?php echo \Core\JDate::displayDateTime($ca->shift_start); ?> تا <?php echo \Core\JDate::displayDateTime($ca->shift_end); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Today's Shifts -->
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-calendar-event me-1"></i>شیفت‌های امروز</h6></div>
    <div class="card-body p-0">
        <?php if (empty($todayShifts)): ?>
        <p class="text-muted text-center py-4 mb-0">شیفتی برای امروز تعریف نشده</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>خط تلفن</th><th>کاربر</th><th>شروع</th><th>پایان</th><th>وضعیت</th><th class="text-center">عملیات</th></tr></thead>
                <tbody>
                <?php foreach ($todayShifts as $ts): ?>
                <tr>
                    <td><i class="bi bi-telephone text-primary me-1"></i><?php echo htmlspecialchars($ts->line_name); ?></td>
                    <td class="fw-semibold"><?php echo htmlspecialchars($ts->user_name); ?></td>
                    <td class="small"><?php echo \Core\JDate::displayDateTime($ts->shift_start); ?></td>
                    <td class="small"><?php echo \Core\JDate::displayDateTime($ts->shift_end); ?></td>
                    <td>
                        <?php if ($ts->status === 'active'): ?>
                        <span class="badge bg-success">فعال</span>
                        <?php elseif ($ts->status === 'cancelled'): ?>
                        <span class="badge bg-secondary">لغو شده</span>
                        <?php else: ?>
                        <span class="badge bg-info">تکمیل</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($ts->status === 'active'): ?>
                        <form method="POST" action="<?php echo $config['url']; ?>/callcenter/cancel/<?php echo $ts->id; ?>" style="display:inline;" onsubmit="return confirm('لغو شود؟')">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Upcoming Shifts -->
<?php if (!empty($upcomingShifts)): ?>
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-calendar3 me-1"></i>شیفت‌های آینده</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>خط تلفن</th><th>کاربر</th><th>شروع</th><th>پایان</th><th class="text-center">عملیات</th></tr></thead>
                <tbody>
                <?php foreach ($upcomingShifts as $us): ?>
                <tr>
                    <td><i class="bi bi-telephone text-primary me-1"></i><?php echo htmlspecialchars($us->line_name); ?></td>
                    <td class="fw-semibold"><?php echo htmlspecialchars($us->user_name); ?></td>
                    <td class="small"><?php echo \Core\JDate::displayDateTime($us->shift_start); ?></td>
                    <td class="small"><?php echo \Core\JDate::displayDateTime($us->shift_end); ?></td>
                    <td class="text-center">
                        <form method="POST" action="<?php echo $config['url']; ?>/callcenter/cancel/<?php echo $us->id; ?>" style="display:inline;" onsubmit="return confirm('لغو شود؟')">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo $config['url']; ?>/callcenter/assign">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-plus-circle me-1"></i>ثبت شیفت جدید</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">خط تلفن *</label>
                        <select name="phone_line_id" class="form-select" required>
                            <option value="">انتخاب کنید</option>
                            <?php foreach ($phoneLines as $pl): ?>
                            <option value="<?php echo $pl->id; ?>"><?php echo htmlspecialchars($pl->name . ' - ' . $pl->phone_number); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">کاربر *</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">انتخاب کنید</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">شروع شیفت *</label>
                            <input type="datetime-local" name="shift_start" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">پایان شیفت *</label>
                            <input type="datetime-local" name="shift_end" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">توضیحات</label>
                        <input type="text" name="notes" class="form-control" placeholder="اختیاری">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ثبت</button>
                </div>
            </form>
        </div>
    </div>
</div>