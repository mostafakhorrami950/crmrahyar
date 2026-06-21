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
            <p style="margin:4px 0 0;color:var(--gray-600);font-size:13px;">با اتوماسیون می‌توانید کارهای تکراری را خودکار کنید. یک قانون اتوماسیون از دو بخش تشکیل شده: <strong>ماشه</strong> (رویدادی که قانون را فعال می‌کند) و <strong>اقدام</strong> (کاری که خودکار انجام می‌شود).</p>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:12px;">
        <div style="background:#fff;padding:12px;border-radius:8px;">
            <strong>🔥 ماشه‌های موجود:</strong>
            <ul style="margin:6px 0 0;padding-right:16px;">
                <li><strong>تغییر مرحله:</strong> وقتی معامله به مرحله خاصی منتقل می‌شود</li>
                <li><strong>ایجاد معامله:</strong> وقتی معامله جدیدی ثبت می‌شود</li>
                <li><strong>برد/باخت:</strong> وقتی معامله موفق یا ناموفق می‌شود</li>
                <li><strong>ایجاد لینک پرداخت:</strong> وقتی برای معامله لینک پرداخت ساخته می‌شود</li>
                <li><strong>تایید پرداخت:</strong> وقتی پرداختی با موفقیت تایید می‌شود</li>
                <li><strong>مخاطب جدید:</strong> وقتی مخاطب جدیدی اضافه می‌شود</li>
            </ul>
        </div>
        <div style="background:#fff;padding:12px;border-radius:8px;">
            <strong>⚡ اقدام‌های موجود:</strong>
            <ul style="margin:6px 0 0;padding-right:16px;">
                <li><strong>ارسال پیامک:</strong> پیامک سفارشی به مخاطب</li>
                <li><strong>ارسال لینک پرداخت:</strong> پیامک با لینک کوتاه پرداخت</li>
                <li><strong>ارسال اعلان:</strong> اعلان داخلی به کاربر سیستم</li>
                <li><strong>ایجاد فعالیت:</strong> ایجاد یادآوری/پیگیری خودکار</li>
                <li><strong>تخصیص کاربر:</strong> اختصاص معامله به کاربر مشخص</li>
                <li><strong>بروزرسانی فیلد:</strong> تغییر خودکار فیلدهای معامله</li>
            </ul>
        </div>
    </div>
</div>

