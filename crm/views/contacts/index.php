<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>👥 مدیریت مخاطبان</h5>
    <div style="display:flex;gap:8px;">
        <?php if (\Core\Auth::hasPermission('contacts.create')): ?>
        <a href="<?php echo $config['url']; ?>/contacts/import" class="btn btn-secondary">📥 ایمپورت</a>
        <?php endif; ?>
        <?php if (\Core\Auth::hasPermission('contacts.view')): ?>
        <a href="<?php echo $config['url']; ?>/export/contacts" class="btn btn-secondary">📤 اکسپورت</a>
        <?php endif; ?>
        <?php if (\Core\Auth::hasPermission('contacts.create')): ?>
        <a href="<?php echo $config['url']; ?>/contacts/create" class="btn btn-primary">➕ مخاطب جدید</a>
        <?php endif; ?>
    </div>
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

<!-- Advanced Search -->
<div class="card" style="padding:16px;margin-bottom:16px;">
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:2;min-width:200px;">
            <label style="font-size:12px;color:var(--gray-500);display:block;margin-bottom:4px;">🔍 جستجو</label>
            <input type="text" name="search" class="form-input" style="width:100%;" placeholder="نام، شماره، ایمیل، شرکت، کد ملی..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div style="flex:1;min-width:140px;">
            <label style="font-size:12px;color:var(--gray-500);display:block;margin-bottom:4px;">📁 دسته‌بندی</label>
            <select name="category_id" class="form-input" style="width:100%;">
                <option value="">همه</option>
                <option value="0" <?php echo $selectedCategory === '0' ? 'selected' : ''; ?>>بدون دسته‌بندی</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat->id; ?>" <?php echo $selectedCategory == $cat->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex:1;min-width:120px;">
            <label style="font-size:12px;color:var(--gray-500);display:block;margin-bottom:4px;">📞 وضعیت تلفن</label>
            <select name="has_phone" class="form-input" style="width:100%;">
                <option value="">همه</option>
                <option value="1" <?php echo $selectedHasPhone === '1' ? 'selected' : ''; ?>>دارای تلفن</option>
                <option value="0" <?php echo $selectedHasPhone === '0' ? 'selected' : ''; ?>>بدون تلفن</option>
            </select>
        </div>
        <div style="flex:1;min-width:100px;">
            <label style="font-size:12px;color:var(--gray-500);display:block;margin-bottom:4px;">📅 از تاریخ</label>
            <input type="date" name="date_from" class="form-input" style="width:100%;" value="<?php echo $dateFrom; ?>">
        </div>
        <div style="flex:1;min-width:100px;">
            <label style="font-size:12px;color:var(--gray-500);display:block;margin-bottom:4px;">📅 تا تاریخ</label>
            <input type="date" name="date_to" class="form-input" style="width:100%;" value="<?php echo $dateTo; ?>">
        </div>
        <div style="display:flex;gap:6px;">
            <button type="submit" class="btn btn-primary">🔍 فیلتر</button>
            <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-secondary">✖ حذف فیلتر</a>
        </div>
    </form>
</div>

<!-- Results Info & Sort -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
    <span style="font-size:13px;color:var(--gray-600);">📊 نمایش <?php echo number_format(($page-1)*$perPage+1); ?>-<?php echo number_format(min($page*$perPage, $total)); ?> از <?php echo number_format($total); ?> مخاطب</span>
    <div style="display:flex;gap:6px;font-size:12px;">
        <span style="color:var(--gray-500);">مرتب‌سازی:</span>
        <?php 
        $sorts = ['created_at'=>'تاریخ', 'full_name'=>'نام', 'company'=>'شرکت'];
        foreach ($sorts as $skey => $slabel): 
            $newDir = ($sortBy === $skey && $sortDir === 'DESC') ? 'ASC' : 'DESC';
        ?>
        <a href="?<?php echo $baseQs ? $baseQs.'&' : ''; ?>sort=<?php echo $skey; ?>&dir=<?php echo $newDir; ?>" 
           style="padding:4px 10px;border-radius:8px;<?php echo $sortBy === $skey ? 'background:var(--primary);color:#fff;' : 'background:var(--gray-100);color:var(--gray-600);'; ?>text-decoration:none;font-weight:600;">
            <?php echo $slabel; ?> <?php echo $sortBy === $skey ? ($sortDir === 'ASC' ? '↑' : '↓') : ''; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Contacts Grid -->
<!-- Bulk Actions Bar -->
<div id="bulkBar" style="display:none;position:sticky;top:0;z-index:100;background:#1e293b;color:#fff;padding:12px 16px;border-radius:12px;margin-bottom:12px;display:none;align-items:center;justify-content:space-between;">
    <span id="bulkCount">۰ مورد انتخاب شده</span>
    <div style="display:flex;gap:8px;">
        <button onclick="bulkDelete('contacts')" class="btn btn-danger btn-sm">🗑️ حذف انتخاب شده‌ها</button>
        <button onclick="clearSelection()" class="btn btn-secondary btn-sm" style="background:#475569;">✕ لغو انتخاب</button>
    </div>
