<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-person me-1"></i> مدیریت کاربران</h5>
    <a href="<?php echo $config['url']; ?>/users/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> کاربر جدید</a>
</div>

<!-- Stats -->
<div class="stats-row" style="margin-bottom:16px;">
    <div class="stat-card" style="background:linear-gradient(135deg,#667eea,#764ba2);">
        <div class="stat-value"><?php echo count($users); ?></div>
        <div class="stat-label"><i class="bi bi-people me-1"></i> کل کاربران</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#10B981,#059669);">
        <?php $active = count(array_filter($users, fn($u) => $u->is_active)); ?>
        <div class="stat-value"><?php echo $active; ?></div>
        <div class="stat-label"><i class="bi bi-check-circle text-success me-1"></i> فعال</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#EF4444,#DC2626);">
        <?php $inactive = count(array_filter($users, fn($u) => !$u->is_active)); ?>
        <div class="stat-value"><?php echo $inactive; ?></div>
        <div class="stat-label"><i class="bi bi-x-circle text-danger me-1"></i> غیرفعال</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
        <?php $admins = count(array_filter($users, fn($u) => $u->role_slug === 'super_admin' || $u->role_slug === 'admin')); ?>
        <div class="stat-value"><?php echo $admins; ?></div>
        <div class="stat-label"><i class="bi bi-key me-1"></i> مدیران</div>
    </div>
</div>

<!-- Users List -->
<?php if (empty($users)): ?>
<div class="card">
    <div style="text-align:center;padding:60px 20px;color:var(--gray-400);">
        <div style="font-size:64px;margin-bottom:16px;"><i class="bi bi-person me-1"></i></div>
        <h3 style="color:var(--gray-500);margin-bottom:8px;">کاربری یافت نشد</h3>
        <a href="<?php echo $config['url']; ?>/users/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>ایجاد کاربر</a>
    </div>
</div>
<?php else: ?>
<div class="card" style="padding:0;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">کاربر</th>
                    <th class="text-nowrap">نقش</th>
                    <th class="text-nowrap">معاملات</th>
                    <th class="text-nowrap">مخاطبین</th>
                    <th class="text-nowrap">پیامک</th>
                    <th class="text-nowrap">وضعیت</th>
                    <th class="text-nowrap">آخرین ورود</th>
                    <th class="text-nowrap">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr style="<?php echo !$u->is_active ? 'opacity:0.5;' : ''; ?>">
                    <td data-label="کاربر">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,<?php echo $u->role_slug === 'super_admin' ? '#EF4444,#DC2626' : ($u->role_slug === 'admin' ? '#F59E0B,#D97706' : ($u->role_slug === 'operator' ? '#3B82F6,#2563EB' : '#10B981,#059669')); ?>);display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;font-weight:700;flex-shrink:0;">
                                <?php echo mb_substr($u->full_name, 0, 1); ?>
                            </div>
                            <div>
                                <strong style="font-size:14px;"><?php echo htmlspecialchars($u->full_name); ?></strong>
                                <div style="font-size:11px;color:var(--gray-400);">@<?php echo htmlspecialchars($u->username); ?></div>
                                <?php if ($u->email): ?>
                                <div style="font-size:11px;color:var(--gray-400);"><?php echo htmlspecialchars($u->email); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td data-label="نقش">
                        <span style="background:<?php echo $u->role_slug === 'super_admin' ? '#fee2e2' : ($u->role_slug === 'admin' ? '#fef3c7' : ($u->role_slug === 'operator' ? '#dbeafe' : '#d1fae5')); ?>;color:<?php echo $u->role_slug === 'super_admin' ? '#991b1b' : ($u->role_slug === 'admin' ? '#92400e' : ($u->role_slug === 'operator' ? '#1e40af' : '#065f46')); ?>;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                            <?php echo $u->role_slug === 'super_admin' ? '👑' : ($u->role_slug === 'admin' ? '<i class="bi bi-key me-1"></i>' : ($u->role_slug === 'operator' ? '👨‍💼' : '<i class="bi bi-person me-1"></i>')); ?>
                            <?php echo htmlspecialchars($u->role_name); ?>
                        </span>
                    </td>
                    <td data-label="معاملات" style="text-align:center;">
                        <strong style="font-size:16px;color:var(--primary);"><?php echo $u->deals_count; ?></strong>
                        <?php if ($u->won_amount > 0): ?>
                        <div style="font-size:10px;color:#059669;"><?php echo number_format($u->won_amount); ?> ت</div>
                        <?php endif; ?>
                    </td>
                    <td data-label="مخاطبین" style="text-align:center;">
                        <strong><?php echo $u->contacts_count; ?></strong>
                    </td>
                    <td data-label="پیامک" style="text-align:center;">
                        <strong><?php echo $u->sms_count; ?></strong>
                    </td>
                    <td data-label="وضعیت">
                        <?php if ($u->is_active): ?>
                        <span style="background:#d1fae5;color:#065f46;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;"><i class="bi bi-check-circle text-success me-1"></i> فعال</span>
                        <?php else: ?>
                        <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;"><i class="bi bi-x-circle text-danger me-1"></i> غیرفعال</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="آخرین ورود" style="white-space:nowrap;">
                        <?php if ($u->last_login): ?>
                        <small style="color:var(--gray-500);"><?php echo \Core\JDate::displayDateTime($u->last_login); ?></small>
                        <?php else: ?>
                        <small style="color:var(--gray-300);">هرگز</small>
                        <?php endif; ?>
                    </td>
                    <td data-label="عملیات">
                        <div style="display:flex;gap:4px;">
                            <a href="<?php echo $config['url']; ?>/users/edit/<?php echo $u->id; ?>" class="btn btn-sm btn-outline-secondary" title="ویرایش"><i class="bi bi-pencil me-1"></i></a>
                            <?php if ($u->id !== \Core\Auth::id()): ?>
                            <form method="POST" action="<?php echo $config['url']; ?>/users/delete/<?php echo $u->id; ?>" style="display:inline;" onsubmit="return confirm('آیا از حذف کاربر «<?php echo htmlspecialchars($u->full_name, ENT_QUOTES); ?>» اطمینان دارید؟')">
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف"><i class="bi bi-trash me-1"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; }
.stat-box { color:white; padding:16px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.stat-value { font-weight:800; font-size:26px; }
.stat-label { font-size:12px; opacity:0.9; }
</style>