<div class="card" style="max-width:900px;">
    <form method="POST" action="<?php echo $config['url']; ?>/automation/store" id="automationForm">
        
        <!-- بخش ۱: نام قانون -->
        <div style="margin-bottom:24px;">
            <h6 style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">📝 بخش ۰: اطلاعات قانون</h6>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">نام قانون *</label>
                    <input type="text" name="name" class="form-input" required placeholder="مثال: ارسال لینک پرداخت به مشتری">
                    <p class="form-hint">نام واضح و قابل تشخیص انتخاب کنید تا بعداً بتوانید آن را پیدا کنید</p>
                </div>
                <div class="form-group">
                    <label class="form-label">توضیحات</label>
                    <input type="text" name="description" class="form-input" placeholder="مثال: پس از ایجاد لینک پرداخت، لینک کوتاه پیامک شود">
                    <p class="form-hint">توضیح کوتاه برای یادآوری عملکرد این قانون</p>
                </div>
            </div>
        </div>

        <!-- بخش ۲: انتخاب ماشه -->
        <div style="background:var(--gray-50);padding:20px;border-radius:12px;margin-bottom:20px;border:1px solid var(--gray-200);">
            <h6 style="margin-bottom:8px;display:flex;align-items:center;gap:8px;">🔥 بخش ۱: ماشه (Trigger)</h6>
            <p style="font-size:12px;color:var(--gray-500);margin-bottom:16px;">ماشه رویدادی است که باعث فعال شدن این قانون می‌شود. ابتدا نوع ماشه را انتخاب کنید، سپس فیلترهای دلخواه را تعیین کنید.</p>
            
            <div class="form-group">
                <label class="form-label">رویداد ماشه *</label>
                <select name="trigger_type" class="form-select" required id="triggerType" onchange="onTriggerChange()">
                    <option value="">— انتخاب کنید —</option>
                    <?php 
                    $currentCategory = '';
                    foreach ($triggerTypes as $key => $t): 
                        if ($t['category'] !== $currentCategory):
                            // Close previous optgroup if not first
                            if ($currentCategory !== '') echo '</optgroup>';
                            $currentCategory = $t['category'];
                    ?>
                    <optgroup label="<?php echo htmlspecialchars($currentCategory); ?>">
                    <?php endif; ?>
                        <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($t['label']); ?></option>
                    <?php endforeach; ?>
                    <?php if ($currentCategory !== '') echo '</optgroup>'; ?>
                </select>
                <p class="form-hint">رویدادی که باعث اجرای خودکار این قانون می‌شود را انتخاب کنید</p>
            </div>

            <!-- توضیحات ماشه انتخاب شده -->
            <div id="triggerDescription" style="display:none;background:#fff;padding:12px;border-radius:8px;margin-bottom:16px;border-right:3px solid var(--primary);">
                <p id="triggerDescText" style="font-size:13px;color:var(--gray-600);margin:0;"></p>
            </div>

            <!-- راهنمای متغیرها -->
            <div id="triggerVarHelp" style="display:none;background:#eff6ff;padding:12px;border-radius:8px;margin-bottom:16px;border:1px solid #bfdbfe;font-size:12px;">
                <strong>🔤 متغیرهای قابل استفاده در متن پیامک:</strong>
                <div id="triggerVarList" style="display:grid;grid-template-columns:1fr 1fr;gap:4px;margin-top:8px;"></div>
            </div>

            <!-- شرایط فیلتر: پایپ‌لاین و مرحله -->
            <div id="stageConditions" style="display:none;">
                <div style="background:#fff;padding:12px;border-radius:8px;margin-bottom:12px;">
                    <p style="font-size:12px;color:var(--gray-500);margin-bottom:12px;">🔽 <strong>فیلتر اختیاری:</strong> اگر خالی بگذارید، قانون برای همه پایپ‌لاین‌ها و مراحل اعمال می‌شود</p>
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
                            <p class="form-hint">فقط وقتی معامله به این مرحله منتقل شود قانون فعال شود</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- شرط حداقل مبلغ -->
            <div id="amountCondition" style="display:none;">
                <div style="background:#fff;padding:12px;border-radius:8px;">
                    <div class="form-group">
                        <label class="form-label">حداقل مبلغ (تومان)</label>
                        <input type="number" name="trigger_conditions[min_amount]" class="form-input" placeholder="خالی = بدون محدودیت مبلغ">
                        <p class="form-hint">فقط وقتی مبلغ پرداخت/معامله بیشتر از این مقدار باشد فعال شود. خالی بگذارید = بدون محدودیت</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش ۳: انتخاب اقدام -->
        <div style="background:var(--primary-light);padding:20px;border-radius:12px;margin-bottom:20px;border:1px solid #c7d2fe;">
            <h6 style="margin-bottom:8px;display:flex;align-items:center;gap:8px;">⚡ بخش ۲: اقدام (Action)</h6>
            <p style="font-size:12px;color:var(--gray-500);margin-bottom:16px;">اقدام کاری است که پس از فعال شدن ماشه، به صورت خودکار انجام می‌شود.</p>
            
            <div class="form-group">
                <label class="form-label">نوع اقدام *</label>
                <select name="action_type" class="form-select" required id="actionType" onchange="onActionChange()">
                    <option value="">— انتخاب کنید —</option>
                    <?php 
                    $currentCategory = '';
                    foreach ($actionTypes as $key => $a): 
                        if ($a['category'] !== $currentCategory):
                            if ($currentCategory !== '') echo '</optgroup>';
                            $currentCategory = $a['category'];
                    ?>
                    <optgroup label="<?php echo htmlspecialchars($currentCategory); ?>">
                    <?php endif; ?>
                        <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($a['label']); ?></option>
                    <?php endforeach; ?>
                    <?php if ($currentCategory !== '') echo '</optgroup>'; ?>
                </select>
            </div>

            <!-- توضیحات اقدام انتخاب شده -->
            <div id="actionDescription" style="display:none;background:#fff;padding:12px;border-radius:8px;margin-bottom:16px;border-right:3px solid var(--info);">
                <p id="actionDescText" style="font-size:13px;color:var(--gray-600);margin:0;"></p>
            </div>

            <!-- تنظیمات پیامک سفارشی -->
            <div id="config-send_sms" class="action-config" style="display:none;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div style="background:#fffbeb;padding:12px;border-radius:8px;margin-bottom:12px;border:1px solid #fcd34d;font-size:12px;">
                        💡 <strong>راهنما:</strong> با استفاده از متغیرهای داخل آکولاد مثل <code>{contact_name}</code> می‌توانید متن پویا بسازید. مثلاً: <em>«سلام {contact_name} عزیز، معامله {deal_title} شما ثبت شد.»</em>
                    </div>
                    <div class="form-group">
                        <label class="form-label">شماره گیرنده</label>
                        <select name="action_config[phone_field]" class="form-select">
                            <option value="contact">📞 شماره مخاطب معامله</option>
                        </select>
                        <p class="form-hint">پیامک به شماره تلفن مخاطب متصل به معامله ارسال می‌شود</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📝 متن پیامک *</label>
                        <textarea name="action_config[message_template]" class="form-textarea" rows="4" placeholder="سلام {contact_name} عزیز، معامله {deal_title} شما با مبلغ {amount} ثبت شد."></textarea>
                        <p class="form-hint">متنی که می‌خواهید پیامک شود. از متغیرهای زیر استفاده کنید.</p>
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
                            💡 <strong>نحوه کار:</strong> این اقدام آخرین لینک کوتاه پرداخت معامله را پیدا کرده و همراه متن پیامک به مشتری ارسال می‌کند. 
                            اگر متن پیامک را خالی بگذارید، متن پیش‌فرض ارسال می‌شود (شامل نام مخاطب، مبلغ و لینک کوتاه پرداخت).
                            <br>⚠️ <strong>نکته:</strong> حتماً این اقدام را با ماشه «<strong>ایجاد لینک پرداخت</strong>» یا «<strong>تایید پرداخت</strong>» استفاده کنید.
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📝 متن پیامک (اختیاری)</label>
                        <textarea name="action_config[message_template]" class="form-textarea" rows="3" placeholder="خالی بگذارید تا متن پیش‌فرض ارسال شود. یا متن دلخواه: سلام {contact_name} عزیز، لینک پرداخت شما: {payment_short_link}"></textarea>
                        <p class="form-hint">خالی = متن پیش‌فرض | سفارشی = از متغیرهای زیر استفاده کنید</p>
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
                    <div style="background:#eff6ff;padding:12px;border-radius:8px;margin-bottom:12px;border:1px solid #bfdbfe;font-size:12px;">
                        💡 <strong>راهنما:</strong> اعلان داخلی در سیستم نمایش داده می‌شود. می‌توانید به خودتان یا کاربر دیگری اعلان بفرستید.
                    </div>
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
                        <p class="form-hint">خالی بگذارید = مسئول معامله اعلان را دریافت می‌کند. شناسه عددی کاربر مورد نظر را وارد کنید.</p>
                    </div>
                </div>
            </div>

            <!-- تنظیمات ایجاد فعالیت -->
            <div id="config-create_activity" class="action-config" style="display:none;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div style="background:#fffbeb;padding:12px;border-radius:8px;margin-bottom:12px;border:1px solid #fcd34d;font-size:12px;">
                        💡 <strong>راهنما:</strong> یک فعالیت جدید (مثلاً یادآوری پیگیری تلفنی) به صورت خودکار برای معامله ایجاد می‌شود. می‌توانید مشخص کنید چند روز بعد از ماشه، این فعالیت ایجاد شود.
                    </div>
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
                            <p class="form-hint">0 = همان لحظه | 1 = فردا | 7 = یک هفته بعد</p>
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
                    <div style="background:#eff6ff;padding:12px;border-radius:8px;margin-bottom:12px;border:1px solid #bfdbfe;font-size:12px;">
                        💡 <strong>راهنما:</strong> معامله به صورت خودکار به کاربر مشخصی اختصاص داده می‌شود. مثلاً هنگام ورود به مرحله «رزرو بلیط» به کاربر بخش بلیط اختصاص یابد.
                    </div>
                    <div class="form-group">
                        <label class="form-label">شناسه کاربر مسئول</label>
                        <input type="number" name="action_config[assign_to]" class="form-input" placeholder="شناسه عددی کاربر">
                        <p class="form-hint">شناسه عددی کاربر مورد نظر را وارد کنید (از بخش مدیریت کاربران قابل مشاهده است)</p>
                    </div>
                </div>
            </div>

            <!-- تنظیمات بروزرسانی فیلد -->
            <div id="config-update_deal_field" class="action-config" style="display:none;">
                <div style="background:#fff;padding:16px;border-radius:8px;">
                    <div style="background:#eff6ff;padding:12px;border-radius:8px;margin-bottom:12px;border:1px solid #bfdbfe;font-size:12px;">
                        💡 <strong>راهنما:</strong> فیلد مشخصی از معامله به صورت خودکار بروزرسانی می‌شود.
                    </div>
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

        <!-- مثال‌های رایج -->
        <div style="background:#f0fdf4;padding:16px;border-radius:12px;margin-bottom:20px;border:1px solid #86efac;">
            <h6 style="margin-bottom:12px;">💡 مثال‌های رایج برای آژانس هواپیمایی:</h6>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:12px;">
                <div style="background:#fff;padding:10px;border-radius:8px;">
                    <strong>📨 ارسال خودکار لینک پرداخت:</strong><br>
                    ماشه: ایجاد لینک پرداخت → اقدام: ارسال پیامک لینک پرداخت
                </div>
                <div style="background:#fff;padding:10px;border-radius:8px;">
                    <strong>✅ تایید پرداخت:</strong><br>
                    ماشه: تایید پرداخت → اقدام: ارسال پیامک + اعلان به مدیر
                </div>
                <div style="background:#fff;padding:10px;border-radius:8px;">
                    <strong>📞 پیگیری خودکار:</strong><br>
                    ماشه: ایجاد معامله → اقدام: ایجاد فعالیت (۳ روز بعد)
                </div>
                <div style="background:#fff;padding:10px;border-radius:8px;">
                    <strong>🔄 انتقال به بخش بلیط:</strong><br>
                    ماشه: تغییر مرحله → اقدام: تخصیص به کاربر بلیط
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

    // Update variable help
    updateTriggerVarHelp(val);
    updatePlaceholderHelp();
}

