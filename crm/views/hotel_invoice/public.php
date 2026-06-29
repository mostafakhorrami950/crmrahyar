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
    $secondaryColor = $invSet['invoice_secondary_color'] ?? '#6c757d';
    $successColor = $invSet['invoice_success_color'] ?? '#198754';
    $invoiceTitle = $invSet['invoice_title'] ?? 'فاکتور هتل';
    $companyName = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $invoiceSubtitle = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logoUrl = $invSet['invoice_logo_url'] ?? '';
    ?>
    <style>
        body { font-family: Vazirmatn, sans-serif; background: #f5f7fa; }
        .invoice-container { max-width: 700px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header-border { border-bottom: 3px solid <?php echo $primaryColor; ?>; padding-bottom: 15px; margin-bottom: 20px; }
        .info-box { background: #f8f9fa; border-radius: 10px; padding: 12px; }
        .total-box { background: <?php echo $successColor; ?>18; border-radius: 12px; padding: 20px; text-align: center; }
        .pay-btn { background: <?php echo $primaryColor; ?>; color: #fff; border: none; padding: 14px 30px; border-radius: 10px; font-size: 16px; font-weight: bold; width: 100%; cursor: pointer; transition: all 0.3s; }
        .pay-btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .pay-btn:disabled { background: #ccc; cursor: not-allowed; transform: none; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <?php if (!empty($errorMessage)): ?>
        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size:48px;"></i>
            <h4 class="mt-3 text-danger">خطا</h4>
            <p class="text-muted"><?php echo htmlspecialchars($errorMessage); ?></p>
        </div>
        <?php elseif ($invoice): ?>
        <div class="header-border text-center">
            <?php if (!empty($logoUrl)): ?>
            <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="لوگو" style="max-height:60px;margin-bottom:10px;">
            <?php endif; ?>
            <h4 class="fw-bold mb-1" style="color:<?php echo $primaryColor; ?>;"><?php echo htmlspecialchars($invoiceTitle); ?></h4>
            <small class="text-muted"><?php echo htmlspecialchars($companyName); ?> | <?php echo htmlspecialchars($invoiceSubtitle); ?></small>
            <div class="mt-2">
                <span class="badge <?php echo $invoice->invoice_status=='paid'?'bg-success':($invoice->invoice_status=='cancelled'?'bg-danger':'bg-warning text-dark'); ?>" style="font-size:12px;">
                    <?php
                    $statusLabels = ['draft'=>'پیش‌نویس','final'=>'نهایی','paid'=>'پرداخت شده','cancelled'=>'لغو شده'];
                    echo $statusLabels[$invoice->invoice_status] ?? $invoice->invoice_status;
                    ?>
                </span>
                <?php if (!empty($invoice->invoice_type)): ?>
                <span class="badge <?php echo $invoice->invoice_type=='confirmed'?'bg-primary':'bg-secondary'; ?>" style="font-size:12px;">
                    <?php echo $invoice->invoice_type=='confirmed'?'فاکتور تایید شده':'پیش فاکتور'; ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="row g-2 mb-4">
            <div class="col-6"><div class="info-box"><small class="text-muted d-block" style="font-size:10px;">شماره فاکتور</small><strong class="small">#<?php echo $invoice->id; ?></strong></div></div>
            <div class="col-6"><div class="info-box"><small class="text-muted d-block" style="font-size:10px;">هتل</small><strong class="small"><?php echo htmlspecialchars($invoice->hotel_name); ?></strong></div></div>
            <div class="col-6"><div class="info-box"><small class="text-muted d-block" style="font-size:10px;">تاریخ ورود</small><strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></strong></div></div>
            <div class="col-6"><div class="info-box"><small class="text-muted d-block" style="font-size:10px;">تاریخ خروج</small><strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></strong></div></div>
            <div class="col-4"><div class="info-box text-center"><small class="text-muted d-block" style="font-size:10px;">شب‌ها</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->nights; ?></strong></div></div>
            <div class="col-4"><div class="info-box text-center"><small class="text-muted d-block" style="font-size:10px;">بزرگسال</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->adults_count ?? 0; ?></strong></div></div>
            <div class="col-4"><div class="info-box text-center"><small class="text-muted d-block" style="font-size:10px;">کودک 3-5</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->children_3to5_count ?? 0; ?></strong></div></div>
        </div>

        <!-- Financial Details -->
        <div class="info-box mb-4">
            <table class="table table-sm table-borderless mb-0">
                <tr><td class="text-muted">قیمت هر نفر هر شب</td><td class="text-start fw-bold"><?php echo number_format($invoice->price_per_person_night); ?> تومان</td></tr>
                <?php if ($invoice->new_price_per_person_night): ?>
                <tr><td class="text-muted">قیمت جدید</td><td class="text-start fw-bold text-warning"><?php echo number_format($invoice->new_price_per_person_night); ?> تومان</td></tr>
                <?php endif; ?>
                <tr><td class="text-muted">مبلغ کل</td><td class="text-start fw-bold"><?php echo number_format(($invoice->total_amount ?? 0) + ($invoice->discount_amount ?? 0)); ?> تومان</td></tr>
                <?php if (($invoice->discount_amount ?? 0) > 0): ?>
                <tr><td class="text-muted">تخفیف (<?php echo $invoice->discount_percent; ?>%)</td><td class="text-start fw-bold text-danger">- <?php echo number_format($invoice->discount_amount); ?> تومان</td></tr>
                <?php endif; ?>
                <tr class="border-top border-2"><td class="fw-bold fs-6">مبلغ نهایی</td><td class="text-start fw-bold fs-5" style="color:<?php echo $successColor; ?>;"><?php echo number_format($invoice->final_amount); ?> تومان</td></tr>
                <?php if (($invoice->deposit_amount ?? 0) > 0): ?>
                <tr><td class="text-muted"><i class="bi bi-wallet2 me-1"></i>بیعانه (مبلغ قابل پرداخت)</td><td class="text-start fw-bold text-primary fs-5"><?php echo number_format($invoice->deposit_amount); ?> تومان</td></tr>
                <?php endif; ?>
            </table>
        </div>

        <?php if ($invoice->notes): ?>
        <div class="mb-3"><small class="text-muted d-block mb-1"><i class="bi bi-journal-text me-1"></i>توضیحات</small><p class="small mb-0"><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></p></div>
        <?php endif; ?>

        <?php if ($invoice->invoice_status === 'paid'): ?>
        <div class="total-box mb-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size:48px;"></i>
            <h5 class="mt-2 fw-bold text-success">پرداخت شده</h5>
            <p class="text-muted mb-0">این فاکتور قبلاً پرداخت شده است.</p>
        </div>
        <?php elseif ($invoice->invoice_status === 'cancelled'): ?>
        <div class="total-box mb-3">
            <i class="bi bi-x-circle-fill text-danger" style="font-size:48px;"></i>
            <h5 class="mt-2 fw-bold text-danger">لغو شده</h5>
            <p class="text-muted mb-0">این فاکتور لغو شده است.</p>
        </div>
        <?php else: ?>
        <div class="total-box mb-3">
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

        <div class="text-center text-muted small mt-4 pt-3 border-top">
            <small><?php echo htmlspecialchars($companyName); ?> | <?php echo htmlspecialchars($invoiceSubtitle); ?></small>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function submitPayment() {
        var btn = document.getElementById('payBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>در حال اتصال...';
        
        var formData = new FormData();
        formData.append('token', '<?php echo htmlspecialchars($invoice->payment_token ?? ''); ?>');
        
        fetch('<?php echo $config['url']; ?>/hotel-pay/submit', {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-credit-card me-2"></i>پرداخت آنلاین';
                var errorDiv = document.getElementById('payError');
                errorDiv.textContent = data.message || 'خطا در اتصال به درگاه پرداخت';
                errorDiv.classList.remove('d-none');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-credit-card me-2"></i>پرداخت آنلاین';
            var errorDiv = document.getElementById('payError');
            errorDiv.textContent = 'خطای شبکه';
            errorDiv.classList.remove('d-none');
        });
    }
    </script>
</body>
</html>