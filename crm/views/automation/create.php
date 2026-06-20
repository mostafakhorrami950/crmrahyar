<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>➕ قانون اتوماسیون جدید</h5>
    <a href="<?php echo $config['url']; ?>/automation" class="btn btn-secondary">بازگشت</a>
</div>

<div class="card" style="max-width:700px;">
    <form method="POST" action="<?php echo $config['url']; ?>/automation/store">
        <div class="form-group">
            <label class="form-label">نام قانون *</label>
            <input type="text" name="name" class="form-input" required placeholder="مثال: ارسال پیامک هنگام برد معامله">
        </div>
        <div class="form-group">
            <label class="form-label">توضیحات</label>
            <textarea name="description" class="form-textarea" rows="2"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">ماشه (Trigger) *</label>
                <select name="trigger_type" class="form-select" required>
                    <option value="">انتخاب کنید...</option>
                    <option value="stage_change">🔄 تغییر مرحله</option>
                    <option value="deal_created">💼 ایجاد معامله جدید</option>
                    <option value="deal_won">🏆 برد معامله</option>
                    <option value="deal_lost">😞 باخت معامله</option>
                    <option value="payment_received">💳 دریافت پرداخت</option>
                    <option value="new_contact">👤 مخاطب جدید</option>
                    <option value="activity_reminder">⏰ یادآوری فعالیت</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">اقدام (Action) *</label>
                <select name="action_type" class="form-select" required onchange="showActionConfig(this.value)">
                    <option value="">انتخاب کنید...</option>
                    <option value="send_sms">✉️ ارسال پیامک</option>
                    <option value="send_notification">🔔 ارسال اعلان</option>
                    <option value="create_activity">📅 ایجاد فعالیت</option>
                    <option value="assign_user">👤 تخصیص کاربر</option>
                </select>
            </div>
        </div>

        <!-- Trigger conditions -->
        <div class="form-group" style="background:var(--gray-50);padding:16px;border-radius:8px;">
            <label class="form-label fw-bold">🔍 شرایط ماشه (اختیاری)</label>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label fs-12">شناسه مرحله</label>
                    <input type="number" name="trigger_conditions[stage_id]" class="form-input" placeholder="برای stage_change">
                </div>
                <div class="form-group">
                    <label class="form-label fs-12">شناسه پایپ‌لاین</label>
                    <input type="number" name="trigger_conditions[pipeline_id]" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label fs-12">حداقل مبلغ</label>
                <input type="number" name="trigger_conditions[min_amount]" class="form-input" placeholder="0">
            </div>
        </div>

        <!-- Action config -->
        <div class="form-group" style="background:var(--primary-light);padding:16px;border-radius:8px;">
            <label class="form-label fw-bold">⚙️ تنظیمات اقدام</label>
            
            <!-- SMS config -->
            <div id="config-sms" style="display:none;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label fs-12">کد الگوی پیامک</label>
                        <input type="text" name="action_config[pattern_code]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label fs-12">تلفن مخاطب</label>
                        <select name="action_config[phone_field]" class="form-select">
                            <option value="contact">از مخاطب معامله</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Notification config -->
            <div id="config-notification" style="display:none;">
                <div class="form-group">
                    <label class="form-label fs-12">عنوان اعلان (از {deal_title} استفاده کنید)</label>
                    <input type="text" name="action_config[title]" class="form-input" placeholder="معامله {deal_title} تغییر کرد">
                </div>
                <div class="form-group">
                    <label class="form-label fs-12">متن اعلان</label>
                    <input type="text" name="action_config[message]" class="form-input" placeholder="مبلغ: {amount}">
                </div>
            </div>
            
            <!-- Activity config -->
            <div id="config-activity" style="display:none;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label fs-12">موضوع</label>
                        <input type="text" name="action_config[subject]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label fs-12">تعداد روز بعد</label>
                        <input type="number" name="action_config[days]" class="form-input" value="1" min="0">
                    </div>
                </div>
            </div>
            
            <!-- Assign config -->
            <div id="config-assign" style="display:none;">
                <div class="form-group">
                    <label class="form-label fs-12">شناسه کاربر</label>
                    <input type="number" name="action_config[assign_to]" class="form-input">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">💾 ذخیره قانون</button>
    </form>
</div>

<script>
function showActionConfig(val) {
    ['sms','notification','activity','assign'].forEach(function(t){ 
        var el = document.getElementById('config-'+t);
        if(el) el.style.display = 'none';
    });
    var map = {'send_sms':'sms','send_notification':'notification','create_activity':'activity','assign_user':'assign'};
    var target = map[val];
    if(target) {
        var el = document.getElementById('config-'+target);
        if(el) el.style.display = 'block';
    }
}
</script>