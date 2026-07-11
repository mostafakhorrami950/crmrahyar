<h2 style="font-size: 18px; font-weight: 800; margin-bottom: 20px;">⚙️ تنظیمات سایت</h2>
<form method="POST" class="card">
    <div class="form-row">
        <div class="form-group"><label>عنوان سایت</label><input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>"></div>
        <div class="form-group"><label>نام شرکت</label><input type="text" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>"></div>
    </div>
    <div class="form-group"><label>توضیحات سایت</label><textarea name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea></div>
    <div class="form-row">
        <div class="form-group"><label>تلفن</label><input type="text" name="site_phone" value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>"></div>
        <div class="form-group"><label>ایمیل</label><input type="email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>"></div>
    </div>
    <div class="form-group"><label>آدرس</label><input type="text" name="site_address" value="<?php echo htmlspecialchars($settings['site_address'] ?? ''); ?>"></div>
    <div class="form-row">
        <div class="form-group"><label>لوگو (URL)</label><input type="text" name="site_logo" value="<?php echo htmlspecialchars($settings['site_logo'] ?? ''); ?>" dir="ltr"></div>
        <div class="form-group"><label>متن فوتر</label><input type="text" name="footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?>"></div>
    </div>
    <button type="submit" class="btn btn-primary">💾 ذخیره تنظیمات</button>
</form>