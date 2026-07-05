<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نرخنامه هتل‌ها</title>
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
    ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Vazirmatn, Tahoma, sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%); min-height: 100vh; }
        
        /* Header */
        .page-header { background: linear-gradient(135deg, <?php echo $pc; ?>, <?php echo $sc; ?>); padding: 30px 0; position: relative; overflow: hidden; }
        .page-header::before { content: ''; position: absolute; top: -50%; right: -20%; width: 400px; height: 400px; background: rgba(255,255,255,0.05); border-radius: 50%; }
        .page-header::after { content: ''; position: absolute; bottom: -60%; left: -10%; width: 300px; height: 300px; background: rgba(255,255,255,0.03); border-radius: 50%; }
        .header-content { position: relative; z-index: 2; }
        .header-content h1 { color: #fff; font-size: 28px; font-weight: 900; margin: 0; }
        .header-content p { color: rgba(255,255,255,0.8); margin: 5px 0 0; font-size: 14px; }
        .header-logo { max-height: 50px; filter: brightness(0) invert(1); }
        
        /* Filter Bar */
        .filter-bar { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-top: -20px; position: relative; z-index: 10; padding: 15px 20px; }
        
        /* Hotel Card */
        .hotel-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); overflow: hidden; margin-bottom: 24px; transition: transform 0.2s; }
        .hotel-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
        .hotel-card-header { background: linear-gradient(135deg, #1a1a2e, #2d3436); padding: 16px 20px; display: flex; align-items: center; gap: 12px; }
        .hotel-icon { width: 44px; height: 44px; background: linear-gradient(135deg, <?php echo $pc; ?>, <?php echo $sc; ?>); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; }
        .hotel-card-header h3 { color: #fff; font-size: 18px; font-weight: 800; margin: 0; }
        .hotel-card-header small { color: rgba(255,255,255,0.6); font-size: 12px; }
        
        /* Rate Table */
        .rate-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .rate-table thead th { background: #f8f9fa; padding: 10px 14px; font-size: 12px; font-weight: 700; color: #555; border-bottom: 2px solid #eef0f2; text-align: center; white-space: nowrap; }
        .rate-table thead th:first-child { text-align: right; }
        .rate-table tbody td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #f0f2f5; text-align: center; }
        .rate-table tbody td:first-child { text-align: right; font-weight: 700; color: #1a1a2e; }
        .rate-table tbody tr:hover { background: #fafbfc; }
        .rate-table tbody tr:last-child td { border-bottom: none; }
        .price-cell { font-family: 'Courier New', monospace; font-weight: 700; color: #2d3436; direction: ltr; font-size: 13px; }
        .price-cell.highlight { color: <?php echo $sc; ?>; font-size: 14px; }
        .room-badge { display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, <?php echo $pc; ?>12, <?php echo $sc; ?>12); color: <?php echo $pc; ?>; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .season-tag { display: inline-flex; align-items: center; gap: 3px; background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 8px; font-size: 11px; font-weight: 600; }
        .date-tag { color: #636e72; font-size: 12px; direction: ltr; }
        
        /* Price type headers */
        .price-type-icon { display: block; font-size: 16px; margin-bottom: 2px; }
        
        /* Empty state */
        .empty-state { text-align: center; padding: 60px 20px; color: #b2bec3; }
        .empty-state i { font-size: 60px; }
        
        /* Footer */
        .page-footer { text-align: center; padding: 20px; color: #b2bec3; font-size: 12px; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .rate-table { font-size: 11px; }
            .rate-table thead th, .rate-table tbody td { padding: 6px 8px; }
            .price-cell { font-size: 11px; }
        }
        
        @media print {
            body { background: #fff !important; }
            .filter-bar, .no-print { display: none !important; }
            .hotel-card { box-shadow: none !important; break-inside: avoid; }
            .page-header { background: <?php echo $pc; ?> !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="page-header">
        <div class="header-content container text-center">
            <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" class="header-logo mb-2" alt="لوگو"><?php endif; ?>
            <h1><i class="bi bi-cash-stack me-2"></i>نرخنامه هتل‌ها</h1>
            <p><?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?></p>
        </div>
    </div>

    <div class="container" style="max-width:1100px; margin-top: -10px;">
        <!-- Filter Bar -->
        <div class="filter-bar mb-4">
            <form method="get" class="d-flex flex-wrap align-items-center gap-2">
                <i class="bi bi-funnel text-muted"></i>
                <strong class="small text-muted">فیلتر هتل:</strong>
                <select name="hotel" class="form-select form-select-sm" style="width:auto;min-width:200px;" onchange="this.form.submit()">
                    <option value="">همه هتل‌ها</option>
                    <?php foreach ($hotels as $h): ?>
                    <option value="<?php echo htmlspecialchars($h->hotel_name); ?>" <?php echo $hotelFilter === $h->hotel_name ? 'selected' : ''; ?>><?php echo htmlspecialchars($h->hotel_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($hotelFilter): ?>
                <a href="<?php echo $config['url'] ?? ''; ?>/hotel-rates/display" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>نمایش همه</a>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-dark btn-sm ms-auto no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>چاپ</button>
            </form>
        </div>

        <?php if (empty($grouped)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox d-block mb-3"></i>
            <h5>نرخنامه‌ای ثبت نشده</h5>
            <p>هنوز هیچ نرخی برای هتل‌ها ثبت نشده است.</p>
        </div>
        <?php else: ?>
        <?php foreach ($grouped as $hotelName => $hotelRates): ?>
        <div class="hotel-card">
            <div class="hotel-card-header">
                <div class="hotel-icon"><i class="bi bi-building"></i></div>
                <div>
                    <h3><?php echo htmlspecialchars($hotelName); ?></h3>
                    <small><?php echo count($hotelRates); ?> نوع اتاق</small>
                </div>
            </div>
            <div class="table-responsive">
                <table class="rate-table">
                    <thead>
                        <tr>
                            <th style="min-width:120px;">نوع اتاق</th>
                            <th>تاریخ</th>
                            <th>فصل</th>
                            <th><span class="price-type-icon">🛏️</span>اقامت</th>
                            <th><span class="price-type-icon">🍳</span>اقامت<br>+صبحانه</th>
                            <th><span class="price-type-icon">🍳🥗</span>اقامت+صبحانه<br>+ناهار</th>
                            <th><span class="price-type-icon">🍽️</span>فولبرد</th>
                            <th><span class="price-type-icon">🍽️✨</span>فولبرد<br>انتخابی</th>
                            <th><span class="price-type-icon">🥂</span>بوفه</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hotelRates as $rate): ?>
                        <tr>
                            <td><span class="room-badge"><i class="bi bi-door-open"></i> <?php echo htmlspecialchars($rate->room_type); ?></span></td>
                            <td><span class="date-tag"><?php echo \Core\JDate::displayDate($rate->rate_date); ?></span></td>
                            <td><?php echo !empty($rate->season_label) ? '<span class="season-tag"><i class="bi bi-sun"></i>' . htmlspecialchars($rate->season_label) . '</span>' : '-'; ?></td>
                            <td class="price-cell"><?php echo $rate->price_ekht > 0 ? number_format($rate->price_ekht) : '-'; ?></td>
                            <td class="price-cell"><?php echo $rate->price_sobhaneh > 0 ? number_format($rate->price_sobhaneh) : '-'; ?></td>
                            <td class="price-cell"><?php echo $rate->price_nahar > 0 ? number_format($rate->price_nahar) : '-'; ?></td>
                            <td class="price-cell highlight"><?php echo $rate->price_fulboard > 0 ? number_format($rate->price_fulboard) : '-'; ?></td>
                            <td class="price-cell"><?php echo $rate->price_entekhabifulboard > 0 ? number_format($rate->price_entekhabifulboard) : '-'; ?></td>
                            <td class="price-cell"><?php echo $rate->price_boufeh > 0 ? number_format($rate->price_boufeh) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Footer -->
        <div class="page-footer">
            <?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?>
            <br><small>قیمت‌ها به تومان می‌باشد و ممکن است بدون اطلاع قبلی تغییر کند.</small>
        </div>
    </div>
</body>
</html>