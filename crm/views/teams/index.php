<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>👥 مدیریت تیم‌ها</h5>
    <a href="<?php echo $config['url']; ?>/teams/create" class="btn btn-primary">➕ تیم جدید</a>
</div>

<?php if (empty($teams)): ?>
<div class="empty-state">
    <div class="empty-icon">👥</div>
    <h5>هنوز تیمی ایجاد نشده</h5>
    <a href="<?php echo $config['url']; ?>/teams/create" class="btn btn-primary">➕ ایجاد اولین تیم</a>
</div>
<?php else: ?>
<div class="card">
    <div class="table-wrapper"><table>
        <thead><tr><th>نام تیم</th><th>توضیحات</th><th>رهبر</th><th>اعضا</th><th>عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($teams as $t): ?>
        <tr>
            <td class="fw-bold"><?php echo htmlspecialchars($t->name); ?></td>
            <td><?php echo htmlspecialchars($t->description ?? '-'); ?></td>
            <td><?php echo $t->leader_name ? htmlspecialchars($t->leader_name) : '-'; ?></td>
            <td><span class="badge badge-info"><?php echo $t->member_count; ?> نفر</span></td>
            <td>
                <div class="d-flex gap-4">
                    <a href="<?php echo $config['url']; ?>/teams/edit/<?php echo $t->id; ?>" class="btn btn-sm btn-secondary">✏️</a>
                    <form method="POST" action="<?php echo $config['url']; ?>/teams/delete/<?php echo $t->id; ?>" style="display:inline;" onsubmit="return confirm('آیا مطمئنید؟')">
                        <button class="btn btn-sm btn-danger">🗑️</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>