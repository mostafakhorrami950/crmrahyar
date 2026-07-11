<div style="max-width: 700px;">
    <a href="/admin/faqs" style="font-size: 13px; color: #64748b;">← بازگشت</a>
    <h2 style="font-size: 18px; font-weight: 800; margin: 12px 0 20px;"><?php echo $faq ? 'ویرایش سوال' : 'سوال جدید'; ?></h2>
    <form method="POST" class="card">
        <div class="form-group"><label>سوال</label><input type="text" name="question" value="<?php echo htmlspecialchars($faq->question ?? ''); ?>" required></div>
        <div class="form-group"><label>پاسخ (HTML)</label><textarea name="answer" rows="5"><?php echo htmlspecialchars($faq->answer ?? ''); ?></textarea></div>
        <div class="form-row-3">
            <div class="form-group"><label>نوع</label><select name="entity_type"><option value="global" <?php echo ($faq->entity_type ?? '') === 'global' ? 'selected' : ''; ?>>عمومی</option><option value="hotel" <?php echo ($faq->entity_type ?? '') === 'hotel' ? 'selected' : ''; ?>>هتل</option></select></div>
            <div class="form-group"><label>شناسه (برای هتل)</label><input type="number" name="entity_id" value="<?php echo $faq->entity_id ?? 0; ?>"></div>
            <div class="form-group"><label>ترتیب</label><input type="number" name="sort_order" value="<?php echo $faq->sort_order ?? 0; ?>"></div>
        </div>
        <div class="form-group"><label><input type="checkbox" name="is_active" value="1" <?php echo ($faq->is_active ?? 1) ? 'checked' : ''; ?>> ✅ فعال</label></div>
        <button type="submit" class="btn btn-primary">💾 ذخیره</button>
    </form>
</div>