<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0">🛡️ مدیریت نقش‌ها و دسترسی‌ها</h5>
    <a href="<?php echo $config['url']; ?>/roles/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> نقش جدید</a>
</div>

<!-- Roles Cards -->
<?php if (empty($roles)): ?>
<div class="card" style="text-align:center;padding:60px;">
    <div style="font-size:64px;margin-bottom:16px;">🛡️</div>
    <h3 style="color:var(--gray-500);">نقشی تعریف نشده</h3>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:16px;">
    <?php foreach ($roles as $role): 
        $perms = $rolePermissions[$role->id] ?? [];
        $permCount = count($perms);
        $ownCount = 0;
        $allCount = 0;
        foreach ($perms as $p) {
            if ($p->scope === 'own') $ownCount++;
            else $allCount++;
        }
    ?>
    <div class="card" style="padding:0;overflow:hidden;">
        <!-- Role Header -->
        <div style="padding:20px 20px 16px;background:linear-gradient(135deg,<?php echo $role->slug === 'super_admin' ? '#EF4444,#DC2626' : ($role->slug === 'operator' ? '#3B82F6,#2563EB' : ($role->slug === 'sales_manager' ? '#F59E0B,#D97706' : '#8B5CF6,#7C3AED')); ?>);color:white;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-size:13px;opacity:0.8;">
                        <?php echo $role->slug === 'super_admin' ? '👑' : ($role->slug === 'operator' ? '👨‍💼' : '🛡️'); ?>
                        <?php echo $role->is_system ? 'سیستمی' : 'سفارشی'; ?>
                    </div>
                    <h3 style="margin:4px 0 0;font-size:20px;font-weight:800;"><?php echo htmlspecialchars($role->name); ?></h3>
                </div>
                <div style="background:rgba(255,255,255,0.2);padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;">
                    <?php echo $role->user_count; ?> کاربر
                </div>
            </div>
        </div>
        
        <!-- Role Body -->
        <div style="padding:16px 20px;">
            <?php if ($role->description): ?>
            <p style="color:var(--gray-500);font-size:13px;margin-bottom:12px;"><?php echo htmlspecialchars($role->description); ?></p>
            <?php endif; ?>
            
            <!-- Permission Summary -->
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;">
                <span style="background:var(--gray-100);padding:4px 10px;border-radius:16px;font-size:11px;">
                    <i class="bi bi-key me-1"></i> <?php echo $permCount; ?> دسترسی
                </span>
                <?php if ($allCount > 0): ?>
                <span style="background:#dbeafe;color:#1e40af;padding:4px 10px;border-radius:16px;font-size:11px;">
                    🌐 <?php echo $allCount; ?> همه
                </span>
                <?php endif; ?>
                <?php if ($ownCount > 0): ?>
                <span style="background:#fef3c7;color:#92400e;padding:4px 10px;border-radius:16px;font-size:11px;">
                    <i class="bi bi-person me-1"></i> <?php echo $ownCount; ?> فقط خودش
                </span>
                <?php endif; ?>
            </div>
            
            <!-- Permission Modules Preview -->
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;">
                <?php 
                $modules = [];
                foreach ($perms as $p) {
                    $module = explode('.', $p->permission)[0];
                    $modules[$module] = true;
                }
                $moduleLabels = ['dashboard'=>'<i class="bi bi-bar-chart me-1"></i>','deals'=>'💼','contacts'=>'<i class="bi bi-people me-1"></i>','pipelines'=>'<i class="bi bi-list-task me-1"></i>','payments'=>'<i class="bi bi-credit-card me-1"></i>','sms'=>'📱','reports'=>'📈','users'=>'<i class="bi bi-person me-1"></i>','roles'=>'🛡️','settings'=>'<i class="bi bi-gear me-1"></i>','database'=>'🗄️','activitylog'=>'<i class="bi bi-journal-text me-1"></i>'];
                foreach ($modules as $mod => $v):
                ?>
                <span style="background:var(--gray-50);padding:3px 8px;border-radius:8px;font-size:13px;" title="<?php echo htmlspecialchars($mod); ?>">
                    <?php echo $moduleLabels[$mod] ?? '📄'; ?>
                </span>
                <?php endforeach; ?>
            </div>
            
            <!-- Actions -->
            <div style="display:flex;gap:6px;">
                <a href="<?php echo $config['url']; ?>/roles/edit/<?php echo $role->id; ?>" class="btn btn-sm btn-outline-secondary" style="flex:1;text-align:center;"><i class="bi bi-pencil me-1"></i>ویرایش دسترسی‌ها</a>
                <?php if (!$role->is_system): ?>
                <form method="POST" action="<?php echo $config['url']; ?>/roles/delete/<?php echo $role->id; ?>" style="display:inline;" onsubmit="return confirm('آیا از حذف این نقش اطمینان دارید؟')">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i></button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>