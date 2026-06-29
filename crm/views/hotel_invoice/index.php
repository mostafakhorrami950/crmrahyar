<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>لیست فاکتورهای هتل</h5>
</div>

<!-- Search & Filter -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="<?php echo $config['url']; ?>/hotel-invoice" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="جستجو در نام هتل، معامله، مخاطب..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="draft" <?php echo $status==='draft'?'selected':''; ?>>پیش‌نویس</option>
                    <option value="final" <?php echo $status==='final'?'selected':''; ?>>نهایی</option>
                    <option value="paid" <?php echo $status==='paid'?'selected':''; ?>>پرداخت شده</option>
                    <option value="cancelled" <?php echo $status==='cancelled'?'selected':''; ?>>لغو شده</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>جستجو</button>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (empty($invoices)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i>
            <p>هیچ فاکتوری ثبت نشده.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>هتل</th>
                        <th>معامله</th>
                        <th>مخاطب</th>
                        <th>تاریخ ورود</th>
                        <th>تاریخ خروج</th>
                        <th>شب</th>
                        <th>نفرات</th>
                        <th>مبلغ نهایی</th>
                        <th>وضعیت</th>
                        <th>نوع فاکتور</th>
                        <th>لینک پرداخت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><?php echo $inv->id; ?></td>
                        <td><strong><?php echo htmlspecialchars($inv->hotel_name); ?></strong></td>
                        <td><small><?php echo htmlspecialchars($inv->deal_title); ?></small></td>
                        <td><small><?php echo htmlspecialchars($inv->contact_name ?? '-'); ?></small></td>
                        <td><small><?php echo \Core\JDate::displayDate($inv->check_in_date); ?></small></td>
                        <td><small><?php echo \Core\JDate::displayDate($inv->check_out_date); ?></small></td>
                        <td><span class="badge bg-primary"><?php echo $inv->nights; ?></span></td>
                        <td><span class="badge bg-info"><?php echo $inv->persons_count; ?></span></td>
                        <td><strong class="text-success"><?php echo number_format($inv->final_amount); ?> تومان</strong></td>
                        <td>
                            <?php
                            $statusLabels = ['draft'=>'پیش‌نویس','final'=>'نهایی','paid'=>'پرداخت شده','cancelled'=>'لغو شده'];
                            $statusColors = ['draft'=>'bg-warning text-dark','final'=>'bg-success','paid'=>'bg-info','cancelled'=>'bg-danger'];
                            $st = $inv->invoice_status;
                            ?>
                            <span class="badge <?php echo $statusColors[$st] ?? 'bg-secondary'; ?>"><?php echo $statusLabels[$st] ?? $st; ?></span>
                        </td>
                        <td>
                            <?php if (!empty($inv->invoice_type)): ?>
                            <span class="badge <?php echo $inv->invoice_type=='confirmed'?'bg-primary':'bg-secondary'; ?>">
                                <?php echo $inv->invoice_type=='confirmed'?'تایید شده':'پیش فاکتور'; ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-secondary">پیش فاکتور</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($inv->invoice_status !== 'paid' && $inv->invoice_status !== 'cancelled'): ?>
                                <?php if (!empty($inv->short_code)): ?>
                                <button class="btn btn-sm btn-outline-success" style="font-size:11px;padding:2px 6px;" onclick="copyPaymentLink('<?php echo $config['url']; ?>/hi/<?php echo htmlspecialchars($inv->short_code); ?>')" title="کپی لینک کوتاه پرداخت"><i class="bi bi-link-45deg"></i></button>
                                <small class="text-muted d-block" style="font-size:9px;direction:ltr;text-align:left;"><?php echo $config['url']; ?>/hi/<?php echo htmlspecialchars($inv->short_code); ?></small>
                                <?php elseif (!empty($inv->payment_token)): ?>
                                <button class="btn btn-sm btn-outline-success" style="font-size:11px;padding:2px 6px;" onclick="copyPaymentLink('<?php echo $config['url']; ?>/hotel-pay/<?php echo htmlspecialchars($inv->payment_token); ?>')" title="کپی لینک پرداخت"><i class="bi bi-link-45deg"></i></button>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:11px;">ندارد</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-success" style="font-size:11px;"><i class="bi bi-check-circle"></i></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?php echo $config['url']; ?>/hotel-invoice/view/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 6px;"><i class="bi bi-eye"></i></a>
                                <a href="<?php echo $config['url']; ?>/hotel-invoice/edit/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-warning" style="font-size:11px;padding:2px 6px;"><i class="bi bi-pencil"></i></a>
                                <a href="<?php echo $config['url']; ?>/hotel-invoice/print/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-success" style="font-size:11px;padding:2px 6px;" target="_blank"><i class="bi bi-printer"></i></a>
                                <a href="<?php echo $config['url']; ?>/hotel-invoice/create/<?php echo $inv->deal_id; ?>" class="btn btn-sm btn-outline-info" style="font-size:11px;padding:2px 6px;"><i class="bi bi-building"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyPaymentLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        alert('لینک پرداخت کپی شد!');
    }).catch(function() {
        prompt('لینک پرداخت:', url);
    });
}
</script>