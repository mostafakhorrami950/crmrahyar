<h2 style="font-size: 18px; font-weight: 800; margin-bottom: 20px;">⚙️ تنظیمات سایت</h2>

<form method="POST">
    <!-- General -->
    <div class="card" style="margin-bottom: 16px;">
        <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🏢 اطلاعات عمومی</h3>
        <div class="form-row">
            <div class="form-group"><label>عنوان سایت</label><input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? 'رزرو هتل مشهد'); ?>"></div>
            <div class="form-group"><label>نام شرکت</label><input type="text" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? 'آژانس مسافرتی رهیار'); ?>"></div>
        </div>
        <div class="form-group"><label>توضیحات سایت (SEO)</label><textarea name="site_description" rows="3" style="max-width: 100%;"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea></div>
    </div>

    <!-- Contact -->
    <div class="card" style="margin-bottom: 16px;">
        <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">📞 اطلاعات تماس</h3>
        <div class="form-row">
            <div class="form-group"><label>تلفن</label><input type="text" name="site_phone" value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>" dir="ltr"></div>
            <div class="form-group"><label>ایمیل</label><input type="text" name="site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>" dir="ltr"></div>
        </div>
        <div class="form-group"><label>آدرس</label><input type="text" name="site_address" value="<?php echo htmlspecialchars($settings['site_address'] ?? ''); ?>"></div>
    </div>

    <!-- OpenRouter AI -->
    <div class="card" style="margin-bottom: 16px; border: 2px solid #c7d2fe;">
        <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🤖 هوش مصنوعی (OpenRouter)</h3>
        <div class="form-row">
            <div class="form-group">
                <label>API Key</label>
                <input type="password" name="openrouter_api_key" value="<?php echo htmlspecialchars($settings['openrouter_api_key'] ?? ''); ?>" dir="ltr" placeholder="sk-or-v1-...">
            </div>
            <div class="form-group">
                <label>مدل هوش مصنوعی</label>
                <input type="text" name="openrouter_model" value="<?php echo htmlspecialchars($settings['openrouter_model'] ?? 'deepseek/deepseek-v4-pro'); ?>" dir="ltr" placeholder="deepseek/deepseek-v4-pro">
            </div>
        </div>
        <small style="color: #64748b; font-size: 11px;">از این API برای تولید محتوای SEO، توضیحات هتل، و مقالات استفاده می‌شود.</small>
    </div>

    <!-- Other -->
    <div class="card" style="margin-bottom: 16px;">
        <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🎨 سایر</h3>
        <div class="form-group"><label>لوگو (URL)</label><input type="text" name="site_logo" value="<?php echo htmlspecialchars($settings['site_logo'] ?? ''); ?>" dir="ltr"></div>
        <div class="form-group"><label>متن فوتر</label><input type="text" name="footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?>"></div>
    </div>

    <button type="submit" class="btn btn-primary" style="font-size: 15px; padding: 12px 32px;">💾 ذخیره تنظیمات</button>
</form>