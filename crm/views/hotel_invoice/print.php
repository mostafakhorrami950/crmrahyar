<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاکتور <?php echo htmlspecialchars($invoice->invoice_number ?? '#' . $invoice->id); ?></title>
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
            .page { box-shadow: none !important; margin: 0 !important; padding: 8px !important; max-width: 100% !important; }
            @page { margin: 3mm; size: A5 landscape; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Vazirmatn, Tahoma, sans-serif; background: #eee; color: #222; font-size: 7pt; line-height: 1.3; padding: 8px; }
        .page { max-width: 700px; margin: 0 auto; background: #fff; padding: 10px 14px; position: relative; overflow: hidden; border: 1px solid #ddd; }
        .page::after { content: '<?php echo $wmText; ?>'; position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(-35deg); font-size: 40px; font-weight: 900; color: rgba(0,0,0,0.025); white-space: nowrap; pointer-events: none; z-index: 10; }

        /* Action bar */
        .action-bar { background: #333; padding: 6px 16px; display: flex; justify-content: center; gap: 10px; margin-bottom: 8px; border-radius: 4px; }
        .action-bar button { padding: 5px 24px; border: 1px solid rgba(255,255,255,0.2); border-radius: 5px; font-family: inherit; font-weight: 700; font-size: 11px; cursor: pointer; }
        .btn-print { background: #fff; color: #333; }
        .btn-close { background: transparent; color: #fff; border-color: rgba(255,255,255,0.3) !important; }

        /* Header */
        .hdr { display: flex; justify-content: space-between; align-items: center; padding-bottom: 6px; border-bottom: 2px solid <?php echo $pc; ?>; margin-bottom: 6px; }
        .hdr-right { text-align: right; }
        .hdr-right .logo { max-height: 20px; }
        .hdr-right .title { font-size: 8pt; font-weight: 900; color: <?php echo $pc; ?>; }
        .hdr-right .meta { font-size: 6.5pt; color: #666; }
        .hdr-right .meta strong { color: <?php echo $pc; ?>; }
        .hdr-left { text-align: left; }
        .hdr-left .co { font-size: 8pt; font-weight: 900; color: <?php echo $pc; ?>; }
        .hdr-left .sub { font-size: 6pt; color: #888; }

        /* Status + Info row */
        .info-row { display: flex; gap: 6px; margin-bottom: 5px; flex-wrap: wrap; align-items: center; }
        .pill { display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 10px; font-size: 6.5pt; font-weight: 700; }
        .pill.st { background: <?php echo $stC[$st] ?? '#999'; ?>; color: #fff; }
        .pill.tp { background: #fff; color: #555; border: 1px solid #ddd; }
        .info-chip { font-size: 6pt; color: #555; background: #f5f5f5; padding: 1px 6px; border-radius: 6px; }
        .info-chip b { color: #333; }
        .creator { margin-right: auto; font-size: 6pt; color: #aaa; }

        /* Table */
        .tbl { width: 100%; border-collapse: collapse; font-size: 6.5pt; margin-bottom: 5px; }
        .tbl th { background: <?php echo $pc; ?>; color: #fff; padding: 3px 4px; font-weight: 700; font-size: 6pt; text-align: right; }
        .tbl td { padding: 2.5px 4px; border-bottom: 1px solid #eee; }
        .tbl .c { text-align: center; }
        .tbl .r { text-align: left; direction: ltr; font-family: 'Courier New', monospace; }
        .tbl .total { font-weight: 900; color: <?php echo $pc; ?>; }
        .tbl tr:last-child td { border-bottom: 2px solid <?php echo $pc; ?>; }

        /* Summary */
        .sum { max-width: 200px; margin-right: auto; font-size: 6.5pt; }
        .sum-row { display: flex; justify-content: space-between; padding: 1px 0; }
        .sum-row .l { color: #666; }
        .sum-row .v { font-weight: 700; font-family: 'Courier New', monospace; direction: ltr; }
        .sum-row .v.red { color: #e74c3c; }
        .sum-hr { border: none; border-top: 1px dashed #ddd; margin: 2px 0; }
        .sum-final { display: flex; justify-content: space-between; padding: 3px 0 0; font-size: 7.5pt; font-weight: 900; }

        /* Notes */
        .notes { display: flex; gap: 8px; margin-top: 4px; font-size: 6pt; color: #555; }
        .note-box { flex: 1; background: #fafafa; border: 1px solid #eee; border-radius: 4px; padding: 3px 6px; border-right: 3px solid <?php echo $pc; ?>; }
        .note-box .nt { font-weight: 800; color: <?php echo $pc; ?>; font-size: 6pt; margin-bottom: 1px; }

        /* Footer */
        .ftr { text-align: center; font-size: 5.5pt; color: #aaa; padding-top: 4px; border-top: 1px solid #eee; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="no-print action-bar">
        <button class="btn-print" onclick="window.print()"><i class="bi bi-printer"></i> چاپ</button>
        <button class="btn-close" onclick="window.close()">بستن</button>
    </div>

    <div class="page" data-wm="<?php echo htmlspecialchars($wmText); ?>">
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

        <!-- Info Row -->
        <div class="info-row">
            <span class="pill st"><i class="bi bi-circle-fill" style="font-size:5px;"></i> <?php echo $stL[$st] ?? $st; ?></span>
            <?php if ($invoice->invoice_type): ?><span class="pill tp"><?php echo $invoice->invoice_type == 'confirmed' ? 'تایید شده' : 'پیش فاکتور'; ?></span><?php endif; ?>
            <span class="info-chip"><b><?php echo htmlspecialchars($invoice->hotel_name); ?></b></span>
            <span class="info-chip">آژانس: <b><?php echo htmlspecialchars($invoice->agency_name ?? '-'); ?></b></span>
            <span class="info-chip">میهمان: <b><?php echo htmlspecialchars($invoice->guest_name ?? '-'); ?></b></span>
            <span class="info-chip">تلفن: <b dir="ltr"><?php echo htmlspecialchars($invoice->guest_phone ?? $invoice->contact_phone ?? '-'); ?></b></span>
            <span class="info-chip"><?php echo $invoice->nights; ?> شب</span>
            <span class="info-chip">ورود: <b><?php echo \Core\JDate::displayDate($invoice->check_in_date); ?></b></span>
            <span class="info-chip">خروج: <b><?php echo \Core\JDate::displayDate($invoice->check_out_date); ?></b></span>
            <?php if ($invoice->valid_until): ?><span class="info-chip">اعتبار: <b><?php echo \Core\JDate::displayDate($invoice->valid_until); ?></b></span><?php endif; ?>
            <span class="creator"><i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($invoice->creator_name ?? '-'); ?></span>
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
                    <td class="c" style="color:#bbb;"><?php echo $i + 1; ?></td>
                    <td><b><?php echo htmlspecialchars($item->description); ?></b></td>
                    <td class="c"><small><?php echo !empty($item->room_type) ? htmlspecialchars($item->room_type) : '-'; ?></small></td>
                    <td class="c"><?php echo number_format((int)$item->quantity); ?></td>
                    <td class="c"><?php echo !empty($item->half_price_qty) && $item->half_price_qty > 0 ? '<span style="color:#e67e22;font-weight:700;">' . $item->half_price_qty . '</span>' : '-'; ?></td>
                    <td class="r"><?php echo number_format($item->unit_price); ?></td>
                    <td class="c"><?php echo $invoice->nights; ?></td>
                    <td class="r total"><?php echo number_format($item->total_price); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Summary + Notes Row -->
        <div style="display:flex;gap:10px;align-items:flex-start;">
            <div class="sum">
                <?php if ($itemsDiscount > 0): ?>
                <div class="sum-row"><span class="l">جمع (قیمت اصلی)</span><span class="v"><?php echo number_format(($invoice->subtotal ?? 0) + $itemsDiscount); ?></span></div>
                <div class="sum-row"><span class="l"><i class="bi bi-tag"></i> تخفیف قیمت</span><span class="v red">- <?php echo number_format($itemsDiscount); ?></span></div>
                <?php endif; ?>
                <div class="sum-row"><span class="l">جمع کل</span><span class="v"><?php echo number_format($invoice->subtotal ?? $invoice->total_amount ?? 0); ?></span></div>
                <?php if (($invoice->tax_percent ?? 0) > 0): ?><div class="sum-row"><span class="l">مالیات (<?php echo $invoice->tax_percent; ?>%)</span><span class="v"><?php echo number_format($invoice->tax_amount ?? 0); ?></span></div><?php endif; ?>
                <?php if (($invoice->discount_amount ?? 0) > 0): ?><div class="sum-row"><span class="l">تخفیف</span><span class="v red">- <?php echo number_format($invoice->discount_amount); ?></span></div><?php endif; ?>
                <?php if ($isPending): ?>
                <hr class="sum-hr">
                <div class="sum-row"><span class="l">بیعانه</span><span class="v red">- <?php echo number_format($invoice->deposit_amount); ?></span></div>
                <div class="sum-final"><span>باقیمانده:</span><span style="background:<?php echo $stC[$st] ?? '#e67e22'; ?>;color:#fff;padding:2px 8px;border-radius:8px;font-size:7pt;"><?php echo number_format($invoice->final_amount - $invoice->deposit_amount); ?> ت</span></div>
                <?php else: ?>
                <hr class="sum-hr">
                <div class="sum-final"><span>مبلغ نهایی:</span><span style="background:linear-gradient(135deg,<?php echo $pc;?>,<?php echo $sc;?>);color:#fff;padding:2px 8px;border-radius:8px;font-size:7pt;"><?php echo number_format($invoice->final_amount); ?> ت</span></div>
                <?php endif; ?>
            </div>

            <?php if (!empty($invoice->ps_note) || !empty($invoice->notes) || !empty($terms)): ?>
            <div class="notes" style="flex:1;">
                <?php if (!empty($invoice->ps_note)): ?><div class="note-box"><div class="nt"><i class="bi bi-pencil-square"></i> پینوشت</div><?php echo nl2br(htmlspecialchars($invoice->ps_note)); ?></div><?php endif; ?>
                <?php if (!empty($invoice->notes)): ?><div class="note-box"><div class="nt"><i class="bi bi-journal-text"></i> توضیحات</div><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></div><?php endif; ?>
                <?php if (!empty($terms)): ?><div class="note-box" style="border-right-color:#333;"><div class="nt" style="color:#333;"><i class="bi bi-shield-check"></i> شرایط</div><?php echo nl2br(htmlspecialchars($terms)); ?></div><?php endif; ?>
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