<?php $config = $GLOBALS['app_config']; 
$triggerConditions = json_decode($rule->trigger_conditions, true) ?: [];
$actionConfig = json_decode($rule->action_config, true) ?: [];
?>
<div class="page-header">
    <h5>✏️ ویرایش قانون: <?php echo htmlspecialchars($rule->name); ?></h5>
    <a href="<?php echo $config['url']; ?>/automation" class="btn btn-secondary">بازگشت</a>
</div>

<div class="card" style="max-width:700px;">
    <form method="POST" action="<?php echo $config['url']; ?>/automation/update/<?php echo $rule->id; ?>">
        <div class="form-group">
            <label class="form-label">نام قانون *</label>
            <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($rule->name); ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">توضیحات</label>
            <textarea name="description" class="form-textarea" rows="2"><?php echo htmlspecialchars($rule->description ?? ''); ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">ماشه (Trigger) *</label>
                <select name="trigger_type" class="form-select" required>
                    <?php foreach (['stage_change'=>'🔄 تغییر مرحله','deal_created'=>'💼 ایجاد معامله','deal_won'=>'🏆 برد معامله','deal_lost'=>'😞 باخت معامله','payment_received'=>'💳 دریافت پرداخت','new_contact'=>'👤 مخاطب جدید','activity_reminder'=>'⏰ یادآوری فعالیت'] as $k=>$v): ?>
                    <option value="<?php echo $k; ?>" <?php echo $rule->trigger_type === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">اقدام (Action) *</label>
                <select name="action_type" class="form-select" required onchange="showActionConfig(this.value)">
                    <?php foreach (['send_sms'=>'✉️ ارسال پیامک','send_notification'=>'🔔 ارسال اعلان','create_activity'=>'📅 ایجاد فعالیت','assign_user'=>'👤 تخصیص کاربر'] as $k=>$v): ?>
                    <option value="<?php echo $k; ?>" <?php echo $rule->action_type === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group" style="background:var(--gray-50);padding:16px;border-radius:8px;">
            <label class="form-label fw-bold">🔍 شرایط ماشه</label>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label fs-12">شناسه مرحله</label>
                    <input type="number" name="trigger_conditions[stage_id]" class="form-input" value="<?php echo $triggerConditions['stage_id'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label fs-12">شناسه پایپ‌لاین</label>
                    <input type="number" name="trigger_conditions[pipeline_id]" class="form-input" value="<?php echo $triggerConditions['pipeline_id'] ?? ''; ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label fs-12">حداقل مبلغ</label>
                <input type="number" name="trigger_conditions[min_amount]" class="form-input" value="<?php echo $triggerConditions['min_amount'] ?? ''; ?>">
            </div>
        </div>

        <div class="form-group" style="background:var(--primary-light);padding:16px;border-radius:8px;">
            <label class="form-label fw-bold">⚙️ تنظیمات اقدام</label>
            <div id="config-sms" style="display:<?php echo $rule->action_type==='send_sms'?'block':'none'; ?>;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label fs-12">کد الگوی پیامک</label>
                        <input type="text" name="action_config[pattern_code]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['pattern_code'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label fs-12">تلفن</label>
                        <select name="action_config[phone_field]" class="form-select"><option value="contact">از مخاطب معامله</option></select>
                    </div>
                </div>
            </div>
            <div id="config-notification" style="display:<?php echo $rule->action_type==='send_notification'?'block':'none'; ?>;">
                <div class="form-group">
                    <label class="form-label fs-12">عنوان اعلان</label>
                    <input type="text" name="action_config[title]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label fs-12">متن اعلان</label>
                    <input type="text" name="action_config[message]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['message'] ?? ''); ?>">
                </div>
            </div>
            <div id="config-activity" style="display:<?php echo $rule->action_type==='create_activity'?'block':'none'; ?>;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label fs-12">موضوع</label>
                        <input type="text" name="action_config[subject]" class="form-input" value="<?php echo htmlspecialchars($actionConfig['subject'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label fs-12">تعداد روز بعد</label>
                        <input type="number" name="action_config[days]" class="form-input" value="<?php echo $actionConfig['days'] ?? 1; ?>">
                    </div>
                </div>
            </div>
            <div id="config-assign" style="display:<?php echo $rule->action_type==='assign_user'?'block':'none'; ?>;">
                <div class="form-group">
                    <label class="form-label fs-12">شناسه کاربر</label>
                    <input type="number" name="action_config[assign_to]" class="form-input" value="<?php echo $actionConfig['assign_to'] ?? ''; ?>">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="toggle-switch">
                <input type="checkbox" name="is_active" <?php echo $rule->is_active ? 'checked' : ''; ?>>
                <span class="toggle-slider"></span>
            </label>
            <span style="margin-right:8px;font-size:14px;">فعال</span>
        </div>

        <button type="submit" class="btn btn-primary">💾 بروزرسانی</button>
    </form>
</div>

<script>
function showActionConfig(val) {
    ['sms','notification','activity','assign'].forEach(function(t){ var el=document.getElementById('config-'+t); if(el) el.style.display='none'; });
    var map={'send_sms':'sms','send_notification':'notification','create_activity':'activity','assign_user':'assign'};
    var target=map[val]; if(target){var el=document.getElementById('config-'+target); if(el) el.style.display='block';}
}
</script>