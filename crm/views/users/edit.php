<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-1"></i>ویرایش کاربر: <?php echo htmlspecialchars($user->full_name); ?></h5>
    <a href="<?php echo $config['url']; ?>/users" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
</div>

<!-- User Profile Header -->
<div class="card" style="padding:24px;margin-bottom:16px;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="width:70px;height:70px;border-radius:18px;background:linear-gradient(135deg,<?php echo $user->role_slug === 'super_admin' ? '#EF4444,#DC2626' : ($user->role_slug === 'admin' ? '#F59E0B,#D97706' : ($user->role_slug === 'operator' ? '#3B82F6,#2563EB' : '#10B981,#059669')); ?>);display:flex;align-items:center;justify-content:center;font-size:28px;color:#fff;font-weight:700;flex-shrink:0;">
            <?php echo mb_substr($user->full_name, 0, 1); ?>
        </div>
        <div style="flex:1;">
            <h3 style="margin:0 0 4px 0;font-weight:800;"><?php echo htmlspecialchars($user->full_name); ?></h3>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <span style="background:<?php echo $user->role_slug === 'super_admin' ? '#fee2e2' : ($user->role_slug === 'admin' ? '#fef3c7' : ($user->role_slug === 'operator' ? '#dbeafe' : '#d1fae5')); ?>;color:<?php echo $user->role_slug === 'super_admin' ? '#991b1b' : ($user->role_slug === 'admin' ? '#92400e' : ($user->role_slug === 'operator' ? '#1e40af' : '#065f46')); ?>;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;">
                    <?php echo $user->role_slug === 'super_admin' ? '👑' : ($user->role_slug === 'admin' ? '<i class="bi bi-key me-1"></i>' : ($user->role_slug === 'operator' ? '👨‍💼' : '<i class="bi bi-person me-1"></i>')); ?>
                    <?php echo htmlspecialchars($user->role_name); ?>
                </span>
                <?php if ($user->is_active): ?>
                <span style="background:#d1fae5;color:#065f46;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;"><i class="bi bi-check-circle text-success me-1"></i> فعال</span>
                <?php else: ?>
                <span style="background:#fee2e2;color:#991b1b;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;"><i class="bi bi-x-circle text-danger me-1"></i> غیرفعال</span>
                <?php endif; ?>
                <span style="color:var(--gray-400);font-size:12px;">@<?php echo htmlspecialchars($user->username); ?></span>
            </div>
        </div>
        <div style="text-align:center;">
            <?php if ($user->last_login): ?>
            <div style="font-size:11px;color:var(--gray-400);">آخرین ورود</div>
            <div style="font-size:13px;font-weight:600;"><?php echo \Core\JDate::displayDateTime($user->last_login); ?></div>
            <?php else: ?>
            <div style="font-size:12px;color:var(--gray-300);">هرگز وارد نشده</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- User Stats -->
<div class="stats-row" style="margin-bottom:16px;">
    <div class="stat-card" style="background:linear-gradient(135deg,#3B82F6,#2563EB);">
        <div class="stat-value"><?php echo $user->deals_count; ?></div>
        <div class="stat-label">💼 کل معاملات</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#10B981,#059669);">
        <div class="stat-value"><?php echo number_format($user->won_amount); ?></div>
        <div class="stat-label"><i class="bi bi-cash me-1"></i> مبلغ موفق (تومان)</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
        <div class="stat-value"><?php echo $user->open_deals; ?></div>
        <div class="stat-label"><i class="bi bi-clock text-warning me-1"></i> معاملات باز</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#8B5CF6,#7C3AED);">
        <div class="stat-value"><?php echo $user->contacts_count; ?></div>
        <div class="stat-label"><i class="bi bi-people me-1"></i> مخاطبین</div>
    </div>
</div>

<!-- Edit Form -->
<div class="card" style="padding:24px;">
    <form method="POST" action="<?php echo $config['url']; ?>/users/update/<?php echo $user->id; ?>">
        <h5 class="fw-bold mb-0"><i class="bi bi-journal-text me-1"></i> اطلاعات کاربری</h5>
        
        <div class="form-row">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">نام و نام خانوادگی *</label>
                <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($user->full_name); ?>" required placeholder="مثال: محمد احمدی">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">نام کاربری</label>
                <input type="text" class="form-input" value="<?php echo htmlspecialchars($user->username); ?>" disabled style="background:var(--gray-100);color:var(--gray-500);">
                <div class="form-hint">نام کاربری قابل تغییر نیست</div>
            </div>
        </div>

        <div class="form-row">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">ایمیل</label>
                <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user->email ?? ''); ?>" placeholder="example@email.com" dir="ltr" style="text-align:left;">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">شماره تلفن</label>
                <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($user->phone ?? ''); ?>" placeholder="0912xxxxxxx" dir="ltr" style="text-align:left;">
            </div>
        </div>

        <div class="form-row">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">نقش کاربر *</label>
                <select name="role_id" class="form-input" required>
                    <?php foreach ($roles as $r): ?>
                    <option value="<?php echo $r->id; ?>" <?php echo $r->id == $user->role_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($r->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">رمز عبور جدید</label>
                <input type="password" name="password" class="form-input" placeholder="خالی بگذارید تا تغییر نکند" dir="ltr" style="text-align:left;">
                <div class="form-hint">حداقل ۶ کاراکتر • خالی بگذارید تا رمز تغییر نکند</div>
            </div>
        </div>

        <div class="mb-3" style="margin-top:8px;">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:var(--gray-50);border-radius:10px;">
                <input type="checkbox" name="is_active" value="1" <?php echo $user->is_active ? 'checked' : ''; ?> style="width:18px;height:18px;cursor:pointer;">
                <div>
                    <strong>حساب فعال</strong>
                    <div style="font-size:12px;color:var(--gray-400);">کاربر می‌تواند وارد سیستم شود</div>
                </div>
            </label>
        </div>

        <div style="display:flex;gap:8px;margin-top:20px;padding-top:16px;border-top:1px solid var(--gray-200);">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره تغییرات</button>
            <a href="<?php echo $config['url']; ?>/users" class="btn btn-outline-secondary">انصراف</a>
        </div>
    </form>
</div>

<style>
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:10px; }
.stat-box { color:white; padding:16px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.stat-value { font-weight:800; font-size:24px; }
.stat-label { font-size:11px; opacity:0.9; margin-top:2px; }
</style>