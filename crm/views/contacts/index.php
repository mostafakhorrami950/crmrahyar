<?php $config = $GLOBALS['app_config']; ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>مدیریت مخاطبان</h5>
    <div class="d-flex flex-wrap gap-2">
        <?php if (\Core\Auth::hasPermission('contacts.create')): ?>
        <a href="<?php echo $config['url']; ?>/contacts/import" class="btn btn-outline-secondary btn-sm"><i class="bi bi-upload me-1"></i>ایمپورت</a>
        <?php endif; ?>
        <?php if (\Core\Auth::hasPermission('contacts.view')): ?>
        <a href="<?php echo $config['url']; ?>/export/contacts" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>اکسپورت</a>
        <?php endif; ?>
        <?php if (\Core\Auth::hasPermission('contacts.create')): ?>
        <a href="<?php echo $config['url']; ?>/contacts/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>مخاطب جدید</a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="stat-box-gradient" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <div class="stat-value"><?php echo count($contacts); ?></div>
            <div class="stat-label">کل مخاطبان</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="stat-box-gradient" style="background: linear-gradient(135deg, #10B981, #059669);">
            <?php $totalPurchases = 0; foreach ($contacts as $c) $totalPurchases += $c->total_purchases ?? 0; ?>
            <div class="stat-value"><?php echo number_format($totalPurchases); ?></div>
            <div class="stat-label"><i class="bi bi-cash me-1"></i>مجموع خرید موفق</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="stat-box-gradient" style="background: linear-gradient(135deg, #3B82F6, #2563EB);">
            <?php $totalDeals = 0; foreach ($contacts as $c) $totalDeals += $c->deals_count ?? 0; ?>
            <div class="stat-value"><?php echo number_format($totalDeals); ?></div>
            <div class="stat-label"><i class="bi bi-briefcase me-1"></i>مجموع معاملات</div>
        </div>
    </div>
</div>

<!-- Search & Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label text-muted small"><i class="bi bi-search me-1"></i>جستجو</label>
                    <input type="text" name="search" class="form-control" placeholder="نام، شماره، ایمیل، شرکت..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label text-muted small"><i class="bi bi-folder me-1"></i>دسته‌بندی</label>
                    <select name="category_id" class="form-select">
                        <option value="">همه</option>
                        <option value="0" <?php echo $selectedCategory === '0' ? 'selected' : ''; ?>>بدون دسته‌بندی</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>" <?php echo $selectedCategory == $cat->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label text-muted small"><i class="bi bi-telephone me-1"></i>وضعیت تلفن</label>
                    <select name="has_phone" class="form-select">
                        <option value="">همه</option>
                        <option value="1" <?php echo $selectedHasPhone === '1' ? 'selected' : ''; ?>>دارای تلفن</option>
                        <option value="0" <?php echo $selectedHasPhone === '0' ? 'selected' : ''; ?>>بدون تلفن</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label text-muted small">از تاریخ</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                </div>
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label text-muted small">تا تاریخ</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                </div>
                <div class="col-12 col-md-12 col-lg-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i></button>
                    <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Info & Sort -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
    <span class="text-muted small"><i class="bi bi-bar-chart me-1"></i>نمایش <?php echo number_format(($page-1)*$perPage+1); ?>-<?php echo number_format(min($page*$perPage, $total)); ?> از <?php echo number_format($total); ?> مخاطب</span>
    <div class="d-flex gap-1 align-items-center">
        <span class="text-muted small">مرتب‌سازی:</span>
        <?php 
        $sorts = ['created_at'=>'تاریخ', 'full_name'=>'نام', 'company'=>'شرکت'];
        foreach ($sorts as $skey => $slabel): 
            $newDir = ($sortBy === $skey && $sortDir === 'DESC') ? 'ASC' : 'DESC';
        ?>
        <a href="?<?php echo $baseQs ? $baseQs.'&' : ''; ?>sort=<?php echo $skey; ?>&dir=<?php echo $newDir; ?>" 
           class="badge <?php echo $sortBy === $skey ? 'bg-primary' : 'bg-light text-dark'; ?> text-decoration-none">
            <?php echo $slabel; ?> <?php echo $sortBy === $skey ? ($sortDir === 'ASC' ? '↑' : '↓') : ''; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div id="bulkBar" class="alert alert-dark d-none align-items-center justify-content-between py-2 mb-2">
    <span id="bulkCount" class="text-white">۰ مورد انتخاب شده</span>
    <div class="d-flex gap-2">
        <button onclick="bulkDelete('contacts')" class="btn btn-danger btn-sm"><i class="bi bi-trash me-1"></i>حذف</button>
        <button onclick="clearSelection()" class="btn btn-secondary btn-sm"><i class="bi bi-x-lg me-1"></i>لغو</button>
    </div>
</div>

<!-- Contacts Table -->
<?php if (empty($contacts)): ?>
<div class="card">
    <div class="empty-state">
        <div class="empty-icon">👥</div>
        <h5>مخاطبی یافت نشد</h5>
        <p><?php echo $search ? 'نتیجه‌ای برای «' . htmlspecialchars($search) . '» یافت نشد.' : 'اولین مخاطب را ایجاد کنید.'; ?></p>
        <a href="<?php echo $config['url']; ?>/contacts/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>ایجاد مخاطب</a>
    </div>
