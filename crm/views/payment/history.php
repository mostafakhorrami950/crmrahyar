<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>💰 تاریخچه پرداخت‌ها</h5>
    <div class="d-flex gap-8">
        <div class="input-group" style="max-width:250px;">
            <input type="text" class="form-search" id="paymentSearch" placeholder="🔍 جستجوی پرداخت..." oninput="filterPayments(this.value)">
        </div>
    </div>
</div>

<div class="stats-row" style="margin-bottom:20px;">
    <div class="stat-box" style="background:linear-gradient(135deg,#10B981,#059669);">
        <div class="stat-value" style="font-size:22px;"><?php echo number_format(array_sum(array_map(function($p){ return ($p->status=='success')?$p->amount:0; }, $payments ?? []))); ?></div>
        <div class="stat-label">مجموع پرداخت‌های موفق (تومان)</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#3B82F6,#2563EB);">
        <div class="stat-value" style="font-size:22px;"><?php echo count(array_filter($payments ?? [], function($p){ return $p->status=='success'; })); ?></div>
        <div class="stat-label">تعداد پرداخت‌های موفق</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
        <div class="stat-value" style="font-size:22px;"><?php echo count(array_filter($payments ?? [], function($p){ return $p->status=='pending'; })); ?></div>
        <div class="stat-label">در انتظار پرداخت</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#EF4444,#DC2626);">
        <div class="stat-value" style="font-size:22px;"><?php echo count(array_filter($payments ?? [], function($p){ return $p->status=='failed'; })); ?></div>
        <div class="stat-label">پرداخت‌های ناموفق</div>
    </div>
</div>

<div class="card" style="padding:0;">
    <div class="table-responsive">
        <table class="table" id="paymentsTable">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>معامله</th>
                    <th>مشتری</th>
                    <th>مبلغ</th>
                    <th>کد پیگیری</th>
                    <th>شماره مرجع</th>
                    <th>وضعیت</th>
                    <th>لینک پرداخت</th>
                    <th>تاریخ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div style="font-size:48px;margin-bottom:10px;">💳</div>
                        <p style="color:var(--gray-500);">هیچ پرداختی ثبت نشده است.</p>
                        <a href="<?php echo $config['url']; ?>/deals" class="btn btn-primary btn-sm mt-2">مشاهده معاملات</a>
                    </td>
                </tr>
                <?php else: ?>
                <?php $counter = 1; ?>
                <?php foreach ($payments as $p): ?>
                <tr class="payment-row" data-search="<?php echo htmlspecialchars(($p->deal_title ?? '') . ' ' . ($p->contact_name ?? '') . ' ' . ($p->track_id ?? '') . ' ' . ($p->ref_number ?? '') . ' ' . number_format($p->amount)); ?>">
                    <td data-label="#" style="color:var(--gray-400);font-size:12px;"><?php echo $counter++; ?></td>
                    <td data-label="معامله">
                        <?php if ($p->deal_id): ?>
                        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $p->deal_id; ?>" class="deal-link" style="font-weight:600;color:var(--primary);text-decoration:none;">
                            <?php echo htmlspecialchars($p->deal_title ?? 'بدون عنوان'); ?>
                            <span style="font-size:12px;margin-right:4px;">🔗</span>
                        </a>
                        <?php else: ?>
                        <span style="color:var(--gray-400);">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="مشتری">
                        <?php if ($p->contact_name): ?>
                        <span style="font-size:13px;"><?php echo htmlspecialchars($p->contact_name); ?></span>
                        <?php else: ?>
                        <span style="color:var(--gray-400);font-size:12px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="مبلغ"><strong style="color:var(--gray-800);"><?php echo number_format($p->amount); ?></strong> <small style="color:var(--gray-400);font-size:11px;">تومان</small></td>
                    <td data-label="کد پیگیری">
                        <?php if ($p->track_id): ?>
                        <span class="badge bg-info" style="font-size:11px;direction:ltr;display:inline-block;"><?php echo htmlspecialchars($p->track_id); ?></span>
                        <?php else: ?>
                        <span style="color:var(--gray-300);font-size:12px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="مرجع">
                        <?php if ($p->ref_number): ?>
                        <span style="font-size:12px;direction:ltr;display:inline-block;"><?php echo htmlspecialchars($p->ref_number); ?></span>
                        <?php else: ?>
                        <span style="color:var(--gray-300);font-size:12px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="وضعیت">
                        <?php if ($p->status == 'success'): ?>
                        <span class="badge bg-success">✅ موفق</span>
                        <?php elseif ($p->status == 'pending'): ?>
                        <span class="badge bg-warning">⏳ در انتظار</span>
                        <?php else: ?>
                        <span class="badge bg-danger">❌ ناموفق</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="لینک">
                        <?php if (!empty($p->public_token) && $p->status == 'pending'): ?>
                        <div class="d-flex gap-4" style="align-items:center;">
                            <div class="input-group" style="width:140px;">
                                <input type="text" class="pay-link-input" value="<?php echo $config['url']; ?>/pay/<?php echo htmlspecialchars($p->public_token); ?>" readonly onclick="this.select();" style="width:100%;padding:4px 8px;border:1px solid #ddd;border-radius:6px;font-size:11px;direction:ltr;text-align:left;background:#f9fafb;">
                            </div>
                            <button type="button" class="btn btn-sm btn-success" onclick="copyPayLink(this)" style="padding:4px 10px;font-size:12px;white-space:nowrap;" title="کپی لینک پرداخت">📋 کپی</button>
                        </div>
                        <?php elseif ($p->status == 'success'): ?>
                        <span style="color:var(--gray-400);font-size:11px;">پرداخت شده</span>
                        <?php else: ?>
                        <span style="color:var(--gray-300);font-size:12px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="تاریخ" style="white-space:nowrap;">
                        <small style="color:var(--gray-500);">
                            <?php echo \Core\JDate::displayDateTime($p->created_at); ?>
                        </small>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
}
.stat-box {
    color: white;
    padding: 18px 16px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.stat-value {
    font-weight: 800;
    margin-bottom: 4px;
}
.stat-label {
    font-size: 12px;
    opacity: 0.9;
}
.pay-link-input {
    font-family: 'Consolas', 'Courier New', monospace;
    font-size: 11px;
}
.deal-link:hover {
    text-decoration: underline !important;
    color: #1a56db !important;
}
.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.badge.bg-success { background: #d1fae5; color: #065f46; }
.badge.bg-warning { background: #fef3c7; color: #92400e; }
.badge.bg-danger { background: #fee2e2; color: #991b1b; }
.badge.bg-info { background: #dbeafe; color: #1e40af; }
@media (max-width: 768px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
}
</style>

<script>
function filterPayments(query) {
    query = query.trim().toLowerCase();
    var rows = document.querySelectorAll('.payment-row');
    rows.forEach(function(row) {
        var searchData = (row.getAttribute('data-search') || '').toLowerCase();
        if (!query || searchData.indexOf(query) !== -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function copyPayLink(btn) {
    var container = btn.closest('.d-flex');
    if (!container) return;
    var input = container.querySelector('.pay-link-input');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        var original = btn.innerHTML;
        btn.innerHTML = '✅ کپی شد';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
        setTimeout(function() {
            btn.innerHTML = original;
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
        }, 2000);
    }).catch(function() {
        document.execCommand('copy');
    });
}
</script>