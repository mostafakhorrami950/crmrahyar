<h2 style="font-size: 18px; font-weight: 800; margin-bottom: 20px;">📄 مدیریت صفحات</h2>
<div class="table-wrap">
<table>
    <thead><tr><th>عنوان</th><th>slug</th><th>وضعیت</th><th>عملیات</th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
    <tr>
        <td style="font-weight: 700;"><?php echo htmlspecialchars($p->title); ?></td>
        <td style="font-size: 11px; color: #64748b;">/<?php echo htmlspecialchars($p->slug); ?></td>
        <td><?php echo ($p->is_active ?? 1) ? '<span style="color:#059669">فعال</span>' : '<span style="color:#dc2626">غیرفعال</span>'; ?></td>
        <td><a href="/admin/pages/<?php echo htmlspecialchars($p->slug); ?>/edit" class="btn btn-sm btn-primary">ویرایش</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>