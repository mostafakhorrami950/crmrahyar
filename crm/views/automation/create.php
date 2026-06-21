<?php $config = $GLOBALS['app_config']; 
$triggerTypes = $triggerTypes ?? [];
$actionTypes = $actionTypes ?? [];
$activityTypes = $activityTypes ?? ['follow_up'=>'📌 پیگیری','call'=>'📞 تماس','meeting'=>'🤝 جلسه','note'=>'📝 یادداشت','email'=>'📧 ایمیل','other'=>'📋 سایر'];
$triggerTypesJson = json_encode($triggerTypes);
$actionTypesJson = json_encode($actionTypes);
?>
<div class="page-header">
    <h5>➕ قانون اتوماسیون جدید</h5>
    <a href="<?php echo $config['url']; ?>/automation" class="btn btn-secondary">بازگشت</a>
</div>

<!-- راهنمای کلی -->
<div class="card" style="border-right:5px solid var(--primary);background:linear-gradient(135deg,#f0f4ff,#e8f0fe);margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <span style="font-size:32px;">🤖</span>
        <div>
            <h4 style="margin:0;">اتوماسیون چیست؟</h4>
            <p style="margin:4px 0 0;color:var(--gray-600);font-size:13px;">با اتوماسیون می‌توانید کارهای تکراری را خودکار کنید. مثلاً: «وقتی لینک پرداخت ساخته شد، خودکار پیامک لینک کوتاه برای مشتری ارسال شود»</p>
        </div>
    </div>
    <div style="display:flex;gap:8px;font-size:12px;color:var(--gray-500);">
        <span>🔥 <strong>ماشه</strong> = رویدادی که قانون را فعال می‌کند</span>
        <span>→</span>
        <span>⚡ <strong>اقدام</strong> = کاری که خودکار انجام می‌شود</span>
    </div>
</div>

