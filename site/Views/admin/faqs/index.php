<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-size: 18px; font-weight: 800;">❓ سوالات متداول</h2>
    <a href="/admin/faqs/create" class="btn btn-primary">➕ سوال جدید</a>
</div>
<?php if (!empty($faqs)): ?>
<div class="table-wrap">
<table>
    <thead><tr><th>سوال</th><th>نوع</th><th>ترتیب</th><th>وضعیت</th><th>عملیات</th></tr></thead>
    <tbody>
    <?php foreach ($faqs as $f): ?>
    <tr>
        <td style="font-weight: 700;"><?php echo htmlspecialchars(mb_substr($f->question, 0, 60)); ?></td>
        <td><?php echo htmlspecialchars($f->entity_type); ?></td>
        <td><?php echo $f->sort_order; ?></td>
        <td><?php echo $f->is_active ? '<span style="color:#059669">فعال</span>' : '<span style="color:#dc2626">غیرفعال</span>'; ?></td>
        <td>
            <a href="/admin/faqs/<?php echo $f->id; ?>/edit" class="btn btn-sm btn-primary">ویرایش</a>
            <a href="/admin/faqs/<?php echo $f->id; ?>/delete" class="btn btn-sm btn-danger" onclick="return confirm('حذف شود؟')">حذف</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php else: ?>
<p style="text-align: center; color: #94a3b8; padding: 40px;">سوالی ثبت نشده.</p>
<?php endif; ?>