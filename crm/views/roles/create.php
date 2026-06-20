<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>➕ ایجاد نقش جدید</h5>
    <a href="<?php echo $config['url']; ?>/roles" class="btn btn-secondary btn-sm">← بازگشت</a>
</div>

<div class="card" style="padding:24px;">
    <form method="POST" action="<?php echo $config['url']; ?>/roles/store">
        <h5 style="font-weight:bold;margin-bottom:16px;">📋 اطلاعات نقش</h5>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">نام نقش *</label>
                <input type="text" name="name" class="form-input" required placeholder="مثال: مدیر فروش، اپراتور، پشتیبانی">
            </div>
            <div class="form-group">
                <label class="form-label">توضیحات</label>
                <input type="text" name="description" class="form-input" placeholder="توضیح کوتاه درباره این نقش">
            </div>
        </div>

        <div style="margin-top:8px;">
            <h5 style="font-weight:bold;margin:20px 0 16px;">🔑 دسترسی‌ها</h5>
            <div style="background:var(--gray-50);padding:12px 16px;border-radius:10px;margin-bottom:16px;">
                <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--gray-600);">
                    <span>🌐 <strong>همه:</strong> دسترسی به تمام داده‌ها</span>
                    <span style="margin:0 8px;">|</span>
                    <span>👤 <strong>فقط خودش:</strong> دسترسی فقط به داده‌های خودش</span>
                </div>
            </div>
            
            <?php 
            $rolePermsMap = [];
            include __DIR__ . '/_permissions.php'; 
            ?>
        </div>

        <div style="display:flex;gap:8px;margin-top:24px;padding-top:16px;border-top:1px solid var(--gray-200);">
            <button type="submit" class="btn btn-primary">💾 ذخیره نقش</button>
            <a href="<?php echo $config['url']; ?>/roles" class="btn btn-secondary">انصراف</a>
        </div>
    </form>
</div>