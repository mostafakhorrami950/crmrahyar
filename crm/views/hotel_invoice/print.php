<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاکتور <?php echo htmlspecialchars($invoice->invoice_number ?? '#' . $invoice->id); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <?php
    $invSet = $invoiceSettings ?? [];
    $pc = $invSet['invoice_primary_color'] ?? '#1a1a2e';
    $sc = $invSet['invoice_success_color'] ?? '#16213e';
    $company = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $sub = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logo = $invSet['invoice_logo_url'] ?? '';
    $footer = $invoice->footer_text ?? $invSet['invoice_footer_text'] ?? '';
    $terms = $invoice->payment_terms ?? $invSet['invoice_terms'] ?? '';

    $stL = ['pending'=>'مانده دارد','settled'=>'تسویه شده','prepaid'=>'پیش فاکتور','paid'=>'پرداخت شده'];
    $stC = ['pending'=>'#d97706','settled'=>'#059669','prepaid'=>'#2563eb','paid'=>'#059669'];
    $st = $invoice->invoice_status;

    $itemsDiscount = 0;
    foreach ($items as $itm) {
        $defP = $itm->default_price ?? $itm->unit_price;
        if ($itm->unit_price < $defP) {
            $diff = $defP - $itm->unit_price;
            $itemsDiscount += ($itm->category === 'hotel' && $invoice->nights > 0) ? $diff * $itm->quantity * $invoice->nights : $diff * $itm->quantity;
        }
    }
    $isPending = $invoice->invoice_status === 'pending' && ($invoice->deposit_amount ?? 0) > 0;

    $wmText = '';
    if ($invoice->invoice_status === 'pending') $wmText = 'مانده دارد';
    elseif ($invoice->invoice_status === 'settled') $wmText = 'تسویه شده';
    elseif ($invoice->invoice_status === 'paid') $wmText = 'پرداخت شده';
    elseif ($invoice->invoice_status === 'prepaid') $wmText = 'پیش فاکتور';
    ?>
    <style>
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
            .no-print { display: none !important; }
            .page { box-shadow: none !important; margin: 0 !important; padding: 12px !important; max-width: 100% !important; border-radius: 0 !important; }
            @page { margin: 4mm; size: A5 portrait; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Vazirmatn, Tahoma, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            font-size: 7pt;
            line-height: 1.3;
            min-height: 100vh;
            padding: 10px;
        }
        .page {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border-radius: 4px;
            padding: 14px 18px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .page::after {
            content: '<?php echo $wmText; ?>';
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%,-50%) rotate(-35deg);
            font-size: 44px; font-weight: 900;
            color: rgba(0,0,0,0.04);
            white-space: nowrap; pointer-events: none; z-index: 10;
            letter-spacing: 6px;
            border: 6px solid rgba(0,0,0,0.03);
            padding: 8px 36px;
        }

        /* Action Bar */
        .action-bar {
            background: #1e293b;
            padding: 8px 16px;
            display: flex; justify-content: center; gap: 12px;
            margin-bottom: 10px; border-radius: 4px;
        }
        .action-bar button {
            padding: 6px 28px; border: 1.5px solid rgba(255,255,255,0.15);
            border-radius: 4px; font-family: inherit; font-weight: 700;
            font-size: 11px; cursor: pointer; transition: all 0.2s;
        }
        .btn-print { background: #fff; color: #1e293b; }
        .btn-close { background: transparent; color: #fff; }

        /* Top Line */
        .top-line { height: 3px; background: #1e293b; margin-bottom: 10px; }

        /* Header */
        .hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .hdr-right .logo { max-height: 22px; margin-bottom: 2px; }
        .hdr-right .title { font-size: 9pt; font-weight: 900; color: #1e293b; margin: 0; }
        .hdr-right .meta { font-size: 6.5pt; color: #64748b; margin-top: 2px; }
        .hdr-right .meta strong { color: #1e293b; font-weight: 800; }
        .hdr-left { text-align: left; }
        .hdr-left .co { font-size: 9pt; font-weight: 900; color: #1e293b; }
        .hdr-left .sub { font-size: 6pt; color: #64748b; margin-top: 1px; }

        /* Info Bar */
        .info-bar {
            display: flex; gap: 4px; flex-wrap: wrap; align-items: center;
            margin-bottom: 8px; padding: 4px 0;
            border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;
        }
        .chip {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 7px; border-radius: 3px;
            font-size: 6pt; font-weight: 600;
            background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;
        }
        .chip b { color: #1e293b; font-weight: 800; }
        .chip.status { background: <?php echo $stC[$st] ?? '#64748b'; ?>; color: #fff; border: none; font-weight: 800; }
        .chip.type { background: #fff; border: 1.5px solid #1e293b; color: #1e293b; }
        .chip.nights { background: #f1f5f9; color: #1e293b; border-color: #cbd5e1; font-weight: 800; }
        .creator-chip { margin-right: auto; font-size: 6pt; color: #94a3b8; }

        /* Table */
        .tbl {
            width: 100%; border-collapse: collapse; font-size: 6.5pt;
            margin-bottom: 6px; border: 1px solid #e2e8f0;
        }
        .tbl th {
            background: #1e293b; color: #fff;
            padding: 4px 5px; font-weight: 700; font-size: 6pt; text-align: right;
        }
        .tbl td { padding: 3px 5px; border-bottom: 1px solid #f1f5f9; }
        .tbl .c { text-align: center; }
        .tbl .r { text-align: left; direction: ltr; font-family: 'Courier New', monospace; font-size: 6.5pt; }
        .tbl .total { font-weight: 900; color: #1e293b; }
        .tbl tbody tr:nth-child(even) { background: #f8fafc; }
        .tbl tbody tr:last-child td { border-bottom: 2px solid #1e293b; }
        .half-badge {
            display: inline-flex; align-items: center; gap: 2px;
            background: #1e293b; color: #fff;
            font-size: 5.5pt; padding: 1px 5px; border-radius: 3px; font-weight: 800;
        }

        /* Summary + Notes */
        .bottom-section { display: flex; gap: 10px; align-items: flex-start; margin-top: 2px; }
        .sum-box {
            min-width: 190px; margin-right: auto;
            background: #f8fafc; border-radius: 4px; padding: 6px 10px;
            border: 1px solid #e2e8f0; position: relative;
        }
        .sum-box::before {
            content: ''; position: absolute; top: 0; right: 0;
            width: 3px; height: 100%; background: #1e293b;
            border-radius: 0 4px 4px 0;
        }
        .sum-row { display: flex; justify-content: space-between; padding: 1.5px 0; font-size: 6.5pt; }
        .sum-row .l { color: #64748b; }
        .sum-row .v { font-weight: 700; font-family: 'Courier New', monospace; direction: ltr; }
        .sum-row .v.red { color: #dc2626; font-weight: 800; }
        .sum-hr { border: none; border-top: 1px dashed #cbd5e1; margin: 3px 0; }
        .sum-final { display: flex; justify-content: space-between; align-items: center; padding: 4px 0 0; font-size: 7.5pt; font-weight: 900; }
        .sum-final .badge-final {
            color: #fff; font-size: 6.5pt; padding: 3px 10px;
            border-radius: 3px; font-weight: 900;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Notes */
        .notes-area { flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .note-card {
            background: #fff; border-radius: 3px; padding: 4px 7px;
            border: 1px solid #e2e8f0; border-right: 3px solid #1e293b;
            font-size: 6pt; color: #475569; line-height: 1.5;
        }
        .note-card .nt { font-weight: 800; color: #1e293b; font-size: 6pt; margin-bottom: 1px; }
        .note-card.dark { border-right-color: #64748b; }
        .note-card.dark .nt { color: #64748b; }

        /* Footer */
        .ftr {
            text-align: center; font-size: 5.5pt; color: #94a3b8;
            padding-top: 5px; border-top: 1px solid #e2e8f0; margin-top: 6px;
        }
    </style>
</head>
<body>
    <div class="no-print action-bar">
        <button class="btn-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>چاپ فاکتور</button>
        <button class="btn-close" onclick="window.close()"><i class="bi bi-x-lg me-1"></i>بستن</button>
    </div>

    <div class="page">
        <!-- Top Line -->
        <div class="top-line"></div>

        <!-- Header -->
        <div class="hdr">
            <div class="hdr-right">
                <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" class="logo" alt="لوگو"><?php endif; ?>
                <div class="title"><?php echo htmlspecialchars($invSet['invoice_title'] ?? 'فاکتور رزرو هتل'); ?></div>
                <div class="meta">شماره: <strong><?php echo $invoice->invoice_number ?? '#' . $invoice->id; ?></strong> &bull; تاریخ: <strong><?php echo \Core\JDate::displayDateTime($invoice->created_at); ?></strong></div>
            </div>
            <div class="hdr-left">
                <div class="co"><?php echo htmlspecialchars($company); ?></div>
                <div class="sub"><?php echo htmlspecialchars($sub); ?></div>
            </div>
        </div>

        <!-- Info Bar -->
        <div class="info-bar">
            <span class="chip status"><i class="bi bi-circle-fill" style="font-size:4px;"></i> <?php echo $stL[$st] ?? $st; ?></span>
            <?php if ($invoice->invoice_type): ?><span class="chip type"><?php echo $invoice->invoice_type == 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور'; ?></span><?php endif; ?>
            <span class="chip"><i class="bi bi-building"></i> هتل : <b><?php echo htmlspecialchars($invoice->hotel_name); ?></b></span>
            <span class="chip"><i class="bi bi-briefcase"></i> آژانس محترم : <?php echo htmlspecialchars($invoice->agency_name ?? '-'); ?></span>
            <span class="chip"><i class="bi bi-person"></i> نام میهمان : <?php echo htmlspecialchars($invoice->guest_name ?? '-'); ?></span>
            <span class="chip"><i class="bi bi-telephone"></i> <b dir="ltr">شماره تماس میهمان : <?php echo htmlspecialchars($invoice->guest_phone ?? $invoice->contact_phone ?? '-'); ?></b></span>
            <span class="chip nights"><i class="bi bi-moon-stars"></i> تعداد شب اقامت :<?php echo $invoice->nights; ?> شب</span>
            <span class="chip"><i class="bi bi-calendar-check"></i> ورود: <b><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></b></span>
            <span class="chip"><i class="bi bi-calendar-x"></i> خروج: <b><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></b></span>
            <?php if ($invoice->valid_until): ?><span class="chip"><i class="bi bi-clock-history"></i> اعتبار: <b><?php echo \Core\JDate::displayDate($invoice->valid_until); ?></b></span><?php endif; ?>
            <span class="creator-chip"><i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($invoice->creator_name ?? '-'); ?></span>
        </div>

        <!-- Items Table -->
        <table class="tbl">
            <thead>
                <tr>
                    <th style="width:4%" class="c">ردیف</th>
                    <th style="width:26%">شرح</th>
                    <th style="width:10%" class="c">نوع اتاق</th>
                    <th style="width:7%" class="c">تعداد</th>
                    <th style="width:9%" class="c">نیم بها</th>
                    <th style="width:13%" class="c">قیمت واحد</th>
                    <th style="width:6%" class="c">شب</th>
                    <th style="width:15%" class="c">مبلغ کل</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td class="c" style="color:#94a3b8;font-weight:700;"><?php echo $i + 1; ?></td>
                    <td style="font-weight:700;"><?php echo htmlspecialchars($item->description); ?></td>
                    <td class="c"><?php echo !empty($item->room_type) ? htmlspecialchars($item->room_type) : '-'; ?></td>
                    <td class="c" style="font-weight:700;"><?php echo number_format((int)$item->quantity); ?></td>
                    <td class="c"><?php echo !empty($item->half_price_qty) && $item->half_price_qty > 0 ? '<span class="half-badge">' . $item->half_price_qty . '</span>' : '-'; ?></td>
                    <td class="r"><?php echo number_format($item->unit_price); ?></td>
                    <td class="c"><?php echo $invoice->nights; ?></td>
                    <td class="r total"><?php echo number_format($item->total_price); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Bottom Section -->
        <div class="bottom-section">
            <!-- Summary -->
            <div class="sum-box">
                <?php if ($itemsDiscount > 0): ?>
                <div class="sum-row"><span class="l">جمع (قیمت اصلی)</span><span class="v"><?php echo number_format(($invoice->subtotal ?? 0) + $itemsDiscount); ?></span></div>
                <div class="sum-row"><span class="l"><i class="bi bi-tag"></i> تخفیف قیمت</span><span class="v red">- <?php echo number_format($itemsDiscount); ?></span></div>
                <?php endif; ?>
                <div class="sum-row"><span class="l">جمع کل</span><span class="v"><?php echo number_format($invoice->subtotal ?? $invoice->total_amount ?? 0); ?></span></div>
                <?php if (($invoice->tax_percent ?? 0) > 0): ?><div class="sum-row"><span class="l">مالیات (<?php echo $invoice->tax_percent; ?>%)</span><span class="v"><?php echo number_format($invoice->tax_amount ?? 0); ?></span></div><?php endif; ?>
                <?php if (($invoice->discount_amount ?? 0) > 0): ?><div class="sum-row"><span class="l">تخفیف</span><span class="v red">- <?php echo number_format($invoice->discount_amount); ?></span></div><?php endif; ?>
                <?php if ($isPending): ?>
                <hr class="sum-hr">
                <div class="sum-row"><span class="l"><i class="bi bi-wallet2"></i> بیعانه</span><span class="v red">- <?php echo number_format($invoice->deposit_amount); ?></span></div>
                <div class="sum-final"><span>باقیمانده:</span><span class="badge-final" style="background:<?php echo $stC[$st] ?? '#d97706'; ?>;"><?php echo number_format($invoice->final_amount - $invoice->deposit_amount); ?> تومان</span></div>
                <?php else: ?>
                <hr class="sum-hr">
                <div class="sum-final"><span>مبلغ نهایی:</span><span class="badge-final" style="background:#1e293b;"><?php echo number_format($invoice->final_amount); ?> تومان</span></div>
                <?php endif; ?>
            </div>

            <!-- Notes -->
            <?php if (!empty($invoice->ps_note) || !empty($invoice->notes) || !empty($terms)): ?>
            <div class="notes-area">
                <?php if (!empty($invoice->ps_note)): ?>
                <div class="note-card"><div class="nt"><i class="bi bi-pencil-square"></i> پینوشت</div><?php echo nl2br(htmlspecialchars($invoice->ps_note)); ?></div>
                <?php endif; ?>
                <?php if (!empty($invoice->notes)): ?>
                <div class="note-card"><div class="nt"><i class="bi bi-journal-text"></i> توضیحات</div><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></div>
                <?php endif; ?>
                <?php if (!empty($terms)): ?>
                <div class="note-card dark"><div class="nt"><i class="bi bi-shield-check"></i> شرایط پرداخت</div><?php echo nl2br(htmlspecialchars($terms)); ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="ftr">
            <?php if ($footer): ?><?php echo htmlspecialchars($footer); ?> &bull; <?php endif; ?>
            <?php echo htmlspecialchars($company); ?> &bull; <?php echo htmlspecialchars($sub); ?>
        </div>
    </div>
</body>
</html>