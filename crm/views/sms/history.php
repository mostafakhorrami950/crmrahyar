<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0">📱 تاریخچه پیامک‌ها</h5>
    <div class="d-flex gap-8">
        <a href="<?php echo $config['url']; ?>/sms/send/0" class="btn btn-primary btn-sm">📤 ارسال انبوه</a>
    </div>
</div>

<div class="stats-row" style="margin-bottom:16px;">
    <div class="stat-card" style="background:linear-gradient(135deg,#3B82F6,#2563EB);">
        <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
        <div class="stat-label">کل پیامک‌ها</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#10B981,#059669);">
        <div class="stat-value"><?php echo number_format($stats['sent']); ?></div>
        <div class="stat-label">ارسال موفق</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#EF4444,#DC2626);">
        <div class="stat-value"><?php echo number_format($stats['failed']); ?></div>
        <div class="stat-label">ارسال ناموفق</div>
    </div>
</div>

<div class="card" style="padding:12px;">
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <input type="text" name="search" class="form-input" style="flex:2;min-width:200px;" placeholder="<i class="bi bi-search me-1"></i>جستجو: نام مخاطب، شماره، متن پیام، معامله..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="status" class="form-input" style="width:auto;">
            <option value="">همه وضعیت‌ها</option>
            <option value="sent" <?php echo $selectedStatus === 'sent' ? 'selected' : ''; ?>><i class="bi bi-check-circle text-success me-1"></i> موفق</option>
            <option value="failed" <?php echo $selectedStatus === 'failed' ? 'selected' : ''; ?>><i class="bi bi-x-circle text-danger me-1"></i> ناموفق</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i></button>
    </form>
</div>

<div class="card" style="padding:0;">
    <div class="table-responsive">
        <table class="table" id="smsTable">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th class="text-nowrap">مخاطب</th>
                    <th class="text-nowrap">شماره</th>
                    <th class="text-nowrap">متن پیامک</th>
                    <th class="text-nowrap">معامله</th>
                    <th class="text-nowrap">ارسال‌کننده</th>
                    <th class="text-nowrap">وضعیت</th>
                    <th class="text-nowrap">تاریخ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr><td colspan="8" class="text-center py-5">
                    <div style="font-size:48px;margin-bottom:10px;">📱</div>
                    <p style="color:var(--gray-500);">هیچ پیامکی ثبت نشده است.</p>
                </td></tr>
                <?php else: $counter = 1; ?>
                <?php foreach ($messages as $msg): ?>
                <tr>
                    <td data-label="#" style="color:var(--gray-400);font-size:12px;"><?php echo $counter++; ?></td>
                    <td data-label="مخاطب">
                        <?php if (!empty($msg->contact_name)): ?>
                        <strong><?php echo htmlspecialchars($msg->contact_name); ?></strong>
                        <?php else: ?>
                        <span style="color:var(--gray-400);">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="شماره" dir="ltr" style="text-align:left;font-size:13px;"><?php echo htmlspecialchars($msg->recipient ?? $msg->contact_phone ?? ''); ?></td>
                    <td data-label="متن پیامک">
                        <div class="sms-text" style="max-width:300px;font-size:13px;line-height:1.6;white-space:pre-wrap;word-wrap:break-word;cursor:pointer;" onclick="this.classList.toggle('expanded')" title="کلیک برای نمایش کامل">
                            <?php echo htmlspecialchars(mb_substr($msg->message, 0, 100)); ?>
                            <?php if (mb_strlen($msg->message) > 100): ?>
                            <span style="color:var(--primary);font-size:11px;">... (کلیک)</span>
                            <?php endif; ?>
                        </div>
                        <div class="sms-full" style="display:none;font-size:13px;line-height:1.6;white-space:pre-wrap;word-wrap:break-word;padding:8px;background:var(--gray-50);border-radius:8px;margin-top:4px;">
                            <?php echo htmlspecialchars($msg->message); ?>
                        </div>
                    </td>
                    <td data-label="معامله">
                        <?php if ($msg->deal_id): ?>
                        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $msg->deal_id; ?>" style="color:var(--primary);font-weight:600;text-decoration:none;font-size:13px;">
                            <?php echo htmlspecialchars(mb_substr($msg->deal_title ?? '-', 0, 20)); ?> 🔗
                        </a>
                        <?php else: ?>
                        <span style="color:var(--gray-300);font-size:12px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="ارسال‌کننده"><small style="color:var(--gray-500);"><?php echo htmlspecialchars($msg->sender_name ?? '-'); ?></small></td>
                    <td data-label="وضعیت">
                        <?php if ($msg->status == 'sent'): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle text-success me-1"></i> موفق</span>
                        <?php else: ?>
                        <span class="badge bg-danger" title="<?php echo htmlspecialchars($msg->error_message ?? ''); ?>"><i class="bi bi-x-circle text-danger me-1"></i> ناموفق</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="تاریخ" style="white-space:nowrap;"><small style="color:var(--gray-500);"><?php echo \Core\JDate::displayDateTime($msg->created_at); ?></small></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if (($totalPages ?? 1) > 1): ?>
<div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:16px;flex-wrap:wrap;">
    <?php if ($page > 1): ?>
    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm btn-outline-secondary">◀ قبلی</a>
    <?php endif; ?>
    
    <?php 
    $startPage = max(1, $page - 3);
    $endPage = min($totalPages, $page + 3);
    if ($startPage > 1): ?>
        <a href="?page=1&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm btn-outline-secondary">1</a>
        <?php if ($startPage > 2): ?><span style="color:var(--gray-400);">...</span><?php endif; ?>
    <?php endif; ?>
    
    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    
    <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?><span style="color:var(--gray-400);">...</span><?php endif; ?>
        <a href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm btn-outline-secondary"><?php echo $totalPages; ?></a>
    <?php endif; ?>
    
    <?php if ($page < $totalPages): ?>
    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($selectedStatus); ?>" class="btn btn-sm btn-outline-secondary">بعدی ▶</a>
    <?php endif; ?>
    
    <span style="color:var(--gray-500);font-size:12px;margin-right:12px;">صفحه <?php echo $page; ?> از <?php echo $totalPages; ?> (<?php echo number_format($totalRecords ?? 0); ?> رکورد)</span>
</div>
<?php endif; ?>

<style>
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; }
.stat-box { color:white; padding:16px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.stat-value { font-weight:800; font-size:24px; }
.stat-label { font-size:12px; opacity:0.9; }
.badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.badge.bg-success { background:#d1fae5; color:#065f46; }
.badge.bg-danger { background:#fee2e2; color:#991b1b; }
.sms-text { max-height:40px; overflow:hidden; transition:max-height 0.3s; }
.sms-text.expanded { max-height:600px; }
.sms-text.expanded + .sms-full { display:block !important; }
.sms-text.expanded { display:none; }
</style>