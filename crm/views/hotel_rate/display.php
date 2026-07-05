<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نرخنامه هتل‌ها</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <?php
    $invSet = $invoiceSettings ?? [];
    $pc = $invSet['invoice_primary_color'] ?? '#6366f1';
    $sc = $invSet['invoice_success_color'] ?? '#10b981';
    $company = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $sub = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logo = $invSet['invoice_logo_url'] ?? '';
    ?>
    <style>
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background: #fff !important; margin: 0 !important; padding: 0 !important; font-size: 7pt !important; }
            .no-print { display: none !important; }
            .page-wrapper { box-shadow: none !important; margin: 0 !important; padding: 8px !important; max-width: 100% !important; border-radius: 0 !important; }
            .page-header { padding: 6px 0 !important; }
            .page-header h1 { font-size: 12pt !important; }
            .page-header p { font-size: 6pt !important; }
            .hotel-section { margin-bottom: 4px !important; page-break-inside: avoid; }
            .hotel-head { padding: 3px 8px !important; }
            .hotel-head h3 { font-size: 8pt !important; }
            .hotel-desc { font-size: 5.5pt !important; padding: 2px 6px !important; }
            .rt th { padding: 1px 3px !important; font-size: 5.5pt !important; }
            .rt td { padding: 1px 3px !important; font-size: 5.5pt !important; }
            .room-b { font-size: 5pt !important; padding: 0px 4px !important; }
            .date-r { font-size: 4.5pt !important; padding: 0px 3px !important; }
            .season-t { font-size: 4pt !important; padding: 0px 3px !important; }
            .price-c { font-size: 5.5pt !important; }
            .page-footer { font-size: 4pt !important; padding: 2px 0 !important; }
            @page { margin: 2mm; size: A4 landscape; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Vazirmatn, Tahoma, sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%); min-height: 100vh; padding: 10px; }
        .page-wrapper { max-width: 1200px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .page-header { background: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa); padding: 16px 20px; text-align: center; position: relative; overflow: hidden; }
        .page-header::before { content: ''; position: absolute; top: -40px; right: -40px; width: 180px; height: 180px; background: rgba(255,255,255,0.06); border-radius: 50%; }
        .page-header::after { content: ''; position: absolute; bottom: -40px; left: -20px; width: 140px; height: 140px; background: rgba(255,255,255,0.04); border-radius: 50%; }
        .page-header h1 { color: #fff; font-size: 20px; font-weight: 900; margin: 0; position: relative; z-index: 2; }
        .page-header p { color: rgba(255,255,255,0.7); margin: 3px 0 0; font-size: 11px; position: relative; z-index: 2; }
        .header-logo { max-height: 32px; filter: brightness(0) invert(1); margin-bottom: 3px; }
        .filter-bar { background: linear-gradient(135deg, #f8fafc, #e2e8f0); padding: 6px 14px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .filter-bar select { padding: 3px 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 11px; background: #fff; }
        .filter-bar .btn { padding: 3px 10px; border-radius: 6px; font-size: 11px; font-family: inherit; cursor: pointer; border: none; font-weight: 600; }
        .filter-bar .btn-print { background: #6366f1; color: #fff; }
        .filter-bar .btn-clear { background: #e2e8f0; color: #475569; text-decoration: none; }
        .filter-label { font-size: 11px; color: #64748b; font-weight: 700; }
        .hotel-section { margin: 8px 12px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .hotel-head { background: linear-gradient(135deg, #0f172a, #1e2936); padding: 7px 12px; display: flex; align-items: center; gap: 10px; }
        .hotel-icon { width: 30px; height: 30px; background: linear-gradient(135deg, #f59e0b, #ef4444); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; flex-shrink: 0; }
        .hotel-head .info h3 { color: #fff; font-size: 13px; font-weight: 800; margin: 0; }
        .hotel-head .info small { color: rgba(255,255,255,0.5); font-size: 9px; }
        .hotel-desc { background: linear-gradient(135deg, #fefce8, #fef9c3); padding: 5px 10px; font-size: 10px; color: #713f12; line-height: 1.6; }
        .hotel-desc strong { color: #92400e; }
        .rt { width: 100%; border-collapse: collapse; }
        .rt th { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; padding: 4px 6px; font-size: 8px; font-weight: 700; text-align: center; white-space: nowrap; border: 1px solid rgba(255,255,255,0.15); }
        .rt th:first-child { text-align: right; }
        .rt td { padding: 4px 6px; font-size: 9px; text-align: center; border: 1px solid #f1f5f9; }
        .rt td:first-child { text-align: right; }
        .rt tbody tr:nth-child(even) { background: #f8fafc; }
        .rt tbody tr:hover { background: #eef2ff; }
        .rt tbody tr:last-child td { border-bottom: 2px solid #6366f1; }
        .room-b { display: inline-flex; align-items: center; gap: 2px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; padding: 1px 6px; border-radius: 10px; font-size: 8px; font-weight: 700; }
        .date-r { display: inline-flex; align-items: center; gap: 2px; background: #ecfdf5; color: #065f46; padding: 1px 5px; border-radius: 5px; font-size: 8px; font-weight: 600; direction: ltr; }
        .season-t { display: inline-flex; align-items: center; gap: 2px; background: #fef3c7; color: #92400e; padding: 1px 5px; border-radius: 5px; font-size: 7px; font-weight: 600; }
        .price-c { font-family: 'Courier New', monospace; font-weight: 800; color: #1e293b; direction: ltr; font-size: 9px; }
        .price-c.hot { color: #dc2626; font-size: 10px; }
        .page-footer { text-align: center; padding: 8px 12px; color: #94a3b8; font-size: 9px; border-top: 1px solid #f1f5f9; }
        .empty { text-align: center; padding: 30px; color: #94a3b8; }
        .empty i { font-size: 36px; display: block; margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="page-header">
            <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" class="header-logo" alt="لوگو"><?php endif; ?>
            <h1>📋 نرخنامه هتل‌ها</h1>
            <p><?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?></p>
        </div>

        <div class="filter-bar no-print">
            <span class="filter-label"><i class="bi bi-funnel"></i> فیلتر:</span>
            <select onchange="if(this.value)location.href='?hotel='+encodeURIComponent(this.value);else location.href='';">
                <option value="">همه هتل‌ها</option>
                <?php foreach ($allHotels as $h): ?>
                <option value="<?php echo htmlspecialchars($h->hotel_name); ?>" <?php echo $hotelFilter === $h->hotel_name ? 'selected' : ''; ?>><?php echo htmlspecialchars($h->hotel_name); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($hotelFilter): ?><a href="?" class="btn btn-clear"><i class="bi bi-x-circle"></i> نمایش همه</a><?php endif; ?>
            <button class="btn btn-print" onclick="window.print()"><i class="bi bi-printer"></i> چاپ</button>
        </div>

        <?php if (empty($hotels)): ?>
        <div class="empty"><i class="bi bi-inbox"></i>نرخنامه‌ای ثبت نشده</div>
        <?php else: ?>
        <?php foreach ($hotels as $hotel): ?>
        <div class="hotel-section">
            <div class="hotel-head">
                <div class="hotel-icon"><i class="bi bi-building"></i></div>
                <div class="info">
                    <h3>
                        <?php echo htmlspecialchars($hotel->hotel_name); ?>
                        <?php if ($hotel->star_rating): ?> <span style="color:#fbbf24;font-size:10px;"><?php echo str_repeat('★', $hotel->star_rating); ?></span><?php endif; ?>
                        <?php if ($hotel->city): ?> <small style="color:rgba(255,255,255,0.5);font-size:9px;">(<?php echo htmlspecialchars($hotel->city); ?>)</small><?php endif; ?>
                    </h3>
                    <small><?php echo count($ratesByHotel[$hotel->id] ?? []); ?> نوع اتاق / بازه زمانی</small>
                </div>
            </div>

            <?php if (!empty($hotel->description) || !empty($hotel->facilities)): ?>
            <div class="hotel-desc">
                <?php if (!empty($hotel->description)): ?><strong>ℹ️ توضیحات:</strong> <?php echo htmlspecialchars($hotel->description); ?><?php endif; ?>
                <?php if (!empty($hotel->facilities)): ?> | <strong>🎯 امکانات:</strong> <?php echo htmlspecialchars($hotel->facilities); ?><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($ratesByHotel[$hotel->id])): ?>
            <table class="rt">
                <thead>
                    <tr>
                        <th>نوع اتاق</th>
                        <th>بازه تاریخ</th>
                        <th>فصل</th>
                        <th>🛏️ اقامت</th>
                        <th>🍳 اقامت+صبحانه</th>
                        <th>🍳🥗 اقامت+صبحانه+ناهار</th>
                        <th>🍽️✨ فولبرد انتخابی</th>
                        <th>🥂 فولبرد بوفه</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ratesByHotel[$hotel->id] as $rate): ?>
                    <tr>
                        <td><span class="room-b"><?php echo htmlspecialchars($rate->room_type); ?></span></td>
                        <td><span class="date-r"><?php echo \Core\JDate::displayDate($rate->date_from); ?> - <?php echo \Core\JDate::displayDate($rate->date_to); ?></span></td>
                        <td><?php echo !empty($rate->season_label) ? '<span class="season-t">☀️ ' . htmlspecialchars($rate->season_label) . '</span>' : '-'; ?></td>
                        <td class="price-c"><?php echo $rate->price_ekht > 0 ? number_format($rate->price_ekht) : '-'; ?></td>
                        <td class="price-c"><?php echo $rate->price_sobhaneh > 0 ? number_format($rate->price_sobhaneh) : '-'; ?></td>
                        <td class="price-c"><?php echo $rate->price_nahar > 0 ? number_format($rate->price_nahar) : '-'; ?></td>
                        <td class="price-c"><?php echo $rate->price_entekhabifulboard > 0 ? number_format($rate->price_entekhabifulboard) : '-'; ?></td>
                        <td class="price-c hot"><?php echo $rate->price_fulboard_boufeh > 0 ? number_format($rate->price_fulboard_boufeh) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align:center;padding:12px;color:#94a3b8;font-size:11px;">نرخی برای این هتل ثبت نشده</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="page-footer">
            <?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?> — قیمت‌ها به تومان و ممکن است بدون اطلاع قبلی تغییر کند.
        </div>
    </div>
</body>
</html>