<?php $config = $GLOBALS['app_config']; ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>ویرایش کاربر: <?php echo htmlspecialchars($user->full_name); ?></h5>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/users/transfer-delete/<?php echo $user->id; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('آیا می‌خواهید این کاربر را حذف کنید؟')">
            <i class="bi bi-person-x me-1"></i>حذف کاربر
        </a>
        <a href="<?php echo $config['url']; ?>/users" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>بازگشت
        </a>
    </div>
</div>

<!-- User Profile Header -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:70px;height:70px;background:linear-gradient(135deg,<?php echo $user->role_slug === 'super_admin' ? '#EF4444,#DC2626' : ($user->role_slug === 'admin' ? '#F59E0B,#D97706' : ($user->role_slug === 'operator' ? '#3B82F6,#2563EB' : '#10B981,#059669')); ?>);font-size:28px;color:#fff;font-weight:700;">
                <?php echo mb_substr($user->full_name, 0, 1); ?>
            </div>
            <div class="flex-grow-1">
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user->full_name); ?></h4>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <?php
                    $roleBadgeClass = match($user->role_slug) {
                        'super_admin' => 'bg-danger',
                        'admin' => 'bg-warning text-dark',
                        'operator' => 'bg-primary',
                        default => 'bg-success'
                    };
                    $roleIcon = match($user->role_slug) {
                        'super_admin' => '👑',
                        'admin' => 'bi-key',
                        'operator' => 'bi-person-workspace',
                        default => 'bi-person'
                    };
                    ?>
                    <span class="badge <?php echo $roleBadgeClass; ?> px-3 py-2">
                        <?php if (str_starts_with($roleIcon, 'bi:')): ?>
                            <i class="bi <?php echo $roleIcon; ?> me-1"></i>
                        <?php else: ?>
                            <?php echo $roleIcon; ?> 
                        <?php endif; ?>
                        <?php echo htmlspecialchars($user->role_name); ?>
                    </span>
                    <?php if ($user->is_active): ?>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i>فعال
                    </span>
                    <?php else: ?>
                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                        <i class="bi bi-x-circle me-1"></i>غیرفعال
                    </span>
                    <?php endif; ?>
                    <span class="text-muted small">@<?php echo htmlspecialchars($user->username); ?></span>
                </div>
            </div>
            <div class="text-center">
                <?php if ($user->last_login): ?>
                <div class="text-muted small mb-1">آخرین ورود</div>
                <div class="fw-semibold"><?php echo \Core\JDate::displayDateTime($user->last_login); ?></div>
                <?php else: ?>
                <div class="text-muted small">هرگز وارد نشده</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- User Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="background:linear-gradient(135deg,#3B82F6,#2563EB);color:#fff;">
            <div class="fs-3 fw-bold"><?php echo $user->deals_count; ?></div>
            <small class="opacity-75">💼 کل معاملات</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="background:linear-gradient(135deg,#10B981,#059669);color:#fff;">
            <div class="fs-3 fw-bold"><?php echo number_format($user->won_amount); ?></div>
            <small class="opacity-75"><i class="bi bi-cash me-1"></i>مبلغ موفق (ریال)</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="background:linear-gradient(135deg,#F59E0B,#D97706);color:#fff;">
            <div class="fs-3 fw-bold"><?php echo $user->open_deals; ?></div>
            <small class="opacity-75"><i class="bi bi-clock me-1"></i>معاملات باز</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="background:linear-gradient(135deg,#8B5CF6,#7C3AED);color:#fff;">
            <div class="fs-3 fw-bold"><?php echo $user->contacts_count; ?></div>
            <small class="opacity-75"><i class="bi bi-people me-1"></i>مخاطبین</small>
        </div>
    </div>
</div>

<!-- Edit Form -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>اطلاعات کاربری</h6>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="<?php echo $config['url']; ?>/users/update/<?php echo $user->id; ?>">
            <div class="row g-3">
                <!-- Full Name -->
                <div class="col-12 col-md-6">
                    <label class="form-label fw-medium">
                        <i class="bi bi-person me-1 text-muted"></i>نام و نام خانوادگی *
                    </label>
                    <input type="text" name="full_name" class="form-control form-control-lg" 
                           value="<?php echo htmlspecialchars($user->full_name); ?>" required 
                           placeholder="مثال: محمد احمدی">
                </div>

                <!-- Username (disabled) -->
                <div class="col-12 col-md-6">
                    <label class="form-label fw-medium">
                        <i class="bi bi-at me-1 text-muted"></i>نام کاربری
                    </label>
                    <input type="text" class="form-control form-control-lg bg-light" 
                           value="<?php echo htmlspecialchars($user->username); ?>" disabled>
                    <div class="form-text">نام کاربری قابل تغییر نیست</div>
                </div>

                <!-- Email -->
                <div class="col-12 col-md-6">
                    <label class="form-label fw-medium">
                        <i class="bi bi-envelope me-1 text-muted"></i>ایمیل
                    </label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($user->email ?? ''); ?>" 
                           placeholder="example@email.com" dir="ltr" style="text-align:left;">
                </div>

                <!-- Phone -->
                <div class="col-12 col-md-6">
                    <label class="form-label fw-medium">
                        <i class="bi bi-phone me-1 text-muted"></i>شماره تلفن
                    </label>
                    <input type="text" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($user->phone ?? ''); ?>" 
                           placeholder="0912xxxxxxx" dir="ltr" style="text-align:left;">
                </div>

                <!-- Role -->
                <div class="col-12 col-md-6">
                    <label class="form-label fw-medium">
                        <i class="bi bi-shield-check me-1 text-muted"></i>نقش کاربر *
                    </label>
                    <select name="role_id" class="form-select form-select-lg" required>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?php echo $r->id; ?>" <?php echo $r->id == $user->role_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r->name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Password -->
                <div class="col-12 col-md-6">
                    <label class="form-label fw-medium">
                        <i class="bi bi-lock me-1 text-muted"></i>رمز عبور جدید
                    </label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="خالی بگذارید تا تغییر نکند" dir="ltr" style="text-align:left;">
                    <div class="form-text">حداقل ۶ کاراکتر • خالی بگذارید تا رمز تغییر نکند</div>
                </div>

                <!-- Is Active -->
                <div class="col-12">
                    <div class="bg-light rounded-3 p-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   id="isActiveSwitch" <?php echo $user->is_active ? 'checked' : ''; ?>
                                   style="width:3em;height:1.5em;">
                            <label class="form-check-label fw-medium ms-2" for="isActiveSwitch">
                                حساب فعال
                                <div class="text-muted small">کاربر می‌تواند وارد سیستم شود</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle me-1"></i>ذخیره تغییرات
                </button>
                <a href="<?php echo $config['url']; ?>/users" class="btn btn-light btn-lg">انصراف</a>
            </div>
        </form>
    </div>
</div>