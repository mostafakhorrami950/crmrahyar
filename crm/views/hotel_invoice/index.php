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
                            <span class="badge <?php echo $inv->invoice_status=='final'?'bg-success':($inv->invoice_status=='cancelled'?'bg-danger':'bg-warning text-dark'); ?>">
                                <?php echo $inv->invoice_status=='final'?'نهایی':($inv->invoice_status=='cancelled'?'لغو شده':'پیش‌نویس'); ?>
                            </span>
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
                            <div class="d-flex gap-1">
                                <a href="<?php echo $config['url']; ?>/hotel-invoice/view/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 6px;"><i class="bi bi-eye"></i></a>
                                <a href="<?php echo $config['url']; ?>/hotel-invoice/print/<?php echo $inv->id; ?>" class="btn btn-sm btn-outline-success" style="font-size:11px;padding:2px 6px;" target="_blank"><i class="bi bi-printer"></i></a>
                                <a href="<?php echo $config['url']; ?>/hotel-invoice/create/<?php echo $inv->deal_id; ?>" class="btn btn-sm btn-outline-warning" style="font-size:11px;padding:2px 6px;"><i class="bi bi-building"></i></a>
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