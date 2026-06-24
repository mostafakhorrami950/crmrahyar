<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-people me-1"></i> مدیریت تیم‌ها</h5>
    <a href="<?php echo $config['url']; ?>/teams/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> تیم جدید</a>
</div>

<?php if (empty($teams)): ?>
<div class="empty-state">
    <div class="empty-icon"><i class="bi bi-people me-1"></i></div>
    <h5 class="fw-bold mb-0">هنوز تیمی ایجاد نشده</h5>
    <a href="<?php echo $config['url']; ?>/teams/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>ایجاد اولین تیم</a>
</div>
<?php else: ?>
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr><th class="text-nowrap">نام تیم</th><th class="text-nowrap">توضیحات</th><th class="text-nowrap">رهبر</th><th class="text-nowrap">اعضا</th><th class="text-nowrap">عملیات</th></tr></thead>
        <tbody>
        <?php foreach ($teams as $t): ?>
        <tr>
            <td class="fw-bold"><?php echo htmlspecialchars($t->name); ?></td>
            <td><?php echo htmlspecialchars($t->description ?? '-'); ?></td>
            <td><?php echo $t->leader_name ? htmlspecialchars($t->leader_name) : '-'; ?></td>
            <td><span class="badge badge-info"><?php echo $t->member_count; ?> نفر</span></td>
            <td>
                <div class="d-flex gap-4">
                    <a href="<?php echo $config['url']; ?>/teams/edit/<?php echo $t->id; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i></a>
                    <form method="POST" action="<?php echo $config['url']; ?>/teams/delete/<?php echo $t->id; ?>" style="display:inline;" onsubmit="return confirm('آیا مطمئنید؟')">
                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i></button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>