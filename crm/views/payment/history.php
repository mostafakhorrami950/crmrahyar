<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2 text-primary"></i>تاریخچه پرداخت‌ها</h5>
    <div class="input-group" style="max-width:250px;">
        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control form-control-sm" id="paymentSearch" placeholder="جستجوی پرداخت..." oninput="filterPayments(this.value)">
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="background:linear-gradient(135deg,#10B981,#059669);color:#fff;">
            <div class="fw-bold" style="font-size:20px;"><?php echo number_format(array_sum(array_map(function($p){ return ($p->status=='success')?$p->amount:0; }, $payments ?? []))); ?></div>
            <small style="opacity:0.9;">مجموع پرداخت‌های موفق (تومان)</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="background:linear-gradient(135deg,#3B82F6,#2563EB);color:#fff;">
            <div class="fw-bold" style="font-size:20px;"><?php echo count(array_filter($payments ?? [], function($p){ return $p->status=='success'; })); ?></div>
            <small style="opacity:0.9;">تعداد پرداخت‌های موفق</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="background:linear-gradient(135deg,#F59E0B,#D97706);color:#fff;">
            <div class="fw-bold" style="font-size:20px;"><?php echo count(array_filter($payments ?? [], function($p){ return $p->status=='pending'; })); ?></div>
            <small style="opacity:0.9;">در انتظار پرداخت</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center p-3 h-100" style="background:linear-gradient(135deg,#EF4444,#DC2626);color:#fff;">
            <div class="fw-bold" style="font-size:20px;"><?php echo count(array_filter($payments ?? [], function($p){ return $p->status=='failed'; })); ?></div>
            <small style="opacity:0.9;">پرداخت‌های ناموفق</small>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($payments)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-credit-card fs-1 d-block mb-2 opacity-25"></i>
            <h5>هیچ پرداختی ثبت نشده</h5>
            <a href="<?php echo $config['url']; ?>/deals" class="btn btn-primary btn-sm mt-2">مشاهده معاملات</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="paymentsTable">
                <thead class="bg-light">
                    <tr>
                        <th class="small fw-bold" style="width:40px;">#</th>
                        <th class="small fw-bold text-nowrap">معامله</th>
                        <th class="small fw-bold text-nowrap d-none d-md-table-cell">مشتری</th>
                        <th class="small fw-bold text-nowrap">مبلغ</th>
                        <th class="small fw-bold text-nowrap d-none d-lg-table-cell">کد پیگیری</th>
                        <th class="small fw-bold text-nowrap d-none d-lg-table-cell">مرجع</th>
                        <th class="small fw-bold text-nowrap">وضعیت</th>
                        <th class="small fw-bold text-nowrap d-none d-md-table-cell">لینک پرداخت</th>
                        <th class="small fw-bold text-nowrap">تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                <?php $counter = 1; ?>
                <?php foreach ($payments as $p): ?>
                <tr class="payment-row" data-search="<?php echo htmlspecialchars(($p->deal_title ?? '') . ' ' . ($p->contact_name ?? '') . ' ' . ($p->track_id ?? '') . ' ' . ($p->ref_number ?? '') . ' ' . number_format($p->amount)); ?>">
                    <td class="small text-muted"><?php echo $counter++; ?></td>
                    <td>
                        <?php if ($p->deal_id): ?>
                        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $p->deal_id; ?>" class="text-decoration-none fw-bold small text-primary">
                            <?php echo htmlspecialchars(mb_substr($p->deal_title ?? 'بدون عنوان', 0, 25)); ?> <i class="bi bi-link-45deg"></i>
                        </a>
                        <?php else: ?>
                        <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-md-table-cell"><small><?php echo htmlspecialchars($p->contact_name ?? '-'); ?></small></td>
                    <td><strong><?php echo number_format($p->amount); ?></strong> <small class="text-muted">تومان</small></td>
                    <td class="d-none d-lg-table-cell">
                        <?php if ($p->track_id): ?>
                        <span class="badge bg-info text-dark" dir="ltr"><?php echo htmlspecialchars($p->track_id); ?></span>
                        <?php else: ?>
                        <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <?php if ($p->ref_number): ?>
                        <small dir="ltr"><?php echo htmlspecialchars($p->ref_number); ?></small>
                        <?php else: ?>
                        <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p->status == 'success'): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>موفق</span>
                        <?php elseif ($p->status == 'pending'): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>در انتظار</span>
                        <?php else: ?>
                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>ناموفق</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <?php if (!empty($p->short_code) && $p->status == 'pending'): ?>
                        <div class="d-flex align-items-center gap-1">
                            <input type="text" class="form-control form-control-sm pay-link-input" value="<?php echo $config['url']; ?>/p/<?php echo htmlspecialchars($p->short_code); ?>" readonly onclick="this.select();" style="width:140px;font-size:10px;font-family:monospace;direction:ltr;text-align:left;">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="copyPayLink(this)" title="کپی"><i class="bi bi-clipboard"></i></button>
                        </div>
                        <?php elseif (!empty($p->public_token) && $p->status == 'pending'): ?>
                        <div class="d-flex align-items-center gap-1">
                            <input type="text" class="form-control form-control-sm pay-link-input" value="<?php echo $config['url']; ?>/pay/<?php echo htmlspecialchars($p->public_token); ?>" readonly onclick="this.select();" style="width:140px;font-size:10px;font-family:monospace;direction:ltr;text-align:left;">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="copyPayLink(this)" title="کپی"><i class="bi bi-clipboard"></i></button>
                        </div>
                        <?php elseif ($p->status == 'success'): ?>
                        <small class="text-muted">پرداخت شده</small>
                        <?php else: ?>
                        <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;"><small class="text-muted"><?php echo \Core\JDate::displayDateTime($p->created_at); ?></small></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.pay-link-input { font-size: 10px !important; padding: 2px 6px !important; }
</style>

<script>
function filterPayments(query) {
    query = query.trim().toLowerCase();
    document.querySelectorAll('.payment-row').forEach(function(row) {
        var searchData = (row.getAttribute('data-search') || '').toLowerCase();
        row.style.display = (!query || searchData.indexOf(query) !== -1) ? '' : 'none';
    });
}

function copyPayLink(btn) {
    var input = btn.parentElement.querySelector('.pay-link-input');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
        setTimeout(function() { btn.innerHTML = original; }, 2000);
    }).catch(function() { document.execCommand('copy'); });
}
</script>