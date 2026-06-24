<?php $config = $GLOBALS['app_config']; 
$triggerTypes = $triggerTypes ?? [];
$actionTypes = $actionTypes ?? [];
$activityTypes = $activityTypes ?? ['follow_up'=>'<i class="bi bi-pin me-1"></i> پیگیری','call'=>'<i class="bi bi-telephone me-1"></i> تماس','meeting'=>'🤝 جلسه','note'=>'<i class="bi bi-journal-text me-1"></i> یادداشت','email'=>'📧 ایمیل','other'=>'<i class="bi bi-list-task me-1"></i> سایر'];
$triggerTypesJson = json_encode($triggerTypes);
$actionTypesJson = json_encode($actionTypes);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-1"></i> قانون اتوماسیون جدید</h5>
    <a href="<?php echo $config['url']; ?>/automation" class="btn btn-outline-secondary">بازگشت</a>
</div>

<!-- راهنمای کلی -->
<div class="card mb-3" style="border-right:5px solid var(--primary);background:linear-gradient(135deg,#f0f4ff,#e8f0fe);">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span style="font-size:32px;">🤖</span>
            <div>
                <h5 class="fw-bold mb-0">اتوماسیون چیست؟</h5>
                <p class="text-muted small mb-0 mt-1">با اتوماسیون می‌توانید کارهای تکراری را خودکار کنید. یک قانون اتوماسیون از دو بخش تشکیل شده: <strong>ماشه</strong> (رویدادی که قانون را فعال می‌کند) و <strong>اقدام</strong> (کاری که خودکار انجام می‌شود).</p>
            </div>
        </div>
        <div class="row g-3 small">
            <div class="col-md-6">
                <div class="bg-white p-3 rounded-3">
                    <strong>🔥 ماشه‌های موجود:</strong>
                    <ul class="mb-0 mt-2 pe-3">
                        <li><strong>تغییر مرحله:</strong> وقتی معامله به مرحله خاصی منتقل می‌شود</li>
                        <li><strong>ایجاد معامله:</strong> وقتی معامله جدیدی ثبت می‌شود</li>
                        <li><strong>برد/باخت:</strong> وقتی معامله موفق یا ناموفق می‌شود</li>
                        <li><strong>ایجاد لینک پرداخت:</strong> وقتی برای معامله لینک پرداخت ساخته می‌شود</li>
                        <li><strong>تایید پرداخت:</strong> وقتی پرداختی با موفقیت تایید می‌شود</li>
                        <li><strong>مخاطب جدید:</strong> وقتی مخاطب جدیدی اضافه می‌شود</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-white p-3 rounded-3">
                    <strong>⚡ اقدام‌های موجود:</strong>
                    <ul class="mb-0 mt-2 pe-3">
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
    </div>
</div>