</div>

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
                        <th style="width:40px;"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
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
                <tr data-id="<?php echo $c->id; ?>">
                    <td data-label=""><input type="checkbox" class="row-check" value="<?php echo $c->id; ?>" onchange="updateBulkBar()"></td>
                    <td data-label="مخاطب">
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
                    <td data-label="تماس">
                        <?php if ($c->phone): ?>
                        <span dir="ltr" style="font-size:13px;"><?php echo htmlspecialchars($c->phone); ?></span>
                        <?php else: ?>
                        <span style="color:var(--gray-300);">-</span>
                        <?php endif; ?>
                        <?php if ($c->company_phone): ?>
                        <div style="font-size:11px;color:var(--gray-400);" dir="ltr">🏢 <?php echo htmlspecialchars($c->company_phone); ?></div>
                        <?php endif; ?>
                    </td>
                    <td data-label="شرکت">
                        <?php echo $c->company ? '<span style="font-size:13px;">🏢 ' . htmlspecialchars($c->company) . '</span>' : '<span style="color:var(--gray-300);">-</span>'; ?>
                    </td>
                    <td data-label="دسته‌بندی">
                        <?php if (!empty($c->category_name)): ?>
                        <span style="background:<?php echo htmlspecialchars($c->category_color ?? '#6B7280'); ?>;color:white;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;">
                            <?php echo htmlspecialchars($c->category_name); ?>
                        </span>
                        <?php else: ?>
                        <span style="color:var(--gray-300);font-size:11px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="معاملات" style="text-align:center;">
                        <span style="font-weight:700;color:var(--primary);font-size:16px;"><?php echo $c->deals_count; ?></span>
                    </td>
                    <td data-label="مبلغ خرید">
                        <?php if ($c->total_purchases > 0): ?>
                        <span style="color:#059669;font-weight:600;font-size:13px;"><?php echo number_format($c->total_purchases); ?></span>
                        <small style="color:var(--gray-400);font-size:10px;">تومان</small>
                        <?php else: ?>
                        <span style="color:var(--gray-300);font-size:12px;">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="تاریخ" style="white-space:nowrap;">
                        <small style="color:var(--gray-500);font-size:12px;"><?php echo \Core\JDate::displayDate($c->created_at); ?></small>
                    </td>
                    <td data-label="عملیات">
                        <div style="display:flex;gap:4px;">
                            <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $c->id; ?>" class="btn btn-sm btn-primary" title="مشاهده">👁️</a>
                            <?php if (\Core\Auth::hasPermission('contacts.edit')): ?>
                            <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $c->id; ?>" class="btn btn-sm btn-secondary" title="ویرایش">✏️</a>
                            <?php endif; ?>
                            <?php if (\Core\Auth::hasPermission('contacts.delete')): ?>
                            <form method="POST" action="<?php echo $config['url']; ?>/contacts/delete/<?php echo $c->id; ?>" style="display:inline;" onsubmit="return confirm('آیا از حذف «<?php echo htmlspecialchars($c->full_name, ENT_QUOTES); ?>» اطمینان دارید؟')">
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">🗑️</button>
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

<?php if ($totalPages > 1): ?>
<!-- Pagination -->
<div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:16px;flex-wrap:wrap;">
    <?php $qsPrefix = $baseQs ? $baseQs.'&' : ''; ?>
    
    <?php if ($page > 1): ?>
    <a href="?<?php echo $qsPrefix; ?>page=1" class="btn btn-sm btn-secondary" title="اول">⏮</a>
    <a href="?<?php echo $qsPrefix; ?>page=<?php echo $page-1; ?>" class="btn btn-sm btn-secondary" title="قبلی">◀</a>
    <?php endif; ?>
    
    <?php 
    $start = max(1, $page - 3);
    $end = min($totalPages, $page + 3);
    if ($start > 1) echo '<span style="color:var(--gray-400);">...</span>';
    for ($i = $start; $i <= $end; $i++): 
    ?>
    <a href="?<?php echo $qsPrefix; ?>page=<?php echo $i; ?>" 
       class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>"
       style="<?php echo $i === $page ? 'font-weight:800;min-width:36px;' : ''; ?>">
        <?php echo $i; ?>
    </a>
    <?php endfor; 
    if ($end < $totalPages) echo '<span style="color:var(--gray-400);">...</span>';
    ?>
    
    <?php if ($page < $totalPages): ?>
    <a href="?<?php echo $qsPrefix; ?>page=<?php echo $page+1; ?>" class="btn btn-sm btn-secondary" title="بعدی">▶</a>
    <a href="?<?php echo $qsPrefix; ?>page=<?php echo $totalPages; ?>" class="btn btn-sm btn-secondary" title="آخر">⏭</a>
    <?php endif; ?>
    
    <span style="font-size:12px;color:var(--gray-500);margin-right:12px;">صفحه <?php echo $page; ?> از <?php echo $totalPages; ?></span>
</div>
<?php endif; ?>

<style>
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; }
.stat-box { color:white; padding:16px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.stat-value { font-weight:800; font-size:24px; }
.stat-label { font-size:12px; opacity:0.9; }
</style>