<div class="card" style="max-width:900px;">
    <form method="POST" action="<?php echo $config['url']; ?>/automation/store" id="automationForm">
        
        <!-- بخش ۱: نام قانون -->
        <div style="margin-bottom:24px;">
            <h6 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">📝 اطلاعات قانون</h6>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">نام قانون *</label>
                    <input type="text" name="name" class="form-input" required placeholder="مثال: ارسال لینک پرداخت به مشتری">
                    <p class="form-hint">نام واضح و قابل تشخیص انتخاب کنید</p>
                </div>
                <div class="form-group">
                    <label class="form-label">توضیحات</label>
                    <input type="text" name="description" class="form-input" placeholder="مثال: پس از ایجاد لینک پرداخت، لینک کوتاه پرداخت پیامک شود">
                </div>
            </div>
        </div>

        <!-- بخش ۲: انتخاب ماشه -->
        <div style="background:var(--gray-50);padding:20px;border-radius:12px;margin-bottom:20px;border:1px solid var(--gray-200);">
            <h6 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">🔥 بخش ۱: ماشه (Trigger) — <small style="font-weight:400;color:var(--gray-500);">کدام رویداد قانون را فعال کند؟</small></h6>
            
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
                        <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($t['label']); ?></option>
                    <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <!-- توضیحات ماشه انتخاب شده -->
            <div id="triggerDescription" style="display:none;background:#fff;padding:12px;border-radius:8px;margin-bottom:16px;border-right:3px solid var(--primary);">
                <p id="triggerDescText" style="font-size:13px;color:var(--gray-600);margin:0;"></p>
            </div>

            <!-- شرایط اضافی برای ماشه‌های مرتبط -->
            <div id="stageConditions" style="display:none;">
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
                            <p class="form-hint">فقط وقتی معامله به این مرحله منتقل شود</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="amountCondition" style="display:none;">
                <div style="background:#fff;padding:12px;border-radius:8px;">
                    <div class="form-group">
                        <label class="form-label">حداقل مبلغ (تومان)</label>
                        <input type="number" name="trigger_conditions[min_amount]" class="form-input" placeholder="خالی = بدون محدودیت مبلغ">
                        <p class="form-hint">فقط وقتی مبلغ بیشتر از این مقدار باشد فعال شود</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش ۳: انتخاب اقدام -->
        <div style="background:var(--primary-light);padding:20px;border-radius:12px;margin-bottom:20px;border:1px solid #c7d2fe;">
            <h6 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">⚡ بخش ۲: اقدام (Action) — <small style="font-weight:400;color:var(--gray-500);">چه کاری خودکار انجام شود؟</small></h6>
            
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
                        <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($a['label']); ?></option>
                    <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <!-- توضیحات اقدام انتخاب شده -->
            <div id="actionDescription" style="display:none;background:#fff;padding:12px;border-radius:8px;margin-bottom:16px;border-right:3px solid var(--info);">
                <p id="actionDescText" style="font-size:13px;color:var(--gray-600);margin:0;"></p>
            </div>

            <!-- تنظیمات پیامک سفارشی -->
            <div id="config-send_sms" class="action-config" style="display:none;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div class="form-group">
                        <label class="form-label">شماره گیرنده</label>
                        <select name="action_config[phone_field]" class="form-select">
                            <option value="contact">شماره مخاطب معامله</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📝 متن پیامک *</label>
                        <textarea name="action_config[message_template]" class="form-textarea" rows="4" placeholder="سلام {contact_name} عزیز، معامله {deal_title} شما با مبلغ {amount} ثبت شد."></textarea>
                    </div>
                    <div id="placeholderHelp-sms" style="background:var(--gray-50);padding:12px;border-radius:8px;font-size:12px;">
                        <strong>🔤 متغیرهای قابل استفاده:</strong>
                        <div id="placeholderList-sms" style="display:grid;grid-template-columns:1fr 1fr;gap:4px;margin-top:8px;"></div>
                    </div>
                </div>
            </div>

            <!-- تنظیمات ارسال پیامک لینک پرداخت -->
            <div id="config-send_payment_sms" class="action-config" style="display:none;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div style="background:#ecfdf5;padding:12px;border-radius:8px;margin-bottom:16px;border:1px solid #a7f3d0;">
                        <p style="font-size:13px;color:#065f46;margin:0;">
                            💡 <strong>نحوه کار:</strong> این اقدام لینک کوتاه آخرین پرداخت ایجاد شده برای معامله را استخراج کرده و با متن پیامک به مشتری ارسال می‌کند. 
                            اگر متن پیامک را خالی بگذارید، متن پیش‌فرض استاندارد ارسال می‌شود.
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📝 متن پیامک (اختیاری)</label>
                        <textarea name="action_config[message_template]" class="form-textarea" rows="3" placeholder="خالی بگذارید تا متن پیش‌فرض ارسال شود. مثال سفارشی: سلام {contact_name} عزیز، لینک پرداخت: {payment_short_link}"></textarea>
                        <p class="form-hint">متن پیش‌فرض شامل نام مخاطب، مبلغ و لینک کوتاه پرداخت است</p>
                    </div>
                    <div id="placeholderHelp-payment_sms" style="background:var(--gray-50);padding:12px;border-radius:8px;font-size:12px;">
                        <strong>🔤 متغیرهای قابل استفاده:</strong>
                        <div id="placeholderList-payment_sms" style="display:grid;grid-template-columns:1fr 1fr;gap:4px;margin-top:8px;"></div>
                    </div>
                </div>
            </div>

            <!-- تنظیمات ارسال اعلان -->
            <div id="config-send_notification" class="action-config" style="display:none;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div class="form-group">
                        <label class="form-label">عنوان اعلان</label>
                        <input type="text" name="action_config[title]" class="form-input" placeholder="مثال: پرداخت جدید برای {deal_title}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">متن اعلان</label>
                        <input type="text" name="action_config[message]" class="form-input" placeholder="مثال: مبلغ {amount} تومان پرداخت شد">
                    </div>
                    <div class="form-group">
                        <label class="form-label">شناسه کاربر گیرنده</label>
                        <input type="number" name="action_config[user_id]" class="form-input" placeholder="شناسه عددی کاربر">
                        <p class="form-hint">خالی = مسئول معامله</p>
                    </div>
                </div>
            </div>

            <!-- تنظیمات ایجاد فعالیت -->
            <div id="config-create_activity" class="action-config" style="display:none;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">موضوع فعالیت</label>
                            <input type="text" name="action_config[subject]" class="form-input" placeholder="مثال: پیگیری تلفنی پرداخت">
                        </div>
                        <div class="form-group">
                            <label class="form-label">نوع فعالیت</label>
                            <select name="action_config[activity_type]" class="form-select">
                                <?php foreach ($activityTypes as $k => $v): ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">تعداد روز بعد از ماشه</label>
                            <input type="number" name="action_config[days]" class="form-input" value="1" min="0">
                            <p class="form-hint">0 = همان لحظه، 1 = فردا، 7 = یک هفته بعد</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">توضیحات فعالیت</label>
                            <input type="text" name="action_config[description]" class="form-input" placeholder="توضیح اضافی اختیاری">
                        </div>
                    </div>
                </div>
            </div>

            <!-- تنظیمات تخصیص کاربر -->
            <div id="config-assign_user" class="action-config" style="display:none;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div class="form-group">
                        <label class="form-label">شناسه کاربر مسئول</label>
                        <input type="number" name="action_config[assign_to]" class="form-input" placeholder="شناسه عددی کاربر">
                        <p class="form-hint">معامله به این کاربر اختصاص داده می‌شود</p>
                    </div>
                </div>
            </div>

            <!-- تنظیمات بروزرسانی فیلد -->
            <div id="config-update_deal_field" class="action-config" style="display:none;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">فیلد مورد نظر</label>
                            <select name="action_config[field]" class="form-select">
                                <option value="source">منبع آشنایی</option>
                                <option value="priority">اولویت</option>
                                <option value="tags">برچسب‌ها</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">مقدار جدید</label>
                            <input type="text" name="action_config[value]" class="form-input" placeholder="مقدار جدید">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-8">
            <button type="submit" class="btn btn-primary btn-lg">💾 ذخیره قانون</button>
            <a href="<?php echo $config['url']; ?>/automation" class="btn btn-secondary">انصراف</a>
        </div>
    </form>
