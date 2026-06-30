<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چاپ فاکتور هتل - <?php echo htmlspecialchars($invoice->hotel_name); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <?php
    $invSet = $invoiceSettings ?? [];
    $primaryColor = $invSet['invoice_primary_color'] ?? '#0d6efd';
    $secondaryColor = $invSet['invoice_secondary_color'] ?? '#6c757d';
    $successColor = $invSet['invoice_success_color'] ?? '#198754';
    $invoiceTitle = $invSet['invoice_title'] ?? 'فاکتور رزرو هتل';
    $companyName = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $invoiceSubtitle = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logoUrl = $invSet['invoice_logo_url'] ?? '';
    $footerText = $invoice->footer_text ?? $invSet['invoice_footer_text'] ?? '';
    $paymentTerms = $invoice->payment_terms ?? $invSet['invoice_terms'] ?? '';
    ?>
    <style>
        @media print {
            body { font-size: 9pt; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .print-container { margin: 0; padding: 10mm; border: none; box-shadow: none; max-width: 100%; }
            @page { margin: 8mm; size: A4; }
            .item-table td, .item-table th { padding: 3px 6px; font-size: 8pt; }
            .info-box { padding: 4px 6px; }
            .info-box small { font-size: 7pt; }
            .info-box strong { font-size: 8pt; }
            .summary-td { font-size: 8pt; padding: 2px 6px; }
        }
        body { font-family: Vazirmatn, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .print-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 20px; border: 1px solid #ddd; }
        .invoice-header { border-bottom: 3px solid <?php echo $primaryColor; ?>; padding-bottom: 8px; margin-bottom: 10px; }
        .item-table th { background: #f8f9fa; font-size: 8pt; padding: 4px 6px; border: 1px solid #dee2e6; }
        .item-table td { padding: 4px 6px; font-size: 8pt; border: 1px solid #dee2e6; }
        .info-box { background: #f8f9fa; border-radius: 4px; padding: 5px 8px; }
        .info-box small { font-size: 8pt; }
        .info-box strong { font-size: 9pt; }
        .summary-table td { padding: 3px 8px; font-size: 9pt; }
        .footer-section { border-top: 1px solid #dee2e6; padding-top: 8px; margin-top: 10px; font-size: 8pt; color: #666; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center;padding:8px;background:<?php echo $primaryColor; ?>;color:#fff;">
        <button onclick="window.print();" style="padding:8px 20px;background:#fff;color:<?php echo $primaryColor; ?>;border:none;border-radius:4px;font-weight:bold;cursor:pointer;"><i class="bi bi-printer me-1"></i>چاپ فاکتور</button>
        <button onclick="window.close();" style="padding:8px 20px;background:#6c757d;color:#fff;border:none;border-radius:4px;font-weight:bold;cursor:pointer;margin-right:10px;">بستن</button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <?php if (!empty($logoUrl)): ?>
                    <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="لوگو" style="max-height:40px;margin-bottom:4px;">
                    <?php endif; ?>
                    <h5 class="fw-bold mb-0" style="color:<?php echo $primaryColor; ?>;font-size:13pt;"><?php echo htmlspecialchars($invoiceTitle); ?></h5>
                    <small class="text-muted">شماره: <?php echo $invoice->invoice_number ?? '#' . $invoice->id; ?> | تاریخ صدور: <?php echo \Core\JDate::displayDateTime($invoice->created_at); ?></small>
                </div>
                <div class="text-start">
                    <div class="fw-bold" style="color:<?php echo $primaryColor; ?>;font-size:13pt;"><?php echo htmlspecialchars($companyName); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($invoiceSubtitle); ?></small>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="row g-1 mb-2">
            <div class="col-3"><div class="info-box"><small class="text-muted d-block">هتل</small><strong><?php echo htmlspecialchars($invoice->hotel_name); ?></strong></div></div>
            <div class="col-3"><div class="info-box"><small class="text-muted d-block">میهمان</small><strong><?php echo htmlspecialchars($invoice->guest_name ?? $invoice->contact_name ?? '-'); ?></strong></div></div>
            <div class="col-3"><div class="info-box"><small class="text-muted d-block">تلفن</small><strong dir="ltr"><?php echo htmlspecialchars($invoice->guest_phone ?? $invoice->contact_phone ?? '-'); ?></strong></div></div>
            <div class="col-3"><div class="info-box"><small class="text-muted d-block">وضعیت</small><strong><?php
                $statusLabels = ['pending'=>'مانده دارد','settled'=>'تسویه شده','prepaid'=>'پیش پرداخت'];
                echo $statusLabels[$invoice->invoice_status] ?? $invoice->invoice_status;
            ?></strong><?php if (!empty($invoice->invoice_type)): ?><br><small style="color:<?php echo $invoice->invoice_type=='confirmed'?$primaryColor:$secondaryColor; ?>;"><?php echo $invoice->invoice_type=='confirmed'?'تایید شده':'پیش فاکتور'; ?></small><?php endif; ?></div></div>
        </div>
        <div class="row g-1 mb-2">
            <div class="col-3"><div class="info-box text-center"><small class="text-muted d-block">ورود</small><strong><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></strong></div></div>
            <div class="col-3"><div class="info-box text-center"><small class="text-muted d-block">خروج</small><strong><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></strong></div></div>
            <div class="col-3"><div class="info-box text-center"><small class="text-muted d-block">شب</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->nights; ?></strong></div></div>
            <div class="col-3"><div class="info-box text-center"><small class="text-muted d-block">خدمات</small><strong><?php
                $services = [];
                if ($invoice->transfer_included) $services[] = 'ترانسفر';
                if ($invoice->visa_included) $services[] = 'ویزا';
                if ($invoice->insurance_included) $services[] = 'بیمه';
                echo !empty($services) ? implode(', ', $services) : '-';
            ?></strong></div></div>
        </div>

        <!-- Line Items Table -->
        <?php if (!empty($items)): ?>
        <table class="table table-bordered item-table mb-2">
            <thead>
                <tr>
                    <th style="width:5%">#</th>
                    <th style="width:45%">شرح آیتم</th>
                    <th style="width:10%" class="text-center">تعداد</th>
                    <th style="width:20%" class="text-center">قیمت واحد</th>
                    <th style="width:20%" class="text-center">مبلغ کل</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td class="text-center"><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($item->description); ?></td>
                    <td class="text-center"><?php echo number_format($item->quantity, 2); ?></td>
                    <td class="text-center" dir="ltr"><?php echo number_format($item->unit_price); ?></td>
                    <td class="text-center" dir="ltr"><?php echo number_format($item->total_price); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="text-center text-muted py-2 mb-2" style="font-size:9pt;">آیتمی ثبت نشده</div>
        <?php endif; ?>

        <!-- Financial Summary -->
        <div class="d-flex justify-content-end">
            <table class="summary-table" style="width:40%;">
                <tr><td class="text-muted">جمع کل:</td><td class="text-start fw-bold" dir="ltr"><?php echo number_format($invoice->subtotal ?? $invoice->total_amount ?? 0); ?> تومان</td></tr>
                <?php if (($invoice->tax_percent ?? 0) > 0): ?>
                <tr><td class="text-muted">مالیات (<?php echo $invoice->tax_percent; ?>%):</td><td class="text-start fw-bold" dir="ltr"><?php echo number_format($invoice->tax_amount ?? 0); ?> تومان</td></tr>
                <?php endif; ?>
                <?php if (($invoice->service_fee ?? 0) > 0): ?>
                <tr><td class="text-muted">هزینه خدمات:</td><td class="text-start fw-bold" dir="ltr"><?php echo number_format($invoice->service_fee); ?> تومان</td></tr>
                <?php endif; ?>
                <?php if (($invoice->discount_amount ?? 0) > 0): ?>
                <tr><td class="text-muted text-danger">تخفیف:</td><td class="text-start fw-bold text-danger" dir="ltr">- <?php echo number_format($invoice->discount_amount); ?> تومان</td></tr>
                <?php endif; ?>
                <tr><td class="fw-bold fs-6 border-top border-2">مبلغ نهایی:</td><td class="text-start fw-bold border-top border-2" style="font-size:12pt;color:<?php echo $successColor; ?>;" dir="ltr"><?php echo number_format($invoice->final_amount); ?> تومان</td></tr>
                <?php if (($invoice->deposit_amount ?? 0) > 0): ?>
                <tr><td class="text-muted">بیعانه:</td><td class="text-start fw-bold" dir="ltr"><?php echo number_format($invoice->deposit_amount); ?> تومان</td></tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Payment Terms -->
        <?php if ($paymentTerms): ?>
        <div class="mb-2 p-2" style="background:#f8f9fa;border-radius:4px;font-size:8pt;">
            <strong class="text-muted"><i class="bi bi-shield-check me-1"></i>شرایط پرداخت:</strong>
            <span class="text-muted"><?php echo nl2br(htmlspecialchars($paymentTerms)); ?></span>
        </div>
        <?php endif; ?>

        <!-- Notes -->
        <?php if ($invoice->notes): ?>
        <div class="mb-2" style="font-size:8pt;">
            <strong class="text-muted"><i class="bi bi-journal-text me-1"></i>توضیحات:</strong>
            <span class="text-muted"><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></span>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer-section text-center">
            <?php if ($footerText): ?>
            <p class="mb-1"><?php echo nl2br(htmlspecialchars($footerText)); ?></p>
            <?php endif; ?>
            <small><?php echo htmlspecialchars($companyName); ?> | <?php echo htmlspecialchars($invoiceSubtitle); ?></small>
        </div>
    </div>
</body>
</html>