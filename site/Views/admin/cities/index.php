<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-size: 18px; font-weight: 800;">🏙️ مدیریت شهرها</h2>
    <a href="/admin/cities/create" class="btn btn-primary">➕ شهر جدید</a>
</div>
<div class="table-wrap">
<table>
    <thead><tr><th>نام</th><th>slug</th><th>وضعیت</th><th>عملیات</th></tr></thead>
    <tbody>
    <?php foreach ($cities as $c): ?>
    <tr>
        <td style="font-weight: 700;"><?php echo htmlspecialchars($c->name); ?></td>
        <td style="font-size: 11px; color: #64748b;"><?php echo htmlspecialchars($c->slug); ?></td>
        <td><?php echo $c->is_active ? '<span style="color:#059669">فعال</span>' : '<span style="color:#dc2626">غیرفعال</span>'; ?></td>
        <td><a href="/admin/cities/<?php echo $c->id; ?>/edit" class="btn btn-sm btn-primary">ویرایش</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>