<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>💾 بکاپ دیتابیس</h5>
    <form method="POST" action="<?php echo $config['url']; ?>/backup/create" style="display:inline;">
        <button type="submit" class="btn btn-primary" onclick="return confirm('بکاپ جدید ایجاد شود؟')">➕ بکاپ جدید</button>
    </form>
</div>

<div class="card">
    <?php if (empty($files)): ?>
    <p class="text-muted" style="padding:30px;text-align:center;">هنوز بکاپی ایجاد نشده. روی «بکاپ جدید» کلیک کنید.</p>
    <?php else: ?>
    <div class="table-wrapper"><table>
        <thead><tr><th>نام فایل</th><th>حجم</th><th>تاریخ</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($files as $f): ?>
        <tr>
            <td class="fw-bold">📄 <?php echo htmlspecialchars($f['name']); ?></td>
            <td><?php echo $f['size']; ?></td>
            <td><?php echo $f['date']; ?></td>
            <td>
                <div class="d-flex gap-4">
                    <a href="<?php echo $config['url']; ?>/backup/download/<?php echo urlencode($f['name']); ?>" class="btn btn-sm btn-success">⬇️ دانلود</a>
                    <form method="POST" action="<?php echo $config['url']; ?>/backup/delete/<?php echo urlencode($f['name']); ?>" style="display:inline;" onsubmit="return confirm('حذف شود؟')">
                        <button class="btn btn-sm btn-danger">🗑️</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>