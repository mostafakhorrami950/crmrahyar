<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاکتور <?php echo htmlspecialchars($invoice->invoice_number ?? '#' . $invoice->id); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <?php
    $invSet = $invoiceSettings ?? [];
    $pc = $invSet['invoice_primary_color'] ?? '#1e3a5f';
    $sc = $invSet['invoice_success_color'] ?? '#166534';
    $company = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $sub = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logo = $invSet['invoice_logo_url'] ?? '';
    $footer = $invoice->footer_text ?? $invSet['invoice_footer_text'] ?? '';
    $terms = $invoice->payment_terms ?? $invSet['invoice_terms'] ?? '';

    $stL = ['pending'=>'مانده دارد','settled'=>'تسویه شده','prepaid'=>'پیش فاکتور','paid'=>'پرداخت شده'];
    $stC = ['pending'=>'#b45309','settled'=>'#15803d','prepaid'=>'#1d4ed8','paid'=>'#15803d'];
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
            .page { box-shadow: none !important; margin: 0 !important; padding: 8mm !important; max-width: 100% !important; border-radius: 0 !important; border: none !important; }
            @page { margin: 5mm; size: A5 portrait; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Vazirmatn, Tahoma, sans-serif;
            background: #e5e7eb;
            color: #1f2937;
            font-size: 7pt;
            line-height: 1.4;
            min-height: 100vh;
            padding: 12px;
        }
        .page {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            border-radius: 2px;
            padding: 10mm 12mm;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            border: 1px solid #d1d5db;
        }
        .page::after {
            content: '<?php echo $wmText; ?>';
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%,-50%) rotate(-30deg);
            font-size: 42px; font-weight: 900;
            color: rgba(0,0,0,0.03);
            white-space: nowrap; pointer-events: none; z-index: 10;
            letter-spacing: 4px;
        }

        /* Action Bar */
        .action-bar {
            background: #1f2937;
            padding: 8px 16px;
            display: flex; justify-content: center; gap: 12px;
            margin-bottom: 12px; border-radius: 2px;
        }
        .action-bar button {
            padding: 6px 28px; border: 1px solid rgba(255,255,255,0.2);
            border-radius: 2px; font-family: inherit; font-weight: 700;
            font-size: 11px; cursor: pointer; transition: all 0.2s;
        }
        .btn-print { background: #fff; color: #1f2937; }
        .btn-close { background: transparent; color: #fff; }

        /* Top border accent */
        .top-accent { height: 4px; background: <?php echo $pc; ?>; margin-bottom: 8px; }

        /* Header */
        .hdr {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 6px; padding-bottom: 6px;
            border-bottom: 2px solid <?php echo $pc; ?>;
        }
        .hdr-right .logo { max-height: 24px; margin-bottom: 3px; }
        .hdr-right .title {
            font-size: 11pt; font-weight: 900; color: <?php echo $pc; ?>;
            margin: 0; letter-spacing: -0.3px;
        }
        .hdr-right .meta { font-size: 7pt; color: #6b7280; margin-top: 2px; }
        .hdr-right .meta strong { color: #1f2937; font-weight: 800; }
        .hdr-left { text-align: left; }
        .hdr-left .co { font-size: 10pt; font-weight: 900; color: <?php echo $pc; ?>; }
        .hdr-left .sub { font-size: 7pt; color: #6b7280; margin-top: 1px; }

        /* Status + Meta bar */
        .meta-bar {
            display: flex; flex-wrap: wrap; gap: 3px; align-items: center;
            padding: 4px 0; margin-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        .chip {
            display: inline-flex; align-items: center; gap: 2px;
            padding: 2px 6px; border-radius: 2px;
            font-size: 6.5pt; font-weight: 600;
            background: #f9fafb; color: #374151; border: 1px solid #e5e7eb;
        }
        .chip b { color: #111827; font-weight: 800; }
        .chip.st-chip {
            background: <?php echo $stC[$st] ?? '#6b7280'; ?>;
            color: #fff; border: none; font-weight: 800;
            padding: 2px 8px;
        }
        .chip.type-chip {
            background: #fff; border: 1.5px solid <?php echo $pc; ?>;
            color: <?php echo $pc; ?>; font-weight: 700;
        }
        .chip.nights-chip {
            background: <?php echo $pc; ?>10; color: <?php echo $pc; ?>;
            border-color: <?php echo $pc; ?>30; font-weight: 800;
        }

        /* Table */
        .tbl {
            width: 100%; border-collapse: collapse; font-size: 7pt;
            margin-bottom: 6px;
        }
        .tbl th {
            background: <?php echo $pc; ?>; color: #fff;
            padding: 4px 5px; font-weight: 700; font-size: 6.5pt;
            text-align: center; border: 1px solid <?php echo $pc; ?>;
        }
        .tbl th:first-child { text-align: right; width: 28px; }
        .tbl td {
            padding: 3px 5px; border: 1px solid #e5e7eb;
            text-align: center;
        }
        .tbl td:first-child { text-align: right; color: #9ca3a8; font-weight: 700; font-size: 6.5pt; }
        .tbl .r { text-align: left; direction: ltr; font-family: 'Courier New', monospace; font-size: 7pt; }
        .tbl .total-cell { font-weight: 900; color: <?php echo $pc; ?>; }
        .tbl tbody tr:nth-child(even) { background: #f9fafb; }
        .tbl tbody tr:last-child td { border-bottom: 2px solid <?php echo $pc; ?>; }
        .half-badge {
            display: inline-flex; align-items: center; gap: 1px;
            background: <?php echo $pc; ?>; color: #fff;
            font-size: 5.5pt; padding: 1px 4px; border-radius: 2px; font-weight: 800;
        }

        /* Summary */
        .bottom { display: flex; gap: 8px; align-items: flex-start; margin-top: 4px; }
        .sum-box {
            min-width: 180px; margin-right: auto;
            background: #f9fafb; border-radius: 2px; padding: 5px 8px;
            border: 1px solid #e5e7eb;
            border-right: 3px solid <?php echo $pc; ?>;
        }
        .sum-row {
            display: flex; justify-content: space-between;
            padding: 1.5px 0; font-size: 7pt;
        }
        .sum-row .l { color: #6b7280; }
        .sum-row .v { font-weight: 700; font-family: 'Courier New', monospace; direction: ltr; }
        .sum-row .v.red { color: #dc2626; font-weight: 800; }
        .sum-hr { border: none; border-top: 1px dashed #d1d5db; margin: 2px 0; }
        .sum-final {
            display: flex; justify-content: space-between; align-items: center;
            padding: 3px 0 0; font-size: 8pt; font-weight: 900;
        }
        .sum-final .badge-final {
            color: #fff; font-size: 7pt; padding: 3px 10px;
            border-radius: 2px; font-weight: 900;
        }

        /* Notes */
        .notes-area { flex: 1; display: flex; flex-direction: column; gap: 3px; }
        .note-card {
            background: #fff; border-radius: 2px; padding: 3px 6px;
            border: 1px solid #e5e7eb; border-right: 3px solid <?php echo $pc; ?>;
            font-size: 6.5pt; color: #4b5563; line-height: 1.5;
        }
        .note-card .nt { font-weight: 800; color: <?php echo $pc; ?>; font-size: 6.5pt; margin-bottom: 1px; }
        .note-card.dark { border-right-color: #9ca3af; }
        .note-card.dark .nt { color: #6b7280; }

        /* Footer */
        .ftr {
            text-align: center; font-size: 6pt; color: #9ca3af;
            padding-top: 4px; border-top: 1px solid #e5e7eb; margin-top: 6px;
        }
    </style>
</head>
<body>
    <div class="no-print action-bar">
        <button class="btn-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>چاپ فاکتور</button>
        <button class="btn-close" onclick="window.close()"><i class="bi bi-x-lg me-1"></i>بستن</button>
    </div>

    <div class="page">
        <!-- Top Accent -->
        <div class="top-accent"></div>

        <!-- Header -->
        <div class="hdr">
            <div class="hdr-right">
                <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" class="logo" alt="لوگو"><?php endif; ?>
                <div class="title"><?php echo htmlspecialchars($invSet['invoice_title'] ?? 'فاکتور رزرو هتل'); ?></div>
                <div class="meta">شماره: <strong><?php echo $invoice->invoice_number ?? '#' . $invoice->id; ?></strong> | تاریخ: <strong><?php echo \Core\JDate::displayDateTime($invoice->created_at); ?></strong></div>
            </div>
            <div class="hdr-left">
                <div class="co"><?php echo htmlspecialchars($company); ?></div>
                <div class="sub"><?php echo htmlspecialchars($sub); ?></div>
            </div>
        </div>

        <!-- Meta Bar -->
        <div class="meta-bar">
            <span class="chip st-chip"><i class="bi bi-circle-fill" style="font-size:3px;"></i> <?php echo $stL[$st] ?? $st; ?></span>
            <?php if ($invoice->invoice_type): ?><span class="chip type-chip"><?php echo $invoice->invoice_type == 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور'; ?></span><?php endif; ?>
            <span class="chip"><i class="bi bi-building"></i> هتل: <b><?php echo htmlspecialchars($invoice->hotel_name); ?></b></span>
            <span class="chip"><i class="bi bi-briefcase"></i> آژانس: <?php echo htmlspecialchars($invoice->agency_name ?? '-'); ?></span>
            <span class="chip"><i class="bi bi-person"></i> میهمان: <?php echo htmlspecialchars($invoice->guest_name ?? '-'); ?></span>
            <span class="chip"><i class="bi bi-telephone"></i> <b dir="ltr"><?php echo htmlspecialchars($invoice->guest_phone ?? $invoice->contact_phone ?? '-'); ?></b></span>
            <span class="chip nights-chip"><i class="bi bi-moon-stars"></i> <?php echo $invoice->nights; ?> شب</span>
            <span class="chip"><i class="bi bi-calendar-check"></i> ورود: <b><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></b></span>
            <span class="chip"><i class="bi bi-calendar-x"></i> خروج: <b><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></b></span>
            <?php if ($invoice->valid_until): ?><span class="chip"><i class="bi bi-clock-history"></i> اعتبار: <b><?php echo \Core\JDate::displayDate($invoice->valid_until); ?></b></span><?php endif; ?>
        </div>

        <!-- Items Table -->
        <table class="tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="width:24%">شرح</th>
                    <th style="width:10%">نوع اتاق</th>
                    <th style="width:7%">تعداد</th>
                    <th style="width:8%">نیم بها</th>
                    <th style="width:12%">قیمت واحد</th>
                    <th style="width:6%">شب</th>
                    <th style="width:14%">مبلغ کل</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td style="text-align:right;font-weight:700;"><?php echo htmlspecialchars($item->description); ?></td>
                    <td><?php echo !empty($item->room_type) ? htmlspecialchars($item->room_type) : '-'; ?></td>
                    <td style="font-weight:700;"><?php echo number_format((int)$item->quantity); ?></td>
                    <td><?php echo !empty($item->half_price_qty) && $item->half_price_qty > 0 ? '<span class="half-badge">' . $item->half_price_qty . '</span>' : '-'; ?></td>
                    <td class="r"><?php echo number_format($item->unit_price); ?></td>
                    <td><?php echo $invoice->nights; ?></td>
                    <td class="r total-cell"><?php echo number_format($item->total_price); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Bottom Section -->
        <div class="bottom">
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
                <div class="sum-final"><span>باقیمانده:</span><span class="badge-final" style="background:<?php echo $stC[$st] ?? '#b45309'; ?>;"><?php echo number_format($invoice->final_amount - $invoice->deposit_amount); ?> تومان</span></div>
                <?php else: ?>
                <hr class="sum-hr">
                <div class="sum-final"><span>مبلغ نهایی:</span><span class="badge-final" style="background:<?php echo $pc; ?>;"><?php echo number_format($invoice->final_amount); ?> تومان</span></div>
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
            <?php if ($footer): ?><?php echo htmlspecialchars($footer); ?> | <?php endif; ?>
            <?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?>
        </div>
    </div>
</body>
</html>