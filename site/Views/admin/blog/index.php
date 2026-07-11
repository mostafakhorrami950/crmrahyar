<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-size: 18px; font-weight: 800;">📝 مقالات بلاگ</h2>
    <a href="/admin/blog/create" class="btn btn-primary">➕ مقاله جدید</a>
</div>

<?php if (!empty($posts)): ?>
<div class="table-wrap">
<table>
    <thead><tr><th>عنوان</th><th>slug</th><th>وضعیت</th><th>تاریخ</th><th>عملیات</th></tr></thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
    <tr>
        <td style="font-weight: 700;"><?php echo htmlspecialchars($p->title); ?></td>
        <td style="font-size: 11px; color: #64748b;"><?php echo htmlspecialchars($p->slug); ?></td>
        <td><?php echo $p->is_published ? '<span style="color:#059669;font-weight:700;">منتشر شده</span>' : '<span style="color:#dc2626;">پیش‌نویس</span>'; ?></td>
        <td style="font-size: 11px;"><?php echo $p->published_at ?? '-'; ?></td>
        <td>
            <a href="/admin/blog/<?php echo $p->id; ?>/edit" class="btn btn-sm btn-primary">ویرایش</a>
            <a href="/blog/<?php echo htmlspecialchars($p->slug); ?>" target="_blank" class="btn btn-sm btn-secondary">مشاهده</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php else: ?>
<p style="text-align: center; color: #94a3b8; padding: 40px;">مقاله‌ای ثبت نشده.</p>
<?php endif; ?>