<div class="card" style="max-width:900px;">
    <div class="card-body">
        <form method="POST" action="<?php echo $config['url']; ?>/automation/store" id="automationForm">
            
            <!-- بخش ۰: نام قانون -->
            <div class="mb-4">
                <h6 class="d-flex align-items-center gap-2 mb-3"><i class="bi bi-journal-text me-1"></i> بخش ۰: اطلاعات قانون</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-medium">نام قانون *</label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: ارسال لینک پرداخت به مشتری">
                        <div class="form-text">نام واضح و قابل تشخیص انتخاب کنید تا بعداً بتوانید آن را پیدا کنید</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-medium">توضیحات</label>
                        <input type="text" name="description" class="form-control" placeholder="مثال: پس از ایجاد لینک پرداخت، لینک کوتاه پیامک شود">
                        <div class="form-text">توضیح کوتاه برای یادآوری عملکرد این قانون</div>
                    </div>
                </div>
            </div>

            <!-- بخش ۱: انتخاب ماشه -->
            <div class="rounded-3 p-3 p-md-4 mb-3" style="background:#f8f9fa;border:1px solid #e9ecef;">
                <h6 class="d-flex align-items-center gap-2 mb-1">🔥 بخش ۱: ماشه (Trigger)</h6>
                <p class="small text-muted mb-3">ماشه رویدادی است که باعث فعال شدن این قانون می‌شود. ابتدا نوع ماشه را انتخاب کنید، سپس فیلترهای دلخواه را تعیین کنید.</p>
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">رویداد ماشه *</label>
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
                    <div class="form-text">رویدادی که باعث اجرای خودکار این قانون می‌شود را انتخاب کنید</div>
                </div>

                <!-- توضیحات ماشه انتخاب شده -->
                <div id="triggerDescription" class="d-none bg-white p-3 rounded-3 mb-3" style="border-right:3px solid var(--primary);">
                    <p id="triggerDescText" class="small text-muted mb-0"></p>
                </div>

                <!-- راهنمای متغیرها -->
                <div id="triggerVarHelp" class="d-none p-3 rounded-3 mb-3 small" style="background:#eff6ff;border:1px solid #bfdbfe;">
                    <strong>🔤 متغیرهای قابل استفاده در متن پیامک:</strong>
                    <div id="triggerVarList" class="row g-1 mt-2"></div>
                </div>

                <!-- شرایط فیلتر: پایپ‌لاین و مرحله -->
                <div id="stageConditions" class="d-none">
                    <div class="bg-white p-3 rounded-3 mb-3">
                        <p class="small text-muted mb-3">🔽 <strong>فیلتر اختیاری:</strong> اگر خالی بگذارید، قانون برای همه پایپ‌لاین‌ها و مراحل اعمال می‌شود</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">پایپ‌لاین مشخص</label>
                                <select name="trigger_conditions[pipeline_id]" class="form-select" id="pipelineSelect" onchange="loadStages()">
                                    <option value="">همه پایپ‌لاین‌ها</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">مرحله مشخص</label>
                                <select name="trigger_conditions[stage_id]" class="form-select" id="stageSelect">
                                    <option value="">همه مراحل</option>
                                </select>
                                <div class="form-text">فقط وقتی معامله به این مرحله منتقل شود قانون فعال شود</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- شرط حداقل مبلغ -->
                <div id="amountCondition" class="d-none">
                    <div class="bg-white p-3 rounded-3">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">حداقل مبلغ (تومان)</label>
                            <input type="number" name="trigger_conditions[min_amount]" class="form-control" placeholder="خالی = بدون محدودیت مبلغ">
                            <div class="form-text">فقط وقتی مبلغ پرداخت/معامله بیشتر از این مقدار باشد فعال شود. خالی بگذارید = بدون محدودیت</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بخش ۲: انتخاب اقدام -->
            <div class="rounded-3 p-3 p-md-4 mb-3" style="background:var(--primary-light);border:1px solid #c7d2fe;">
                <h6 class="d-flex align-items-center gap-2 mb-1">⚡ بخش ۲: اقدام (Action)</h6>
                <p class="small text-muted mb-3">اقدام کاری است که پس از فعال شدن ماشه، به صورت خودکار انجام می‌شود.</p>
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">نوع اقدام *</label>
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
                <div id="actionDescription" class="d-none bg-white p-3 rounded-3 mb-3" style="border-right:3px solid var(--info);">
                    <p id="actionDescText" class="small text-muted mb-0"></p>
                </div>

                <!-- تنظیمات پیامک سفارشی -->
                <div id="config-send_sms" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3">
                        <div class="rounded-3 p-3 mb-3 small" style="background:#fffbeb;border:1px solid #fcd34d;">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> با استفاده از متغیرهای داخل آکولاد مثل <code>{contact_name}</code> می‌توانید متن پویا بسازید. مثلاً: <em>«سلام {contact_name} عزیز، معامله {deal_title} شما ثبت شد.»</em>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">شماره گیرنده</label>
                            <select name="action_config[phone_field]" class="form-select">
                                <option value="contact"><i class="bi bi-telephone me-1"></i> شماره مخاطب معامله</option>
                            </select>
                            <div class="form-text">پیامک به شماره تلفن مخاطب متصل به معامله ارسال می‌شود</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-journal-text me-1"></i> متن پیامک *</label>
                            <textarea name="action_config[message_template]" class="form-control" rows="4" placeholder="سلام {contact_name} عزیز، معامله {deal_title} شما با مبلغ {amount} ثبت شد."></textarea>
                            <div class="form-text">متنی که می‌خواهید پیامک شود. از متغیرهای زیر استفاده کنید.</div>
                        </div>
                        <div id="placeholderHelp-sms" class="rounded-3 p-3 small" style="background:#f8f9fa;">
                            <strong>🔤 متغیرهای قابل استفاده:</strong>
                            <div id="placeholderList-sms" class="row g-1 mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات ارسال پیامک لینک پرداخت -->
                <div id="config-send_payment_sms" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3">
                        <div class="rounded-3 p-3 mb-3" style="background:#ecfdf5;border:1px solid #a7f3d0;">
                            <p class="small mb-0" style="color:#065f46;">
                                <i class="bi bi-lightbulb me-1"></i> <strong>نحوه کار:</strong> این اقدام آخرین لینک کوتاه پرداخت معامله را پیدا کرده و همراه متن پیامک به مشتری ارسال می‌کند. 
                                اگر متن پیامک را خالی بگذارید، متن پیش‌فرض ارسال می‌شود (شامل نام مخاطب، مبلغ و لینک کوتاه پرداخت).
                                <br>⚠️ <strong>نکته:</strong> حتماً این اقدام را با ماشه «<strong>ایجاد لینک پرداخت</strong>» یا «<strong>تایید پرداخت</strong>» استفاده کنید.
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium"><i class="bi bi-journal-text me-1"></i> متن پیامک (اختیاری)</label>
                            <textarea name="action_config[message_template]" class="form-control" rows="3" placeholder="خالی بگذارید تا متن پیش‌فرض ارسال شود. یا متن دلخواه: سلام {contact_name} عزیز، لینک پرداخت شما: {payment_short_link}"></textarea>
                            <div class="form-text">خالی = متن پیش‌فرض | سفارشی = از متغیرهای زیر استفاده کنید</div>
                        </div>
                        <div id="placeholderHelp-payment_sms" class="rounded-3 p-3 small" style="background:#f8f9fa;">
                            <strong>🔤 متغیرهای قابل استفاده:</strong>
                            <div id="placeholderList-payment_sms" class="row g-1 mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات ارسال اعلان -->
                <div id="config-send_notification" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3">
                        <div class="rounded-3 p-3 mb-3 small" style="background:#eff6ff;border:1px solid #bfdbfe;">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> اعلان داخلی در سیستم نمایش داده می‌شود. می‌توانید به خودتان یا کاربر دیگری اعلان بفرستید.
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">عنوان اعلان</label>
                            <input type="text" name="action_config[title]" class="form-control" placeholder="مثال: پرداخت جدید برای {deal_title}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">متن اعلان</label>
                            <input type="text" name="action_config[message]" class="form-control" placeholder="مثال: مبلغ {amount} تومان پرداخت شد">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">شناسه کاربر گیرنده</label>
                            <input type="number" name="action_config[user_id]" class="form-control" placeholder="شناسه عددی کاربر">
                            <div class="form-text">خالی بگذارید = مسئول معامله اعلان را دریافت می‌کند. شناسه عددی کاربر مورد نظر را وارد کنید.</div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات ایجاد فعالیت -->
                <div id="config-create_activity" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3">
                        <div class="rounded-3 p-3 mb-3 small" style="background:#fffbeb;border:1px solid #fcd34d;">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> یک فعالیت جدید (مثلاً یادآوری پیگیری تلفنی) به صورت خودکار برای معامله ایجاد می‌شود. می‌توانید مشخص کنید چند روز بعد از ماشه، این فعالیت ایجاد شود.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">موضوع فعالیت</label>
                                <input type="text" name="action_config[subject]" class="form-control" placeholder="مثال: پیگیری تلفنی پرداخت">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">نوع فعالیت</label>
                                <select name="action_config[activity_type]" class="form-select">
                                    <?php foreach ($activityTypes as $k => $v): ?>
                                    <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">تعداد روز بعد از ماشه</label>
                                <input type="number" name="action_config[days]" class="form-control" value="1" min="0">
                                <div class="form-text">0 = همان لحظه | 1 = فردا | 7 = یک هفته بعد</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">توضیحات فعالیت</label>
                                <input type="text" name="action_config[description]" class="form-control" placeholder="توضیح اضافی اختیاری">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات تخصیص کاربر -->
                <div id="config-assign_user" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3">
                        <div class="rounded-3 p-3 mb-3 small" style="background:#eff6ff;border:1px solid #bfdbfe;">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> معامله به صورت خودکار به کاربر مشخصی اختصاص داده می‌شود. مثلاً هنگام ورود به مرحله «رزرو بلیط» به کاربر بخش بلیط اختصاص یابد.
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">شناسه کاربر مسئول</label>
                            <input type="number" name="action_config[assign_to]" class="form-control" placeholder="شناسه عددی کاربر">
                            <div class="form-text">شناسه عددی کاربر مورد نظر را وارد کنید (از بخش مدیریت کاربران قابل مشاهده است)</div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات بروزرسانی فیلد -->
                <div id="config-update_deal_field" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3">
                        <div class="rounded-3 p-3 mb-3 small" style="background:#eff6ff;border:1px solid #bfdbfe;">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> فیلد مشخصی از معامله به صورت خودکار بروزرسانی می‌شود.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">فیلد مورد نظر</label>
                                <select name="action_config[field]" class="form-select">
                                    <option value="source">منبع آشنایی</option>
                                    <option value="priority">اولویت</option>
                                    <option value="tags">برچسب‌ها</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">مقدار جدید</label>
                                <input type="text" name="action_config[value]" class="form-control" placeholder="مقدار جدید">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- مثال‌های رایج -->
            <div class="rounded-3 p-3 mb-3" style="background:#f0fdf4;border:1px solid #86efac;">
                <h6 class="mb-3"><i class="bi bi-lightbulb me-1"></i> مثال‌های رایج برای آژانس هواپیمایی:</h6>
                <div class="row g-3 small">
                    <div class="col-md-6">
                        <div class="bg-white p-3 rounded-3">
                            <strong>📨 ارسال خودکار لینک پرداخت:</strong><br>
                            ماشه: ایجاد لینک پرداخت → اقدام: ارسال پیامک لینک پرداخت
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-white p-3 rounded-3">
                            <strong><i class="bi bi-check-circle text-success me-1"></i> تایید پرداخت:</strong><br>
                            ماشه: تایید پرداخت → اقدام: ارسال پیامک + اعلان به مدیر
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-white p-3 rounded-3">
                            <strong><i class="bi bi-telephone me-1"></i> پیگیری خودکار:</strong><br>
                            ماشه: ایجاد معامله → اقدام: ایجاد فعالیت (۳ روز بعد)
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-white p-3 rounded-3">
                            <strong><i class="bi bi-arrow-repeat me-1"></i> انتقال به بخش بلیط:</strong><br>
                            ماشه: تغییر مرحله → اقدام: تخصیص به کاربر بلیط
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-1"></i>ذخیره قانون</button>
                <a href="<?php echo $config['url']; ?>/automation" class="btn btn-outline-secondary">انصراف</a>
            </div>
        </form>
    </div>
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
        opt.textContent = p.name + (p.is_active ? '' : ' (غیرفعال)');
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
        descDiv.classList.remove('d-none');
    } else {
        descDiv.classList.add('d-none');
    }

    // Show/hide conditions
    var needsStage = val === 'stage_change';
    var needsAmount = val === 'payment_created' || val === 'payment_verified' || val === 'deal_created';
    
    var stageEl = document.getElementById('stageConditions');
    var amountEl = document.getElementById('amountCondition');
    
    if (needsStage) { stageEl.classList.remove('d-none'); } else { stageEl.classList.add('d-none'); }
    if (needsAmount) { amountEl.classList.remove('d-none'); } else { amountEl.classList.add('d-none'); }

    // Update variable help
    updateTriggerVarHelp(val);
    updatePlaceholderHelp();
}

