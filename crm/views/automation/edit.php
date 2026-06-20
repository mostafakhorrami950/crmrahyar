<?php $config = $GLOBALS['app_config']; 
$triggerConditions = json_decode($rule->trigger_conditions, true) ?: [];
$actionConfig = json_decode($rule->action_config, true) ?: [];
?>
<div class="page-header">
    <h5>✏️ ویرایش قانون: <?php echo htmlspecialchars($rule->name); ?></h5>
    <a href="<?php echo $config['url']; ?>/automation" class="btn btn-secondary">بازگشت</a>
</div>

<!-- Guide -->
<div class="card" style="border-right:4px solid var(--info);background:#f0f9ff;">
    <div class="card-header" style="border:none;margin-bottom:8px;">📖 راهنمای اتوماسیون</div>
    <p style="font-size:13px;color:var(--gray-600);margin-bottom:12px;">اتوماسیون به شما اجازه می‌دهد کارهای تکراری را خودکار کنید. هر قانون از دو بخش تشکیل شده:</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13px;">
        <div style="background:#fff;padding:12px;border-radius:8px;">
            <strong>🔥 ماشه (Trigger):</strong> رویدادی که باعث فعال شدن قانون می‌شود
            <ul style="margin-top:8px;padding-right:16px;list-style:disc;">
                <li><strong>تغییر مرحله:</strong> وقتی معامله به مرحله خاصی منتقل شود</li>
                <li><strong>ایجاد معامله:</strong> وقتی معامله جدیدی ثبت شود</li>
                <li><strong>برد معامله:</strong> وقتی معامله موفق شود</li>
                <li><strong>باخت معامله:</strong> وقتی معامله ناموفق شود</li>
                <li><strong>دریافت پرداخت:</strong> وقتی پرداختی ثبت شود</li>
                <li><strong>مخاطب جدید:</strong> وقتی مخاطب جدیدی اضافه شود</li>
            </ul>
        </div>
        <div style="background:#fff;padding:12px;border-radius:8px;">
            <strong>⚡ اقدام (Action):</strong> کاری که پس از فعال شدن ماشه انجام می‌شود
            <ul style="margin-top:8px;padding-right:16px;list-style:disc;">
                <li><strong>ارسال پیامک:</strong> ارسال پیامک خودکار به مخاطب</li>
                <li><strong>ارسال اعلان:</strong> ارسال اعلان به کاربر مشخص</li>
                <li><strong>ایجاد فعالیت:</strong> ایجاد یادآوری یا فعالیت جدید</li>
                <li><strong>تخصیص کاربر:</strong> اختصاص معامله به کاربر خاص</li>
            </ul>
        </div>
    </div>
    <div style="margin-top:12px;background:#fff;padding:12px;border-radius:8px;font-size:13px;">
        <strong>💡 مثال:</strong> «وقتی معامله‌ای به مرحله <em>پرداخت شده</em> منتقل شد → پیامک تبریک به مشتری ارسال شود»
    </div>
</div>

