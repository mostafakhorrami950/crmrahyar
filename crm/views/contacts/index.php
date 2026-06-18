<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>👥 مدیریت مخاطبان</h5>
    <a href="<?php echo $config['url']; ?>/contacts/create" class="btn btn-primary">➕ مخاطب جدید</a>
</div>

<!-- Stats -->
<div class="stats-row" style="margin-bottom:16px;">
    <div class="stat-box" style="background:linear-gradient(135deg,#667eea,#764ba2);">
        <div class="stat-value"><?php echo count($contacts); ?></div>
        <div class="stat-label">کل مخاطبان</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#10B981,#059669);">
        <?php $totalPurchases = 0; foreach ($contacts as $c) $totalPurchases += $c->total_purchases ?? 0; ?>
        <div class="stat-value"><?php echo number_format($totalPurchases); ?></div>
        <div class="stat-label">💰 مجموع خرید موفق</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#3B82F6,#2563EB);">
        <?php $totalDeals = 0; foreach ($contacts as $c) $totalDeals += $c->deals_count ?? 0; ?>
        <div class="stat-value"><?php echo number_format($totalDeals); ?></div>
        <div class="stat-label">💼 مجموع معاملات</div>
    </div>
</div>

<!-- Search -->
<div class="card" style="padding:12px;margin-bottom:16px;">
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="text" name="search" class="form-input" style="flex:1;min-width:200px;" placeholder="🔍 جستجو با نام، شماره یا ایمیل..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">🔍</button>
        <?php if ($search): ?>
        <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-secondary">✖ حذف فیلتر</a>
        <?php endif; ?>
    </form>
</div>

<!-- Contacts Grid -->
<?php if (empty($contacts)): ?>
<div class="card">
    <div style="text-align:center;padding:60px 20px;color:var(--gray-400);">
        <div style="font-size:64px;margin-bottom:16px;">👥</div>
        <h3 style="color:var(--gray-500);margin-bottom:8px;">مخاطبی یافت نشد</h3>
        <p style="margin-bottom:20px;"><?php echo $search ? 'نتیجه‌ای برای «' . htmlspecialchars($search) . '» یافت نشد.' : 'اولین مخاطب را ایجاد کنید.'; ?></p>
        <a href="<?php echo $config['url']; ?>/contacts/create" class="btn btn-primary">➕ ایجاد مخاطب</a>
    </div>
</div>
<?php else: ?>
<div class="card" style="padding:0;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>مخاطب</th>
                    <th>تماس</th>
                    <th>شرکت</th>
                    <th>دسته‌بندی</th>
                    <th>معاملات</th>
                    <th>مبلغ خرید</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;font-weight:700;flex-shrink:0;">
                                <?php echo mb_substr($c->full_name, 0, 1); ?>
                            </div>
                            <div>
                                <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $c->id; ?>" style="font-weight:600;color:var(--gray-900);text-decoration:none;font-size:14px;">
                                    <?php echo htmlspecialchars($c->full_name); ?>
                                </a>
                                <?php if ($c->email): ?>
                                <div style="font-size:11px;color:var(--gray-400);"><?php echo htmlspecialchars($c->email); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($c->phone): ?>
                        <span dir="ltr" style="font-size:13px;"><?php echo htmlspecialchars($c->phone); ?></span>
                        <?php else: ?>
                        <span style="color:var(--gray-300);">-</span>
                        <?php endif; ?>
                        <?php if ($c->company_phone): ?>
                        <div style="font-size:11px;color:var(--gray-400);" dir="ltr">🏢 <?php echo htmlspecialchars($c->company_phone); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo $c->company ? '<span style="font-size:13px;">🏢 ' . htmlspecialchars($c->company) . '</span>' : '<span style="color:var(--gray-300);">-</span>'; ?>
                    </td>
                    <td>
                        <?php if (!empty($c->category_name)): ?>
                        <span style="background:<?php echo htmlspecialchars($c->category_color ?? '#6B7280'); ?>;color:white;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;">
                            <?php echo htmlspecialchars($c->category_name); ?>
                        </span>
                        <?php else: ?>
                        <span style="color:var(--gray-300);font-size:11px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-weight:700;color:var(--primary);font-size:16px;"><?php echo $c->deals_count; ?></span>
                    </td>
                    <td>
                        <?php if ($c->total_purchases > 0): ?>
                        <span style="color:#059669;font-weight:600;font-size:13px;"><?php echo number_format($c->total_purchases); ?></span>
                        <small style="color:var(--gray-400);font-size:10px;">تومان</small>
                        <?php else: ?>
                        <span style="color:var(--gray-300);font-size:12px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <small style="color:var(--gray-500);font-size:12px;"><?php echo \Core\JDate::displayDate($c->created_at); ?></small>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $c->id; ?>" class="btn btn-sm btn-primary" title="مشاهده">👁️</a>
                            <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $c->id; ?>" class="btn btn-sm btn-secondary" title="ویرایش">✏️</a>
                            <form method="POST" action="<?php echo $config['url']; ?>/contacts/delete/<?php echo $c->id; ?>" style="display:inline;" onsubmit="return confirm('آیا از حذف «<?php echo htmlspecialchars($c->full_name, ENT_QUOTES); ?>» اطمینان دارید؟')">
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">🗑️</button>
                            </form>
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
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; }
.stat-box { color:white; padding:16px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.stat-value { font-weight:800; font-size:24px; }
.stat-label { font-size:12px; opacity:0.9; }
</style>