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
            .no-print, .bg-particles, .floating-shapes { display: none !important; }
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
        body { font-family: Vazirmatn, Tahoma, sans-serif; min-height: 100vh; overflow-x: hidden; }

        /* ===== ANIMATED BACKGROUND ===== */
        .bg-animated {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;
            background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #1a1a2e, #16213e, #0f3460);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating shapes */
        .floating-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .shape {
            position: absolute; border-radius: 50%; opacity: 0.06;
            animation: floatShape linear infinite;
        }
        .shape:nth-child(1) { width: 300px; height: 300px; background: #6366f1; top: 10%; left: 10%; animation-duration: 20s; }
        .shape:nth-child(2) { width: 200px; height: 200px; background: #f59e0b; top: 60%; right: 10%; animation-duration: 25s; animation-delay: -5s; }
        .shape:nth-child(3) { width: 150px; height: 150px; background: #10b981; bottom: 10%; left: 30%; animation-duration: 18s; animation-delay: -8s; }
        .shape:nth-child(4) { width: 250px; height: 250px; background: #ef4444; top: 30%; right: 30%; animation-duration: 22s; animation-delay: -3s; }
        .shape:nth-child(5) { width: 180px; height: 180px; background: #8b5cf6; bottom: 30%; right: 5%; animation-duration: 28s; animation-delay: -10s; }
        @keyframes floatShape {
            0%, 100% { transform: translate(0, 0) rotate(0deg) scale(1); }
            25% { transform: translate(30px, -40px) rotate(90deg) scale(1.1); }
            50% { transform: translate(-20px, 20px) rotate(180deg) scale(0.9); }
            75% { transform: translate(40px, 30px) rotate(270deg) scale(1.05); }
        }

        /* Particles */
        .bg-particles { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; pointer-events: none; }
        .particle {
            position: absolute; width: 3px; height: 3px; background: rgba(255,255,255,0.3); border-radius: 50%;
            animation: particleFloat linear infinite;
        }
        @keyframes particleFloat {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        /* ===== PAGE WRAPPER ===== */
        .page-wrapper {
            max-width: 1200px; margin: 0 auto; background: rgba(255,255,255,0.97);
            border-radius: 16px; overflow: hidden;
            box-shadow: 0 25px 80px rgba(0,0,0,0.4), 0 0 40px rgba(99,102,241,0.1);
            position: relative; z-index: 1;
            animation: pageSlideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes pageSlideUp {
            from { opacity: 0; transform: translateY(60px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ===== HEADER ===== */
        .page-header {
            background: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa, #c084fc);
            background-size: 300% 300%;
            animation: headerGradient 8s ease infinite;
            padding: 20px 24px; text-align: center; position: relative; overflow: hidden;
        }
        @keyframes headerGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .page-header::before {
            content: ''; position: absolute; top: -60px; right: -60px; width: 200px; height: 200px;
            background: rgba(255,255,255,0.08); border-radius: 50%;
            animation: pulse 4s ease-in-out infinite;
        }
        .page-header::after {
            content: ''; position: absolute; bottom: -50px; left: -30px; width: 160px; height: 160px;
            background: rgba(255,255,255,0.05); border-radius: 50%;
            animation: pulse 5s ease-in-out infinite reverse;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.08; }
            50% { transform: scale(1.3); opacity: 0.15; }
        }
        .page-header h1 {
            color: #fff; font-size: 22px; font-weight: 900; margin: 0;
            position: relative; z-index: 2;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: titleGlow 3s ease-in-out infinite;
        }
        @keyframes titleGlow {
            0%, 100% { text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
            50% { text-shadow: 0 2px 20px rgba(255,255,255,0.3), 0 0 40px rgba(255,255,255,0.1); }
        }
        .page-header p { color: rgba(255,255,255,0.75); margin: 4px 0 0; font-size: 11px; position: relative; z-index: 2; }
        .header-logo { max-height: 32px; filter: brightness(0) invert(1); margin-bottom: 3px; }

        /* ===== FILTER BAR ===== */
        .filter-bar {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            padding: 8px 16px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            border-bottom: 1px solid #e2e8f0;
        }
        .filter-bar select {
            padding: 5px 12px; border: 1px solid #cbd5e1; border-radius: 8px;
            font-family: inherit; font-size: 12px; background: #fff;
            transition: all 0.3s; cursor: pointer;
        }
        .filter-bar select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); outline: none; }
        .filter-bar .btn {
            padding: 5px 14px; border-radius: 8px; font-size: 12px; font-family: inherit;
            cursor: pointer; border: none; font-weight: 600; transition: all 0.3s;
        }
        .filter-bar .btn-print { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; }
        .filter-bar .btn-print:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.4); }
        .filter-bar .btn-clear { background: #e2e8f0; color: #475569; text-decoration: none; }
        .filter-bar .btn-clear:hover { background: #cbd5e1; }
        .filter-label { font-size: 12px; color: #64748b; font-weight: 700; }

        /* ===== HOTEL SECTION ===== */
        .hotel-section {
            margin: 12px 16px; border-radius: 14px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            animation: cardFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .hotel-section:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .hotel-section:nth-child(2) { animation-delay: 0.1s; }
        .hotel-section:nth-child(3) { animation-delay: 0.2s; }
        .hotel-section:nth-child(4) { animation-delay: 0.3s; }
        @keyframes cardFadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hotel-head {
            background: linear-gradient(135deg, #0f172a, #1e2936);
            padding: 10px 14px; display: flex; align-items: center; gap: 10px;
            position: relative; overflow: hidden;
        }
        .hotel-head::after {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .hotel-icon {
            width: 34px; height: 34px; background: linear-gradient(135deg, #f59e0b, #ef4444);
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 14px; flex-shrink: 0;
            animation: iconPulse 2s ease-in-out infinite;
        }
        @keyframes iconPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.4); }
            50% { box-shadow: 0 0 0 6px rgba(245,158,11,0); }
        }
        .hotel-head .info h3 { color: #fff; font-size: 14px; font-weight: 800; margin: 0; position: relative; z-index: 2; }
        .hotel-head .info small { color: rgba(255,255,255,0.5); font-size: 10px; position: relative; z-index: 2; }

        .hotel-desc {
            background: linear-gradient(135deg, #fefce8, #fef9c3);
            padding: 6px 12px; font-size: 11px; color: #713f12; line-height: 1.7;
            border-bottom: 1px solid #fde68a;
        }
        .hotel-desc strong { color: #92400e; }

        /* ===== TABLE ===== */
        .rt { width: 100%; border-collapse: collapse; }
        .rt th {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff; padding: 6px 8px; font-size: 9px; font-weight: 700;
            text-align: center; white-space: nowrap;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .rt th:first-child { text-align: right; }
        .rt td { padding: 6px 8px; font-size: 10px; text-align: center; border: 1px solid #f1f5f9; transition: background 0.2s; }
        .rt td:first-child { text-align: right; }
        .rt tbody tr { transition: all 0.2s; }
        .rt tbody tr:nth-child(even) { background: #f8fafc; }
        .rt tbody tr:hover { background: #eef2ff; transform: scale(1.005); }
        .rt tbody tr:last-child td { border-bottom: 2px solid #6366f1; }

        .room-b {
            display: inline-flex; align-items: center; gap: 3px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff; padding: 2px 8px; border-radius: 12px;
            font-size: 9px; font-weight: 700;
            transition: all 0.3s;
        }
        .room-b:hover { transform: scale(1.05); box-shadow: 0 2px 8px rgba(99,102,241,0.3); }
        .date-r {
            display: inline-flex; align-items: center; gap: 3px;
            background: #ecfdf5; color: #065f46; padding: 2px 7px; border-radius: 6px;
            font-size: 9px; font-weight: 600; direction: ltr;
        }
        .season-t {
            display: inline-flex; align-items: center; gap: 2px;
            background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 6px;
            font-size: 8px; font-weight: 600;
        }
        .price-c { font-family: 'Courier New', monospace; font-weight: 800; color: #1e293b; direction: ltr; font-size: 10px; }
        .price-c.hot { color: #dc2626; font-size: 11px; }

        /* ===== FOOTER ===== */
        .page-footer {
            text-align: center; padding: 12px 16px; color: #94a3b8; font-size: 10px;
            border-top: 1px solid #f1f5f9;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }

        /* ===== EMPTY STATE ===== */
        .empty { text-align: center; padding: 40px; color: #94a3b8; }
        .empty i { font-size: 40px; display: block; margin-bottom: 8px; animation: bounce 2s ease-in-out infinite; }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animated"></div>
    <div class="floating-shapes">
        <div class="shape"></div><div class="shape"></div><div class="shape"></div><div class="shape"></div><div class="shape"></div>
    </div>
    <div class="bg-particles" id="particles"></div>

    <div class="page-wrapper">
        <!-- Header -->
        <div class="page-header">
            <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" class="header-logo" alt="لوگو"><?php endif; ?>
            <h1>📋 نرخنامه هتل‌ها</h1>
            <p><?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?></p>
        </div>

        <!-- Filter -->
        <div class="filter-bar no-print">
            <span class="filter-label"><i class="bi bi-funnel"></i> فیلتر:</span>
            <select onchange="if(this.value)location.href='?hotel='+encodeURIComponent(this.value);else location.href='<?php echo $config['url'] ?? ''; ?>/hotel-rates/display';">
                <option value="">همه هتل‌ها</option>
                <?php foreach ($allHotels as $h): ?>
                <option value="<?php echo htmlspecialchars($h->hotel_name); ?>" <?php echo $hotelFilter === $h->hotel_name ? 'selected' : ''; ?>><?php echo htmlspecialchars($h->hotel_name); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($hotelFilter): ?><a href="<?php echo $config['url'] ?? ''; ?>/hotel-rates/display" class="btn btn-clear"><i class="bi bi-x-circle"></i> نمایش همه</a><?php endif; ?>
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
                        <?php if ($hotel->star_rating): ?> <span style="color:#fbbf24;font-size:11px;"><?php echo str_repeat('★', $hotel->star_rating); ?></span><?php endif; ?>
                        <?php if ($hotel->city): ?> <small style="color:rgba(255,255,255,0.5);font-size:10px;">(<?php echo htmlspecialchars($hotel->city); ?>)</small><?php endif; ?>
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
            <div style="text-align:center;padding:14px;color:#94a3b8;font-size:12px;">نرخی برای این هتل ثبت نشده</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Footer -->
        <div class="page-footer">
            <?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?> — قیمت‌ها به تومان و ممکن است بدون اطلاع قبلی تغییر کند.
        </div>
    </div>

    <script>
    // Generate particles
    (function() {
        var container = document.getElementById('particles');
        if (!container) return;
        for (var i = 0; i < 30; i++) {
            var p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + '%';
            p.style.animationDuration = (8 + Math.random() * 12) + 's';
            p.style.animationDelay = (Math.random() * 10) + 's';
            p.style.width = p.style.height = (2 + Math.random() * 3) + 'px';
            container.appendChild(p);
        }
    })();
    </script>
</body>
</html>