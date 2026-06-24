<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-briefcase me-2"></i>مدیریت معاملات</h5>
    <?php if (\Core\Auth::hasPermission('deals.create')): ?>
    <button type="button" class="btn btn-primary btn-sm" onclick="new bootstrap.Modal(document.getElementById('quickDealModal')).show()"><i class="bi bi-lightning me-1"></i>افزودن سریع</button>
    <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>معامله جدید</a>
    <?php endif; ?>
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">کل معاملات</div>
            <div class="stat-value"><?php echo count($deals); ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><i class="bi bi-cash me-1"></i>مجموع ارزش</div>
            <div class="stat-value" style="color:var(--primary);font-size:18px;">
                <?php 
                $total = 0;
                foreach ($deals as $d) $total += $d->amount;
                echo number_format($total);
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?php echo parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <input type="text" name="search" class="form-control" placeholder="🔍 جستجو در معاملات..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-6 col-md-2">
                    <select name="pipeline_id" class="form-select">
                        <option value="">همه پایپ لاین‌ها</option>
                        <?php foreach ($pipelines as $p): ?>
                        <option value="<?php echo $p->id; ?>" <?php echo $selectedPipeline == $p->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="stage_id" class="form-select">
                        <option value="">همه مراحل</option>
                        <?php foreach ($stages as $s): ?>
                        <option value="<?php echo $s->id; ?>" <?php echo $selectedStage == $s->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="assigned_to" class="form-select">
                        <option value="">همه کاربران</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u->id; ?>" <?php echo $selectedAssigned == $u->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">همه</option>
                        <option value="open" <?php echo $selectedStatus == 'open' ? 'selected' : ''; ?>>باز</option>
                        <option value="won" <?php echo $selectedStatus == 'won' ? 'selected' : ''; ?>>موفق</option>
                        <option value="lost" <?php echo $selectedStatus == 'lost' ? 'selected' : ''; ?>>ناموفق</option>
                    </select>
                </div>
                <div class="col-12 col-md-12 col-lg-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div id="bulkBar" class="alert alert-dark d-none align-items-center justify-content-between py-2 mb-2">
    <span id="bulkCount" class="text-white">۰ مورد انتخاب شده</span>
    <div class="d-flex gap-2">
        <button onclick="bulkDelete('deals')" class="btn btn-danger btn-sm"><i class="bi bi-trash me-1"></i>حذف</button>
        <button onclick="clearSelection()" class="btn btn-secondary btn-sm"><i class="bi bi-x-lg me-1"></i>لغو</button>
    </div>
</div>

<!-- Deals Table -->
<div class="card p-0">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="selectAll" onchange="toggleAll(this)" class="form-check-input"></th>
                    <th>عنوان</th>
                    <th>مخاطب</th>
                    <th class="d-none d-md-table-cell">مرحله</th>
                    <th>مبلغ</th>
                    <th class="d-none d-lg-table-cell">تاریخ</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deals)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">هیچ معامله‌ای یافت نشد.</td></tr>
                <?php else: ?>
                <?php foreach ($deals as $deal): ?>
                <tr data-id="<?php echo $deal->id; ?>">
                    <td><input type="checkbox" class="row-check form-check-input" value="<?php echo $deal->id; ?>" onchange="updateBulkBar()"></td>
                    <td>
                        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="text-primary fw-medium text-decoration-none">
                            <?php echo htmlspecialchars(mb_substr($deal->title, 0, 30)); ?>
                        </a>
                    </td>
                    <td>
                        <span class="small"><?php echo htmlspecialchars($deal->contact_name ?? '-'); ?></span>
                        <?php if ($deal->contact_phone): ?>
                        <br><small class="text-muted" dir="ltr"><?php echo htmlspecialchars($deal->contact_phone); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <span class="badge" style="background:<?php echo $deal->stage_color; ?>20;color:<?php echo $deal->stage_color; ?>;">
                            <?php echo htmlspecialchars($deal->stage_name); ?>
                        </span>
                    </td>
                    <td class="amount-value"><?php echo number_format($deal->amount); ?></td>
                    <td class="d-none d-lg-table-cell"><small class="text-muted"><?php echo \Core\JDate::displayDate($deal->created_at); ?></small></td>
                    <td>
                        <?php if ($deal->is_won): ?>
                        <span class="badge bg-success">✅ موفق</span>
                        <?php elseif ($deal->is_lost): ?>
                        <span class="badge bg-danger">❌ ناموفق</span>
                        <?php else: ?>
                        <span class="badge bg-warning text-dark">⏳ در جریان</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-sm btn-outline-primary" title="مشاهده"><i class="bi bi-eye"></i></a>
                            <?php if (\Core\Auth::hasPermission('deals.edit')): ?>
                            <a href="<?php echo $config['url']; ?>/deals/edit/<?php echo $deal->id; ?>" class="btn btn-sm btn-outline-secondary" title="ویرایش"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                            <?php if (\Core\Auth::hasPermission('deals.delete')): ?>
                            <form method="POST" action="<?php echo $config['url']; ?>/deals/delete/<?php echo $deal->id; ?>" class="d-inline" onsubmit="return confirm('آیا از حذف این معامله اطمینان دارید؟')">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Edit Modal -->
