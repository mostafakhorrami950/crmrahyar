<h2 style="font-size: 18px; font-weight: 800; margin-bottom: 20px;">🔍 مدیریت سئو</h2>

<!-- AI Content Generator -->
<div class="card" style="margin-bottom: 16px; border: 2px solid #c7d2fe;">
    <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🤖 تولید محتوای SEO با هوش مصنوعی</h3>
    <div class="form-row">
        <div class="form-group">
            <label>کلمه کلیدی</label>
            <input type="text" id="aiKeyword" placeholder="مثال: هتل مشهد، رزرو هتل ارزان">
        </div>
        <div class="form-group">
            <label>نوع محتوا</label>
            <select id="aiType">
                <option value="meta">متا تگ‌ها (عنوان + توضیحات)</option>
                <option value="blog">مقاله کامل</option>
                <option value="faq">سوالات متداول</option>
                <option value="hotel_description">توضیحات هتل</option>
            </select>
        </div>
    </div>
    <button type="button" class="btn btn-primary" onclick="generateAI()" id="aiBtn">🤖 تولید با AI</button>
    <div id="aiResult" style="margin-top: 12px; display: none;">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <strong style="font-size: 13px;">نتیجه:</strong>
                <button type="button" class="btn btn-sm btn-secondary" onclick="copyAI()">📋 کپی</button>
            </div>
            <div id="aiContent" style="white-space: pre-wrap; font-size: 13px; direction: rtl; line-height: 1.8;"></div>
        </div>
    </div>
    <div id="aiLoading" style="display: none; padding: 12px; text-align: center; color: #4f46e5;">⏳ در حال تولید محتوا...</div>
</div>

<!-- Keywords Manager -->
<div class="card" style="margin-bottom: 16px;">
    <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🏷️ مدیریت کلمات کلیدی</h3>
    <form method="POST" action="/admin/seo" style="margin-bottom: 16px;">
        <input type="hidden" name="add_keyword" value="1">
        <div class="form-row-3">
            <div class="form-group">
                <label>کلمه کلیدی</label>
                <input type="text" name="keyword" placeholder="هتل مشهد" required>
            </div>
            <div class="form-group">
                <label>slug (URL)</label>
                <input type="text" name="keyword_slug" dir="ltr" style="font-family: monospace;" placeholder="hotel-mashhad">
            </div>
            <div class="form-group">
                <label>صفحه مقصد</label>
                <input type="text" name="target_url" dir="ltr" placeholder="/hotels">
            </div>
        </div>
        <div class="form-group">
            <label>توضیحات SEO</label>
            <textarea name="keyword_description" rows="2" style="max-width: 100%;" placeholder="توضیحات این کلمه کلیدی برای SEO..."></textarea>
        </div>
        <button type="submit" class="btn btn-success">➕ افزودن کلمه کلیدی</button>
    </form>

    <!-- Keywords List -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>کلمه کلیدی</th><th>slug</th><th>صفحه مقصد</th><th>عملیات</th></tr>
            </thead>
            <tbody>
            <?php
            $keywords = [];
            try { $keywords = $db->fetchAll("SELECT * FROM site_seo_keywords ORDER BY id DESC LIMIT 50"); } catch (\Exception $e) {}
            if (empty($keywords)):
            ?>
                <tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">هنوز کلمه کلیدی ثبت نشده</td></tr>
            <?php else: foreach ($keywords as $kw): ?>
                <tr>
                    <td style="font-weight: 600;"><?php echo htmlspecialchars($kw->keyword); ?></td>
                    <td dir="ltr" style="font-family: monospace; font-size: 12px;"><?php echo htmlspecialchars($kw->keyword_slug); ?></td>
                    <td dir="ltr" style="font-size: 12px;"><?php echo htmlspecialchars($kw->target_url ?? ''); ?></td>
                    <td><a href="/admin/seo/keyword/<?php echo $kw->id; ?>/delete" class="btn btn-sm btn-danger" onclick="return confirm('حذف شود؟')">🗑️</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Redirects -->
<div class="card" style="margin-bottom: 16px;">
    <h3 style="margin-bottom: 12px; font-weight: 700; font-size: 14px;">🔄 ریدایرکت‌ها (301/302)</h3>
    <form method="POST" action="/admin/seo" style="margin-bottom: 16px;">
        <input type="hidden" name="add_redirect" value="1">
        <div class="form-row-3">
            <div class="form-group"><label>از URL</label><input type="text" name="from_url" dir="ltr" required placeholder="/old-page"></div>
            <div class="form-group"><label>به URL</label><input type="text" name="to_url" dir="ltr" required placeholder="/new-page"></div>
            <div class="form-group"><label>نوع</label><select name="redirect_type"><option value="301">301 دائمی</option><option value="302">302 موقت</option></select></div>
        </div>
        <button type="submit" class="btn btn-success">➕ افزودن ریدایرکت</button>
    </form>

    <?php if (!empty($redirects)): ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>از</th><th>به</th><th>نوع</th><th>عملیات</th></tr></thead>
            <tbody>
            <?php foreach ($redirects as $r): ?>
                <tr>
                    <td dir="ltr" style="font-family: monospace;"><?php echo htmlspecialchars($r->from_url); ?></td>
                    <td dir="ltr" style="font-family: monospace;"><?php echo htmlspecialchars($r->to_url); ?></td>
                    <td><?php echo $r->redirect_type; ?></td>
                    <td><a href="/admin/seo/redirect/<?php echo $r->id; ?>/delete" class="btn btn-sm btn-danger">🗑️</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function generateAI() {
    var keyword = document.getElementById('aiKeyword').value.trim();
    if (!keyword) { alert('کلمه کلیدی را وارد کنید'); return; }
    var type = document.getElementById('aiType').value;
    document.getElementById('aiLoading').style.display = 'block';
    document.getElementById('aiResult').style.display = 'none';
    document.getElementById('aiBtn').disabled = true;

    fetch('/admin/seo/generate', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({keyword: keyword, type: type})
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('aiLoading').style.display = 'none';
        document.getElementById('aiBtn').disabled = false;
        if (data.success) {
            document.getElementById('aiContent').textContent = data.content;
            document.getElementById('aiResult').style.display = 'block';
        } else {
            alert(data.message || 'خطا در تولید محتوا');
        }
    })
    .catch(err => {
        document.getElementById('aiLoading').style.display = 'none';
        document.getElementById('aiBtn').disabled = false;
        alert('خطای شبکه: ' + err.message);
    });
}

function copyAI() {
    var text = document.getElementById('aiContent').textContent;
    navigator.clipboard.writeText(text).then(() => alert('کپی شد!'));
}
</script>