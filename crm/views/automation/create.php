<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>➕ قانون اتوماسیون جدید</h5>
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
    <form method="POST" action="<?php echo $config['url']; ?>/automation/store" id="automationForm">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">📝 نام قانون *</label>
                <input type="text" name="name" class="form-input" required placeholder="مثال: ارسال پیامک هنگام برد معامله">
                <p class="form-hint">نامی واضح انتخاب کنید تا بعداً بتوانید آن را شناسایی کنید</p>
            </div>
            <div class="form-group">
                <label class="form-label">📄 توضیحات</label>
                <input type="text" name="description" class="form-input" placeholder="توضیح کوتاه اختیاری">
            </div>
        </div>

        <div style="background:var(--gray-50);padding:16px;border-radius:8px;margin-bottom:16px;">
            <h6 style="margin-bottom:12px;">🔥 بخش ۱: ماشه (Trigger) - کی فعال شود؟</h6>
            <div class="form-group">
                <label class="form-label">رویداد ماشه *</label>
                <select name="trigger_type" class="form-select" required id="triggerType">
                    <option value="">— انتخاب کنید —</option>
                    <option value="stage_change">🔄 تغییر مرحله معامله</option>
                    <option value="deal_created">💼 ایجاد معامله جدید</option>
                    <option value="deal_won">🏆 موفق شدن معامله</option>
                    <option value="deal_lost">😞 ناموفق شدن معامله</option>
                    <option value="payment_received">💳 دریافت پرداخت</option>
                    <option value="new_contact">👤 افزودن مخاطب جدید</option>
                    <option value="activity_reminder">⏰ یادآوری فعالیت</option>
                </select>
            </div>

            <!-- Pipeline/Stage selection for stage_change -->
            <div id="stageConditions" style="display:none;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">پایپ‌لاین (اختیاری - همه پایپ‌لاین‌ها)</label>
                        <select name="trigger_conditions[pipeline_id]" class="form-select" id="pipelineSelect" onchange="loadStages()">
                            <option value="">همه پایپ‌لاین‌ها</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">مرحله مقصد (اختیاری - همه مراحل)</label>
                        <select name="trigger_conditions[stage_id]" class="form-select" id="stageSelect">
                            <option value="">همه مراحل</option>
                        </select>
                    </div>
                </div>
                <p class="form-hint">اگر پایپ‌لاین یا مرحله خاصی انتخاب نکنید، قانون برای همه اعمال می‌شود</p>
            </div>
        </div>

        <div style="background:var(--primary-light);padding:16px;border-radius:8px;margin-bottom:16px;">
            <h6 style="margin-bottom:12px;">⚡ بخش ۲: اقدام (Action) - چه کاری انجام شود؟</h6>
            <div class="form-group">
                <label class="form-label">نوع اقدام *</label>
                <select name="action_type" class="form-select" required id="actionType" onchange="showActionConfig()">
                    <option value="">— انتخاب کنید —</option>
                    <option value="send_sms">✉️ ارسال پیامک</option>
                    <option value="send_notification">🔔 ارسال اعلان به کاربر</option>
                    <option value="create_activity">📅 ایجاد فعالیت/یادآوری</option>
                    <option value="assign_user">👤 تخصیص معامله به کاربر</option>
                </select>
            </div>

            <!-- SMS Config -->
            <div id="config-sms" class="action-config" style="display:none;">
                <div class="form-group">
                    <label class="form-label">شماره گیرنده</label>
                    <select name="action_config[phone_field]" class="form-select">
                        <option value="contact">شماره مخاطب معامله</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">📝 متن پیامک *</label>
                    <textarea name="action_config[message_template]" class="form-textarea" rows="5" placeholder="سلام {contact_name} عزیز، معامله {deal_title} شما با مبلغ {amount} ثبت شد. لینک پرداخت: {payment_link}"></textarea>
                </div>
                <div style="background:#fff;padding:12px;border-radius:8px;font-size:12px;">
                    <strong>🔤 متغیرهای قابل استفاده (جایگذاری خودکار):</strong>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px;">
                        <div><code>{contact_name}</code> → نام مخاطب</div>
                        <div><code>{deal_title}</code> → عنوان معامله</div>
                        <div><code>{amount}</code> → مبلغ معامله</div>
                        <div><code>{payment_link}</code> → لینک پرداخت</div>
                        <div><code>{stage_name}</code> → نام مرحله فعلی</div>
                        <div><code>{pipeline_name}</code> → نام پایپ‌لاین</div>
                    </div>
                    <div style="margin-top:8px;padding:8px;background:var(--warning);border-radius:6px;">
                        💡 <strong>مثال:</strong> سلام {contact_name} عزیز، سفر {deal_title} شما آماده شد. برای پرداخت {amount} ریال روی لینک کلیک کنید: {payment_link}
                    </div>
                </div>
            </div>

            <!-- Notification Config -->
            <div id="config-notification" class="action-config" style="display:none;">
                <div class="form-group">
                    <label class="form-label">عنوان اعلان</label>
                    <input type="text" name="action_config[title]" class="form-input" placeholder="مثال: معامله {deal_title} برد شد!">
                    <p class="form-hint">از متغیرهای {deal_title} و {amount} استفاده کنید</p>
                </div>
                <div class="form-group">
                    <label class="form-label">متن اعلان</label>
                    <input type="text" name="action_config[message]" class="form-input" placeholder="مثال: مبلغ {amount} ریال وصول شد">
                </div>
                <div class="form-group">
                    <label class="form-label">شناسه کاربر گیرنده اعلان</label>
                    <input type="number" name="action_config[user_id]" class="form-input" placeholder="شناسه عددی کاربر">
                    <p class="form-hint">شناسه کاربری که اعلان برای او ارسال شود. خالی = مسئول معامله</p>
                </div>
            </div>

            <!-- Activity Config -->
            <div id="config-activity" class="action-config" style="display:none;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">موضوع فعالیت</label>
                        <input type="text" name="action_config[subject]" class="form-input" placeholder="مثال: پیگیری تلفنی">
                    </div>
                    <div class="form-group">
                        <label class="form-label">تعداد روز بعد از ماشه</label>
                        <input type="number" name="action_config[days]" class="form-input" value="1" min="0">
                        <p class="form-hint">0 = همان لحظه، 1 = فردا، 7 = یک هفته بعد</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">نوع فعالیت</label>
                    <select name="action_config[activity_type]" class="form-select">
                        <option value="follow_up">یادآوری / پیگیری</option>
                        <option value="call">تماس تلفنی</option>
                        <option value="meeting">جلسه</option>
                        <option value="note">یادداشت</option>
                        <option value="email">ایمیل</option>
                        <option value="other">سایر</option>
                    </select>
                </div>
            </div>

            <!-- Assign User Config -->
            <div id="config-assign" class="action-config" style="display:none;">
                <div class="form-group">
                    <label class="form-label">شناسه کاربر مسئول</label>
                    <input type="number" name="action_config[assign_to]" class="form-input" placeholder="شناسه عددی کاربر">
                    <p class="form-hint">معامله به این کاربر اختصاص داده می‌شود</p>
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

// Load pipelines on page load
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
            opt.style.borderRight = '4px solid ' + s.color;
            stageSel.appendChild(opt);
        });
    }
}

// Show/hide trigger conditions
document.getElementById('triggerType').addEventListener('change', function() {
    var stageDiv = document.getElementById('stageConditions');
    stageDiv.style.display = this.value === 'stage_change' ? 'block' : 'none';
});

// Show/hide action configs
function showActionConfig() {
    document.querySelectorAll('.action-config').forEach(function(el) { el.style.display = 'none'; });
    var val = document.getElementById('actionType').value;
    var map = {'send_sms':'config-sms','send_notification':'config-notification','create_activity':'config-activity','assign_user':'config-assign'};
    var target = map[val];
    if (target) document.getElementById(target).style.display = 'block';
}
</script>