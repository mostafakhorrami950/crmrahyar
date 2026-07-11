<h2 style="font-size: 18px; font-weight: 800; margin-bottom: 20px;">🔍 مدیریت سئو</h2>

<!-- SEO Checklist -->
<div class="card" style="margin-bottom: 20px;">
    <h3 style="margin-bottom: 12px; font-weight: 700;">✅ چک‌لیست سئو</h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 13px;">
        <div>✅ نقشه سایت XML: <a href="/sitemap.xml" target="_blank" style="color:#4f46e5;">sitemap.xml</a></div>
        <div>✅ robots.txt: <a href="/robots.txt" target="_blank" style="color:#4f46e5;">robots.txt</a></div>
        <div>✅ Schema.org (Hotel, Room, Article, Breadcrumb)</div>
        <div>✅ Open Graph + Twitter Card</div>
        <div>✅ Canonical URLs</div>
        <div>✅ آدرس‌های SEO-friendly</div>
        <div>✅ <a href="/llms.txt" target="_blank" style="color:#4f46e5;">llms.txt</a> (AI engines)</div>
        <div>✅ FAQ Schema در صفحات هتل</div>
    </div>
</div>

<!-- Add Redirect -->
<div class="card" style="margin-bottom: 20px;">
    <h3 style="margin-bottom: 12px; font-weight: 700;">🔗 افزودن ریدایرکت</h3>
    <form method="POST">
        <div class="form-row-3">
            <div class="form-group"><label>از آدرس</label><input type="text" name="from_url" placeholder="/old-page" dir="ltr"></div>
            <div class="form-group"><label>به آدرس</label><input type="text" name="to_url" placeholder="/new-page" dir="ltr"></div>
            <div class="form-group"><label>نوع</label><select name="redirect_type"><option value="301">301 دائمی</option><option value="302">302 موقت</option></select></div>
        </div>
        <button type="submit" name="add_redirect" class="btn btn-primary">➕ افزودن</button>
    </form>
</div>

<!-- Redirects List -->
<?php if (!empty($redirects)): ?>
<div class="card">
    <h3 style="margin-bottom: 12px; font-weight: 700;">لیست ریدایرکت‌ها</h3>
    <table>
        <thead><tr><th>از</th><th>به</th><th>نوع</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($redirects as $r): ?>
        <tr>
            <td dir="ltr"><?php echo htmlspecialchars($r->from_url); ?></td>
            <td dir="ltr"><?php echo htmlspecialchars($r->to_url); ?></td>
            <td><?php echo $r->redirect_type; ?></td>
            <td><a href="/admin/seo/redirect/<?php echo $r->id; ?>/delete" class="btn btn-sm btn-danger" onclick="return confirm('حذف شود؟')">حذف</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>