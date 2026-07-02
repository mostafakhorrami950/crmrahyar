<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشاهده فاکتور - <?php echo htmlspecialchars($invoice->hotel_name ?? 'فاکتور'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <?php
    $invSet = $invoiceSettings ?? [];
    $primaryColor = $invSet['invoice_primary_color'] ?? '#0d6efd';
    $successColor = $invSet['invoice_success_color'] ?? '#198754';
    $invoiceTitle = $invSet['invoice_title'] ?? 'فاکتور رزرو هتل';
    $companyName = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $invoiceSubtitle = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logoUrl = $invSet['invoice_logo_url'] ?? '';
    $footerText = $invoice->footer_text ?? $invSet['invoice_footer_text'] ?? '';
    ?>
    <style>
        body { font-family: Vazirmatn, sans-serif; background: #f5f7fa; }
        .inv-box { max-width: 700px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .inv-top { border-bottom: 3px solid <?php echo $primaryColor; ?>; padding-bottom: 15px; margin-bottom: 20px; }
        .info-item { background: #f8f9fa; border-radius: 8px; padding: 10px 12px; }
        .info-item small { font-size: 10px; color: #666; display: block; }
        .info-item strong { font-size: 13px; }
        .pay-btn { background: <?php echo $primaryColor; ?>; color: #fff; border: none; padding: 14px 30px; border-radius: 10px; font-size: 16px; font-weight: bold; width: 100%; cursor: pointer; }
        .pay-btn:hover { opacity: 0.9; }
        .pay-btn:disabled { background: #ccc; cursor: not-allowed; }
        .item-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
        .item-tbl th { background: #f1f3f5; padding: 8px 10px; border: 1px solid #dee2e6; text-align: right; font-size: 11px; }
        .item-tbl td { padding: 7px 10px; border: 1px solid #dee2e6; }
        .sum-tbl { width: 100%; font-size: 13px; }
        .sum-tbl td { padding: 5px 10px; }
    </style>
</head>
<body>
<div class="inv-box">
    <?php if (!empty($errorMessage)): ?>
    <div class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size:48px;"></i>
        <h4 class="mt-3 text-danger">خطا</h4>
        <p class="text-muted"><?php echo htmlspecialchars($errorMessage); ?></p>
    </div>

    <?php elseif ($invoice): ?>
    <!-- Header -->
    <div class="inv-top text-center">
        <?php if ($logoUrl): ?><img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="لوگو" style="max-height:55px;margin-bottom:8px;"><?php endif; ?>
        <h4 class="fw-bold mb-1" style="color:<?php echo $primaryColor; ?>;"><?php echo htmlspecialchars($invoiceTitle); ?></h4>
        <small class="text-muted"><?php echo htmlspecialchars($companyName); ?> | <?php echo htmlspecialchars($invoiceSubtitle); ?></small>
        <div class="mt-2">
            <?php
            $stL = ['pending'=>'مانده دارد','settled'=>'تسویه شده','prepaid'=>'پیش پرداخت','paid'=>'پرداخت شده'];
            $stC = ['pending'=>'bg-warning text-dark','settled'=>'bg-success','prepaid'=>'bg-info','paid'=>'bg-success'];
            $st = $invoice->invoice_status;
            ?>
            <span class="badge <?php echo $stC[$st]??'bg-secondary'; ?>" style="font-size:12px;"><?php echo $stL[$st]??$st; ?></span>
            <?php if ($invoice->invoice_type): ?>
            <span class="badge <?php echo $invoice->invoice_type=='confirmed'?'bg-primary':'bg-secondary'; ?>" style="font-size:12px;"><?php echo $invoice->invoice_type=='confirmed'?'تایید شده':'پیش فاکتور'; ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="row g-2 mb-3">
        <div class="col-6"><div class="info-item"><small>شماره فاکتور</small><strong><?php echo $invoice->invoice_number ?? '#' . $invoice->id; ?></strong></div></div>
        <div class="col-6"><div class="info-item"><small>هتل</small><strong><?php echo htmlspecialchars($invoice->hotel_name); ?></strong></div></div>
        <div class="col-6"><div class="info-item"><small>میهمان</small><strong><?php echo htmlspecialchars($invoice->guest_name ?? '-'); ?></strong></div></div>
        <div class="col-6"><div class="info-item"><small>تلفن</small><strong dir="ltr"><?php echo htmlspecialchars($invoice->guest_phone ?? '-'); ?></strong></div></div>
        <div class="col-4"><div class="info-item text-center"><small>ورود</small><strong><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></strong></div></div>
        <div class="col-4"><div class="info-item text-center"><small>خروج</small><strong><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></strong></div></div>
        <div class="col-4"><div class="info-item text-center"><small>شب‌ها</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->nights; ?></strong></div></div>
    </div>

    <!-- Line Items -->
    <?php if (!empty($items)): ?>
    <table class="item-tbl mb-3">
        <thead>
            <tr><th style="width:5%">#</th><th style="width:45%">شرح</th><th style="width:10%" class="text-center">تعداد</th><th style="width:20%" class="text-center">قیمت واحد</th><th style="width:20%" class="text-center">مبلغ کل</th></tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr>
                <td class="text-center"><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($item->description); ?></td>
                <td class="text-center"><?php echo number_format((int)$item->quantity); ?></td>
                <td class="text-center" dir="ltr"><?php echo number_format($item->unit_price); ?></td>
                <td class="text-center fw-bold" dir="ltr"><?php echo number_format($item->total_price); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Financial Summary -->
    <div class="mb-3">
        <?php
        $itemsDiscount = 0;
        foreach ($items as $itm) {
            $defP = $itm->default_price ?? $itm->unit_price;
            if ($itm->unit_price < $defP) {
                $diff = $defP - $itm->unit_price;
                $itemsDiscount += ($itm->category === 'hotel' && $invoice->nights > 0) ? $diff * $itm->quantity * $invoice->nights : $diff * $itm->quantity;
            }
        }
        ?>
        <table class="sum-tbl">
            <tr><td style="color:#666;">جمع کل (قیمت اصلی):</td><td class="text-start fw-bold" dir="ltr"><?php echo number_format(($invoice->subtotal ?? 0) + $itemsDiscount); ?> تومان</td></tr>
            <?php if ($itemsDiscount > 0): ?>
            <tr><td style="color:#dc3545;"><i class="bi bi-tag"></i> تخفیف تغییر قیمت:</td><td class="text-start fw-bold text-danger" dir="ltr">- <?php echo number_format($itemsDiscount); ?> تومان</td></tr>
            <?php endif; ?>
            <tr><td style="color:#666;">جمع کل:</td><td class="text-start fw-bold" dir="ltr"><?php echo number_format($invoice->subtotal ?? $invoice->total_amount ?? 0); ?> تومان</td></tr>
            <?php if (($invoice->tax_percent ?? 0) > 0): ?>
            <tr><td style="color:#666;">مالیات (<?php echo $invoice->tax_percent; ?>%):</td><td class="text-start fw-bold" dir="ltr"><?php echo number_format($invoice->tax_amount ?? 0); ?> تومان</td></tr>
            <?php endif; ?>
            <?php if (($invoice->discount_amount ?? 0) > 0): ?>
            <tr><td style="color:#dc3545;">تخفیف:</td><td class="text-start fw-bold text-danger" dir="ltr">- <?php echo number_format($invoice->discount_amount); ?> تومان</td></tr>
            <?php endif; ?>
            <?php if ($invoice->invoice_status === 'pending' && ($invoice->deposit_amount ?? 0) > 0): ?>
            <tr><td style="color:#666;"><i class="bi bi-wallet2"></i> بیعانه پرداخت شده:</td><td class="text-start fw-bold text-danger" dir="ltr">- <?php echo number_format($invoice->deposit_amount); ?> تومان</td></tr>
            <tr><td style="font-weight:700;font-size:16px;padding-top:8px;border-top:2px solid #333;">مبلغ باقیمانده:</td><td class="text-start fw-bold" style="font-size:18px;color:#dc3545;padding-top:8px;border-top:2px solid #333;" dir="ltr"><?php echo number_format($invoice->final_amount - $invoice->deposit_amount); ?> تومان</td></tr>
            <?php else: ?>
            <tr><td style="font-weight:700;font-size:16px;padding-top:8px;border-top:2px solid #333;">مبلغ نهایی:</td><td class="text-start fw-bold" style="font-size:18px;color:<?php echo $successColor; ?>;padding-top:8px;border-top:2px solid #333;" dir="ltr"><?php echo number_format($invoice->final_amount); ?> تومان</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <?php if ($invoice->notes): ?>
    <div class="mb-3"><small class="text-muted d-block mb-1"><i class="bi bi-journal-text me-1"></i>توضیحات</small><p class="small mb-0"><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></p></div>
    <?php endif; ?>

    <?php if ($invoice->payment_terms): ?>
    <div class="mb-3"><small class="text-muted d-block mb-1"><i class="bi bi-shield-check me-1"></i>شرایط پرداخت</small><p class="small mb-0"><?php echo nl2br(htmlspecialchars($invoice->payment_terms)); ?></p></div>
    <?php endif; ?>

    <!-- Payment Status -->
    <?php if ($invoice->invoice_status === 'settled' || $invoice->invoice_status === 'paid'): ?>
    <div class="text-center p-4 mb-3" style="background:<?php echo $successColor; ?>18;border-radius:12px;">
        <i class="bi bi-check-circle-fill text-success" style="font-size:48px;"></i>
        <h5 class="mt-2 fw-bold text-success"><?php echo $invoice->invoice_status === 'paid' ? 'پرداخت شده' : 'تسویه شده'; ?></h5>
        <p class="text-muted mb-0">این فاکتور <?php echo $invoice->invoice_status === 'paid' ? 'پرداخت شده' : 'تسویه شده'; ?> است.</p>
    </div>
    <?php elseif ($invoice->invoice_status === 'pending'): ?>
    <div class="text-center p-4 mb-3" style="background:#ffc10718;border-radius:12px;">
        <i class="bi bi-hourglass-split text-warning" style="font-size:48px;"></i>
        <h5 class="mt-2 fw-bold text-warning">مانده دارد</h5>
        <p class="text-muted mb-0">این فاکتور دارای مانده است و در انتظار تسویه نهایی می‌باشد.</p>
    </div>
    <?php elseif ($invoice->invoice_status === 'prepaid'): ?>
    <div class="text-center p-4 mb-3" style="background:<?php echo $successColor; ?>18;border-radius:12px;">
        <?php if (($invoice->deposit_amount ?? 0) > 0): ?>
        <small class="text-muted">مبلغ قابل پرداخت (بیعانه)</small>
        <?php else: ?>
        <small class="text-muted">مبلغ قابل پرداخت</small>
        <?php endif; ?>
        <h3 class="fw-bold mt-1" style="color:<?php echo $successColor; ?>;"><?php echo number_format($payAmount); ?> تومان</h3>
    </div>

    <div id="paySection">
        <button class="pay-btn" id="payBtn" onclick="submitPayment()">
            <i class="bi bi-credit-card me-2"></i>پرداخت آنلاین
        </button>
        <div id="payError" class="alert alert-danger mt-3 d-none"></div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="text-center text-muted small mt-4 pt-3 border-top">
        <?php if ($footerText): ?><p class="mb-1"><?php echo nl2br(htmlspecialchars($footerText)); ?></p><?php endif; ?>
        <small><?php echo htmlspecialchars($companyName); ?> | <?php echo htmlspecialchars($invoiceSubtitle); ?></small>
    </div>
    <?php endif; ?>
</div>

<script>
function submitPayment() {
    var btn = document.getElementById('payBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>در حال اتصال...';
    var fd = new FormData();
    fd.append('token', '<?php echo htmlspecialchars($invoice->payment_token ?? ''); ?>');
    fd.append('code', '<?php echo htmlspecialchars($invoice->short_code ?? ''); ?>');
    fetch('<?php echo $config['url']; ?>/hotel-pay/submit', { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.redirect) { window.location.href = data.redirect; }
        else { btn.disabled = false; btn.innerHTML = '<i class="bi bi-credit-card me-2"></i>پرداخت آنلاین'; document.getElementById('payError').textContent = data.message || 'خطا'; document.getElementById('payError').classList.remove('d-none'); }
    })
    .catch(function() { btn.disabled = false; btn.innerHTML = '<i class="bi bi-credit-card me-2"></i>پرداخت آنلاین'; document.getElementById('payError').textContent = 'خطای شبکه'; document.getElementById('payError').classList.remove('d-none'); });
}
</script>
</body>
</html>