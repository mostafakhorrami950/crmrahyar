<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-telephone me-2"></i>مدیریت خطوط تلفن</h5>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/callcenter" class="btn btn-outline-secondary"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLineModal"><i class="bi bi-plus-circle me-1"></i>خط جدید</button>
    </div>
</div>

<div class="card">
    <?php if (empty($lines)): ?>
    <div class="card-body text-center text-muted py-5">خط تلفنی تعریف نشده</div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>نام</th><th>شماره تلفن</th><th>توضیحات</th><th>وضعیت</th><th class="text-center">عملیات</th></tr></thead>
            <tbody>
            <?php foreach ($lines as $l): ?>
            <tr>
                <td class="fw-semibold"><?php echo htmlspecialchars($l->name); ?></td>
                <td><i class="bi bi-telephone text-primary me-1"></i><?php echo htmlspecialchars($l->phone_number); ?></td>
                <td class="small text-muted"><?php echo htmlspecialchars($l->description ?? ''); ?></td>
                <td>
                    <?php if ($l->is_active): ?>
                    <span class="badge bg-success">فعال</span>
                    <?php else: ?>
                    <span class="badge bg-secondary">غیرفعال</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <form method="POST" action="<?php echo $config['url']; ?>/callcenter/lines/delete/<?php echo $l->id; ?>" style="display:inline;" onsubmit="return confirm('حذف شود؟')">
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Add Line Modal -->
<div class="modal fade" id="addLineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo $config['url']; ?>/callcenter/lines/add">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-plus-circle me-1"></i>افزودن خط تلفن</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نام خط *</label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: خط ۱ - پشتیبانی">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">شماره تلفن *</label>
                        <input type="text" name="phone_number" class="form-control" required placeholder="مثال: 021-12345678">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <input type="text" name="description" class="form-control" placeholder="اختیاری">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>افزودن</button>
                </div>
            </form>
        </div>
    </div>
</div>