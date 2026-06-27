<?php $config = $GLOBALS['app_config']; 
$triggerTypes = $triggerTypes ?? [];
$actionTypes = $actionTypes ?? [];
$activityTypes = $activityTypes ?? ['follow_up'=>'<i class="bi bi-pin me-1"></i> پیگیری','call'=>'<i class="bi bi-telephone me-1"></i> تماس','meeting'=>'🤝 جلسه','note'=>'<i class="bi bi-journal-text me-1"></i> یادداشت','email'=>'📧 ایمیل','other'=>'<i class="bi bi-list-task me-1"></i> سایر'];
$triggerTypesJson = json_encode($triggerTypes);
$actionTypesJson = json_encode($actionTypes);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>قانون اتوماسیون جدید</h5>
    <a href="<?php echo $config['url']; ?>/automation" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
</div>

<!-- راهنمای کلی - ریسپانسیو و زیبا و سازگار با بوت استرپ -->
<div class="card mb-4 border-0 shadow-sm overflow-hidden" style="border-right: 5px solid var(--primary) !important;">
    <div class="card-body p-3 p-md-4" style="background: linear-gradient(135deg, #f0f4ff, #e8f0fe);">
        <div class="d-flex align-items-start gap-3 mb-3">
            <span style="font-size:32px;" class="flex-shrink-0">🤖</span>
            <div>
                <h5 class="fw-bold mb-1">اتوماسیون چیست؟</h5>
                <p class="text-muted small mb-0">با اتوماسیون می‌توانید کارهای تکراری را خودکار کنید. یک قانون اتوماسیون از دو بخش تشکیل شده: <strong>ماشه</strong> (رویدادی که قانون را فعال می‌کند) و <strong>اقدام</strong> (کاری که خودکار انجام می‌شود).</p>
            </div>
        </div>
        <div class="row g-3 small">
            <div class="col-12 col-md-6">
                <div class="bg-white p-3 rounded shadow-xs h-100">
                    <strong class="text-primary"><i class="bi bi-fire me-1"></i> ماشه‌های موجود:</strong>
                    <ul class="mb-0 mt-2 pe-3 text-muted">
                        <li class="mb-1"><strong>تغییر مرحله:</strong> وقتی معامله به مرحله خاصی منتقل می‌شود</li>
                        <li class="mb-1"><strong>ایجاد معامله:</strong> وقتی معامله جدیدی ثبت می‌شود</li>
                        <li class="mb-1"><strong>برد/باخت:</strong> وقتی معامله موفق یا ناموفق می‌شود</li>
                        <li class="mb-1"><strong>ایجاد لینک پرداخت:</strong> وقتی برای معامله لینک پرداخت ساخته می‌شود</li>
                        <li class="mb-1"><strong>تایید پرداخت:</strong> وقتی پرداختی با موفقیت تایید می‌شود</li>
                        <li class="mb-0"><strong>مخاطب جدید:</strong> وقتی مخاطب جدیدی اضافه می‌شود</li>
                    </ul>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="bg-white p-3 rounded shadow-xs h-100">
                    <strong class="text-success"><i class="bi bi-lightning-charge me-1"></i> اقدام‌های موجود:</strong>
                    <ul class="mb-0 mt-2 pe-3 text-muted">
                        <li class="mb-1"><strong>ارسال پیامک:</strong> پیامک سفارشی به مخاطب</li>
                        <li class="mb-1"><strong>ارسال لینک پرداخت:</strong> پیامک با لینک کوتاه پرداخت</li>
                        <li class="mb-1"><strong>ارسال اعلان:</strong> اعلان داخلی به کاربر سیستم</li>
                        <li class="mb-1"><strong>ایجاد فعالیت:</strong> ایجاد یادآوری/پیگیری خودکار</li>
                        <li class="mb-1"><strong>تخصیص کاربر:</strong> اختصاص معامله به کاربر مشخص</li>
                        <li class="mb-0"><strong>بروزرسانی فیلد:</strong> تغییر خودکار فیلدهای معامله</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="POST" action="<?php echo $config['url']; ?>/automation/store" id="automationForm">
            
            <!-- بخش ۰: نام قانون -->
            <div class="mb-4">
                <h6 class="d-flex align-items-center gap-2 mb-3 fw-bold text-dark border-bottom pb-2"><i class="bi bi-journal-text text-primary"></i> اطلاعات قانون (بخش ۰)</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted small fw-semibold">نام قانون *</label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: ارسال لینک پرداخت به مشتری">
                        <div class="form-text small text-muted">نام واضح و قابل تشخیص انتخاب کنید تا بعداً بتوانید آن را پیدا کنید</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted small fw-semibold">توضیحات</label>
                        <input type="text" name="description" class="form-control" placeholder="مثال: پس از ایجاد لینک پرداخت، لینک کوتاه پیامک شود">
                        <div class="form-text small text-muted">توضیح کوتاه برای یادآوری عملکرد این قانون</div>
                    </div>
                </div>
            </div>

            <!-- بخش ۱: انتخاب ماشه -->
            <div class="rounded-3 p-3 p-md-4 mb-3 bg-light border">
                <h6 class="d-flex align-items-center gap-2 mb-1 fw-bold text-dark"><i class="bi bi-fire text-danger"></i> بخش ۱: ماشه (Trigger)</h6>
                <p class="small text-muted mb-3">ماشه رویدادی است که باعث فعال شدن این قانون می‌شود. ابتدا نوع ماشه را انتخاب کنید، سپس فیلترهای دلخواه را تعیین کنید.</p>
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">رویداد ماشه *</label>
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
                    <div class="form-text small text-muted">رویدادی که باعث اجرای خودکار این قانون می‌شود را انتخاب کنید</div>
                </div>

                <!-- توضیحات ماشه انتخاب شده -->
                <div id="triggerDescription" class="d-none bg-white p-3 rounded-3 mb-3 border-start border-primary border-3">
                    <p id="triggerDescText" class="small text-muted mb-0"></p>
                </div>

                <!-- راهنمای متغیرها -->
                <div id="triggerVarHelp" class="d-none p-3 rounded-3 mb-3 small bg-white border border-info">
                    <strong class="text-info"><i class="bi bi-type me-1"></i> متغیرهای قابل استفاده در متن پیامک:</strong>
                    <div id="triggerVarList" class="row g-2 mt-2"></div>
                </div>

                <!-- شرایط فیلتر: پایپ‌لاین و مرحله -->
                <div id="stageConditions" class="d-none">
                    <div class="bg-white p-3 rounded-3 mb-3 border">
                        <p class="small text-muted mb-3">🔽 <strong>فیلتر اختیاری:</strong> اگر خالی بگذارید، قانون برای همه پایپ‌لاین‌ها و مراحل اعمال می‌شود</p>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-semibold">پایپ‌لاین مشخص</label>
                                <select name="trigger_conditions[pipeline_id]" class="form-select" id="pipelineSelect" onchange="loadStages()">
                                    <option value="">همه پایپ‌لاین‌ها</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-semibold">مرحله مشخص</label>
                                <select name="trigger_conditions[stage_id]" class="form-select" id="stageSelect">
                                    <option value="">همه مراحل</option>
                                </select>
                                <div class="form-text small text-muted">فقط وقتی معامله به این مرحله منتقل شود قانون فعال شود</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- شرط حداقل مبلغ -->
                <div id="amountCondition" class="d-none">
                    <div class="bg-white p-3 rounded-3 border">
                        <div class="mb-0">
                            <label class="form-label text-muted small fw-semibold">حداقل مبلغ (تومان)</label>
                            <input type="number" name="trigger_conditions[min_amount]" class="form-control" placeholder="خالی = بدون محدودیت مبلغ">
                            <div class="form-text small text-muted">فقط وقتی مبلغ پرداخت/معامله بیشتر از این مقدار باشد فعال شود. خالی بگذارید = بدون محدودیت</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بخش ۲: انتخاب اقدام -->
            <div class="rounded-3 p-3 p-md-4 mb-3 border" style="background: var(--primary-light);">
                <h6 class="d-flex align-items-center gap-2 mb-1 fw-bold text-dark"><i class="bi bi-lightning-charge text-warning"></i> بخش ۲: اقدام (Action)</h6>
                <p class="small text-muted mb-3">اقدام کاری است که پس از فعال شدن ماشه، به صورت خودکار انجام می‌شود.</p>
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">نوع اقدام *</label>
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
                    <div class="bg-white p-3 rounded-3 border">
                        <div class="rounded-3 p-3 mb-3 small alert alert-warning border border-warning">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> با استفاده از متغیرهای داخل آکولاد مثل <code>{contact_name}</code> می‌توانید متن پویا بسازید. مثلاً: <em>«سلام {contact_name} عزیز، معامله {deal_title} شما ثبت شد.»</em>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">شماره گیرنده</label>
                            <select name="action_config[phone_field]" class="form-select">
                                <option value="contact">شماره مخاطب معامله</option>
                            </select>
                            <div class="form-text small text-muted">پیامک به شماره تلفن مخاطب متصل به معامله ارسال می‌شود</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold"><i class="bi bi-journal-text me-1"></i> متن پیامک *</label>
                            <textarea name="action_config[message_template]" class="form-control" rows="4" placeholder="سلام {contact_name} عزیز، معامله {deal_title} شما با مبلغ {amount} ثبت شد."></textarea>
                            <div class="form-text small text-muted">متنی که می‌خواهید پیامک شود. از متغیرهای زیر استفاده کنید.</div>
                        </div>
                        <div id="placeholderHelp-sms" class="rounded-3 p-3 small bg-light border">
                            <strong>🔤 متغیرهای قابل استفاده:</strong>
                            <div id="placeholderList-sms" class="row g-2 mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات ارسال پیامک لینک پرداخت -->
                <div id="config-send_payment_sms" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3 border">
                        <div class="rounded-3 p-3 mb-3 alert alert-success border border-success">
                            <p class="small mb-0">
                                <i class="bi bi-lightbulb me-1"></i> <strong>نحوه کار:</strong> این اقدام آخرین لینک کوتاه پرداخت معامله را پیدا کرده و همراه متن پیامک به مشتری ارسال می‌کند. 
                                اگر متن پیامک را خالی بگذارید، متن پیش‌فرض ارسال می‌شود (شامل نام مخاطب، مبلغ و لینک کوتاه پرداخت).
                                <br>⚠️ <strong>نکته:</strong> حتماً این اقدام را با ماشه «<strong>ایجاد لینک پرداخت</strong>» یا «<strong>تایید پرداخت</strong>» استفاده کنید.
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold"><i class="bi bi-journal-text me-1"></i> متن پیامک (اختیاری)</label>
                            <textarea name="action_config[message_template]" class="form-control" rows="3" placeholder="خالی بگذارید تا متن پیش‌فرض ارسال شود. یا متن دلخواه: سلام {contact_name} عزیز، لینک پرداخت شما: {payment_short_link}"></textarea>
                            <div class="form-text small text-muted">خالی = متن پیش‌فرض | سفارشی = از متغیرهای زیر استفاده کنید</div>
                        </div>
                        <div id="placeholderHelp-payment_sms" class="rounded-3 p-3 small bg-light border">
                            <strong>🔤 متغیرهای قابل استفاده:</strong>
                            <div id="placeholderList-payment_sms" class="row g-2 mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات ارسال اعلان -->
                <div id="config-send_notification" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3 border">
                        <div class="rounded-3 p-3 mb-3 small alert alert-info border border-info">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> اعلان داخلی در سیستم نمایش داده می‌شود. می‌توانید به خودتان یا کاربر دیگری اعلان بفرستید.
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">عنوان اعلان</label>
                            <input type="text" name="action_config[title]" class="form-control" placeholder="مثال: پرداخت جدید برای {deal_title}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">متن اعلان</label>
                            <input type="text" name="action_config[message]" class="form-control" placeholder="مثال: مبلغ {amount} تومان پرداخت شد">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">شناسه کاربر گیرنده</label>
                            <input type="number" name="action_config[user_id]" class="form-control" placeholder="مثال: 5">
                            <div class="form-text small text-muted">خالی بگذارید = مسئول معامله اعلان را دریافت می‌کند. شناسه عددی کاربر مورد نظر را وارد کنید.</div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات ایجاد فعالیت -->
                <div id="config-create_activity" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3 border">
                        <div class="rounded-3 p-3 mb-3 small alert alert-warning border border-warning">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> یک فعالیت جدید (مثلاً یادآوری پیگیری تلفنی) به صورت خودکار برای معامله ایجاد می‌شود. می‌توانید مشخص کنید چند ساعت بعد از ماشه ایجاد شود.
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-semibold">موضوع فعالیت</label>
                                <input type="text" name="action_config[subject]" class="form-control" placeholder="مثال: پیگیری تلفنی پرداخت">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-semibold">نوع فعالیت</label>
                                <select name="action_config[activity_type]" class="form-select">
                                    <?php foreach ($activityTypes as $k => $v): ?>
                                    <option value="<?php echo $k; ?>"><?php echo strip_tags($v); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-muted small fw-semibold">زمان اجرا بعد از ماشه</label>
                                <div class="row g-2 align-items-center">
                                    <div class="col-6 col-sm-auto">
                                        <div class="input-group">
                                            <input type="number" name="action_config[delay_hours]" class="form-control" value="0" min="0" max="720" id="delayHours">
                                            <span class="input-group-text">ساعت</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-auto">
                                        <div class="input-group">
                                            <input type="number" name="action_config[delay_minutes]" class="form-control" value="0" min="0" max="59" id="delayMinutes">
                                            <span class="input-group-text">دقیقه</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text small text-muted">مثال: ۲ ساعت و ۳۰ دقیقه بعد از وقوع ماشه | ۰ و ۰ = همان لحظه</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small fw-semibold">توضیحات فعالیت</label>
                                <input type="text" name="action_config[description]" class="form-control" placeholder="توضیح اضافی اختیاری">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات تخصیص کاربر -->
                <div id="config-assign_user" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3 border">
                        <div class="rounded-3 p-3 mb-3 small alert alert-info border border-info">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> معامله به صورت خودکار به کاربر مشخصی اختصاص داده می‌شود. مثلاً هنگام ورود به مرحله «رزرو بلیط» به کاربر بخش بلیط اختصاص یابد.
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">شناسه کاربر مسئول</label>
                            <input type="number" name="action_config[assign_to]" class="form-control" placeholder="مثال: 3">
                            <div class="form-text small text-muted">شناسه عددی کاربر مورد نظر را وارد کنید (از بخش مدیریت کاربران قابل مشاهده است)</div>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات بروزرسانی فیلد -->
                <div id="config-update_deal_field" class="action-config d-none">
                    <div class="bg-white p-3 rounded-3 border">
                        <div class="rounded-3 p-3 mb-3 small alert alert-info border border-info">
                            <i class="bi bi-lightbulb me-1"></i> <strong>راهنما:</strong> فیلد مشخصی از معامله به صورت خودکار بروزرسانی می‌شود.
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-semibold">فیلد مورد نظر</label>
                                <select name="action_config[field]" class="form-select">
                                    <option value="source">منبع آشنایی</option>
                                    <option value="priority">اولویت</option>
                                    <option value="tags">برچسب‌ها</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-semibold">مقدار جدید</label>
                                <input type="text" name="action_config[value]" class="form-control" placeholder="مقدار جدید">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- مثال‌های رایج -->
            <div class="rounded-3 p-3 mb-4 border" style="background:#f0fdf4;border-color: #bbf7d0 !important;">
                <h6 class="mb-3 fw-bold text-success"><i class="bi bi-lightbulb-fill me-1"></i> مثال‌های رایج برای آژانس هواپیمایی:</h6>
                <div class="row g-3 small">
                    <div class="col-12 col-md-6">
                        <div class="bg-white p-3 rounded border">
                            <strong>📨 ارسال خودکار لینک پرداخت:</strong><br>
                            <span class="text-muted">ماشه: ایجاد لینک پرداخت → اقدام: ارسال پیامک لینک پرداخت</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="bg-white p-3 rounded border">
                            <strong><i class="bi bi-check-circle text-success me-1"></i> تایید پرداخت:</strong><br>
                            <span class="text-muted">ماشه: تایید پرداخت → اقدام: ارسال پیامک + اعلان به مدیر</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="bg-white p-3 rounded border">
                            <strong><i class="bi bi-telephone text-primary me-1"></i> پیگیری خودکار:</strong><br>
                            <span class="text-muted">ماشه: ایجاد معامله → اقدام: ایجاد فعالیت (۳ روز بعد)</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="bg-white p-3 rounded border">
                            <strong><i class="bi bi-arrow-repeat text-warning me-1"></i> انتقال به بخش بلیط:</strong><br>
                            <span class="text-muted">ماشه: تغییر مرحله → اقدام: تخصیص به کاربر بلیط</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i>ذخیره قانون</button>
                <a href="<?php echo $config['url']; ?>/automation" class="btn btn-outline-secondary px-4">انصراف</a>
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