<div class="card" style="max-width:800px;">
    <form method="POST" action="<?php echo $config['url']; ?>/automation/update/<?php echo $rule->id; ?>" id="automationForm">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">📝 نام قانون *</label>
                <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($rule->name); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">📄 توضیحات</label>
                <input type="text" name="description" class="form-input" value="<?php echo htmlspecialchars($rule->description ?? ''); ?>">
            </div>
        </div>

        <div style="background:var(--gray-50);padding:16px;border-radius:8px;margin-bottom:16px;">
            <h6 style="margin-bottom:12px;">🔥 بخش ۱: ماشه (Trigger) - کی فعال شود؟</h6>
            <div class="form-group">
                <label class="form-label">رویداد ماشه *</label>
                <select name="trigger_type" class="form-select" required id="triggerType">
                    <option value="">— انتخاب کنید —</option>
                    <?php foreach (['stage_change'=>'🔄 تغییر مرحله معامله','deal_created'=>'💼 ایجاد معامله جدید','deal_won'=>'🏆 موفق شدن معامله','deal_lost'=>'😞 ناموفق شدن معامله','payment_received'=>'💳 دریافت پرداخت','new_contact'=>'👤 افزودن مخاطب جدید','activity_reminder'=>'⏰ یادآوری فعالیت'] as $k=>$v): ?>
                    <option value="<?php echo $k; ?>" <?php echo $rule->trigger_type === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="stageConditions" style="display:<?php echo $rule->trigger_type === 'stage_change' ? 'block' : 'none'; ?>;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">پایپ‌لاین</label>
                        <select name="trigger_conditions[pipeline_id]" class="form-select" id="pipelineSelect" onchange="loadStages()">
                            <option value="">همه پایپ‌لاین‌ها</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">مرحله مقصد</label>
                        <select name="trigger_conditions[stage_id]" class="form-select" id="stageSelect">
                            <option value="">همه مراحل</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="background:var(--primary-light);padding:16px;border-radius:8px;margin-bottom:16px;">
            <h6 style="margin-bottom:12px;">⚡ بخش ۲: اقدام (Action) - چه کاری انجام شود؟</h6>
            <div class="form-group">
                <label class="form-label">نوع اقدام *</label>
                <select name="action_type" class="form-select" required id="actionType" onchange="showActionConfig()">
                    <option value="">— انتخاب کنید —</option>
                    <?php foreach (['send_sms'=>'✉️ ارسال پیامک','send_notification'=>'🔔 ارسال اعلان به کاربر','create_activity'=>'📅 ایجاد فعالیت/یادآوری','assign_user'=>'👤 تخصیص معامله به کاربر'] as $k=>$v): ?>
                    <option value="<?php echo $k; ?>" <?php echo $rule->action_type === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="config-sms" class="action-config" style="display:<?php echo $rule->action_type==='send_sms'?'block':'none'; ?>;">
                <div class="form-group">
                    <label class="form-label">شماره گیرنده</label>
                    <select name="action_config[phone_field]" class="form-select"><option value="contact">شماره مخاطب معامله</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label">📝 متن پیامک *</label>
                    <textarea name="action_config[message_template]" class="form-textarea" rows="5" placeholder="سلام {contact_name} عزیز..."><?php echo htmlspecialchars($actionConfig['message_template'] ?? ''); ?></textarea>
                </div>
                <div style="background:#fff;padding:12px;border-radius:8px;font-size:12px;">
                    <strong>🔤 متغیرهای قابل استفاده:</strong>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px;">
                        <div><code>{contact_name}</code> → نام مخاطب</div>
                        <div><code>{deal_title}</code> → عنوان معامله</div>
                        <div><code>{amount}</code> → مبلغ معامله</div>
                        <div><code>{payment_link}</code> → لینک پرداخت</div>
                        <div><code>{stage_name}</code> → نام مرحله فعلی</div>
                        <div><code>{pipeline_name}</code> → نام پایپ‌لاین</div>
                    </div>
                </div>
            </div>

            <div id="config-notification" class="action-config" style="display:<?php echo $rule->action_type==='send_notification'?'block':'none'; ?>;">
                <div class="form-group">
                    <label class="form-label">عنوان اعلان</label>
                    <input type="text" name="action_config[title]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">متن اعلان</label>
                    <input type="text" name="action_config[message]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['message'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">شناسه کاربر گیرنده</label>
                    <input type="number" name="action_config[user_id]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['user_id'] ?? ''); ?>">
                </div>
            </div>

            <div id="config-activity" class="action-config" style="display:<?php echo $rule->action_type==='create_activity'?'block':'none'; ?>;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">موضوع فعالیت</label>
                        <input type="text" name="action_config[subject]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['subject'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">تعداد روز بعد</label>
                        <input type="number" name="action_config[days]" class="form-input" value="<?php echo $actionConfig['days'] ?? 1; ?>" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">نوع فعالیت</label>
                    <select name="action_config[activity_type]" class="form-select">
                        <?php foreach (['reminder'=>'یادآوری','call'=>'تماس تلفنی','meeting'=>'جلسه','todo'=>'کار انجام دادنی'] as $k=>$v): ?>
                        <option value="<?php echo $k; ?>" <?php echo ($actionConfig['activity_type'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="config-assign" class="action-config" style="display:<?php echo $rule->action_type==='assign_user'?'block':'none'; ?>;">
                <div class="form-group">
                    <label class="form-label">شناسه کاربر مسئول</label>
                    <input type="number" name="action_config[assign_to]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['assign_to'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:16px;">
            <label class="toggle-switch">
                <input type="checkbox" name="is_active" <?php echo $rule->is_active ? 'checked' : ''; ?>>
                <span class="toggle-slider"></span>
            </label>
            <span style="margin-right:8px;font-size:14px;">فعال</span>
        </div>

        <div class="d-flex gap-8">
            <button type="submit" class="btn btn-primary btn-lg">💾 بروزرسانی</button>
            <a href="<?php echo $config['url']; ?>/automation" class="btn btn-secondary">انصراف</a>
        </div>
    </form>
</div>

<script>
var baseUrl = '<?php echo $config['url']; ?>';
var allPipelines = [];
var savedPipelineId = '<?php echo $triggerConditions['pipeline_id'] ?? ''; ?>';
var savedStageId = '<?php echo $triggerConditions['stage_id'] ?? ''; ?>';

fetch(baseUrl + '/pipelines/api/all')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) allPipelines = data.pipelines;
        populatePipelines();
        if (savedPipelineId) {
            document.getElementById('pipelineSelect').value = savedPipelineId;
            loadStages();
            setTimeout(function() { document.getElementById('stageSelect').value = savedStageId; }, 100);
        }
    })
    .catch(function() {});

function populatePipelines() {
    var sel = document.getElementById('pipelineSelect');
    if (!sel) return;
    allPipelines.forEach(function(p) {
        var opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = '📋 ' + p.name;
        sel.appendChild(opt);
    });
}

function loadStages() {
    var pipelineId = document.getElementById('pipelineSelect').value;
    var stageSel = document.getElementById('stageSelect');
    stageSel.innerHTML = '<option value="">همه مراحل</option>';
    if (!pipelineId) return;
    var pipeline = allPipelines.find(function(p) { return p.id == pipelineId; });
    if (pipeline && pipeline.stages) {
        pipeline.stages.forEach(function(s) {
            var opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name;
            stageSel.appendChild(opt);
        });
    }
}

document.getElementById('triggerType').addEventListener('change', function() {
    document.getElementById('stageConditions').style.display = this.value === 'stage_change' ? 'block' : 'none';
});

function showActionConfig() {
    document.querySelectorAll('.action-config').forEach(function(el) { el.style.display = 'none'; });
    var val = document.getElementById('actionType').value;
    var map = {'send_sms':'config-sms','send_notification':'config-notification','create_activity':'config-activity','assign_user':'config-assign'};
    var target = map[val];
    if (target) document.getElementById(target).style.display = 'block';
}
</script>