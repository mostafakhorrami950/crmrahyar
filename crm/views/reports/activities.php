<div class="filter-section">
    <form method="GET" class="row g-3">
        <div class="col-md-3"><label class="form-label text-muted small fw-medium">از تاریخ</label><input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>"></div>
        <div class="col-md-3"><label class="form-label text-muted small fw-medium">تا تاریخ</label><input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>"></div>
        <div class="col-md-3"><label class="form-label text-muted small fw-medium">کاربر</label><select name="user_id" class="form-select"><option value="">همه</option><?php foreach ($users as $u): ?><option value="<?php echo $u->id; ?>" <?php echo $selectedUser == $u->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><label class="form-label text-muted small fw-medium">&nbsp;</label><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> فیلتر</button></div>
    </form>
</div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-0">
            <h6 style="font-weight:bold;margin-bottom:15px;">خلاصه فعالیت‌ها</h6>
            <?php foreach ($activitySummary as $as): ?>
            <div class="d-flex justify-content-between mb-2 pb-2" style="border-bottom:1px solid #eee;">
                <span><?php echo $as->type == 'call' ? 'تماس' : ($as->type == 'meeting' ? 'جلسه' : ($as->type == 'sms' ? 'پیامک' : 'یادداشت')); ?></span>
                <span class="badge bg-info"><?php echo $as->count; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-0">
            <h6 style="font-weight:bold;margin-bottom:15px;">لیست فعالیت‌ها</h6>
            <div class="table-responsive"><table class="table table-sm">
                <thead><tr><th class="text-nowrap">کاربر</th><th class="text-nowrap">نوع</th><th class="text-nowrap">موضوع</th><th class="text-nowrap">معامله</th><th class="text-nowrap">تاریخ</th></tr></thead>
                <tbody><?php foreach ($activities as $a): ?><tr><td><?php echo htmlspecialchars($a->user_name ?? '-'); ?></td><td><?php echo $a->type; ?></td><td><?php echo htmlspecialchars($a->subject ?? '-'); ?></td><td><?php echo htmlspecialchars($a->deal_title ?? '-'); ?></td><td><small><?php echo \Core\JDate::displayDate($a->created_at); ?></small></td></tr><?php endforeach; ?></tbody>
            </table></div>
        </div>
    </div>
</div>