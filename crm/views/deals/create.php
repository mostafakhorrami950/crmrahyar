<?php $config = $GLOBALS['app_config']; $db = \Core\Database::getInstance(); ?>
<div class="page-header">
    <h5>➕ ایجاد معامله جدید</h5>
    <a href="<?php echo $config['url']; ?>/deals" class="btn btn-secondary btn-sm">← بازگشت به لیست</a>
</div>

<div class="card" style="padding:28px;max-width:800px;margin:0 auto;">
    <div class="ajax-error alert alert-danger" style="display:none;"></div>
    <form method="POST" action="<?php echo $config['url']; ?>/deals/store" data-ajax="true" id="dealForm">
        
        <!-- Row 1: Title + Amount -->
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">📝 عنوان معامله *</label>
                <input type="text" name="title" class="form-input" required placeholder="مثال: تور استانبول عید نوروز">
            </div>
            <div class="form-group">
                <label class="form-label">💰 مبلغ (تومان)</label>
                <input type="text" name="amount" class="form-input" placeholder="مثلاً 5,000,000" dir="ltr" style="text-align:left;" oninput="formatAmountInput(this)">
            </div>
        </div>

        <!-- Row 2: Pipeline + Stage -->
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">🔄 پایپ لاین *</label>
                <select name="pipeline_id" class="form-select" id="dealPipelineSelect" required>
                    <option value="">انتخاب پایپ لاین</option>
                    <?php foreach ($pipelines as $p): ?>
                    <option value="<?php echo $p->id; ?>" <?php echo ($p->is_default ?? 0) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">📍 مرحله *</label>
                <select name="stage_id" class="form-select" id="dealStageSelect" required>
                    <option value="">ابتدا پایپ لاین را انتخاب کنید</option>
                    <?php foreach ($stages as $s): ?>
                    <option value="<?php echo $s->id; ?>"><?php echo htmlspecialchars($s->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Row 3: Contact + Assigned -->
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">👤 مخاطب</label>
                <div style="display:flex;gap:6px;margin-bottom:6px;">
                    <input type="text" class="form-input" id="contactSearch" placeholder="🔍 جستجو با نام یا شماره..." style="flex:1;" autocomplete="off">
                    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('newContactModal')" style="white-space:nowrap;">➕ جدید</button>
                </div>
                <select name="contact_id" class="form-input" id="contactSelect" size="4" style="width:100%;">
                    <option value="">انتخاب مخاطب</option>
                    <?php foreach ($contacts as $c): ?>
                    <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->full_name . ($c->phone ? ' - ' . $c->phone : '')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <?php if (\Core\Auth::canAccessAll('deals.create')): ?>
                <label class="form-label">👨‍💼 مسئول</label>
                <select name="assigned_to" class="form-input">
                    <option value="">انتخاب مسئول</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>" <?php echo (\Core\Auth::id() == $u->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <input type="hidden" name="assigned_to" value="<?php echo \Core\Auth::id(); ?>">
                <label class="form-label">👨‍💼 مسئول</label>
                <div style="padding:10px 14px;background:var(--gray-50);border-radius:10px;font-size:14px;font-weight:600;color:var(--primary);">
                    👤 <?php echo htmlspecialchars(\Core\Auth::user()->full_name ?? 'شما'); ?> (خودتان)
                </div>
                <?php endif; ?>
                
                <label class="form-label" style="margin-top:16px;">🎯 نحوه آشنایی</label>
                <select name="source" class="form-input">
                    <option value="">انتخاب کنید</option>
                    <?php foreach ($sources as $s): ?>
                    <option value="<?php echo htmlspecialchars($s->name); ?>"><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label class="form-label">📋 توضیحات</label>
            <textarea name="description" class="form-textarea" rows="3" placeholder="توضیحات معامله...&#10;از # برای تگ‌گذاری استفاده کنید: #تور #نوروز #استانبول"></textarea>
        </div>

        <!-- Activity Section -->
        <details style="margin-top:16px;">
            <summary style="cursor:pointer;font-weight:600;font-size:14px;padding:10px;background:var(--gray-50);border-radius:8px;margin-bottom:12px;">📅 برنامه‌ریزی فعالیت (اختیاری)</summary>
            <div class="form-row" style="margin-top:8px;">
                <div class="form-group">
                    <label class="form-label">نوع فعالیت</label>
                    <select name="activity_type" class="form-input">
                        <option value="">بدون فعالیت</option>
                        <option value="call">📞 تماس تلفنی</option>
                        <option value="meeting">🤝 جلسه</option>
                        <option value="follow_up">📌 پیگیری</option>
                        <option value="email">📧 ارسال ایمیل</option>
                        <option value="note">📝 یادداشت</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">موضوع</label>
                    <input type="text" name="activity_subject" class="form-input" placeholder="مثال: پیگیری مدارک">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">تاریخ و ساعت</label>
                    <input type="datetime-local" name="activity_date" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">یادآوری</label>
                    <input type="datetime-local" name="reminder_at" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">توضیحات فعالیت</label>
                <textarea name="activity_description" class="form-textarea" rows="2" placeholder="توضیحات بیشتر..."></textarea>
            </div>
        </details>

        <!-- Submit -->
        <div style="display:flex;gap:8px;margin-top:24px;">
            <button type="submit" class="btn btn-primary btn-lg">✅ ایجاد معامله</button>
            <button type="reset" class="btn btn-secondary">🔄 پاک کردن</button>
        </div>
    </form>
</div>

<!-- Modal: New Contact -->
<div class="modal-overlay" id="newContactModal">
    <div class="modal-box" style="max-width:550px;">
        <div class="modal-header">
            <h5>➕ مخاطب جدید</h5>
            <button type="button" class="modal-close" onclick="closeModal('newContactModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/contacts/store" data-ajax="true">
            <div class="modal-body">
                <input type="hidden" name="quick" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">نام و نام خانوادگی *</label>
                        <input type="text" name="full_name" class="form-input" required placeholder="مثال: سعید محمدی">
                    </div>
                    <div class="form-group">
                        <label class="form-label">📞 شماره تماس *</label>
                        <input type="text" name="phone" class="form-input" required placeholder="0912xxxxxxx" dir="ltr" style="text-align:left;">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">🏢 تلفن شرکت</label>
                        <input type="text" name="company_phone" class="form-input" placeholder="021xxxxxxxx" dir="ltr" style="text-align:left;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">✉️ ایمیل</label>
                        <input type="email" name="email" class="form-input" placeholder="example@mail.com">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">📂 دسته‌بندی</label>
                        <select name="category_id" class="form-input">
                            <?php 
                            $categoriesList = $db->fetchAll("SELECT id, name, color FROM contact_categories ORDER BY sort_order ASC, name ASC");
                            $defaultCat = $db->fetch("SELECT id FROM contact_categories WHERE is_default = 1");
                            foreach ($categoriesList as $cat): 
                            ?>
                            <option value="<?php echo $cat->id; ?>" <?php echo ($defaultCat && $cat->id == $defaultCat->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">🎯 نحوه آشنایی</label>
                        <select name="source" class="form-input">
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
                <button type="submit" class="btn btn-primary">✅ ذخیره و انتخاب</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('newContactModal')">لغو</button>
            </div>
        </form>
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
</script>

<style>
details summary::-webkit-details-marker { display:none; }
details summary::marker { display:none; content:''; }
details[open] summary { background:var(--primary); color:white; }
</style>