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
            body { margin: 0; padding: 0; background: #fff !important; }
            .no-print { display: none !important; }
            .page { padding: 0; max-width: 100%; box-shadow: none; margin: 0; border-radius: 0; }
            @page { margin: 8mm; size: A4; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Vazirmatn, Tahoma, sans-serif; background: #f0f2f5; color: #2d3436; font-size: 9pt; line-height: 1.5; }

        /* Page Container */
        .page { max-width: 780px; margin: 20px auto; background: #fff; border-radius: 12px; box-shadow: 0 8px 40px rgba(0,0,0,0.12); overflow: hidden; position: relative; }

        /* Watermark */
        .page::after { content: attr(data-wm); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-40deg); font-size: 80px; font-weight: 900; color: rgba(0,0,0,0.03); white-space: nowrap; pointer-events: none; z-index: 10; letter-spacing: 12px; text-transform: uppercase; }

        /* Action Bar */
        .action-bar { background: linear-gradient(135deg, <?php echo $pc; ?>, <?php echo $pc; ?>dd); padding: 10px 20px; display: flex; justify-content: center; gap: 12px; }
        .action-bar button { padding: 8px 28px; border: 2px solid rgba(255,255,255,0.3); border-radius: 8px; font-family: inherit; font-weight: 700; font-size: 12px; cursor: pointer; transition: all 0.2s; }
        .btn-print { background: #fff; color: <?php echo $pc; ?>; }
        .btn-print:hover { background: rgba(255,255,255,0.9); transform: translateY(-1px); }
        .btn-close { background: transparent; color: #fff; }
        .btn-close:hover { background: rgba(255,255,255,0.15); }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: center; padding: 24px 30px 20px; position: relative; }
        .header::after { content: ''; position: absolute; bottom: 0; left: 30px; right: 30px; height: 3px; background: linear-gradient(90deg, <?php echo $pc; ?>, <?php echo $pc; ?>88, transparent); border-radius: 2px; }
        .header-right { text-align: right; }
        .header-right .logo { max-height: 40px; margin-bottom: 6px; }
        .header-right .title { font-size: 16pt; font-weight: 900; color: #2d3436; margin: 0; letter-spacing: -0.5px; }
        .header-right .inv-meta { font-size: 8pt; color: #636e72; margin-top: 6px; display: flex; gap: 16px; align-items: center; }
        .header-right .inv-meta strong { color: <?php echo $pc; ?>; }
        .header-left { text-align: left; }
        .header-left .company { font-size: 13pt; font-weight: 800; color: <?php echo $pc; ?>; letter-spacing: -0.3px; }
        .header-left .subtitle { font-size: 8pt; color: #636e72; margin-top: 2px; }

        /* Status Bar */
        .status-bar { padding: 10px 30px; display: flex; gap: 10px; align-items: center; background: linear-gradient(135deg, #f8f9fa, #fff); border-bottom: 1px solid #eee; }
        .status-pill { display: inline-flex; align-items: center; gap: 4px; padding: 4px 14px; border-radius: 20px; font-size: 8pt; font-weight: 700; }
        .status-pill.active { background: <?php echo $pc; ?>; color: #fff; }
        .status-pill.type { background: #fff; color: #555; border: 1.5px solid #ddd; }
        .creator-info { margin-right: auto; font-size: 7.5pt; color: #999; }

        /* Info Grid */
        .info-section { padding: 16px 30px; }
        .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
        .info-card { background: linear-gradient(135deg, #f8f9fa, #fff); border-radius: 8px; padding: 10px 12px; border: 1px solid #eef0f2; transition: all 0.2s; }
        .info-card:hover { border-color: <?php echo $pc; ?>33; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .info-card .ic-icon { width: 28px; height: 28px; border-radius: 6px; background: <?php echo $pc; ?>12; display: flex; align-items: center; justify-content: center; margin-bottom: 6px; color: <?php echo $pc; ?>; font-size: 12px; }
        .info-card .label { font-size: 7pt; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; font-weight: 600; }
        .info-card .value { font-size: 8.5pt; font-weight: 700; color: #2d3436; line-height: 1.3; }
        .info-card .value.accent { color: <?php echo $pc; ?>; }

        /* Section Divider */
        .section-divider { padding: 0 30px; margin: 12px 0 8px; display: flex; align-items: center; gap: 10px; }
        .section-divider .icon { width: 28px; height: 28px; border-radius: 6px; background: <?php echo $pc; ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
        .section-divider .text { font-size: 9pt; font-weight: 800; color: #2d3436; white-space: nowrap; }
        .section-divider .line { flex: 1; height: 1px; background: linear-gradient(90deg, #ddd, transparent); }

        /* Items Table */
        .items-section { padding: 0 30px; }
        .items-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 8pt; border-radius: 8px; overflow: hidden; border: 1px solid #e8ecef; }
        .items-table thead th { background: linear-gradient(135deg, <?php echo $pc; ?>, <?php echo $pc; ?>dd); color: #fff; padding: 10px 12px; font-weight: 700; font-size: 7.5pt; text-align: right; letter-spacing: 0.3px; }
        .items-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f0f2f5; line-height: 1.4; vertical-align: middle; }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table tbody tr:hover { background: <?php echo $pc; ?>08; }
        .items-table .num { text-align: center; font-weight: 700; }
        .items-table .price { text-align: left; direction: ltr; font-family: 'Courier New', monospace; font-size: 7.5pt; }
        .items-table .total { text-align: left; direction: ltr; font-weight: 800; font-family: 'Courier New', monospace; font-size: 8pt; color: <?php echo $pc; ?>; }
        .item-tag { display: inline-flex; align-items: center; gap: 3px; background: <?php echo $pc; ?>12; color: <?php echo $pc; ?>; font-size: 6.5pt; padding: 2px 8px; border-radius: 10px; margin-top: 3px; font-weight: 600; }

        /* Financial Summary */
        .summary-section { padding: 0 30px 16px; }
        .summary-box { max-width: 300px; margin-right: auto; background: linear-gradient(135deg, #f8f9fa, #fff); border-radius: 10px; padding: 16px 20px; border: 2px solid <?php echo $pc; ?>22; position: relative; overflow: hidden; }
        .summary-box::before { content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 100%; background: <?php echo $pc; ?>; border-radius: 0 10px 10px 0; }
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; font-size: 8pt; }
        .summary-row .lbl { color: #636e72; display: flex; align-items: center; gap: 4px; }
        .summary-row .val { font-weight: 700; font-family: 'Courier New', monospace; direction: ltr; font-size: 8pt; }
        .summary-row .val.red { color: #e74c3c; font-weight: 800; }
        .summary-divider { border: none; border-top: 2px dashed #e0e0e0; margin: 8px 0; }
        .summary-total { display: flex; justify-content: space-between; align-items: center; padding: 8px 0 0; font-size: 10pt; font-weight: 900; }
        .summary-total .lbl { color: #2d3436; }
        .summary-total .val { font-family: 'Courier New', monospace; direction: ltr; }
        .summary-total .val.remaining { color: #fff; font-size: 11pt; background: linear-gradient(135deg, <?php echo $stC[$st] ?? '#e67e22'; ?>, <?php echo $stC[$st] ?? '#e67e22'; ?>dd); padding: 6px 16px; border-radius: 8px; box-shadow: 0 4px 12px <?php echo $stC[$st] ?? '#e67e22'; ?>33; }
        .summary-total .val.final { color: #fff; font-size: 11pt; background: linear-gradient(135deg, <?php echo $pc; ?>, <?php echo $pc; ?>dd); padding: 6px 16px; border-radius: 8px; box-shadow: 0 4px 12px <?php echo $pc; ?>33; }

        /* Notes & Terms */
        .notes-section { padding: 0 30px 16px; display: flex; gap: 10px; flex-wrap: wrap; }
        .note-card { flex: 1; min-width: 200px; background: #f8f9fa; border-radius: 8px; padding: 12px 14px; border-right: 4px solid <?php echo $pc; ?>; }
        .note-card .note-title { font-weight: 800; color: <?php echo $pc; ?>; margin-bottom: 6px; font-size: 7.5pt; display: flex; align-items: center; gap: 4px; }
        .note-card .note-body { font-size: 7.5pt; color: #555; line-height: 1.5; }
        .terms-card { flex: 1; min-width: 200px; background: #f8f9fa; border-radius: 8px; padding: 12px 14px; border-right: 4px solid #2d3436; }
        .terms-card .terms-title { font-weight: 800; color: #2d3436; margin-bottom: 6px; font-size: 7.5pt; display: flex; align-items: center; gap: 4px; }
        .terms-card .terms-body { font-size: 7.5pt; color: #555; line-height: 1.5; }

        /* Signature */
        .signature-section { padding: 16px 30px; display: flex; justify-content: space-between; gap: 40px; }
        .sig-box { flex: 1; text-align: center; padding-top: 30px; border-top: 2px solid <?php echo $pc; ?>33; position: relative; }
        .sig-box::before { content: ''; position: absolute; top: -2px; right: 50%; transform: translateX(50%); width: 40px; height: 2px; background: <?php echo $pc; ?>; }
        .sig-box .sig-label { font-size: 7.5pt; color: #999; font-weight: 600; }
        .sig-box .sig-img { max-height: 50px; margin-bottom: 4px; }

        /* Footer */
        .invoice-footer { background: linear-gradient(135deg, #f8f9fa, #fff); padding: 14px 30px; text-align: center; border-top: 2px solid <?php echo $pc; ?>22; }
        .invoice-footer p { font-size: 7.5pt; color: #636e72; margin-bottom: 4px; font-weight: 600; }
        .invoice-footer .company-line { font-size: 7pt; color: #b2bec3; }

        /* Decorative Elements */
        .accent-line { height: 4px; background: linear-gradient(90deg, <?php echo $pc; ?>, <?php echo $sc; ?>, <?php echo $pc; ?>); }
    </style>
</head>
<body>
    <div class="no-print action-bar">
        <button class="btn-print" onclick="window.print()"><i class="bi bi-printer me-2"></i>چاپ فاکتور</button>
        <button class="btn-close" onclick="window.close()"><i class="bi bi-x-lg me-2"></i>بستن</button>
    </div>

    <div class="page" data-wm="<?php echo htmlspecialchars($wmText); ?>">
        <!-- Accent Line -->
        <div class="accent-line"></div>

        <!-- Header -->
        <div class="header">
            <div class="header-right">
                <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" class="logo" alt="لوگو"><?php endif; ?>
                <h1 class="title"><?php echo htmlspecialchars($invSet['invoice_title'] ?? 'فاکتور رزرو هتل'); ?></h1>
                <div class="inv-meta">
                    <span>شماره: <strong><?php echo $invoice->invoice_number ?? '#' . $invoice->id; ?></strong></span>
                    <span>تاریخ: <strong><?php echo \Core\JDate::displayDateTime($invoice->created_at); ?></strong></span>
                </div>
            </div>
            <div class="header-left">
                <div class="company"><?php echo htmlspecialchars($company); ?></div>
                <div class="subtitle"><?php echo htmlspecialchars($sub); ?></div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <span class="status-pill active"><?php echo $stL[$st] ?? $st; ?></span>
            <?php if ($invoice->invoice_type): ?>
            <span class="status-pill type"><?php echo $invoice->invoice_type == 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور'; ?></span>
            <?php endif; ?>
            <span class="creator-info">صادر شده توسط: <strong><?php echo htmlspecialchars($invoice->creator_name ?? '-'); ?></strong></span>
        </div>

        <!-- Info Cards -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-card">
                    <div class="ic-icon"><i class="bi bi-building"></i></div>
                    <div class="label">نام هتل</div>
                    <div class="value"><?php echo htmlspecialchars($invoice->hotel_name); ?></div>
                </div>
                <div class="info-card">
                    <div class="ic-icon"><i class="bi bi-briefcase"></i></div>
                    <div class="label">نام آژانس</div>
                    <div class="value"><?php echo htmlspecialchars($invoice->agency_name ?? '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="ic-icon"><i class="bi bi-person"></i></div>
                    <div class="label">نام میهمان</div>
                    <div class="value"><?php echo htmlspecialchars($invoice->guest_name ?? '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="ic-icon"><i class="bi bi-telephone"></i></div>
                    <div class="label">تلفن</div>
                    <div class="value" dir="ltr" style="text-align:left;"><?php echo htmlspecialchars($invoice->guest_phone ?? $invoice->contact_phone ?? '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="ic-icon"><i class="bi bi-moon-stars"></i></div>
                    <div class="label">تعداد شب‌ها</div>
                    <div class="value accent"><?php echo $invoice->nights; ?> شب</div>
                </div>
                <div class="info-card">
                    <div class="ic-icon"><i class="bi bi-calendar-check"></i></div>
                    <div class="label">تاریخ ورود</div>
                    <div class="value"><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></div>
                </div>
                <div class="info-card">
                    <div class="ic-icon"><i class="bi bi-calendar-x"></i></div>
                    <div class="label">تاریخ خروج</div>
                    <div class="value"><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></div>
                </div>
                <?php if ($invoice->valid_until): ?>
                <div class="info-card">
                    <div class="ic-icon"><i class="bi bi-clock-history"></i></div>
                    <div class="label">تاریخ اعتبار</div>
                    <div class="value"><?php echo \Core\JDate::displayDate($invoice->valid_until); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Section -->
        <div class="section-divider">
            <div class="icon"><i class="bi bi-list-ol"></i></div>
            <div class="text">اقلام فاکتور</div>
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
                        <td class="num" style="color:#b2bec3;"><?php echo $i + 1; ?></td>
                        <td>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($item->description); ?></div>
                            <?php if (!empty($item->room_type)): ?><span class="item-tag"><i class="bi bi-door-open" style="font-size:8px;"></i><?php echo htmlspecialchars($item->room_type); ?></span><?php endif; ?>
                            <?php if (!empty($item->category) && $item->category !== 'general'): ?>
                            <span class="item-tag"><?php echo $item->category === 'hotel' ? 'هتل' : ($item->category === 'transfer' ? 'ترانسفر' : $item->category); ?></span>
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
        <div class="section-divider">
            <div class="icon"><i class="bi bi-calculator"></i></div>
            <div class="text">خلاصه مالی</div>
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
                    <span class="lbl"><i class="bi bi-tag"></i> تخفیف تغییر قیمت</span>
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
                    <span class="lbl"><i class="bi bi-wallet2"></i> بیعانه پرداخت شده</span>
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
        <?php if (!empty($invoice->ps_note) || $terms || $invoice->notes): ?>
        <div class="notes-section">
            <?php if (!empty($invoice->ps_note)): ?>
            <div class="note-card">
                <div class="note-title"><i class="bi bi-pencil-square"></i> پینوشت</div>
                <div class="note-body"><?php echo nl2br(htmlspecialchars($invoice->ps_note)); ?></div>
            </div>
            <?php endif; ?>

            <?php if ($invoice->notes): ?>
            <div class="note-card">
                <div class="note-title"><i class="bi bi-journal-text"></i> توضیحات</div>
                <div class="note-body"><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></div>
            </div>
            <?php endif; ?>
            <?php if ($terms): ?>
            <div class="terms-card">
                <div class="terms-title"><i class="bi bi-shield-check"></i> شرایط پرداخت</div>
                <div class="terms-body"><?php echo nl2br(htmlspecialchars($terms)); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Signature -->
        <div class="signature-section">
            <div class="sig-box">
                <?php if (!empty($invSet['invoice_signature_url'])): ?>
                <img src="<?php echo htmlspecialchars($invSet['invoice_signature_url']); ?>" alt="امضا" class="sig-img"><br>
                <?php endif; ?>
                <div class="sig-label">امضای صادرکننده</div>
            </div>
            <div class="sig-box">
                <div class="sig-label">امضای تاییدکننده</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <?php if ($footer): ?><p><?php echo nl2br(htmlspecialchars($footer)); ?></p><?php endif; ?>
            <div class="company-line"><?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?></div>
        </div>
    </div>
</body>
</html>