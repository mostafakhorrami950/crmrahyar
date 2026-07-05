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

    $stL = ['pending'=>'مانده دارد','settled'=>'تسویه شده','prepaid'=>'پرداخت نشده','paid'=>'پرداخت شده'];
    $stC = ['pending'=>'#e67e22','settled'=>'#27ae60','prepaid'=>'#3498db','paid'=>'#27ae60'];
    $st = $invoice->invoice_status;

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
            @page { margin: 6mm; size: A4; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Vazirmatn, Tahoma, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #2d3436; font-size: 9pt; line-height: 1.5; min-height: 100vh; padding: 20px 0; }

        .page { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 0; box-shadow: 0 20px 80px rgba(0,0,0,0.3); overflow: hidden; position: relative; }

        /* Watermark */
        .page::after { content: attr(data-wm); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-40deg); font-size: 90px; font-weight: 900; color: rgba(0,0,0,0.025); white-space: nowrap; pointer-events: none; z-index: 10; letter-spacing: 15px; }

        /* Top Accent Bar */
        .top-bar { height: 6px; background: linear-gradient(90deg, <?php echo $pc; ?>, <?php echo $sc; ?>, <?php echo $pc; ?>); }

        /* Action Bar */
        .action-bar { background: linear-gradient(135deg, #2d3436, #636e72); padding: 12px 24px; display: flex; justify-content: center; gap: 12px; }
        .action-bar button { padding: 8px 32px; border: 2px solid rgba(255,255,255,0.2); border-radius: 8px; font-family: inherit; font-weight: 700; font-size: 12px; cursor: pointer; transition: all 0.3s; letter-spacing: 0.5px; }
        .btn-print { background: #fff; color: #2d3436; }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .btn-close { background: transparent; color: #fff; }
        .btn-close:hover { background: rgba(255,255,255,0.1); }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: center; padding: 28px 36px 24px; background: linear-gradient(135deg, #fafbfc, #fff); }
        .header-right { text-align: right; }
        .header-right .logo { max-height: 45px; margin-bottom: 8px; }
        .header-right .title { font-size: 20pt; font-weight: 900; color: #1a1a2e; margin: 0; letter-spacing: -1px; }
        .header-right .inv-meta { font-size: 8pt; color: #636e72; margin-top: 8px; display: flex; gap: 20px; align-items: center; }
        .header-right .inv-meta span { display: flex; align-items: center; gap: 4px; }
        .header-right .inv-meta strong { color: <?php echo $pc; ?>; font-weight: 800; }
        .header-left { text-align: left; }
        .header-left .company { font-size: 15pt; font-weight: 900; background: linear-gradient(135deg, <?php echo $pc; ?>, <?php echo $sc; ?>); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .header-left .subtitle { font-size: 9pt; color: #636e72; margin-top: 4px; }

        /* Status Bar */
        .status-bar { padding: 12px 36px; display: flex; gap: 10px; align-items: center; background: #f8f9fa; border-top: 1px solid #eee; border-bottom: 1px solid #eee; }
        .status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 5px 16px; border-radius: 20px; font-size: 8pt; font-weight: 700; }
        .status-pill.active { background: <?php echo $pc; ?>; color: #fff; box-shadow: 0 2px 8px <?php echo $pc; ?>44; }
        .status-pill.type { background: #fff; color: #555; border: 1.5px solid #ddd; }
        .creator-info { margin-right: auto; font-size: 7.5pt; color: #999; }

        /* Info Cards */
        .info-section { padding: 20px 36px; }
        .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .info-card { background: #fff; border-radius: 10px; padding: 14px 16px; border: 1px solid #eef0f2; position: relative; overflow: hidden; }
        .info-card::before { content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 100%; background: <?php echo $pc; ?>; border-radius: 0 10px 10px 0; opacity: 0.5; }
        .info-card .ic-icon { width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, <?php echo $pc; ?>18, <?php echo $pc; ?>08); display: flex; align-items: center; justify-content: center; margin-bottom: 8px; color: <?php echo $pc; ?>; font-size: 14px; }
        .info-card .label { font-size: 7pt; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 600; }
        .info-card .value { font-size: 9pt; font-weight: 700; color: #1a1a2e; line-height: 1.3; }
        .info-card .value.accent { color: <?php echo $pc; ?>; font-size: 11pt; }

        /* Section Divider */
        .section-divider { padding: 0 36px; margin: 16px 0 10px; display: flex; align-items: center; gap: 12px; }
        .section-divider .icon { width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, <?php echo $pc; ?>, <?php echo $pc; ?>cc); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; box-shadow: 0 3px 10px <?php echo $pc; ?>33; }
        .section-divider .text { font-size: 10pt; font-weight: 800; color: #1a1a2e; white-space: nowrap; }
        .section-divider .line { flex: 1; height: 2px; background: linear-gradient(90deg, <?php echo $pc; ?>33, transparent); }

        /* Items Table */
        .items-section { padding: 0 36px; }
        .items-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 8pt; border-radius: 10px; overflow: hidden; border: 1px solid #e8ecef; }
        .items-table thead th { background: linear-gradient(135deg, #1a1a2e, #2d3436); color: #fff; padding: 12px 14px; font-weight: 700; font-size: 8pt; text-align: right; letter-spacing: 0.3px; }
        .items-table tbody td { padding: 12px 14px; border-bottom: 1px solid #f0f2f5; line-height: 1.5; vertical-align: middle; }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table tbody tr:hover { background: <?php echo $pc; ?>06; }
        .items-table .num { text-align: center; font-weight: 700; }
        .items-table .price { text-align: left; direction: ltr; font-family: 'Courier New', monospace; font-size: 8pt; }
        .items-table .total { text-align: left; direction: ltr; font-weight: 900; font-family: 'Courier New', monospace; font-size: 9pt; color: <?php echo $pc; ?>; }
        .item-tag { display: inline-flex; align-items: center; gap: 4px; background: <?php echo $pc; ?>12; color: <?php echo $pc; ?>; font-size: 8pt; padding: 3px 10px; border-radius: 12px; margin-top: 4px; font-weight: 700; }
        .item-tag i { font-size: 10px; }
        .half-price-tag { display: inline-flex; align-items: center; gap: 4px; background: #e67e22; color: #fff; font-size: 7.5pt; padding: 3px 10px; border-radius: 12px; margin-top: 4px; font-weight: 700; margin-right: 4px; }
        .half-price-tag i { font-size: 10px; }
        .room-type-badge { display: inline-flex; align-items: center; gap: 5px; background: linear-gradient(135deg, #6c5ce7, #a29bfe); color: #fff; font-size: 9pt; padding: 4px 14px; border-radius: 15px; margin-top: 5px; font-weight: 800; letter-spacing: 0.3px; box-shadow: 0 2px 8px #6c5ce733; }
        .room-type-badge i { font-size: 12px; }

        /* Financial Summary */
        .summary-section { padding: 0 36px 20px; }
        .summary-box { max-width: 320px; margin-right: auto; background: #fff; border-radius: 12px; padding: 20px 24px; border: 2px solid <?php echo $pc; ?>22; position: relative; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .summary-box::before { content: ''; position: absolute; top: 0; right: 0; width: 5px; height: 100%; background: linear-gradient(180deg, <?php echo $pc; ?>, <?php echo $sc; ?>); border-radius: 0 12px 12px 0; }
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 8.5pt; }
        .summary-row .lbl { color: #636e72; display: flex; align-items: center; gap: 5px; }
        .summary-row .val { font-weight: 700; font-family: 'Courier New', monospace; direction: ltr; font-size: 8.5pt; }
        .summary-row .val.red { color: #e74c3c; font-weight: 800; }
        .summary-divider { border: none; border-top: 2px dashed #e0e0e0; margin: 10px 0; }
        .summary-total { display: flex; justify-content: space-between; align-items: center; padding: 10px 0 0; font-size: 11pt; font-weight: 900; }
        .summary-total .lbl { color: #1a1a2e; }
        .summary-total .val { font-family: 'Courier New', monospace; direction: ltr; }
        .summary-total .val.remaining { color: #fff; font-size: 12pt; background: linear-gradient(135deg, <?php echo $stC[$st] ?? '#e67e22'; ?>, <?php echo $stC[$st] ?? '#e67e22'; ?>cc); padding: 8px 20px; border-radius: 10px; box-shadow: 0 4px 15px <?php echo $stC[$st] ?? '#e67e22'; ?>44; }
        .summary-total .val.final { color: #fff; font-size: 12pt; background: linear-gradient(135deg, <?php echo $pc; ?>, <?php echo $sc; ?>); padding: 8px 20px; border-radius: 10px; box-shadow: 0 4px 15px <?php echo $pc; ?>44; }

        /* Notes & Terms */
        .notes-section { padding: 0 36px 20px; display: flex; gap: 12px; flex-wrap: wrap; }
        .note-card { flex: 1; min-width: 200px; background: #fff; border-radius: 10px; padding: 14px 16px; border: 1px solid #eef0f2; border-right: 4px solid <?php echo $pc; ?>; }
        .note-card .note-title { font-weight: 800; color: <?php echo $pc; ?>; margin-bottom: 8px; font-size: 8pt; display: flex; align-items: center; gap: 5px; }
        .note-card .note-body { font-size: 8pt; color: #555; line-height: 1.6; }
        .terms-card { flex: 1; min-width: 200px; background: #fff; border-radius: 10px; padding: 14px 16px; border: 1px solid #eef0f2; border-right: 4px solid #1a1a2e; }
        .terms-card .terms-title { font-weight: 800; color: #1a1a2e; margin-bottom: 8px; font-size: 8pt; display: flex; align-items: center; gap: 5px; }
        .terms-card .terms-body { font-size: 8pt; color: #555; line-height: 1.6; }

        /* Signature */
        .signature-section { padding: 20px 36px; display: flex; justify-content: space-between; gap: 40px; }
        .sig-box { flex: 1; text-align: center; padding-top: 35px; border-top: 2px solid #eef0f2; position: relative; }
        .sig-box::before { content: ''; position: absolute; top: -2px; right: 50%; transform: translateX(50%); width: 50px; height: 3px; background: linear-gradient(90deg, <?php echo $pc; ?>, <?php echo $sc; ?>); border-radius: 2px; }
        .sig-box .sig-label { font-size: 8pt; color: #999; font-weight: 600; }
        .sig-box .sig-img { max-height: 55px; margin-bottom: 6px; }

        /* Footer */
        .invoice-footer { background: linear-gradient(135deg, #f8f9fa, #fff); padding: 18px 36px; text-align: center; border-top: 2px solid #eef0f2; }
        .invoice-footer p { font-size: 8pt; color: #636e72; margin-bottom: 6px; font-weight: 600; }
        .invoice-footer .company-line { font-size: 7.5pt; color: #b2bec3; }
        .invoice-footer .powered { font-size: 6.5pt; color: #ddd; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="no-print action-bar">
        <button class="btn-print" onclick="window.print()"><i class="bi bi-printer me-2"></i>چاپ فاکتور</button>
        <button class="btn-close" onclick="window.close()"><i class="bi bi-x-lg me-2"></i>بستن</button>
    </div>

    <div class="page" data-wm="<?php echo htmlspecialchars($wmText); ?>">
        <!-- Top Accent Bar -->
        <div class="top-bar"></div>

        <!-- Header -->
        <div class="header">
            <div class="header-right">
                <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" class="logo" alt="لوگو"><?php endif; ?>
                <h1 class="title"><?php echo htmlspecialchars($invSet['invoice_title'] ?? 'فاکتور رزرو هتل'); ?></h1>
                <div class="inv-meta">
                    <span><i class="bi bi-hash"></i> شماره: <strong><?php echo $invoice->invoice_number ?? '#' . $invoice->id; ?></strong></span>
                    <span><i class="bi bi-calendar3"></i> تاریخ: <strong><?php echo \Core\JDate::displayDateTime($invoice->created_at); ?></strong></span>
                </div>
            </div>
            <div class="header-left">
                <div class="company"><?php echo htmlspecialchars($company); ?></div>
                <div class="subtitle"><?php echo htmlspecialchars($sub); ?></div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <span class="status-pill active"><i class="bi bi-circle-fill" style="font-size:6px;"></i> <?php echo $stL[$st] ?? $st; ?></span>
            <?php if ($invoice->invoice_type): ?>
            <span class="status-pill type"><i class="bi bi-file-earmark-text"></i> <?php echo $invoice->invoice_type == 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور'; ?></span>
            <?php endif; ?>
            <span class="creator-info"><i class="bi bi-person-badge"></i> صادر شده توسط: <strong><?php echo htmlspecialchars($invoice->creator_name ?? '-'); ?></strong></span>
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
                        <td class="num" style="color:#b2bec3;font-size:10pt;"><?php echo $i + 1; ?></td>
                        <td>
                            <div style="font-weight:700;font-size:9pt;color:#1a1a2e;"><?php echo htmlspecialchars($item->description); ?></div>
                            <?php if (!empty($item->room_type)): ?>
                            <span class="room-type-badge"><i class="bi bi-door-open"></i> <?php echo htmlspecialchars($item->room_type); ?></span>
                            <?php endif; ?>
                            <div style="margin-top:4px;">
                                <?php if (!empty($item->category) && $item->category !== 'general'): ?>
                                <span class="item-tag"><i class="bi bi-tag"></i> <?php echo $item->category === 'hotel' ? 'هتل' : ($item->category === 'transfer' ? 'ترانسفر' : $item->category); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item->is_half_price)): ?>
                                <span class="half-price-tag"><i class="bi bi-percent"></i> نیم بها<?php if (!empty($item->half_price_rate) && $item->half_price_rate > 0): ?> (<?php echo number_format($item->half_price_rate); ?> ت)<?php endif; ?></span>
                                <?php endif; ?>
                            </div>
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