</div>

<script>
var baseUrl = '<?php echo $config['url']; ?>';
var allPipelines = [];
var triggerTypes = <?php echo $triggerTypesJson; ?>;
var actionTypes = <?php echo $actionTypesJson; ?>;

// Load pipelines
fetch(baseUrl + '/pipelines/api/all')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) allPipelines = data.pipelines;
        populatePipelines();
    })
    .catch(function() {});

function populatePipelines() {
    var sel = document.getElementById('pipelineSelect');
    if (!sel) return;
    allPipelines.forEach(function(p) {
        var opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = '📋 ' + p.name + (p.is_active ? '' : ' (غیرفعال)');
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
    
    // Show description
    var descDiv = document.getElementById('triggerDescription');
    if (val && triggerTypes[val]) {
        document.getElementById('triggerDescText').innerHTML = triggerTypes[val].description;
        descDiv.style.display = 'block';
    } else {
        descDiv.style.display = 'none';
    }

    // Show/hide conditions
    var needsStage = val === 'stage_change';
    var needsAmount = val === 'payment_created' || val === 'payment_verified' || val === 'deal_created';
    
    document.getElementById('stageConditions').style.display = needsStage ? 'block' : 'none';
    document.getElementById('amountCondition').style.display = needsAmount ? 'block' : 'none';
}

function onActionChange() {
    var val = document.getElementById('actionType').value;
    
    // Show description
    var descDiv = document.getElementById('actionDescription');
    if (val && actionTypes[val]) {
        document.getElementById('actionDescText').innerHTML = actionTypes[val].description;
        descDiv.style.display = 'block';
    } else {
        descDiv.style.display = 'none';
    }

    // Show/hide configs
    document.querySelectorAll('.action-config').forEach(function(el) { el.style.display = 'none'; });
    var target = document.getElementById('config-' + val);
    if (target) target.style.display = 'block';

    // Update placeholder help
    updatePlaceholderHelp();
}

function updatePlaceholderHelp() {
    var triggerType = document.getElementById('triggerType').value;
    var actionType = document.getElementById('actionType').value;
    var listEl = null;
    
    if (actionType === 'send_sms') {
        listEl = document.getElementById('placeholderList-sms');
    } else if (actionType === 'send_payment_sms') {
        listEl = document.getElementById('placeholderList-payment_sms');
    }
    
    if (!listEl) return;
    
    var placeholders = {
        '{contact_name}': 'نام مخاطب',
        '{contact_phone}': 'تلفن مخاطب',
        '{deal_title}': 'عنوان معامله',
        '{amount}': 'مبلغ معامله',
        '{stage_name}': 'نام مرحله',
        '{pipeline_name}': 'نام پایپ‌لاین',
    };
    
    if (triggerType === 'payment_created' || triggerType === 'payment_verified') {
        placeholders['{payment_link}'] = 'لینک پرداخت';
        placeholders['{payment_short_link}'] = 'لینک کوتاه پرداخت';
        placeholders['{payment_amount}'] = 'مبلغ پرداخت';
        placeholders['{payment_short_link}'] = 'لینک کوتاه پرداخت';
    }
    
    var html = '';
    for (var key in placeholders) {
        html += '<div><code>' + key + '</code> → ' + placeholders[key] + '</div>';
    }
    listEl.innerHTML = html;
}
</script>