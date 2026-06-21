<?php $config = $GLOBALS['app_config']; 
$triggerTypes = $triggerTypes ?? [];
$actionTypes = $actionTypes ?? [];
$activityTypes = $activityTypes ?? ['follow_up'=>'📌 پیگیری','call'=>'📞 تماس','meeting'=>'🤝 جلسه','note'=>'📝 یادداشت','email'=>'📧 ایمیل','other'=>'📋 سایر'];
$triggerTypesJson = json_encode($triggerTypes);
$actionTypesJson = json_encode($actionTypes);
$triggerConditions = json_decode($rule->trigger_conditions, true) ?: [];
$actionConfig = json_decode($rule->action_config, true) ?: [];
?>
<div class="page-header">
    <h5>✏️ ویرایش قانون: <?php echo htmlspecialchars($rule->name); ?></h5>
    <a href="<?php echo $config['url']; ?>/automation" class="btn btn-secondary">بازگشت</a>
</div>

<!-- راهنمای کلی -->
<div class="card" style="border-right:5px solid var(--primary);background:linear-gradient(135deg,#f0f4ff,#e8f0fe);margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
        <span style="font-size:32px;">🤖</span>
        <div>
            <h4 style="margin:0;">ویرایش قانون اتوماسیون</h4>
            <p style="margin:4px 0 0;color:var(--gray-600);font-size:13px;">تنظیمات قانون را تغییر دهید. تغییرات فقط روی اجراهای آینده اعمال می‌شوند.</p>
        </div>
    </div>
</div>

