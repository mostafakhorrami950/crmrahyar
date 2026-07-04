<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاکتور <?php echo htmlspecialchars($invoice->invoice_number ?? '#' . $invoice->id); ?> - <?php echo htmlspecialchars($invoice->hotel_name); ?></title>
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

    // Status labels
    $stL = ['pending'=>'مانده دارد','settled'=>'تسویه شده','prepaid'=>'پرداخت نشده','paid'=>'پرداخت شده'];
    $stC = ['pending'=>'#e67e22','settled'=>'#27ae60','prepaid'=>'#3498db','paid'=>'#27ae60'];
    $st = $invoice->invoice_status;

    // Calculate items discount
    $itemsDiscount = 0;
    foreach ($items as $itm) {
        $defP = $itm->default_price ?? $itm->unit_price;
        if ($itm->unit_price < $defP) {
            $diff = $defP - $itm->unit_price;
            $itemsDiscount += ($itm->category === 'hotel' && $invoice->nights > 0) ? $diff * $itm->quantity * $invoice->nights : $diff * $itm->quantity;
        }
    }
    $hasDiscount = $itemsDiscount > 0 || ($invoice->discount_amount ?? 0) > 0;
    $isPending = $invoice->invoice_status === 'pending' && ($invoice->deposit_amount ?? 0) > 0;
    ?>
        <?php
    $wmText = '';
    if ($invoice->invoice_status === 'pending') $wmText = 'مانده دارد';
    elseif ($invoice->invoice_status === 'settled') $wmText = 'تسویه شده';
    elseif ($invoice->invoice_status === 'paid') $wmText = 'پرداخت شده';
    elseif ($invoice->invoice_status === 'prepaid' || $invoice->invoice_type === 'proforma') $wmText = 'پیش فاکتور';
    ?>
    <style>
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { margin: 0; padding: 0; background: #fff !important; font-size: 8pt !important; }
            .no-print { display: none !important; }
            .inv-wrap { padding: 4mm; max-width: 100%; border: none; box-shadow: none; margin: 0; border-radius: 0; }
            @page { margin: 5mm; size: A4; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Vazirmatn, Tahoma, sans-serif; background: #e8e8e8; color: #1a1a1a; font-size: 8pt; line-height: 1.3; }
        /* Watermark */
        .inv-wrap { position: relative; overflow: hidden; }
        .inv-wrap::after { content: attr(data-wm); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-35deg); font-size: 72px; font-weight: 900; color: rgba(0,0,0,0.04); white-space: nowrap; pointer-events: none; z-index: 10; letter-spacing: 10px; }
        .inv-wrap { max-width: 750px; margin: 10px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 24px rgba(0,0,0,0.1); overflow: hidden; }

        /* Print/Close buttons */
        .action-bar { background: <?php echo $pc; ?>; padding: 8px; text-align: center; }
        .action-bar button { padding: 6px 24px; border: 2px solid #fff; border-radius: 6px; font-family: inherit; font-weight: 700; font-size: 11px; cursor: pointer; margin: 0 4px; }
        .btn-print { background: #fff; color: <?php echo $pc; ?>; }
        .btn-close { background: transparent; color: #fff; }

        /* Header - compact */
        .header { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px 10px; border-bottom: 3px solid <?php echo $pc; ?>; }
        .header-right { text-align: right; }
        .header-right .logo { max-height: 30px; margin-bottom: 2px; }
        .header-right .title { font-size: 11pt; font-weight: 800; color: #1a1a1a; margin: 0; }
        .header-right .inv-number { font-size: 7pt; color: #666; margin-top: 2px; }
        .header-left { text-align: left; }
        .header-left .company { font-size: 10pt; font-weight: 700; color: <?php echo $pc; ?>; }
        .header-left .subtitle { font-size: 7pt; color: #666; }

        /* Badges - compact */
        .badges { padding: 5px 16px; display: flex; gap: 6px; align-items: center; background: <?php echo $pc; ?>0D; font-size: 7pt; border-bottom: 2px solid <?php echo $pc; ?>33; }
        .badge { display: inline-block; padding: 1px 8px; border-radius: 10px; font-size: 7pt; font-weight: 700; }
        .badge-status { background: <?php echo $pc; ?>; color: #fff; border: 1.5px solid <?php echo $pc; ?>; }
        .badge-type { background: #fff; color: #555; border: 1px solid #999; }
        .creator-info { margin-left: auto; font-size: 7pt; color: #888; }

        /* Info Cards - very compact */
        .info-section { padding: 6px 16px; }
        .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; }
        .info-card { background: #fafafa; border-radius: 3px; padding: 4px 6px; border: 1px solid #ddd; }
        .info-card .label { font-size: 5.5pt; color: #888; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 1px; }
        .info-card .value { font-size: 7.5pt; font-weight: 700; color: #1a1a1a; line-height: 1.2; }
        .info-card .value.accent { color: #000; text-decoration: underline; }

        /* Divider - compact */
        .section-title { padding: 0 16px; margin: 4px 0 3px; display: flex; align-items: center; gap: 6px; }
        .section-title .line { flex: 1; height: 2px; background: <?php echo $pc; ?>33; }
        .section-title span { font-size: 7pt; font-weight: 800; color: #1a1a1a; white-space: nowrap; }

        /* Items Table - very compact */
        .items-section { padding: 0 16px; }
        .items-table { width: 100%; border-collapse: collapse; font-size: 7.5pt; border: 1.5px solid #1a1a1a; }
        .items-table thead th { background: <?php echo $pc; ?>; color: #fff; padding: 4px 6px; font-weight: 700; font-size: 6.5pt; text-align: right; }
        .items-table tbody td { padding: 3px 6px; border-bottom: 1px solid #ddd; line-height: 1.2; }
        .items-table tbody tr:nth-child(even) { background: #f8f8f8; }
        .items-table tbody tr:last-child td { border-bottom: 1.5px solid #1a1a1a; }
        .items-table .num { text-align: center; font-weight: 700; }
        .items-table .price { text-align: left; direction: ltr; font-family: 'Courier New', monospace; font-size: 7pt; }
        .items-table .total { text-align: left; direction: ltr; font-weight: 800; font-family: 'Courier New', monospace; font-size: 7pt; }
        .item-cat { display: inline-block; background: #fff; color: #555; font-size: 5.5pt; padding: 0px 4px; border: 1px solid #999; border-radius: 2px; margin-top: 1px; }

        /* Financial Summary - compact */
        .summary-section { padding: 0 16px 6px; }
        .summary-box { max-width: 260px; margin-right: auto; background: #fafafa; border-radius: 4px; padding: 6px 10px; border: 2px solid <?php echo $pc; ?>; }
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 1px 0; font-size: 7.5pt; }
        .summary-row .lbl { color: #555; }
        .summary-row .val { font-weight: 700; font-family: 'Courier New', monospace; direction: ltr; font-size: 7.5pt; }
        .summary-row .val.red { color: #333; font-weight: 800; text-decoration: underline; }
        .summary-divider { border: none; border-top: 1.5px dashed #333; margin: 3px 0; }
        .summary-total { display: flex; justify-content: space-between; align-items: center; padding: 3px 0 0; font-size: 9pt; font-weight: 900; }
        .summary-total .lbl { color: #000; text-transform: uppercase; }
        .summary-total .val { font-family: 'Courier New', monospace; direction: ltr; }
        .summary-total .val.remaining { color: #fff; font-size: 10pt; background: <?php echo $stC[$st] ?? '#e67e22'; ?>; padding: 2px 10px; border-radius: 3px; }
        .summary-total .val.final { color: #fff; font-size: 10pt; background: <?php echo $pc; ?>; padding: 2px 10px; border-radius: 3px; }

        /* Notes & Terms - compact */
        .notes-section { padding: 0 16px 6px; }
        .note-box { background: #fafafa; border-radius: 3px; padding: 4px 8px; border-right: 3px solid <?php echo $pc; ?>; font-size: 7pt; color: #333; margin-bottom: 4px; line-height: 1.3; }
        .note-box .note-title { font-weight: 800; color: #333; margin-bottom: 1px; font-size: 6.5pt; text-transform: uppercase; }
        .terms-box { background: #fafafa; border-radius: 3px; padding: 4px 8px; border-right: 3px solid #1a1a1a; font-size: 7pt; color: #333; line-height: 1.3; }
        .terms-box .terms-title { font-weight: 800; color: #1a1a1a; margin-bottom: 1px; font-size: 6.5pt; text-transform: uppercase; }

        /* Signature - compact */
        .signature-section { padding: 8px 16px; display: flex; justify-content: space-between; gap: 30px; }
        .sig-box { flex: 1; text-align: center; padding-top: 24px; border-top: 2px solid <?php echo $pc; ?>; font-size: 7pt; color: #555; font-weight: 600; }

        /* Footer - compact */
        .invoice-footer { background: <?php echo $pc; ?>0D; padding: 6px 16px; text-align: center; border-top: 2px solid <?php echo $pc; ?>; }
        .invoice-footer p { font-size: 6.5pt; color: #555; margin-bottom: 2px; font-weight: 600; }
        .invoice-footer .company-line { font-size: 6pt; color: #888; }
    </style>
</head>
<body>
    <div class="no-print action-bar">
        <button class="btn-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>چاپ فاکتور</button>
        <button class="btn-close" onclick="window.close()"><i class="bi bi-x-lg me-1"></i>بستن</button>
    </div>

    <div class="inv-wrap" data-wm="<?php echo htmlspecialchars($wmText); ?>">
        <!-- Header -->
        <div class="header">
            <div class="header-right">
                <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" class="logo" alt="لوگو"><?php endif; ?>
                <h1 class="title"><?php echo htmlspecialchars($invSet['invoice_title'] ?? 'فاکتور رزرو هتل'); ?></h1>
                <div class="inv-number">
                    شماره: <strong><?php echo $invoice->invoice_number ?? '#' . $invoice->id; ?></strong>
                    &nbsp;&bull;&nbsp;
                    تاریخ صدور: <strong><?php echo \Core\JDate::displayDateTime($invoice->created_at); ?></strong>
                </div>
            </div>
            <div class="header-left">
                <div class="company"><?php echo htmlspecialchars($company); ?></div>
                <div class="subtitle"><?php echo htmlspecialchars($sub); ?></div>
            </div>
        </div>

        <!-- Badges -->
        <div class="badges">
            <span class="badge badge-status"><?php echo $stL[$st] ?? $st; ?></span>
            <?php if ($invoice->invoice_type): ?>
            <span class="badge badge-type"><?php echo $invoice->invoice_type == 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور'; ?></span>
            <?php endif; ?>
            <span class="creator-info">صادر شده توسط: <strong><?php echo htmlspecialchars($invoice->creator_name ?? '-'); ?></strong></span>
        </div>

        <!-- Info Cards -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-card">
                    <div class="label">نام هتل</div>
                    <div class="value"><?php echo htmlspecialchars($invoice->hotel_name); ?></div>
                </div>
                <div class="info-card">
                    <div class="label">نام آژانس</div>
                    <div class="value"><?php echo htmlspecialchars($invoice->agency_name ?? '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="label">نام میهمان</div>
                    <div class="value"><?php echo htmlspecialchars($invoice->guest_name ?? '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="label">تلفن</div>
                    <div class="value" dir="ltr" style="text-align:left;"><?php echo htmlspecialchars($invoice->guest_phone ?? $invoice->contact_phone ?? '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="label">تعداد شب‌ها</div>
                    <div class="value accent"><?php echo $invoice->nights; ?> شب</div>
                </div>
                <div class="info-card">
                    <div class="label">تاریخ ورود</div>
                    <div class="value"><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></div>
                </div>
                <div class="info-card">
                    <div class="label">تاریخ خروج</div>
                    <div class="value"><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></div>
                </div>
                <?php if ($invoice->valid_until): ?>
                <div class="info-card">
                    <div class="label">تاریخ اعتبار</div>
                    <div class="value"><?php echo \Core\JDate::displayDate($invoice->valid_until); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Section -->
        <div class="section-title">
            <span><i class="bi bi-list-ol me-1"></i>اقلام فاکتور</span>
            <div class="line"></div>
        </div>

        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:4%">#</th>
                        <th style="width:36%">شرح</th>
                        <th style="width:8%" class="num">تعداد</th>
                        <th style="width:14%" class="price">قیمت واحد</th>
                        <th style="width:8%" class="num">شب</th>
                        <th style="width:15%" class="total">مبلغ کل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td class="num" style="color:#95a5a6;"><?php echo $i + 1; ?></td>
                        <td>
                            <div><?php echo htmlspecialchars($item->description); ?>
                                    <?php if (!empty($item->room_type)): ?><br><small class="item-cat"><?php echo htmlspecialchars($item->room_type); ?></small><?php endif; ?></div>
                            <?php if (!empty($item->category) && $item->category !== 'general'): ?>
                            <span class="item-cat"><?php echo $item->category === 'hotel' ? 'هتل' : ($item->category === 'transfer' ? 'ترانسفر' : $item->category); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="num"><?php echo number_format((int)$item->quantity); ?></td>
                        <td class="price"><?php echo number_format($item->unit_price); ?></td>
                        <td class="num"><?php echo $invoice->nights; ?></td>
                        <td class="total"><?php echo number_format($item->total_price); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Financial Summary -->
        <div class="section-title">
            <span><i class="bi bi-calculator me-1"></i>خلاصه مالی</span>
            <div class="line"></div>
        </div>

        <div class="summary-section">
            <div class="summary-box">
                <?php if ($itemsDiscount > 0): ?>
                <div class="summary-row">
                    <span class="lbl">جمع کل (قیمت اصلی)</span>
                    <span class="val"><?php echo number_format(($invoice->subtotal ?? 0) + $itemsDiscount); ?></span>
                </div>
                <div class="summary-row">
                    <span class="lbl"><i class="bi bi-tag me-1"></i>تخفیف تغییر قیمت</span>
                    <span class="val red">- <?php echo number_format($itemsDiscount); ?></span>
                </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span class="lbl">جمع کل</span>
                    <span class="val"><?php echo number_format($invoice->subtotal ?? $invoice->total_amount ?? 0); ?></span>
                </div>

                <?php if (($invoice->tax_percent ?? 0) > 0): ?>
                <div class="summary-row">
                    <span class="lbl">مالیات (<?php echo $invoice->tax_percent; ?>%)</span>
                    <span class="val"><?php echo number_format($invoice->tax_amount ?? 0); ?></span>
                </div>
                <?php endif; ?>

                <?php if (($invoice->discount_amount ?? 0) > 0): ?>
                <div class="summary-row">
                    <span class="lbl">تخفیف</span>
                    <span class="val red">- <?php echo number_format($invoice->discount_amount); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($isPending): ?>
                <hr class="summary-divider">
                <div class="summary-row">
                    <span class="lbl"><i class="bi bi-wallet2 me-1"></i>بیعانه پرداخت شده</span>
                    <span class="val red">- <?php echo number_format($invoice->deposit_amount); ?></span>
                </div>
                <div class="summary-total">
                    <span class="lbl">مبلغ باقیمانده:</span>
                    <span class="val remaining"><?php echo number_format($invoice->final_amount - $invoice->deposit_amount); ?> تومان</span>
                </div>
                <?php else: ?>
                <hr class="summary-divider">
                <div class="summary-total">
                    <span class="lbl">مبلغ نهایی:</span>
                    <span class="val final"><?php echo number_format($invoice->final_amount); ?> تومان</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notes & Terms -->
        <?php if ($terms || $invoice->notes): ?>
        <div class="notes-section">
            <?php if (!empty($invoice->ps_note)): ?>
            <div class="note-box">
                <div class="note-title"><i class="bi bi-pencil-square me-1"></i>پینوشت</div>
                <?php echo nl2br(htmlspecialchars($invoice->ps_note)); ?>
            </div>
            <?php endif; ?>

            <?php if ($invoice->notes): ?>
            <div class="note-box">
                <div class="note-title"><i class="bi bi-journal-text me-1"></i>توضیحات</div>
                <?php echo nl2br(htmlspecialchars($invoice->notes)); ?>
            </div>
            <?php endif; ?>
            <?php if ($terms): ?>
            <div class="terms-box">
                <div class="terms-title"><i class="bi bi-shield-check me-1"></i>شرایط پرداخت</div>
                <?php echo nl2br(htmlspecialchars($terms)); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Signature -->
        <div class="signature-section">
            <div class="sig-box">
                <?php if (!empty($invSet['invoice_signature_url'])): ?>
                <img src="<?php echo htmlspecialchars($invSet['invoice_signature_url']); ?>" alt="امضا" style="max-height:50px;margin-bottom:4px;"><br>
                <?php endif; ?>
                امضای صادرکننده
            </div>
            <div class="sig-box">امضای تاییدکننده</div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <?php if ($footer): ?><p><?php echo nl2br(htmlspecialchars($footer)); ?></p><?php endif; ?>
            <div class="company-line"><?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?></div>
        </div>
    </div>
</body>
</html>