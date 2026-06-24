<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-envelope me-2 text-primary"></i>تاریخچه پیامک‌ها</h5>
    <a href="<?php echo $config['url']; ?>/sms/send/0" class="btn btn-primary btn-sm"><i class="bi bi-send me-1"></i>ارسال انبوه</a>
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center p-3" style="border-right:4px solid #3B82F6!important;">
            <div class="fw-bold fs-4 text-primary"><?php echo number_format($stats['total']); ?></div>
            <small class="text-muted">کل پیامک‌ها</small>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center p-3" style="border-right:4px solid #10B981!important;">
            <div class="fw-bold fs-4 text-success"><?php echo number_format($stats['sent']); ?></div>
            <small class="text-muted">ارسال موفق</small>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center p-3" style="border-right:4px solid #EF4444!important;">
            <div class="fw-bold fs-4 text-danger"><?php echo number_format($stats['failed']); ?></div>
            <small class="text-muted">ناموفق</small>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="🔍 جستجو: نام، شماره، متن، معامله..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="sent" <?php echo $selectedStatus === 'sent' ? 'selected' : ''; ?>>✅ موفق</option>
                        <option value="failed" <?php echo $selectedStatus === 'failed' ? 'selected' : ''; ?>>❌ ناموفق</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="bi bi-search me-1"></i>فیلتر</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- SMS Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>مخاطب</th>
                    <th>شماره</th>
                    <th>متن پیامک</th>
                    <th class="d-none d-md-table-cell">معامله</th>
                    <th class="d-none d-lg-table-cell">ارسال‌کننده</th>
                    <th>وضعیت</th>
                    <th class="d-none d-lg-table-cell">تاریخ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr><td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-envelope fs-1 d-block mb-2 opacity-25"></i>
                    هیچ پیامکی ثبت نشده است.
                </td></tr>
                <?php else: $counter = 1; ?>
                <?php foreach ($messages as $msg): ?>
                <tr>
                    <td class="text-muted small"><?php echo $counter++; ?></td>
                    <td>
                        <?php if (!empty($msg->contact_name)): ?>
                        <strong class="small"><?php echo htmlspecialchars($msg->contact_name); ?></strong>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td dir="ltr" class="text-start small"><?php echo htmlspecialchars($msg->recipient ?? $msg->contact_phone ?? ''); ?></td>
                    <td>
                        <div class="small" style="max-width:250px;cursor:pointer;" onclick="this.querySelector('.sms-preview').classList.toggle('d-none');this.querySelector('.sms-full').classList.toggle('d-none');" title="کلیک برای نمایش کامل">
                            <span class="sms-preview"><?php echo htmlspecialchars(mb_substr($msg->message, 0, 80)); ?><?php echo mb_strlen($msg->message) > 80 ? '...' : ''; ?></span>
                            <span class="sms-full d-none"><?php echo htmlspecialchars($msg->message); ?></span>
                        </div>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <?php if ($msg->deal_id): ?>
                        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $msg->deal_id; ?>" class="text-primary text-decoration-none small fw-semibold">
                            <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars(mb_substr($msg->deal_title ?? '-', 0, 20)); ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-lg-table-cell"><small class="text-muted"><?php echo htmlspecialchars($msg->sender_name ?? '-'); ?></small></td>
                    <td>
                        <?php if ($msg->status == 'sent'): ?>
                        <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle me-1"></i>موفق</span>
                        <?php else: ?>
                        <span class="badge bg-danger bg-opacity-10 text-danger" title="<?php echo htmlspecialchars($msg->error_message ?? ''); ?>"><i class="bi bi-x-circle me-1"></i>ناموفق</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-lg-table-cell"><small class="text-muted"><?php echo \Core\JDate::displayDateTime($msg->created_at); ?></small></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if (($totalPages ?? 1) > 1): ?>
<nav class="d-flex justify-content-center align-items-center gap-2 mt-3 flex-wrap">
    <?php if ($page > 1): ?>
    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
    <?php endif; ?>
    
    <?php 
    $startPage = max(1, $page - 3);
    $endPage = min($totalPages, $page + 3);
    if ($startPage > 1): ?>
        <a href="?page=1&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm btn-outline-secondary">1</a>
        <?php if ($startPage > 2): ?><span class="text-muted small">...</span><?php endif; ?>
    <?php endif; ?>
    
    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-outline-secondary'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    
    <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?><span class="text-muted small">...</span><?php endif; ?>
        <a href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm btn-outline-secondary"><?php echo $totalPages; ?></a>
    <?php endif; ?>
    
    <?php if ($page < $totalPages): ?>
    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
    <?php endif; ?>
    
    <span class="text-muted small ms-2">صفحه <?php echo $page; ?> از <?php echo $totalPages; ?> (<?php echo number_format($totalRecords ?? 0); ?> رکورد)</span>
</nav>
<?php endif; ?>