<?php $config = $GLOBALS['app_config']; $db = \Core\Database::getInstance(); ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>ایجاد معامله جدید</h5>
    <a href="<?php echo $config['url']; ?>/deals" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="card mx-auto" style="max-width:850px;">
    <div class="card-body p-3 p-md-4">
        <div class="ajax-error alert alert-danger d-none"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/deals/store" data-ajax="true" id="dealForm">
            <div class="row g-3">
                <!-- Title + Amount -->
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-pencil me-1"></i>عنوان معامله <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="مثال: تور استانبول عید نوروز">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-cash me-1"></i>مبلغ (تومان)</label>
                    <input type="text" name="amount" class="form-control" placeholder="مثلاً 5,000,000" dir="ltr" style="text-align:left;" oninput="formatAmountInput(this)">
                </div>

                <!-- Pipeline + Stage -->
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-arrow-repeat me-1"></i>پایپ لاین <span class="text-danger">*</span></label>
                    <select name="pipeline_id" class="form-select" id="dealPipelineSelect" required>
                        <option value="">انتخاب پایپ لاین</option>
                        <?php foreach ($pipelines as $p): ?>
                        <option value="<?php echo $p->id; ?>" <?php echo ($p->is_default ?? 0) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-geo-alt me-1"></i>مرحله <span class="text-danger">*</span></label>
                    <select name="stage_id" class="form-select" id="dealStageSelect" required>
                        <option value="">ابتدا پایپ لاین را انتخاب کنید</option>
                        <?php foreach ($stages as $s): ?>
                        <option value="<?php echo $s->id; ?>"><?php echo htmlspecialchars($s->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Contact + Assigned -->
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-person me-1"></i>مخاطب</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="contactSearch" placeholder="🔍 جستجو با نام یا شماره..." autocomplete="off">
                        <button type="button" class="btn btn-primary" onclick="new bootstrap.Modal(document.getElementById('newContactModal')).show()"><i class="bi bi-plus"></i></button>
                    </div>
                    <select name="contact_id" class="form-select" id="contactSelect" size="4">
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
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-person-badge me-1"></i>مسئول</label>
                    <div class="bg-light rounded p-3 text-primary fw-semibold">
                        <i class="bi bi-person me-1"></i><?php echo htmlspecialchars(\Core\Auth::user()->full_name ?? 'شما'); ?> (خودتان)
                    </div>
                    <?php endif; ?>
                    
                    <label class="form-label text-muted small fw-medium mt-3"><i class="bi bi-crosshair me-1"></i>نحوه آشنایی</label>
                    <select name="source" class="form-select">
                        <option value="">انتخاب کنید</option>
                        <?php foreach ($sources as $s): ?>
                        <option value="<?php echo htmlspecialchars($s->name); ?>"><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Loss Reason -->
                <div class="col-12">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-emoji-frown me-1"></i>دلیل شکست (در صورت عدم موفقیت)</label>
                    <select name="loss_reason_id" class="form-select" id="lossReasonSelect">
                        <option value="">ناموفق نبوده / انتخاب کنید</option>
                        <?php
                        $lossReasons = $db->fetchAll("SELECT id, name, icon FROM deal_loss_reasons WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
                        foreach ($lossReasons as $lr):
                        ?>
                        <option value="<?php echo $lr->id; ?>"><?php echo htmlspecialchars($lr->icon . ' ' . $lr->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="lossReasonNoteWrap" class="d-none mt-2">
                        <textarea name="loss_reason_note" class="form-control" rows="2" placeholder="توضیحات تکمیلی درباره دلیل شکست..."></textarea>
                    </div>
                </div>

                <!-- Description -->
                <div class="col-12">
                    <label class="form-label text-muted small fw-medium"><i class="bi bi-card-text me-1"></i>توضیحات</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="توضیحات معامله...&#10;از # برای تگ‌گذاری استفاده کنید: #تور #نوروز #استانبول"></textarea>
                </div>
            </div>

            <!-- Activity Section -->
            <div class="accordion mt-3" id="activityAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold small" type="button" data-bs-toggle="collapse" data-bs-target="#activityCollapse">
                            <i class="bi bi-calendar-plus me-2"></i>برنامه‌ریزی فعالیت (اختیاری)
                        </button>
                    </h2>
                    <div id="activityCollapse" class="accordion-collapse collapse" data-bs-parent="#activityAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label text-muted small">نوع فعالیت</label>
                                    <select name="activity_type" class="form-select">
                                        <option value="">بدون فعالیت</option>
                                        <option value="call"><i class="bi bi-telephone"></i> تماس تلفنی</option>
                                        <option value="meeting">🤝 جلسه</option>
                                        <option value="follow_up">📌 پیگیری</option>
                                        <option value="email">📧 ارسال ایمیل</option>
                                        <option value="note">📝 یادداشت</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label text-muted small">موضوع</label>
                                    <input type="text" name="activity_subject" class="form-control" placeholder="مثال: پیگیری مدارک">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label text-muted small">تاریخ و ساعت</label>
                                    <input type="datetime-local" name="activity_date" class="form-control">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label text-muted small">یادآوری</label>
                                    <input type="datetime-local" name="reminder_at" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">توضیحات فعالیت</label>
                                    <textarea name="activity_description" class="form-control" rows="2" placeholder="توضیحات بیشتر..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-1"></i>ایجاد معامله</button>
                <button type="reset" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>پاک کردن</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: New Contact -->
<div class="modal fade" id="newContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>مخاطب جدید</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="ajax-error alert alert-danger d-none mb-0 rounded-0"></div>
            <form method="POST" action="<?php echo $config['url']; ?>/contacts/store" data-ajax="true">
                <div class="modal-body">
                    <input type="hidden" name="quick" value="1">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small">نام و نام خانوادگی <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required placeholder="مثال: سعید محمدی">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small"><i class="bi bi-phone me-1"></i>شماره تماس <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" required placeholder="0912xxxxxxx" dir="ltr" style="text-align:left;">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small"><i class="bi bi-building me-1"></i>تلفن شرکت</label>
                            <input type="text" name="company_phone" class="form-control" placeholder="021xxxxxxxx" dir="ltr" style="text-align:left;">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small"><i class="bi bi-envelope me-1"></i>ایمیل</label>
                            <input type="email" name="email" class="form-control" placeholder="example@mail.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small"><i class="bi bi-folder me-1"></i>دسته‌بندی</label>
                            <select name="category_id" class="form-select">
                                <?php 
                                $categoriesList = $db->fetchAll("SELECT id, name, color FROM contact_categories ORDER BY sort_order ASC, name ASC");
                                $defaultCat = $db->fetch("SELECT id FROM contact_categories WHERE is_default = 1");
                                foreach ($categoriesList as $cat): 
                                ?>
                                <option value="<?php echo $cat->id; ?>" <?php echo ($defaultCat && $cat->id == $defaultCat->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted small"><i class="bi bi-crosshair me-1"></i>نحوه آشنایی</label>
                            <select name="source" class="form-select">
                                <option value="">انتخاب کنید</option>
                                <?php 
                                $sourcesList = $db->fetchAll("SELECT id, name, icon FROM deal_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
                                foreach ($sourcesList as $s): 
                                ?>
                                <option value="<?php echo htmlspecialchars($s->name); ?>"><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره و انتخاب</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Contact search with live filter
document.getElementById('contactSearch')?.addEventListener('input', function() {
    var select = document.getElementById('contactSelect');
    var query = this.value.trim().toLowerCase();
    for (var i = 0; i < select.options.length; i++) {
        var opt = select.options[i];
        if (!query || opt.textContent.toLowerCase().indexOf(query) !== -1) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    }
});

// Pipeline -> Stages loading
document.getElementById('dealPipelineSelect')?.addEventListener('change', function() {
    var pipelineId = this.value;
    var stageSelect = document.getElementById('dealStageSelect');
    stageSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';
    
    fetch('<?php echo $config['url']; ?>/pipelines/' + pipelineId + '/stages')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        stageSelect.innerHTML = '<option value="">انتخاب مرحله</option>';
        if (data.success && data.stages) {
            data.stages.forEach(function(s) {
                stageSelect.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
            });
        } else {
            stageSelect.innerHTML = '<option value="">مرحله‌ای یافت نشد</option>';
        }
    })
    .catch(function() {
        stageSelect.innerHTML = '<option value="">خطا در بارگذاری</option>';
    });
});

// Format amount input
function formatAmountInput(el) {
    var v = el.value.replace(/[^\d]/g, '');
    if (v) el.value = parseInt(v).toLocaleString('en-US');
}

// Show/hide loss reason note
document.getElementById('lossReasonSelect')?.addEventListener('change', function() {
    document.getElementById('lossReasonNoteWrap').classList.toggle('d-none', !this.value);
});
</script>