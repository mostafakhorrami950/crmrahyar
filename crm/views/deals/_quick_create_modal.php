<?php $config = $GLOBALS['app_config']; $db = \Core\Database::getInstance();
// Load required data if not already available
if (!isset($pipelines)) $pipelines = $db->fetchAll("SELECT * FROM pipelines WHERE is_active = 1");
if (!isset($contacts)) {
    $cScope = \Core\Auth::scopeFilter('contacts.view', ['created_by']);
    $cScopeWhere = $cScope['where'] === '1=1' ? '' : "WHERE {$cScope['where']}";
    $contacts = $db->fetchAll("SELECT id, full_name, phone FROM contacts {$cScopeWhere} ORDER BY full_name", $cScope['params']);
}
if (!isset($users)) $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1");
if (!isset($sources)) $sources = $db->fetchAll("SELECT id, name, icon FROM deal_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
$defaultPipeline = $db->fetch("SELECT id FROM pipelines WHERE is_default = 1");
?>

<!-- Quick Create Deal Modal -->
<div class="modal fade" id="quickDealModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-lightning me-2"></i>افزودن سریع معامله</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo $config['url']; ?>/deals/store" data-ajax="true" id="quickDealForm">
                <div class="modal-body">
                    <div class="ajax-error alert alert-danger d-none mb-3"></div>
                    <div class="row g-3">
                        <!-- Title + Amount -->
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-pencil me-1"></i>عنوان معامله <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="مثال: تور استانبول عید نوروز">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-cash me-1"></i>مبلغ (تومان)</label>
                            <input type="text" name="amount" class="form-control" placeholder="مثلاً 5,000,000" dir="ltr" style="text-align:left;" oninput="qdcFormatAmount(this)">
                        </div>

                        <!-- Pipeline + Stage -->
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-arrow-repeat me-1"></i>پایپ لاین <span class="text-danger">*</span></label>
                            <select name="pipeline_id" class="form-select" id="qdcPipeline" required onchange="qdcLoadStages(this.value)">
                                <option value="">انتخاب پایپ لاین</option>
                                <?php foreach ($pipelines as $p): ?>
                                <option value="<?php echo $p->id; ?>" <?php echo ($p->is_default ?? 0) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-geo-alt me-1"></i>مرحله <span class="text-danger">*</span></label>
                            <select name="stage_id" class="form-select" id="qdcStage" required>
                                <option value="">ابتدا پایپ لاین را انتخاب کنید</option>
                            </select>
                        </div>

                        <!-- Contact + Assigned -->
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-person me-1"></i>مخاطب
                                <?php if (\Core\Auth::hasPermission('contacts.create')): ?>
                                <button type="button" class="btn btn-link text-decoration-none p-0 ms-1" onclick="new bootstrap.Modal(document.getElementById('quickContactModal')).show()" title="افزودن مخاطب جدید"><i class="bi bi-plus-circle text-primary"></i></button>
                                <?php endif; ?>
                            </label>
                            <input type="text" class="form-control mb-1" id="qdcContactSearch" placeholder="🔍 جستجوی مخاطب..." autocomplete="off" oninput="qdcFilterContacts(this.value)">
                            <select name="contact_id" class="form-select" id="qdcContactSelect" size="3">
                                <option value="">انتخاب مخاطب</option>
                                <?php foreach ($contacts as $c): ?>
                                <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->full_name . ($c->phone ? ' - ' . $c->phone : '')); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php if (\Core\Auth::canAccessAll('deals.create')): ?>
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-person-badge me-1"></i>مسئول</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">انتخاب مسئول</option>
                                <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u->id; ?>" <?php echo (\Core\Auth::id() == $u->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input type="hidden" name="assigned_to" value="<?php echo \Core\Auth::id(); ?>">
                            <?php endif; ?>
                            
                            <label class="form-label text-muted small fw-medium mt-2"><i class="bi bi-crosshair me-1"></i>نحوه آشنایی</label>
                            <select name="source" class="form-select">
                                <option value="">انتخاب کنید</option>
                                <?php foreach ($sources as $s): ?>
                                <option value="<?php echo htmlspecialchars($s->name); ?>"><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Travel Info -->
                        <div class="col-12">
                            <div class="bg-light rounded-3 p-3">
                                <h6 class="fw-bold mb-2" style="font-size:13px;"><i class="bi bi-airplane me-2 text-primary"></i>اطلاعات سفر</h6>
                                <div class="row g-2">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label text-muted small">تاریخ ورود / شروع سفر</label>
                                        <input type="date" name="travel_date_from" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label text-muted small">تاریخ خروج / پایان سفر</label>
                                        <input type="date" name="travel_date_to" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label text-muted small">تعداد نفرات</label>
                                        <input type="number" name="passengers_count" class="form-control form-control-sm" min="0" placeholder="2">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-card-text me-1"></i>توضیحات</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="توضیحات... (#تور #نوروز)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-lightning me-1"></i>ایجاد سریع</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">انصراف</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function qdcFormatAmount(el) {
    var v = el.value.replace(/[^\d]/g, '');
    if (v) el.value = parseInt(v).toLocaleString('en-US');
}

function qdcLoadStages(pipelineId) {
    var sel = document.getElementById('qdcStage');
    sel.innerHTML = '<option value="">در حال بارگذاری...</option>';
    fetch('<?php echo $config['url']; ?>/pipelines/' + pipelineId + '/stages')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        sel.innerHTML = '<option value="">انتخاب مرحله</option>';
        if (data.success && data.stages) {
            data.stages.forEach(function(s) {
                sel.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
            });
        }
    })
    .catch(function() { sel.innerHTML = '<option value="">خطا</option>'; });
}

function qdcFilterContacts(q) {
    q = q.trim().toLowerCase();
    var sel = document.getElementById('qdcContactSelect');
    for (var i = 0; i < sel.options.length; i++) {
        sel.options[i].style.display = (!q || sel.options[i].text.toLowerCase().indexOf(q) !== -1) ? '' : 'none';
    }
}

// Auto-load stages for default pipeline on modal show
document.getElementById('quickDealModal')?.addEventListener('shown.bs.modal', function() {
    var pSel = document.getElementById('qdcPipeline');
    if (pSel.value) qdcLoadStages(pSel.value);
});
</script>