<div class="page-header">
    <h5>➕ ایجاد معامله جدید</h5>
    <a href="<?php echo $config['url']; ?>/deals" class="btn btn-secondary">بازگشت به لیست</a>
</div>

<div class="card">
    <div class="ajax-error alert alert-danger" style="display:none;"></div>
    <form method="POST" action="<?php echo $config['url']; ?>/deals/store" data-ajax="true" id="dealForm">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">عنوان معامله *</label>
                <input type="text" name="title" class="form-input" required placeholder="مثال: تور استانبول">
            </div>
            <div class="form-group">
                <label class="form-label">مبلغ (تومان) *</label>
                <input type="number" name="amount" class="form-input" required placeholder="مثلاً 5000000" min="1000" step="1000">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">پایپ لاین *</label>
                <select name="pipeline_id" class="form-select" id="dealPipelineSelect" required>
                    <option value="">انتخاب پایپ لاین</option>
                    <?php foreach ($pipelines as $p): ?>
                    <option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">مرحله *</label>
                <select name="stage_id" class="form-select" id="dealStageSelect" required>
                    <option value="">ابتدا پایپ لاین را انتخاب کنید</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">مخاطب</label>
                <div class="d-flex gap-8" style="flex-direction:column;">
                    <div class="d-flex gap-8" style="width:100%;">
                        <input type="text" class="form-input" id="contactSearch" placeholder="🔍 جستجوی مخاطب با نام یا شماره تماس..." style="flex:1;" autocomplete="off">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('newContactModal')">➕ جدید</button>
                    </div>
                    <select name="contact_id" class="form-input" id="contactSelect" style="width:100%;" size="4">
                        <option value="">انتخاب مخاطب موجود</option>
                        <?php foreach ($contacts as $c): ?>
                        <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->full_name . ' - ' . $c->phone); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">مسئول</label>
                <select name="assigned_to" class="form-input">
                    <option value="">انتخاب مسئول</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>" <?php echo (\Core\Auth::id() == $u->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">🎯 نحوه آشنایی</label>
            <select name="source" class="form-input">
                <option value="">انتخاب کنید</option>
                <?php foreach ($sources as $s): ?>
                <option value="<?php echo htmlspecialchars($s->name); ?>"><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">توضیحات (با # تگ‌گذاری کنید)</label>
            <textarea name="description" class="form-textarea" rows="3" placeholder="توضیحات معامله...&#10;مثال: تور نوروزی استانبول #تور #نوروز #استانبول"></textarea>
            <div class="form-hint">از <strong>#</strong> برای دسته‌بندی استفاده کنید. مثال: <code>#تور #ویژه #نوروز</code></div>
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">

        <h5 style="font-weight:bold;margin-bottom:15px;">📅 برنامه‌ریزی فعالیت (اختیاری)</h5>
        <p style="font-size:13px;color:var(--gray-500);margin-bottom:12px;">یک فعالیت/یادآوری برای این معامله تنظیم کنید.</p>

        <div class="form-row">
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
                <label class="form-label">موضوع فعالیت</label>
                <input type="text" name="activity_subject" class="form-input" placeholder="مثال: پیگیری مدارک">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">تاریخ و ساعت فعالیت</label>
                <input type="datetime-local" name="activity_date" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">یادآوری (اختیاری)</label>
                <input type="datetime-local" name="reminder_at" class="form-input">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">توضیحات فعالیت</label>
            <textarea name="activity_description" class="form-textarea" rows="2" placeholder="توضیحات بیشتر درباره این فعالیت..."></textarea>
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">

        <button type="submit" class="btn btn-primary btn-lg">✅ ایجاد معامله</button>
        <button type="reset" class="btn btn-secondary">🔄 پاک کردن فرم</button>
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
                <div class="form-group">
                    <label class="form-label">🎯 نحوه آشنایی</label>
                    <select name="source" class="form-input">
                        <option value="">انتخاب کنید</option>
                        <?php 
                        $db = \Core\Database::getInstance();
                        $sources = $db->fetchAll("SELECT id, name, icon FROM deal_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
                        foreach ($sources as $s): 
                        ?>
                        <option value="<?php echo htmlspecialchars($s->name); ?>"><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                        <?php endforeach; ?>
                    </select>
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
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo $config['url']; ?>/pipelines/' + pipelineId + '/stages', true);
    xhr.onload = function() {
        try {
            var data = JSON.parse(xhr.responseText);
            stageSelect.innerHTML = '<option value="">انتخاب مرحله</option>';
            if (data.success && data.stages) {
                data.stages.forEach(function(s) {
                    stageSelect.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
                });
            } else {
                stageSelect.innerHTML = '<option value="">هیچ مرحله‌ای یافت نشد</option>';
            }
        } catch(e) {
            stageSelect.innerHTML = '<option value="">خطا در بارگذاری</option>';
        }
    };
    xhr.onerror = function() {
        stageSelect.innerHTML = '<option value="">خطا در ارتباط</option>';
    };
    xhr.send();
});
</script>