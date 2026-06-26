<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-person-x me-2 text-danger"></i>انتقال اطلاعات و حذف کاربر</h5>
        <p class="text-muted small mb-0 mt-1">ابتدا اطلاعات کاربر را به شخص دیگری منتقل کنید، سپس حذف انجام می‌شود.</p>
    </div>
    <a href="<?php echo $config['url']; ?>/users" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-right me-1"></i>بازگشت
    </a>
</div>

<!-- User Info Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-person text-danger fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($user->full_name); ?></h5>
                <span class="text-muted small">@<?php echo htmlspecialchars($user->username); ?> · <?php echo htmlspecialchars($user->role_name); ?></span>
            </div>
        </div>

        <!-- Data Summary -->
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="bg-light rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold text-primary"><?php echo $user->deals_count; ?></div>
                    <small class="text-muted">معاملات مسئول</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold text-info"><?php echo $user->created_deals_count; ?></div>
                    <small class="text-muted">معاملات ایجاد شده</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold text-success"><?php echo $user->contacts_count; ?></div>
                    <small class="text-muted">مخاطبین ایجاد شده</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold text-warning"><?php echo $user->activities_count; ?></div>
                    <small class="text-muted">فعالیت‌ها</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold text-secondary"><?php echo $user->sms_count; ?></div>
                    <small class="text-muted">پیامک‌ها</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Form -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?php echo $config['url']; ?>/users/transfer-delete/<?php echo $user->id; ?>" id="transferForm">
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="bi bi-person-check me-1 text-primary"></i>
                    کاربر مقصد برای انتقال اطلاعات *
                </label>
                <select name="transfer_to" class="form-select form-select-lg" required id="transferToSelect">
                    <option value="">-- انتخاب کنید --</option>
                    <?php foreach ($otherUsers as $u): ?>
                    <option value="<?php echo $u->id; ?>">
                        <?php echo htmlspecialchars($u->full_name); ?> (<?php echo htmlspecialchars($u->username); ?>) - <?php echo htmlspecialchars($u->role_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">تمام معاملات، مخاطبین، فعالیت‌ها، پیامک‌ها و سایر اطلاعات به این کاربر منتقل خواهد شد.</div>
            </div>

            <!-- Transfer Details -->
            <div class="bg-light rounded-3 p-3 mb-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i>مواردی که منتقل می‌شوند:</h6>
                <div class="row small">
                    <div class="col-6 col-md-4">
                        <i class="bi bi-check-circle text-success me-1"></i>معاملات (مسئول و ایجادکننده)
                    </div>
                    <div class="col-6 col-md-4">
                        <i class="bi bi-check-circle text-success me-1"></i>مخاطبین
                    </div>
                    <div class="col-6 col-md-4">
                        <i class="bi bi-check-circle text-success me-1"></i>فعالیت‌های معاملات
                    </div>
                    <div class="col-6 col-md-4">
                        <i class="bi bi-check-circle text-success me-1"></i>پیامک‌ها
                    </div>
                    <div class="col-6 col-md-4">
                        <i class="bi bi-check-circle text-success me-1"></i>پرداخت‌ها
                    </div>
                    <div class="col-6 col-md-4">
                        <i class="bi bi-check-circle text-success me-1"></i>لاگ فعالیت‌ها
                    </div>
                    <div class="col-6 col-md-4">
                        <i class="bi bi-check-circle text-success me-1"></i>لاگ تغییرات
                    </div>
                    <div class="col-6 col-md-4">
                        <i class="bi bi-check-circle text-success me-1"></i>اعلان‌ها
                    </div>
                    <div class="col-6 col-md-4">
                        <i class="bi bi-check-circle text-success me-1"></i>یادداشت‌ها
                    </div>
                </div>
            </div>

            <!-- Warning -->
            <div class="alert alert-danger d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                <div>
                    <strong>هشدار:</strong> این عمل غیرقابل بازگشت است! پس از انتقال اطلاعات، کاربر «<?php echo htmlspecialchars($user->full_name); ?>» به طور کامل حذف خواهد شد.
                </div>
            </div>

            <!-- Confirm Checkbox -->
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                <label class="form-check-label fw-medium" for="confirmCheck">
                    من از حذف کاربر «<?php echo htmlspecialchars($user->full_name); ?>» و انتقال تمام اطلاعات او اطمینان دارم.
                </label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger btn-lg" id="submitBtn" disabled>
                    <i class="bi bi-person-x me-1"></i>انتقال اطلاعات و حذف کاربر
                </button>
                <a href="<?php echo $config['url']; ?>/users" class="btn btn-light btn-lg">انصراف</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var confirmCheck = document.getElementById('confirmCheck');
    var submitBtn = document.getElementById('submitBtn');
    var transferSelect = document.getElementById('transferToSelect');
    
    function checkForm() {
        submitBtn.disabled = !confirmCheck.checked || !transferSelect.value;
    }
    
    confirmCheck.addEventListener('change', checkForm);
    transferSelect.addEventListener('change', checkForm);
    
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        if (!confirm('آیا از انتقال اطلاعات و حذف این کاربر اطمینان دارید?\nاین عمل غیرقابل بازگشت است!')) {
            e.preventDefault();
        }
    });
});
</script>