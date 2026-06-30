<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چاپ فاکتور - <?php echo htmlspecialchars($invoice->hotel_name); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <?php
    $invSet = $invoiceSettings ?? [];
    $pc = $invSet['invoice_primary_color'] ?? '#0d6efd';
    $sc = $invSet['invoice_success_color'] ?? '#198754';
    $company = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $sub = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logo = $invSet['invoice_logo_url'] ?? '';
    $footer = $invoice->footer_text ?? $invSet['invoice_footer_text'] ?? '';
    $terms = $invoice->payment_terms ?? $invSet['invoice_terms'] ?? '';
    ?>
    <style>
        @media print {
            body { margin:0; padding:0; font-size:9pt; }
            .no-print { display:none!important; }
            .inv-wrap { padding:8mm; max-width:100%; }
            @page { margin:8mm; size:A4; }
        }
        body { font-family:Vazirmatn,sans-serif; background:#f0f2f5; margin:0; }
        .inv-wrap { max-width:780px; margin:15px auto; background:#fff; padding:20px; border:1px solid #e0e0e0; border-radius:8px; }
        .inv-top { border-bottom:2px solid <?php echo $pc; ?>; padding-bottom:10px; margin-bottom:12px; }
        .inv-badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:8pt; font-weight:600; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:12px; }
        .info-item { background:#f8f9fa; border-radius:4px; padding:6px 8px; }
        .info-item small { font-size:7.5pt; color:#666; display:block; }
        .info-item strong { font-size:8.5pt; }
        .item-tbl { width:100%; border-collapse:collapse; margin-bottom:12px; font-size:8.5pt; }
        .item-tbl th { background:#f1f3f5; padding:6px 8px; border:1px solid #dee2e6; text-align:right; font-size:8pt; }
        .item-tbl td { padding:5px 8px; border:1px solid #dee2e6; }
        .sum-tbl { width:45%; margin-left:0; margin-right:auto; font-size:8.5pt; }
        .sum-tbl td { padding:3px 8px; }
        .footer-sec { border-top:1px dashed #dee2e6; padding-top:8px; margin-top:12px; font-size:7.5pt; color:#666; }
        .stamp-area { margin-top:20px; display:flex; justify-content:space-between; }
        .stamp-box { width:45%; text-align:center; border-top:1px solid #999; padding-top:5px; font-size:7.5pt; color:#666; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center;padding:8px;background:<?php echo $pc; ?>;color:#fff;border-radius:0 0 8px 8px;">
        <button onclick="window.print()" style="padding:8px 24px;background:#fff;color:<?php echo $pc; ?>;border:none;border-radius:4px;font-weight:bold;cursor:pointer;margin:4px;"><i class="bi bi-printer me-1"></i>چاپ</button>
        <button onclick="window.close()" style="padding:8px 24px;background:#666;color:#fff;border:none;border-radius:4px;font-weight:bold;cursor:pointer;margin:4px;">بستن</button>
    </div>

    <div class="inv-wrap">
        <!-- Header -->
        <div class="inv-top">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" style="max-height:40px;margin-bottom:4px;" alt="لوگو"><?php endif; ?>
                    <h5 style="color:<?php echo $pc; ?>;font-size:14pt;font-weight:700;margin:0 0 2px 0;"><?php echo htmlspecialchars($invoiceTitle ?? 'فاکتور رزرو هتل'); ?></h5>
                    <span style="font-size:8pt;color:#666;">شماره: <strong><?php echo $invoice->invoice_number ?? '#'.$invoice->id; ?></strong></span>
                    &nbsp;|&nbsp;
                    <span style="font-size:8pt;color:#666;">تاریخ صدور: <strong><?php echo \Core\JDate::displayDateTime($invoice->created_at); ?></strong></span>
                </div>
                <div class="text-start">
                    <div style="color:<?php echo $pc; ?>;font-size:14pt;font-weight:700;"><?php echo htmlspecialchars($company); ?></div>
                    <div style="font-size:8pt;color:#666;"><?php echo htmlspecialchars($sub); ?></div>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:8pt;">
            <div>
                <?php
                $stL = ['pending'=>'مانده دارد','settled'=>'تسویه شده','prepaid'=>'پیش پرداخت'];
                $stC = ['pending'=>'#ffc107','settled'=>'#198754','prepaid'=>'#0dcaf0'];
                $st = $invoice->invoice_status;
                ?>
                <span class="inv-badge" style="background:<?php echo $stC[$st]??'#6c757d'; ?>20;color:<?php echo $stC[$st]??'#6c757d'; ?>;"><?php echo $stL[$st]??$st; ?></span>
                <?php if ($invoice->invoice_type): ?>
                <span class="inv-badge" style="background:<?php echo $pc; ?>15;color:<?php echo $pc; ?>;"><?php echo $invoice->invoice_type=='confirmed'?'تایید شده':'پیش فاکتور'; ?></span>
                <?php endif; ?>
            </div>
            <div style="color:#999;">صادر شده توسط: <strong style="color:#333;"><?php echo htmlspecialchars($invoice->creator_name ?? '-'); ?></strong></div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-item"><small>هتل</small><strong><?php echo htmlspecialchars($invoice->hotel_name); ?></strong></div>
            <div class="info-item"><small>میهمان</small><strong><?php echo htmlspecialchars($invoice->guest_name ?? $invoice->contact_name ?? '-'); ?></strong></div>
            <div class="info-item"><small>تلفن</small><strong dir="ltr"><?php echo htmlspecialchars($invoice->guest_phone ?? $invoice->contact_phone ?? '-'); ?></strong></div>
            <div class="info-item"><small>خدمات</small><strong><?php
                $svcs = [];
                if ($invoice->transfer_included) $svcs[] = 'ترانسفر';
                if ($invoice->visa_included) $svcs[] = 'ویزا';
                if ($invoice->insurance_included) $svcs[] = 'بیمه';
                echo !empty($svcs) ? implode(' | ', $svcs) : '-';
            ?></strong></div>
        </div>
        <div class="info-grid">
            <div class="info-item"><small>تاریخ ورود</small><strong><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></strong></div>
            <div class="info-item"><small>تاریخ خروج</small><strong><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></strong></div>
            <div class="info-item"><small>مدت اقامت</small><strong style="color:<?php echo $pc; ?>;"><?php echo $invoice->nights; ?> شب</strong></div>
            <?php if ($invoice->valid_until): ?>
            <div class="info-item"><small>تاریخ اعتبار</small><strong><?php echo \Core\JDate::displayDate($invoice->valid_until); ?></strong></div>
            <?php endif; ?>
        </div>

        <!-- Items Table with Nights Column -->
        <table class="item-tbl">
            <thead>
                <tr>
                    <th style="width:4%">#</th>
                    <th style="width:38%">شرح آیتم</th>
                    <th style="width:8%" class="text-center">تعداد</th>
                    <th style="width:14%" class="text-center">قیمت واحد</th>
                    <th style="width:8%" class="text-center">شب</th>
                    <th style="width:14%" class="text-center">مبلغ کل</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td class="text-center"><?php echo $i + 1; ?></td>
                    <td>
                        <?php echo htmlspecialchars($item->description); ?>
                        <?php if (!empty($item->category) && $item->category === 'hotel'): ?>
                        <br><small style="font-size:7pt;color:#999;">(قیمت هر شب)</small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo number_format((int)$item->quantity); ?></td>
                    <td class="text-center" dir="ltr"><?php echo number_format($item->unit_price); ?></td>
                    <td class="text-center"><?php echo $invoice->nights; ?></td>
                    <td class="text-center fw-bold" dir="ltr"><?php echo number_format($item->total_price); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Financial Summary -->
        <table class="sum-tbl">
            <tr><td style="color:#666;">جمع کل:</td><td class="text-end fw-bold" dir="ltr"><?php echo number_format($invoice->subtotal ?? $invoice->total_amount ?? 0); ?> <small>تومان</small></td></tr>
            <?php if (($invoice->tax_percent ?? 0) > 0): ?>
            <tr><td style="color:#666;">مالیات (<?php echo $invoice->tax_percent; ?>%):</td><td class="text-end fw-bold" dir="ltr"><?php echo number_format($invoice->tax_amount ?? 0); ?> <small>تومان</small></td></tr>
            <?php endif; ?>
            <?php if (($invoice->service_fee ?? 0) > 0): ?>
            <tr><td style="color:#666;">هزینه خدمات:</td><td class="text-end fw-bold" dir="ltr"><?php echo number_format($invoice->service_fee); ?> <small>تومان</small></td></tr>
            <?php endif; ?>
            <?php if (($invoice->discount_amount ?? 0) > 0): ?>
            <tr><td style="color:#dc3545;">تخفیف:</td><td class="text-end fw-bold text-danger" dir="ltr">- <?php echo number_format($invoice->discount_amount); ?> <small>تومان</small></td></tr>
            <?php endif; ?>
            <tr><td style="font-weight:700;font-size:10pt;padding-top:6px;border-top:2px solid #333;">مبلغ نهایی:</td><td class="text-end fw-bold" style="font-size:11pt;color:<?php echo $sc; ?>;padding-top:6px;border-top:2px solid #333;" dir="ltr"><?php echo number_format($invoice->final_amount); ?> <small>تومان</small></td></tr>
            <?php if (($invoice->deposit_amount ?? 0) > 0): ?>
            <tr><td style="color:#666;"><i class="bi bi-wallet2"></i> بیعانه:</td><td class="text-end fw-bold" dir="ltr"><?php echo number_format($invoice->deposit_amount); ?> <small>تومان</small></td></tr>
            <?php endif; ?>
        </table>

        <!-- Payment Terms -->
        <?php if ($terms): ?>
        <div style="background:#f8f9fa;border-radius:4px;padding:8px;margin-top:10px;font-size:8pt;">
            <strong style="color:#666;"><i class="bi bi-shield-check me-1"></i>شرایط پرداخت:</strong>
            <span style="color:#555;"><?php echo nl2br(htmlspecialchars($terms)); ?></span>
        </div>
        <?php endif; ?>

        <!-- Notes -->
        <?php if ($invoice->notes): ?>
        <div style="margin-top:8px;font-size:8pt;">
            <strong style="color:#666;"><i class="bi bi-journal-text me-1"></i>توضیحات:</strong>
            <span style="color:#555;"><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></span>
        </div>
        <?php endif; ?>

        <!-- Signature Area -->
        <div class="stamp-area">
            <div class="stamp-box">امضای صادرکننده</div>
            <div class="stamp-box">امضای تاییدکننده</div>
        </div>

        <!-- Footer -->
        <div class="footer-sec text-center">
            <?php if ($footer): ?><p style="margin:0 0 4px 0;"><?php echo nl2br(htmlspecialchars($footer)); ?></p><?php endif; ?>
            <small><?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?> | صادر شده توسط: <?php echo htmlspecialchars($invoice->creator_name ?? '-'); ?></small>
        </div>
    </div>
</body>
</html>