<div class="card" style="max-width:900px;">
    <form method="POST" action="<?php echo $config['url']; ?>/automation/update/<?php echo $rule->id; ?>" id="automationForm">
        
        <!-- بخش ۱: نام قانون -->
        <div style="margin-bottom:24px;">
            <h6 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">📝 اطلاعات قانون</h6>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">نام قانون *</label>
                    <input type="text" name="name" class="form-input" required value="<?php echo htmlspecialchars($rule->name); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">توضیحات</label>
                    <input type="text" name="description" class="form-input" value="<?php echo htmlspecialchars($rule->description ?? ''); ?>">
                </div>
            </div>
        </div>

        <!-- بخش ۲: انتخاب ماشه -->
        <div style="background:var(--gray-50);padding:20px;border-radius:12px;margin-bottom:20px;border:1px solid var(--gray-200);">
            <h6 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">🔥 بخش ۱: ماشه (Trigger)</h6>
            
            <div class="form-group">
                <label class="form-label">رویداد ماشه *</label>
                <select name="trigger_type" class="form-select" required id="triggerType" onchange="onTriggerChange()">
                    <option value="">— انتخاب کنید —</option>
                    <?php 
                    $currentCategory = '';
                    foreach ($triggerTypes as $key => $t): 
                        if ($t['category'] !== $currentCategory):
                            $currentCategory = $t['category'];
                    ?>
                    <optgroup label="<?php echo htmlspecialchars($currentCategory); ?>">
                    <?php endif; ?>
                        <option value="<?php echo $key; ?>" <?php echo $rule->trigger_type === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['label']); ?></option>
                    <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <!-- توضیحات ماشه -->
            <div id="triggerDescription" style="display:none;background:#fff;padding:12px;border-radius:8px;margin-bottom:16px;border-right:3px solid var(--primary);">
                <p id="triggerDescText" style="font-size:13px;color:var(--gray-600);margin:0;"></p>
            </div>

            <!-- شرایط مرحله -->
            <div id="stageConditions" style="display:<?php echo $rule->trigger_type === 'stage_change' ? 'block' : 'none'; ?>;">
                <div style="background:#fff;padding:12px;border-radius:8px;margin-bottom:12px;">
                    <p style="font-size:12px;color:var(--gray-500);margin-bottom:12px;">🔽 <strong>فیلتر اختیاری:</strong> اگر خالی بگذارید، قانون برای همه اعمال می‌شود</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">پایپ‌لاین مشخص</label>
                            <select name="trigger_conditions[pipeline_id]" class="form-select" id="pipelineSelect" onchange="loadStages()">
                                <option value="">همه پایپ‌لاین‌ها</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">مرحله مشخص</label>
                            <select name="trigger_conditions[stage_id]" class="form-select" id="stageSelect">
                                <option value="">همه مراحل</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- شرط مبلغ -->
            <div id="amountCondition" style="display:<?php echo in_array($rule->trigger_type, ['payment_created','payment_verified','deal_created']) ? 'block' : 'none'; ?>;">
                <div style="background:#fff;padding:12px;border-radius:8px;">
                    <div class="form-group">
                        <label class="form-label">حداقل مبلغ (تومان)</label>
                        <input type="number" name="trigger_conditions[min_amount]" class="form-input" value="<?php echo htmlspecialchars($triggerConditions['min_amount'] ?? ''); ?>" placeholder="خالی = بدون محدودیت مبلغ">
                        <p class="form-hint">فقط وقتی مبلغ بیشتر از این مقدار باشد فعال شود</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش ۳: انتخاب اقدام -->
        <div style="background:var(--primary-light);padding:20px;border-radius:12px;margin-bottom:20px;border:1px solid #c7d2fe;">
            <h6 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">⚡ بخش ۲: اقدام (Action)</h6>
            
            <div class="form-group">
                <label class="form-label">نوع اقدام *</label>
                <select name="action_type" class="form-select" required id="actionType" onchange="onActionChange()">
                    <option value="">— انتخاب کنید —</option>
                    <?php 
                    $currentCategory = '';
                    foreach ($actionTypes as $key => $a): 
                        if ($a['category'] !== $currentCategory):
                            $currentCategory = $a['category'];
                    ?>
                    <optgroup label="<?php echo htmlspecialchars($currentCategory); ?>">
                    <?php endif; ?>
                        <option value="<?php echo $key; ?>" <?php echo $rule->action_type === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['label']); ?></option>
                    <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <!-- توضیحات اقدام -->
            <div id="actionDescription" style="display:none;background:#fff;padding:12px;border-radius:8px;margin-bottom:16px;border-right:3px solid var(--info);">
                <p id="actionDescText" style="font-size:13px;color:var(--gray-600);margin:0;"></p>
            </div>

            <!-- پیامک سفارشی -->
            <div id="config-send_sms" class="action-config" style="display:<?php echo $rule->action_type==='send_sms'?'block':'none'; ?>;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div class="form-group">
                        <label class="form-label">شماره گیرنده</label>
                        <select name="action_config[phone_field]" class="form-select">
                            <option value="contact" <?php echo ($actionConfig['phone_field'] ?? '') === 'contact' ? 'selected' : ''; ?>>شماره مخاطب معامله</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📝 متن پیامک *</label>
                        <textarea name="action_config[message_template]" class="form-textarea" rows="4"><?php echo htmlspecialchars($actionConfig['message_template'] ?? ''); ?></textarea>
                    </div>
                    <div id="placeholderHelp-sms" style="background:var(--gray-50);padding:12px;border-radius:8px;font-size:12px;">
                        <strong>🔤 متغیرهای قابل استفاده:</strong>
                        <div id="placeholderList-sms" style="display:grid;grid-template-columns:1fr 1fr;gap:4px;margin-top:8px;"></div>
                    </div>
                </div>
            </div>

            <!-- پیامک لینک پرداخت -->
            <div id="config-send_payment_sms" class="action-config" style="display:<?php echo $rule->action_type==='send_payment_sms'?'block':'none'; ?>;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div style="background:#ecfdf5;padding:12px;border-radius:8px;margin-bottom:16px;border:1px solid #a7f3d0;">
                        <p style="font-size:13px;color:#065f46;margin:0;">
                            💡 <strong>نحوه کار:</strong> لینک کوتاه آخرین پرداخت معامله استخراج شده و با پیامک به مشتری ارسال می‌شود. خالی = متن پیش‌فرض.
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📝 متن پیامک (اختیاری)</label>
                        <textarea name="action_config[message_template]" class="form-textarea" rows="3" placeholder="خالی بگذارید تا متن پیش‌فرض ارسال شود"><?php echo htmlspecialchars($actionConfig['message_template'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- اعلان -->
            <div id="config-send_notification" class="action-config" style="display:<?php echo $rule->action_type==='send_notification'?'block':'none'; ?>;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
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
                        <p class="form-hint">خالی = مسئول معامله</p>
                    </div>
                </div>
            </div>

            <!-- فعالیت -->
            <div id="config-create_activity" class="action-config" style="display:<?php echo $rule->action_type==='create_activity'?'block':'none'; ?>;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">موضوع فعالیت</label>
                            <input type="text" name="action_config[subject]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['subject'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">نوع فعالیت</label>
                            <select name="action_config[activity_type]" class="form-select">
                                <?php foreach ($activityTypes as $k => $v): ?>
                                <option value="<?php echo $k; ?>" <?php echo ($actionConfig['activity_type'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">تعداد روز بعد</label>
                            <input type="number" name="action_config[days]" class="form-input" value="<?php echo $actionConfig['days'] ?? 1; ?>" min="0">
                            <p class="form-hint">0 = همان لحظه، 1 = فردا، 7 = یک هفته بعد</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">توضیحات فعالیت</label>
                            <input type="text" name="action_config[description]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['description'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- تخصیص کاربر -->
            <div id="config-assign_user" class="action-config" style="display:<?php echo $rule->action_type==='assign_user'?'block':'none'; ?>;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div class="form-group">
                        <label class="form-label">شناسه کاربر مسئول</label>
                        <input type="number" name="action_config[assign_to]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['assign_to'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- بروزرسانی فیلد -->
            <div id="config-update_deal_field" class="action-config" style="display:<?php echo $rule->action_type==='update_deal_field'?'block':'none'; ?>;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">فیلد مورد نظر</label>
                            <select name="action_config[field]" class="form-select">
                                <?php foreach (['source'=>'منبع آشنایی','priority'=>'اولویت','tags'=>'برچسب‌ها'] as $fk=>$fv): ?>
                                <option value="<?php echo $fk; ?>" <?php echo ($actionConfig['field'] ?? '') === $fk ? 'selected' : ''; ?>><?php echo $fv; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">مقدار جدید</label>
                            <input type="text" name="action_config[value]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['value'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- فعال/غیرفعال -->
        <div class="form-group" style="margin-bottom:20px;">
            <label class="toggle-switch" style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="is_active" style="width:18px;height:18px;" <?php echo $rule->is_active ? 'checked' : ''; ?>>
                <span style="font-size:14px;font-weight:600;">قانون فعال باشد</span>
            </label>
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
var triggerTypes = <?php echo $triggerTypesJson; ?>;
var actionTypes = <?php echo $actionTypesJson; ?>;
var savedPipelineId = '<?php echo $triggerConditions['pipeline_id'] ?? ''; ?>';
var savedStageId = '<?php echo $triggerConditions['stage_id'] ?? ''; ?>';

// Load pipelines
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
        // Trigger initial descriptions
        onTriggerChange();
        onActionChange();
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

function onTriggerChange() {
    var val = document.getElementById('triggerType').value;
    var descDiv = document.getElementById('triggerDescription');
    if (val && triggerTypes[val]) {
        document.getElementById('triggerDescText').innerHTML = triggerTypes[val].description;
        descDiv.style.display = 'block';
    } else {
        descDiv.style.display = 'none';
    }
    var needsStage = val === 'stage_change';
    var needsAmount = val === 'payment_created' || val === 'payment_verified' || val === 'deal_created';
    document.getElementById('stageConditions').style.display = needsStage ? 'block' : 'none';
    document.getElementById('amountCondition').style.display = needsAmount ? 'block' : 'none';
}

function onActionChange() {
    var val = document.getElementById('actionType').value;
    var descDiv = document.getElementById('actionDescription');
    if (val && actionTypes[val]) {
        document.getElementById('actionDescText').innerHTML = actionTypes[val].description;
        descDiv.style.display = 'block';
    } else {
        descDiv.style.display = 'none';
    }
    document.querySelectorAll('.action-config').forEach(function(el) { el.style.display = 'none'; });
    var target = document.getElementById('config-' + val);
    if (target) target.style.display = 'block';
}
</script>