</div>
<?php else: ?>
<div class="card p-0">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="selectAll" onchange="toggleAll(this)" class="form-check-input"></th>
                    <th>مخاطب</th>
                    <th>تماس</th>
                    <th class="d-none d-md-table-cell">شرکت</th>
                    <th class="d-none d-md-table-cell">دسته‌بندی</th>
                    <th class="d-none d-lg-table-cell">معاملات</th>
                    <th class="d-none d-lg-table-cell">مبلغ خرید</th>
                    <th class="d-none d-lg-table-cell">تاریخ</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c): ?>
                <tr data-id="<?php echo $c->id; ?>">
                    <td><input type="checkbox" class="row-check form-check-input" value="<?php echo $c->id; ?>" onchange="updateBulkBar()"></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff;font-weight:700;flex-shrink:0;">
                                <?php echo mb_substr($c->full_name, 0, 1); ?>
                            </div>
                            <div>
                                <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $c->id; ?>" class="fw-semibold text-dark text-decoration-none" style="font-size:14px;">
                                    <?php echo htmlspecialchars($c->full_name); ?>
                                </a>
                                <?php if ($c->email): ?>
                                <div class="text-muted" style="font-size:11px;"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($c->email); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($c->phone): ?>
                        <span dir="ltr" class="small"><?php echo htmlspecialchars($c->phone); ?></span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                        <?php if ($c->company_phone): ?>
                        <div class="text-muted" style="font-size:11px;" dir="ltr"><i class="bi bi-building me-1"></i><?php echo htmlspecialchars($c->company_phone); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <?php echo $c->company ? '<i class="bi bi-building me-1"></i>' . htmlspecialchars($c->company) : '<span class="text-muted">-</span>'; ?>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <?php if (!empty($c->category_name)): ?>
                        <span class="badge" style="background:<?php echo htmlspecialchars($c->category_color ?? '#6B7280'); ?>;color:white;font-size:11px;">
                            <?php echo htmlspecialchars($c->category_name); ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-lg-table-cell text-center">
                        <span class="fw-bold text-primary fs-6"><?php echo $c->deals_count; ?></span>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <?php if ($c->total_purchases > 0): ?>
                        <span class="text-success fw-semibold small"><?php echo number_format($c->total_purchases); ?></span>
                        <small class="text-muted" style="font-size:10px;">تومان</small>
                        <?php else: ?>
                        <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <small class="text-muted"><?php echo \Core\JDate::displayDate($c->created_at); ?></small>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $c->id; ?>" class="btn btn-sm btn-outline-primary" title="مشاهده"><i class="bi bi-eye"></i></a>
                            <?php if (\Core\Auth::hasPermission('contacts.edit')): ?>
                            <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $c->id; ?>" class="btn btn-sm btn-outline-secondary" title="ویرایش"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                            <?php if (\Core\Auth::hasPermission('contacts.delete')): ?>
                            <form method="POST" action="<?php echo $config['url']; ?>/contacts/delete/<?php echo $c->id; ?>" class="d-inline" onsubmit="return confirm('آیا از حذف «<?php echo htmlspecialchars($c->full_name, ENT_QUOTES); ?>» اطمینان دارید؟')">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف"><i class="bi bi-trash"></i></button>
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

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="d-flex justify-content-center mt-3">
    <ul class="pagination pagination-sm flex-wrap">
        <?php $qsPrefix = $baseQs ? $baseQs.'&' : ''; ?>
        
        <?php if ($page > 1): ?>
        <li class="page-item"><a class="page-link" href="?<?php echo $qsPrefix; ?>page=1"><i class="bi bi-chevron-double-right"></i></a></li>
        <li class="page-item"><a class="page-link" href="?<?php echo $qsPrefix; ?>page=<?php echo $page-1; ?>"><i class="bi bi-chevron-right"></i></a></li>
        <?php endif; ?>
        
        <?php 
        $start = max(1, $page - 3);
        $end = min($totalPages, $page + 3);
        if ($start > 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        for ($i = $start; $i <= $end; $i++): 
        ?>
        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
            <a class="page-link" href="?<?php echo $qsPrefix; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; 
        if ($end < $totalPages) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        ?>
        
        <?php if ($page < $totalPages): ?>
        <li class="page-item"><a class="page-link" href="?<?php echo $qsPrefix; ?>page=<?php echo $page+1; ?>"><i class="bi bi-chevron-left"></i></a></li>
        <li class="page-item"><a class="page-link" href="?<?php echo $qsPrefix; ?>page=<?php echo $totalPages; ?>"><i class="bi bi-chevron-double-left"></i></a></li>
        <?php endif; ?>
    </ul>
</nav>
<div class="text-center text-muted small">صفحه <?php echo $page; ?> از <?php echo $totalPages; ?></div>
<?php endif; ?>