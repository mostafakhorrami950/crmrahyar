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
    $invoiceTitle = $invSet['invoice_title'] ?? 'فاکتور هتل';
    $companyName = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $invoiceSubtitle = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logoUrl = $invSet['invoice_logo_url'] ?? '';
    ?>
    <style>
        @media print {
            body { font-size: 10pt; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .print-container { margin: 0; padding: 15px; border: none; box-shadow: none; max-width: 100%; }
            .invoice-table td, .invoice-table th { padding: 4px 8px; font-size: 9pt; }
            .info-box { padding: 6px; }
            .total-row td { font-size: 11pt; }
            @page { margin: 10mm; size: A4; }
        }
        body { font-family: Vazirmatn, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .print-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 20px; border: 1px solid #ddd; }
        .invoice-header { border-bottom: 3px solid <?php echo $primaryColor; ?>; padding-bottom: 10px; margin-bottom: 12px; }
        .invoice-table th { background: #f8f9fa; font-size: 9pt; padding: 5px 8px; }
        .invoice-table td { padding: 5px 8px; font-size: 9pt; }
        .total-row td { font-weight: bold; font-size: 12pt; border-top: 2px solid #000; }
        .info-box { background: #f8f9fa; border-radius: 6px; padding: 8px; }
        .info-box small { font-size: 9px; }
        .info-box strong { font-size: 10pt; }
        .notes-section { margin-top: auto; padding-top: 10px; border-top: 1px dashed #ddd; }
        .footer-section { text-align: center; color: #999; font-size: 8pt; margin-top: 15px; padding-top: 8px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center;padding:10px;background:<?php echo $primaryColor; ?>;color:#fff;">
        <button onclick="window.print();" style="padding:8px 20px;background:#fff;color:<?php echo $primaryColor; ?>;border:none;border-radius:4px;font-weight:bold;cursor:pointer;"><i class="bi bi-printer me-1"></i>چاپ فاکتور</button>
        <button onclick="window.close();" style="padding:8px 20px;background:#6c757d;color:#fff;border:none;border-radius:4px;font-weight:bold;cursor:pointer;margin-right:10px;">بستن</button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <?php if (!empty($logoUrl)): ?>
                    <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="لوگو" style="max-height:45px;margin-bottom:5px;">
                    <?php endif; ?>
                    <h5 class="fw-bold mb-0" style="color:<?php echo $primaryColor; ?>;font-size:14pt;"><?php echo htmlspecialchars($invoiceTitle); ?></h5>
                    <small class="text-muted">شماره فاکتور: #<?php echo $invoice->id; ?> | تاریخ صدور: <?php echo \Core\JDate::displayDateTime($invoice->created_at); ?></small>
                </div>
                <div class="text-start">
                    <div class="fw-bold" style="color:<?php echo $primaryColor; ?>;font-size:14pt;"><?php echo htmlspecialchars($companyName); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($invoiceSubtitle); ?></small>
                </div>
            </div>
        </div>

        <!-- Info Row 1: Deal, Contact, Hotel, Status -->
        <div class="row g-1 mb-2">
            <div class="col-3"><div class="info-box"><small class="text-muted d-block">معامله</small><strong class="small"><?php echo htmlspecialchars($invoice->deal_title); ?></strong></div></div>
            <div class="col-3"><div class="info-box"><small class="text-muted d-block">مخاطب</small><strong class="small"><?php echo htmlspecialchars($invoice->contact_name ?? '-'); ?></strong><?php if ($invoice->contact_phone): ?><br><small class="text-muted" dir="ltr"><?php echo htmlspecialchars($invoice->contact_phone); ?></small><?php endif; ?></div></div>
            <div class="col-3"><div class="info-box"><small class="text-muted d-block">هتل</small><strong class="small"><?php echo htmlspecialchars($invoice->hotel_name); ?></strong></div></div>
            <div class="col-3"><div class="info-box"><small class="text-muted d-block">وضعیت</small><strong class="small"><?php
                $statusLabels = ['draft'=>'پیش‌نویس','final'=>'نهایی','paid'=>'پرداخت شده','cancelled'=>'لغو شده'];
                echo $statusLabels[$invoice->invoice_status] ?? $invoice->invoice_status;
            ?></strong><?php if (!empty($invoice->invoice_type)): ?><br><small style="color:<?php echo $invoice->invoice_type=='confirmed'?$primaryColor:$secondaryColor; ?>;"><?php echo $invoice->invoice_type=='confirmed'?'تایید شده':'پیش فاکتور'; ?></small><?php endif; ?></div></div>
        </div>

        <!-- Info Row 2: Dates, Nights, Persons -->
        <div class="row g-1 mb-2">
            <div class="col-3"><div class="info-box text-center"><small class="text-muted d-block">تاریخ ورود</small><strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></strong></div></div>
            <div class="col-3"><div class="info-box text-center"><small class="text-muted d-block">تاریخ خروج</small><strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></strong></div></div>
            <div class="col-2"><div class="info-box text-center"><small class="text-muted d-block">شب‌ها</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->nights; ?></strong></div></div>
            <div class="col-2"><div class="info-box text-center"><small class="text-muted d-block">بزرگسال</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->adults_count ?? 0; ?></strong></div></div>
            <div class="col-2"><div class="info-box text-center"><small class="text-muted d-block">کودک 3-5</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->children_3to5_count ?? 0; ?></strong></div></div>
        </div>

        <!-- Invoice Table -->
        <table class="table table-bordered invoice-table mb-2">
            <thead>
                <tr>
                    <th style="width:40%">شرح</th>
                    <th style="width:15%" class="text-center">بزرگسال</th>
                    <th style="width:15%" class="text-center">کودک 3-5</th>
                    <th style="width:15%" class="text-center">نرخ (تومان)</th>
                    <th style="width:15%" class="text-center">مبلغ (تومان)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?php echo htmlspecialchars($invoice->hotel_name); ?></strong><br><small class="text-muted"><?php echo $invoice->nights; ?> شب</small></td>
                    <td class="text-center"><?php echo $invoice->adults_count ?? 0; ?></td>
                    <td class="text-center"><?php echo $invoice->children_3to5_count ?? 0; ?></td>
                    <td class="text-center" dir="ltr"><?php echo number_format($invoice->new_price_per_person_night ?? $invoice->price_per_person_night); ?></td>
                    <td class="text-center" dir="ltr"><?php echo number_format($invoice->total_amount); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr><td colspan="4" class="text-start">مبلغ کل</td><td class="text-center fw-bold" dir="ltr"><?php echo number_format(($invoice->total_amount ?? 0) + ($invoice->discount_amount ?? 0)); ?></td></tr>
                <?php if (($invoice->discount_amount ?? 0) > 0): ?>
                <tr><td colspan="4" class="text-start text-danger">تخفیف (<?php echo $invoice->discount_percent; ?>%)</td><td class="text-center fw-bold text-danger" dir="ltr">- <?php echo number_format($invoice->discount_amount); ?></td></tr>
                <?php endif; ?>
                <tr class="total-row"><td colspan="4" class="text-start" style="color:<?php echo $successColor; ?>;">مبلغ نهایی</td><td class="text-center" style="color:<?php echo $successColor; ?>;" dir="ltr"><?php echo number_format($invoice->final_amount); ?></td></tr>
                <?php if (($invoice->deposit_amount ?? 0) > 0): ?>
                <tr><td colspan="4" class="text-start"><i class="bi bi-wallet2 me-1"></i>بیعانه</td><td class="text-center fw-bold" dir="ltr"><?php echo number_format($invoice->deposit_amount); ?></td></tr>
                <?php endif; ?>
            </tfoot>
        </table>

        <!-- Notes Section (at the bottom) -->
        <?php if ($invoice->notes): ?>
        <div class="notes-section mb-2">
            <small class="text-muted d-block mb-1"><i class="bi bi-journal-text me-1"></i>توضیحات</small>
            <p class="small mb-0" style="font-size:9pt;"><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></p>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer-section">
            <small><?php echo htmlspecialchars($companyName); ?> | <?php echo htmlspecialchars($invoiceSubtitle); ?></small>
            <br><small>این فاکتور به صورت الکترونیکی صادر شده است.</small>
        </div>
    </div>
</body>
</html>