function updateTriggerVarHelp(triggerType) {
    var helpDiv = document.getElementById('triggerVarHelp');
    var listDiv = document.getElementById('triggerVarList');
    
    if (!triggerType || !triggerTypes[triggerType]) {
        helpDiv.classList.add('d-none');
        return;
    }
    
    helpDiv.classList.remove('d-none');
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
        vars['{payment_amount}'] = '<i class="bi bi-cash me-1"></i> مبلغ پرداخت (تومان)';
    }
    
    var html = '';
    for (var key in vars) {
        html += '<div class="col-md-6"><code class="bg-primary bg-opacity-10 px-2 py-1 rounded">' + key + '</code> → ' + vars[key] + '</div>';
    }
    listDiv.innerHTML = html;
}

function onActionChange() {
    var val = document.getElementById('actionType').value;
    
    // Show description
    var descDiv = document.getElementById('actionDescription');
    if (val && actionTypes[val]) {
        document.getElementById('actionDescText').innerHTML = actionTypes[val].description;
        descDiv.classList.remove('d-none');
    } else {
        descDiv.classList.add('d-none');
    }

    // Show/hide configs
    document.querySelectorAll('.action-config').forEach(function(el) { el.classList.add('d-none'); });
    var target = document.getElementById('config-' + val);
    if (target) target.classList.remove('d-none');

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
        placeholders['{payment_amount}'] = '<i class="bi bi-cash me-1"></i> مبلغ پرداخت (تومان)';
    }
    
    var html = '';
    for (var key in placeholders) {
        html += '<div class="col-md-6"><code class="bg-primary bg-opacity-10 px-2 py-1 rounded">' + key + '</code> → ' + placeholders[key] + '</div>';
    }
    listEl.innerHTML = html;
}
</script>