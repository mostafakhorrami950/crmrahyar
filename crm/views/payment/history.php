<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 style="margin:0;font-weight:bold;">تاریخچه پرداخت‌ها</h5>
</div>
<div class="table-container">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>معامله</th><th>مبلغ</th><th>کد پیگیری</th><th>شماره مرجع</th><th>لینک پرداخت</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
            <tbody>
                <?php if (empty($payments)): ?><tr><td colspan="7" class="text-center py-4">هیچ پرداختی ثبت نشده است.</td></tr><?php endif; ?>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $p->deal_id; ?>"><?php echo htmlspecialchars($p->deal_title ?? '-'); ?></a></td>
                    <td><strong><?php echo number_format($p->amount); ?></strong></td>
                    <td><small><?php echo htmlspecialchars($p->track_id ?? '-'); ?></small></td>
                    <td><?php echo htmlspecialchars($p->ref_number ?? '-'); ?></td>
                    <td>
                        <?php if (!empty($p->public_token) && $p->status == 'pending'): ?>
                        <div style="display:flex;gap:4px;align-items:center;">
                            <input type="text" value="<?php echo $config['url']; ?>/pay/<?php echo htmlspecialchars($p->public_token); ?>" style="width:120px;padding:4px 6px;border:1px solid #ddd;border-radius:6px;font-size:11px;direction:ltr;text-align:left;background:#fff;" readonly onclick="this.select();">
                            <button type="button" class="btn btn-sm btn-success" onclick="copyPayLink(this)" style="padding:4px 8px;font-size:11px;">📋</button>
                        </div>
                        <?php else: ?>
                        <small style="color:#aaa;">-</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $p->status == 'success' ? 'success' : ($p->status == 'pending' ? 'warning' : 'danger'); ?>">
                            <?php echo $p->status == 'success' ? 'موفق' : ($p->status == 'pending' ? 'در انتظار' : 'ناموفق'); ?>
                        </span>
                    </td>
                    <td><small style="color:#888;"><?php echo \Core\JDate::displayDateTime($p->created_at); ?></small></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copyPayLink(btn) {
    var input = btn.previousElementSibling;
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        var original = btn.innerHTML;
        btn.innerHTML = '✅';
        setTimeout(function() { btn.innerHTML = original; }, 1500);
    }).catch(function() {
        document.execCommand('copy');
    });
}
</script>
