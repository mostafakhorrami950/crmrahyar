<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-credit-card me-2 text-primary"></i>ایجاد لینک پرداخت</h5>
    <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت به معامله</a>
</div>

<div class="row g-3">
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-link-45deg me-2 text-primary"></i>ایجاد لینک اختصاصی پرداخت</h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3 p-3 bg-light rounded-3 mb-3">
                    <div class="rounded-3 bg-primary d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width:48px;height:48px;">
                        <i class="bi bi-briefcase fs-4"></i>
                    </div>
                    <div>
                        <strong class="d-block"><?php echo htmlspecialchars($deal->title); ?></strong>
                        <small class="text-muted"><?php echo htmlspecialchars($deal->contact_name ?? ''); ?> | <?php echo htmlspecialchars($deal->contact_phone ?? ''); ?></small>
                        <br><strong class="text-primary"><?php echo number_format($deal->amount); ?> تومان</strong>
                    </div>
                </div>

                <form method="POST" action="<?php echo $config['url']; ?>/payment/request" id="payForm">
                    <input type="hidden" name="deal_id" value="<?php echo $deal->id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-medium">مبلغ (تومان) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-lg" value="<?php echo $deal->amount ?: 0; ?>" required min="1000" step="1000" dir="ltr" style="text-align:left;font-weight:bold;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-phone me-1"></i>شماره موبایل</label>
                        <input type="text" name="mobile" class="form-control" placeholder="09120000000" value="<?php echo htmlspecialchars($deal->contact_phone ?? ''); ?>" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-journal-text me-1"></i>توضیحات</label>
                        <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($deal->title); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold" id="submitPayBtn">
                        <i class="bi bi-credit-card me-1"></i>اتصال به درگاه پرداخت زیبال
                    </button>
                    <p class="text-center text-muted small mt-2"><i class="bi bi-shield-lock me-1"></i>پرداخت امن توسط درگاه زیبال</p>
                </form>

                <div id="payLinkSection" class="d-none mt-3 p-3 rounded-3" style="background:#d4edda;border:1px solid #c3e6cb;">
                    <h6 class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>لینک پرداخت اختصاصی</h6>
                    <div class="d-flex gap-2">
                        <input type="text" id="publicPayLink" class="form-control form-control-sm" dir="ltr" style="text-align:left;" readonly>
                        <button type="button" class="btn btn-success btn-sm text-nowrap" onclick="copyLink(this)"><i class="bi bi-clipboard me-1"></i>کپی</button>
                    </div>
                    <small class="text-muted mt-1 d-block">در حال انتقال به درگاه پرداخت...</small>
                </div>
                <div id="payError" class="alert alert-danger d-none mt-3"></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>تاریخچه پرداخت‌ها</h6>
            </div>
            <div class="card-body">
                <?php $db = \Core\Database::getInstance(); $payments = $db->fetchAll("SELECT * FROM payments WHERE deal_id = :id ORDER BY created_at DESC", [':id' => $deal->id]); ?>
                <?php if (empty($payments)): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-credit-card fs-1 d-block mb-2 opacity-25"></i>
                    <p>هیچ پرداختی ثبت نشده است.</p>
                </div>
                <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($payments as $p): ?>
                    <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                        <div>
                            <strong class="small"><?php echo number_format($p->amount); ?> تومان</strong>
                            <br><small class="text-muted"><?php echo \Core\JDate::displayDateTime($p->created_at); ?></small>
                            <?php if ($p->track_id): ?><br><small class="text-muted">کد: <?php echo $p->track_id; ?></small><?php endif; ?>
                        </div>
                        <div>
                            <?php if ($p->status == 'success'): ?>
                            <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle me-1"></i>موفق</span>
                            <?php elseif ($p->status == 'pending'): ?>
                            <span class="badge bg-warning bg-opacity-10 text-warning"><i class="bi bi-clock me-1"></i>در انتظار</span>
                            <?php else: ?>
                            <span class="badge bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle me-1"></i>ناموفق</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var payForm = document.getElementById('payForm');
    if (!payForm) return;
    
    payForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('submitPayBtn');
        var payError = document.getElementById('payError');
        var payLinkSection = document.getElementById('payLinkSection');
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>در حال اتصال...';
        payError.classList.add('d-none');
        payLinkSection.classList.add('d-none');
        
        fetch(payForm.action, {
            method: 'POST',
            body: new FormData(payForm),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (data.public_link) {
                    document.getElementById('publicPayLink').value = data.public_link;
                    payLinkSection.classList.remove('d-none');
                }
                if (data.redirect) {
                    setTimeout(function() { window.location.href = data.redirect; }, 1500);
                }
            } else {
                payError.classList.remove('d-none');
                payError.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' + (data.message || 'خطا در اتصال');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-credit-card me-1"></i>اتصال به درگاه پرداخت زیبال';
            }
        })
        .catch(function() {
            payError.classList.remove('d-none');
            payError.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>خطای شبکه';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-credit-card me-1"></i>اتصال به درگاه پرداخت زیبال';
        });
    });
});

function copyLink(btn) {
    var input = document.getElementById('publicPayLink');
    if (input && input.value) {
        navigator.clipboard.writeText(input.value);
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check me-1"></i>کپی شد!';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-dark');
        setTimeout(function() { btn.innerHTML = orig; btn.classList.remove('btn-dark'); btn.classList.add('btn-success'); }, 2000);
    }
}
</script>