<div class="modal fade" id="quickEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>ویرایش سریع معامله</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="ajax-error alert alert-danger d-none mb-0 rounded-0"></div>
            <form method="POST" action="" data-ajax="true" id="quickEditForm">
                <input type="hidden" name="quick_edit" value="1">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small">عنوان</label>
                            <input type="text" name="title" class="form-control" required id="qe_title">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small">مبلغ (تومان)</label>
                            <input type="number" name="amount" class="form-control" required id="qe_amount">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small">مرحله</label>
                            <select name="stage_id" class="form-select" id="qe_stage"></select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small">مخاطب</label>
                            <select name="contact_id" class="form-select" id="qe_contact">
                                <option value="">بدون مخاطب</option>
                                <?php 
                                $cScope = \Core\Auth::scopeFilter('contacts.view', ['created_by']);
                                $cWhere = $cScope['where'] === '1=1' ? '' : "WHERE {$cScope['where']}";
                                $allContacts = \Core\Database::getInstance()->fetchAll("SELECT id, full_name, phone FROM contacts {$cWhere} ORDER BY full_name", $cScope['params']);
                                foreach ($allContacts as $c): 
                                ?>
                                <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->full_name . ' - ' . $c->phone); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small">وضعیت</label>
                            <select name="deal_status" class="form-select" id="qe_status">
                                <option value="open">⏳ در جریان</option>
                                <option value="won">✅ موفق</option>
                                <option value="lost">❌ ناموفق</option>
                            </select>
                        </div>
                        <div class="col-12 d-none" id="lostReasonDiv">
                            <label class="form-label text-muted small">دلیل عدم موفقیت</label>
                            <textarea name="lost_reason" class="form-control" rows="2" id="qe_lost_reason" placeholder="دلیل عدم موفقیت را وارد کنید..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function quickEdit(id) {
    fetch('<?php echo $config['url']; ?>/deals/get-data/' + id)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                document.getElementById('quickEditForm').action = '<?php echo $config['url']; ?>/deals/update/' + id;
                document.getElementById('qe_title').value = d.deal.title;
                document.getElementById('qe_amount').value = d.deal.amount;
                var sSelect = document.getElementById('qe_stage');
                sSelect.innerHTML = '';
                if (d.stages) {
                    d.stages.forEach(function(s) {
                        var opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.name;
                        if (s.id == d.deal.stage_id) opt.selected = true;
                        sSelect.appendChild(opt);
                    });
                }
                document.getElementById('qe_status').value = d.deal.is_won ? 'won' : (d.deal.is_lost ? 'lost' : 'open');
                var modal = new bootstrap.Modal(document.getElementById('quickEditModal'));
                modal.show();
            }
        })
        .catch(function() { alert('خطا در بارگذاری اطلاعات'); });
}

document.getElementById('qe_status')?.addEventListener('change', function() {
    document.getElementById('lostReasonDiv').classList.toggle('d-none', this.value !== 'lost');
});
</script>

<?php include __DIR__ . '/_quick_create_modal.php'; ?>