function updateTriggerVarHelp(triggerType) {
    var helpDiv = document.getElementById('triggerVarHelp');
    var listDiv = document.getElementById('triggerVarList');
    
    if (!triggerType || !triggerTypes[triggerType]) {
        helpDiv.style.display = 'none';
        return;
    }
    
    helpDiv.style.display = 'block';
    var vars = {
        '{contact_name}': 'نام مخاطب',
        '{contact_phone}': 'تلفن مخاطب',
        '{deal_title}': 'عنوان معامله',
        '{amount}': 'مبلغ معامله (تومان)',
        '{stage_name}': 'نام مرحله فعلی',
        '{pipeline_name}': 'نام پایپ‌لاین',
    };
    
    if (triggerType === 'payment_created' || triggerType === 'payment_verified') {
        vars['{payment_link}'] = '🔗 لینک کوتاه پرداخت';
        vars['{payment_short_link}'] = '🔗 لینک کوتاه پرداخت (یکسان)';
        vars['{payment_amount}'] = '💰 مبلغ پرداخت (تومان)';
    }
    
    var html = '';
    for (var key in vars) {
        html += '<div><code style="background:#dbeafe;padding:2px 6px;border-radius:4px;">' + key + '</code> → ' + vars[key] + '</div>';
    }
    listDiv.innerHTML = html;
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
        '{amount}': 'مبلغ معامله (تومان)',
        '{stage_name}': 'نام مرحله',
        '{pipeline_name}': 'نام پایپ‌لاین',
    };
    
    if (triggerType === 'payment_created' || triggerType === 'payment_verified') {
        placeholders['{payment_link}'] = '🔗 لینک کوتاه پرداخت';
        placeholders['{payment_short_link}'] = '🔗 لینک کوتاه پرداخت';
        placeholders['{payment_amount}'] = '💰 مبلغ پرداخت (تومان)';
    }
    
    var html = '';
    for (var key in placeholders) {
        html += '<div><code>' + key + '</code> → ' + placeholders[key] + '</div>';
    }
    listEl.innerHTML = html;
}
</script>