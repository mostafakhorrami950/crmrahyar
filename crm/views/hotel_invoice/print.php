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
        @media print { body { font-size: 12pt; } .no-print { display: none !important; } .print-container { margin: 0; padding: 0; } }
        body { font-family: Vazirmatn, sans-serif; background: #f5f5f5; }
        .print-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 30px; border: 1px solid #ddd; }
        .invoice-header { border-bottom: 3px solid <?php echo $primaryColor; ?>; padding-bottom: 15px; margin-bottom: 20px; }
        .invoice-table th { background: #f8f9fa; }
        .invoice-table td { padding: 8px 12px; }
        .total-row td { font-weight: bold; font-size: 14pt; border-top: 2px solid #000; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center;padding:10px;background:<?php echo $primaryColor; ?>;color:#fff;">
        <button onclick="window.print();" style="padding:8px 20px;background:#fff;color:<?php echo $primaryColor; ?>;border:none;border-radius:4px;font-weight:bold;cursor:pointer;"><i class="bi bi-printer me-1"></i>چاپ فاکتور</button>
        <button onclick="window.close();" style="padding:8px 20px;background:#6c757d;color:#fff;border:none;border-radius:4px;font-weight:bold;cursor:pointer;margin-right:10px;">بستن</button>
    </div>

    <div class="print-container">
        <div class="invoice-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <?php if (!empty($logoUrl)): ?>
                    <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="لوگو" style="max-height:60px;margin-bottom:10px;">
                    <?php endif; ?>
                    <h4 class="fw-bold mb-1" style="color:<?php echo $primaryColor; ?>;"><?php echo htmlspecialchars($invoiceTitle); ?></h4>
                    <small class="text-muted">شماره فاکتور: #<?php echo $invoice->id; ?></small>
                    <br><small class="text-muted">تاریخ صدور: <?php echo \Core\JDate::displayDateTime($invoice->created_at); ?></small>
                </div>
                <div class="text-start">
                    <div class="fw-bold" style="color:<?php echo $primaryColor; ?>;font-size:18px;"><?php echo htmlspecialchars($companyName); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($invoiceSubtitle); ?></small>
                </div>
            </div>
        </div>

        <!-- Deal & Contact Info -->
        <div class="row g-2 mb-4">
            <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:10px;">معامله</small><strong class="small"><?php echo htmlspecialchars($invoice->deal_title); ?></strong></div></div>
            <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:10px;">مخاطب</small><strong class="small"><?php echo htmlspecialchars($invoice->contact_name ?? '-'); ?></strong><?php if ($invoice->contact_phone): ?><br><small class="text-muted" dir="ltr"><?php echo htmlspecialchars($invoice->contact_phone); ?></small><?php endif; ?></div></div>
            <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:10px;">هتل</small><strong class="small"><?php echo htmlspecialchars($invoice->hotel_name); ?></strong></div></div>
            <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:10px;">وضعیت</small><strong class="small"><?php echo $invoice->invoice_status=='final'?'نهایی':($invoice->invoice_status=='cancelled'?'لغو شده':'پیش‌نویس'); ?></strong><?php if (!empty($invoice->invoice_type)): ?> | <strong class="small" style="color:<?php echo $invoice->invoice_type=='confirmed'?$primaryColor:$secondaryColor; ?>;"><?php echo $invoice->invoice_type=='confirmed'?'فاکتور تایید شده':'پیش فاکتور'; ?></strong><?php endif; ?></div></div>
        </div>

        <!-- Dates -->
        <div class="row g-2 mb-4">
            <div class="col-3"><div class="bg-light rounded p-2 text-center"><small class="text-muted d-block" style="font-size:10px;">تاریخ ورود</small><strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></strong></div></div>
            <div class="col-3"><div class="bg-light rounded p-2 text-center"><small class="text-muted d-block" style="font-size:10px;">تاریخ خروج</small><strong class="small"><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></strong></div></div>
            <div class="col-3"><div class="bg-light rounded p-2 text-center"><small class="text-muted d-block" style="font-size:10px;">تعداد شب‌ها</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->nights; ?></strong></div></div>
            <div class="col-3"><div class="bg-light rounded p-2 text-center"><small class="text-muted d-block" style="font-size:10px;">نفر-شب</small><strong style="color:<?php echo $primaryColor; ?>;"><?php echo $invoice->person_night_count; ?></strong></div></div>
        </div>

        <!-- Persons Breakdown -->
        <div class="row g-2 mb-4">
            <div class="col-4"><div class="bg-light rounded p-2 text-center"><small class="text-muted d-block" style="font-size:10px;">بزرگسال</small><strong><?php echo $invoice->adults_count ?? 0; ?></strong></div></div>
            <div class="col-4"><div class="bg-light rounded p-2 text-center"><small class="text-muted d-block" style="font-size:10px;">کودک 3-5 سال</small><strong><?php echo $invoice->children_3to5_count ?? 0; ?></strong><br><small class="text-muted">نیم بها</small></div></div>
            <div class="col-4"><div class="bg-light rounded p-2 text-center"><small class="text-muted d-block" style="font-size:10px;">کودک زیر 3 سال</small><strong><?php echo $invoice->children_under3_count ?? 0; ?></strong><br><small class="text-muted">رایگان</small></div></div>
        </div>

        <!-- Invoice Table -->
        <table class="table table-bordered invoice-table mb-4">
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
                <tr><td colspan="4" class="text-start">مبلغ کل</td><td class="text-center fw-bold" dir="ltr"><?php echo number_format($invoice->total_amount); ?></td></tr>
                <tr><td colspan="4" class="text-start text-danger">تخفیف (<?php echo $invoice->discount_percent; ?>%)</td><td class="text-center fw-bold text-danger" dir="ltr">- <?php echo number_format($invoice->discount_amount); ?></td></tr>
                <tr class="total-row"><td colspan="4" class="text-start" style="color:<?php echo $successColor; ?>;">مبلغ نهایی</td><td class="text-center" style="color:<?php echo $successColor; ?>;" dir="ltr"><?php echo number_format($invoice->final_amount); ?></td></tr>
                <?php if (($invoice->deposit_amount ?? 0) > 0): ?>
                <tr><td colspan="4" class="text-start"><i class="bi bi-wallet2 me-1"></i>بیعانه</td><td class="text-center fw-bold" dir="ltr"><?php echo number_format($invoice->deposit_amount); ?></td></tr>
                <?php endif; ?>
            </tfoot>
        </table>

        <?php if ($invoice->notes): ?>
        <div class="mb-4"><small class="text-muted d-block mb-1"><i class="bi bi-journal-text me-1"></i>توضیحات</small><p class="small mb-0"><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></p></div>
        <?php endif; ?>

        <div class="text-center text-muted small mt-5 pt-3 border-top">
            <small><?php echo htmlspecialchars($companyName); ?> | <?php echo htmlspecialchars($invoiceSubtitle); ?></small>
            <br><small>این فاکتور به صورت الکترونیکی صادر شده است.</small>
        </div>
    </div>
</body>
</html>