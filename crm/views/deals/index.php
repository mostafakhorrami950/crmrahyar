<div class="page-header">
    <h5>💼 مدیریت معاملات</h5>
    <?php if (\Core\Auth::hasPermission('deals.create')): ?>
    <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-primary">➕ معامله جدید</a>
    <?php endif; ?>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-label">کل معاملات</div>
        <div class="stat-value"><?php echo count($deals); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">💰 مجموع ارزش</div>
        <div class="stat-value" style="color:var(--primary);font-size:18px;">
            <?php 
            $total = 0;
            foreach ($deals as $d) $total += $d->amount;
            echo number_format($total);
            ?>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filter-section card">
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="text" name="search" class="form-input" style="flex:2;min-width:150px;" placeholder="🔍 جستجو در معاملات..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="pipeline_id" class="form-input" style="flex:1;min-width:120px;">
            <option value="">همه پایپ لاین‌ها</option>
            <?php foreach ($pipelines as $p): ?>
            <option value="<?php echo $p->id; ?>" <?php echo $selectedPipeline == $p->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="stage_id" class="form-input" style="flex:1;min-width:120px;">
            <option value="">همه مراحل</option>
            <?php foreach ($stages as $s): ?>
            <option value="<?php echo $s->id; ?>" <?php echo $selectedStage == $s->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->name); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="assigned_to" class="form-input" style="flex:1;min-width:120px;">
            <option value="">همه کاربران</option>
            <?php foreach ($users as $u): ?>
            <option value="<?php echo $u->id; ?>" <?php echo $selectedAssigned == $u->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="form-input" style="flex:0.5;min-width:100px;">
            <option value="">همه</option>
            <option value="open" <?php echo $selectedStatus == 'open' ? 'selected' : ''; ?>>باز</option>
            <option value="won" <?php echo $selectedStatus == 'won' ? 'selected' : ''; ?>>موفق</option>
            <option value="lost" <?php echo $selectedStatus == 'lost' ? 'selected' : ''; ?>>ناموفق</option>
        </select>
        <button type="submit" class="btn btn-primary">🔍 جستجو</button>
    </form>
</div>

<div class="card" style="padding:0;">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>مخاطب</th>
                    <th>مرحله</th>
                    <th>مبلغ</th>
                    <th>تاریخ</th>
                    <th>وضعیت</th>
                    <th style="width:160px;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deals)): ?>
                <tr><td colspan="7" class="text-center py-4" style="color:var(--gray-500);">هیچ معامله‌ای یافت نشد.</td></tr>
                <?php else: ?>
                <?php foreach ($deals as $deal): ?>
                <tr>
                    <td>
                        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" style="color:var(--primary);font-weight:500;">
                            <?php echo htmlspecialchars(mb_substr($deal->title, 0, 30)); ?>
                        </a>
                    </td>
                    <td style="font-size:13px;">
                        <?php echo htmlspecialchars($deal->contact_name ?? '-'); ?>
                        <?php if ($deal->contact_phone): ?>
                        <br><small style="color:var(--gray-500);"><?php echo htmlspecialchars($deal->contact_phone); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-primary" style="background:<?php echo $deal->stage_color; ?>20;color:<?php echo $deal->stage_color; ?>;">
                            <?php echo htmlspecialchars($deal->stage_name); ?>
                        </span>
                    </td>
                    <td class="amount-value"><?php echo number_format($deal->amount); ?></td>
                    <td style="font-size:12px;color:var(--gray-500);"><?php echo date('Y/m/d', strtotime($deal->created_at)); ?></td>
                    <td>
                        <?php if ($deal->is_won): ?>
                        <span class="badge badge-success">✅ موفق</span>
                        <?php elseif ($deal->is_lost): ?>
                        <span class="badge badge-danger">❌ ناموفق</span>
                        <?php else: ?>
                        <span class="badge badge-warning">⏳ در جریان</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-primary btn-sm" title="مشاهده">👁️</a>
                            <?php if (\Core\Auth::hasPermission('deals.edit')): ?>
                            <button type="button" class="btn btn-secondary btn-sm" title="ویرایش سریع" onclick="quickEdit(<?php echo $deal->id; ?>)">✏️</button>
                            <?php endif; ?>
                            <?php if (\Core\Auth::hasPermission('deals.delete')): ?>
                            <form method="POST" action="<?php echo $config['url']; ?>/deals/delete/<?php echo $deal->id; ?>" style="display:inline;" onsubmit="return confirm('آیا از حذف این معامله اطمینان دارید؟')">
                                <button type="submit" class="btn btn-danger btn-sm" title="حذف">🗑️</button>
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
<div class="modal-overlay" id="quickEditModal">
    <div class="modal-box" style="max-width:600px;">
        <div class="modal-header">
            <h5 class="modal-title">✏️ ویرایش سریع معامله</h5>
            <button type="button" class="modal-close" onclick="closeModal('quickEditModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="" data-ajax="true" id="quickEditForm">
            <input type="hidden" name="quick_edit" value="1">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">عنوان</label>
                        <input type="text" name="title" class="form-input" required id="qe_title">
                    </div>
                    <div class="form-group">
                        <label class="form-label">مبلغ (تومان)</label>
                        <input type="number" name="amount" class="form-input" required id="qe_amount">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">مرحله</label>
                        <select name="stage_id" class="form-input" id="qe_stage">
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">مخاطب</label>
                        <select name="contact_id" class="form-input" id="qe_contact">
                            <option value="">بدون مخاطب</option>
                            <?php 
                            $allContacts = \Core\Database::getInstance()->fetchAll("SELECT id, full_name, phone FROM contacts ORDER BY full_name");
                            foreach ($allContacts as $c): 
                            ?>
                            <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->full_name . ' - ' . $c->phone); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">وضعیت</label>
                    <select name="deal_status" class="form-input" id="qe_status">
                        <option value="open">⏳ در جریان</option>
                        <option value="won">✅ موفق</option>
                        <option value="lost">❌ ناموفق</option>
                    </select>
                </div>
                <div class="form-group" id="lostReasonDiv" style="display:none;">
                    <label class="form-label">دلیل عدم موفقیت</label>
                    <textarea name="lost_reason" class="form-textarea" rows="2" id="qe_lost_reason" placeholder="دلیل عدم موفقیت را وارد کنید..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">✅ ذخیره تغییرات</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('quickEditModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script>
// Quick edit function
function quickEdit(id) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo $config['url']; ?>/deals/view/' + id, true);
    xhr.onload = function() {
        // Set form action
        document.getElementById('quickEditForm').action = '<?php echo $config['url']; ?>/deals/update/' + id;
        
        // We need the deal data - fetch from a simple endpoint
        var xhr2 = new XMLHttpRequest();
        xhr2.open('GET', '<?php echo $config['url']; ?>/deals/get-data/' + id, true);
        xhr2.onload = function() {
            try {
                var d = JSON.parse(xhr2.responseText);
                if (d.success) {
                    document.getElementById('qe_title').value = d.deal.title;
                    document.getElementById('qe_amount').value = d.deal.amount;
                    
                    // Load stages for this pipeline
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
                    openModal('quickEditModal');
                }
            } catch(e) { alert('خطا در بارگذاری اطلاعات'); }
        };
        xhr2.send();
    };
    xhr.send();
}

// Show/hide lost reason
document.getElementById('qe_status')?.addEventListener('change', function() {
    document.getElementById('lostReasonDiv').style.display = this.value === 'lost' ? 'block' : 'none';
});
</script>