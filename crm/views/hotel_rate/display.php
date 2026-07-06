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
    $pc = $invSet['invoice_primary_color'] ?? '#1e40af';
    $sc = $invSet['invoice_success_color'] ?? '#059669';
    $company = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $sub = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logo = $invSet['invoice_logo_url'] ?? '';
    ?>
    <style>
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
            .no-print, .orb, .stars, .glow-line { display: none !important; }
            .wrapper { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; }
            .header { padding: 6px 0 !important; }
            .header h1 { font-size: 11pt !important; }
            .header p { font-size: 6pt !important; }
            .h-card { margin-bottom: 3px !important; border-radius: 4px !important; }
            .h-card .h-top { padding: 3px 8px !important; font-size: 8pt !important; }
            .h-card .tags { padding: 2px 4px !important; }
            .h-card .tags span { font-size: 5pt !important; padding: 1px 3px !important; }
            .tbl th { padding: 2px 4px !important; font-size: 5pt !important; }
            .tbl td { padding: 2px 4px !important; font-size: 5pt !important; }
            .tbl .rb { font-size: 5pt !important; padding: 0 3px !important; }
            .tbl .dr { font-size: 4pt !important; }
            .tbl .st { font-size: 4pt !important; }
            .tbl .pc { font-size: 5pt !important; }
            .ft { font-size: 4pt !important; padding: 2px 0 !important; }
            @page { margin: 2mm; size: A4 landscape; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Vazirmatn, Tahoma, sans-serif; min-height: 100vh; overflow-x: hidden; background: #0a0a1a; }

        /* ===== COSMIC BACKGROUND ===== */
        .cosmos { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -3; background: radial-gradient(ellipse at 20% 50%, #1a0a2e 0%, transparent 50%), radial-gradient(ellipse at 80% 20%, #0a1628 0%, transparent 50%), radial-gradient(ellipse at 50% 80%, #0f1a2e 0%, transparent 50%), #060612; }
        .stars { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; }
        .star { position: absolute; background: #fff; border-radius: 50%; animation: twinkle ease-in-out infinite; }
        @keyframes twinkle { 0%, 100% { opacity: 0.2; transform: scale(1); } 50% { opacity: 1; transform: scale(1.5); } }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); z-index: -1; animation: orbFloat 20s ease-in-out infinite; }
        .orb-1 { width: 400px; height: 400px; background: rgba(99,102,241,0.15); top: -100px; right: -100px; }
        .orb-2 { width: 300px; height: 300px; background: rgba(245,158,11,0.1); bottom: -50px; left: -50px; animation-delay: -7s; }
        .orb-3 { width: 250px; height: 250px; background: rgba(16,185,129,0.08); top: 40%; left: 20%; animation-delay: -14s; }
        @keyframes orbFloat { 0%, 100% { transform: translate(0, 0); } 33% { transform: translate(40px, -30px); } 66% { transform: translate(-20px, 20px); } }

        /* ===== WRAPPER ===== */
        .wrapper { max-width: 1100px; margin: 20px auto; background: rgba(255,255,255,0.98); border-radius: 24px; overflow: hidden; box-shadow: 0 30px 100px rgba(0,0,0,0.5), 0 0 60px rgba(99,102,241,0.08); position: relative; z-index: 1; animation: slideUp 1s cubic-bezier(0.22, 1, 0.36, 1) both; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(80px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }

        /* ===== HEADER ===== */
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%); padding: 28px 30px; text-align: center; position: relative; overflow: hidden; }
        .header::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.15), rgba(236,72,153,0.1)); }
        .header h1 { color: #fff; font-size: 26px; font-weight: 900; position: relative; z-index: 2; letter-spacing: -0.5px; }
        .header h1 i { background: linear-gradient(135deg, #fbbf24, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header p { color: rgba(255,255,255,0.5); font-size: 12px; margin-top: 4px; position: relative; z-index: 2; }
        .glow-line { position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, transparent, #6366f1, #a78bfa, #6366f1, transparent); animation: glowMove 3s linear infinite; background-size: 200% 100%; }
        @keyframes glowMove { 0% { background-position: -100% 0; } 100% { background-position: 200% 0; } }

        /* ===== FILTER ===== */
        .filter { background: #f8fafc; padding: 10px 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; border-bottom: 1px solid #e2e8f0; }
        .filter select { padding: 6px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font: inherit; font-size: 12px; background: #fff; transition: all 0.3s; cursor: pointer; }
        .filter select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); outline: none; }
        .filter .fb { padding: 6px 16px; border-radius: 10px; font: inherit; font-size: 12px; cursor: pointer; border: none; font-weight: 700; transition: all 0.3s; }
        .filter .fb-prt { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; }
        .filter .fb-prt:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
        .filter .fb-clr { background: #e2e8f0; color: #475569; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .filter .fb-clr:hover { background: #cbd5e1; }
        .filter .fl { font-size: 12px; color: #64748b; font-weight: 700; }

        /* ===== HOTEL CARD ===== */
        .h-card { margin: 16px 20px; border-radius: 18px; overflow: hidden; background: #fff; box-shadow: 0 4px 30px rgba(0,0,0,0.06); animation: cardIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both; transition: all 0.4s; border: 1px solid #f1f5f9; }
        .h-card:hover { transform: translateY(-5px); box-shadow: 0 15px 50px rgba(0,0,0,0.12); border-color: #e0e7ff; }
        .h-card:nth-child(2) { animation-delay: 0.15s; }
        .h-card:nth-child(3) { animation-delay: 0.3s; }
        @keyframes cardIn { from { opacity: 0; transform: translateY(40px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .h-top { background: linear-gradient(135deg, #0f172a, #1e293b, #334155); padding: 14px 18px; display: flex; align-items: center; gap: 14px; position: relative; overflow: hidden; }
        .h-top::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.02), transparent); animation: shimmer 4s ease-in-out infinite; }
        @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
        .h-ico { width: 46px; height: 46px; background: linear-gradient(135deg, #f59e0b, #ef4444, #ec4899); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; flex-shrink: 0; box-shadow: 0 4px 15px rgba(245,158,11,0.3); animation: icoBeat 3s ease-in-out infinite; }
        @keyframes icoBeat { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.08); } }
        .h-top .inf h3 { color: #fff; font-size: 16px; font-weight: 900; position: relative; z-index: 2; }
        .h-top .inf small { color: rgba(255,255,255,0.4); font-size: 10px; position: relative; z-index: 2; }
        .stars-badge { display: inline-flex; gap: 2px; margin-right: 6px; }
        .stars-badge span { color: #fbbf24; font-size: 11px; }
        .city-badge { display: inline-flex; align-items: center; gap: 3px; background: rgba(255,255,255,0.1); padding: 2px 8px; border-radius: 8px; font-size: 10px; color: rgba(255,255,255,0.7); margin-right: 8px; }

        /* Tags */
        .tags { background: linear-gradient(135deg, #f0f9ff, #e0f2fe); padding: 10px 16px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; border-bottom: 1px solid #bae6fd; }
        .tags .t { display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; transition: all 0.3s; cursor: default; }
        .tags .t:hover { transform: translateY(-2px); }
        .tags .t-desc { background: #fff; color: #0c4a6e; border: 1px solid #7dd3fc; }
        .tags .t-fac { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border: none; box-shadow: 0 2px 8px rgba(99,102,241,0.3); }
        .tags .t-lbl { font-size: 10px; color: #0369a1; font-weight: 800; margin-left: 4px; }

        /* ===== TABLE ===== */
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl th { background: linear-gradient(135deg, #1e293b, #334155); color: #e2e8f0; padding: 8px 12px; font-size: 10px; font-weight: 700; text-align: center; white-space: nowrap; }
        .tbl th:first-child { text-align: right; border-radius: 0; }
        .tbl td { padding: 8px 12px; font-size: 11px; text-align: center; border-bottom: 1px solid #f1f5f9; transition: all 0.2s; }
        .tbl td:first-child { text-align: right; }
        .tbl tbody tr { transition: all 0.2s; }
        .tbl tbody tr:nth-child(even) { background: #fafbfe; }
        .tbl tbody tr:hover { background: linear-gradient(90deg, #eef2ff, #f5f3ff); }
        .tbl tbody tr:last-child td { border-bottom: 3px solid #6366f1; }
        .rb { display: inline-flex; align-items: center; gap: 3px; background: linear-gradient(135deg, #1e293b, #334155); color: #fff; padding: 3px 12px; border-radius: 14px; font-size: 10px; font-weight: 700; transition: all 0.3s; }
        .rb:hover { transform: scale(1.08); box-shadow: 0 3px 12px rgba(30,41,59,0.3); }
        .dr { display: inline-flex; align-items: center; gap: 3px; background: #ecfdf5; color: #065f46; padding: 3px 10px; border-radius: 8px; font-size: 10px; font-weight: 600; direction: ltr; }
        .st { display: inline-flex; align-items: center; gap: 3px; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; padding: 3px 10px; border-radius: 8px; font-size: 9px; font-weight: 700; }
        .pc { font-family: 'Courier New', monospace; font-weight: 900; color: #1e293b; direction: ltr; font-size: 11px; }
        .pc-hot { color: #dc2626; font-size: 12px; font-weight: 900; }
        .pc-best { color: #059669; font-size: 12px; font-weight: 900; }

        /* ===== FOOTER ===== */
        .ft { text-align: center; padding: 16px 20px; color: #94a3b8; font-size: 11px; border-top: 1px solid #f1f5f9; background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
        .ft a { color: #6366f1; text-decoration: none; }

        /* ===== EMPTY ===== */
        .empty { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty i { font-size: 56px; display: block; margin-bottom: 12px; background: linear-gradient(135deg, #6366f1, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; animation: bounce 2.5s ease-in-out infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        .empty p { font-size: 14px; }
    </style>
</head>
<body>
    <div class="cosmos"></div>
    <div class="stars" id="stars"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="wrapper">
        <div class="header">
            <?php if ($logo): ?><img src="<?php echo htmlspecialchars($logo); ?>" style="max-height:36px;filter:brightness(0) invert(1);margin-bottom:6px;" alt="لوگو"><?php endif; ?>
            <h1><i class="bi bi-stars"></i> نرخنامه هتل‌ها</h1>
            <p><?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?></p>
            <div class="glow-line"></div>
        </div>

        <div class="filter no-print">
            <span class="fl"><i class="bi bi-funnel-fill"></i> فیلتر:</span>
            <select onchange="if(this.value)location.href='?hotel='+encodeURIComponent(this.value);else location.href='<?php echo $config['url'] ?? ''; ?>/hotel-rates/display';">
                <option value="">همه هتل‌ها</option>
                <?php foreach ($allHotels as $h): ?>
                <option value="<?php echo htmlspecialchars($h->hotel_name); ?>" <?php echo $hotelFilter === $h->hotel_name ? 'selected' : ''; ?>><?php echo htmlspecialchars($h->hotel_name); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($hotelFilter): ?><a href="<?php echo $config['url'] ?? ''; ?>/hotel-rates/display" class="fb fb-clr"><i class="bi bi-x-circle"></i> نمایش همه</a><?php endif; ?>
            <button class="fb fb-prt" onclick="window.print()"><i class="bi bi-printer-fill"></i> چاپ</button>
        </div>

        <?php if (empty($hotels)): ?>
        <div class="empty"><i class="bi bi-building"></i><p>نرخنامه‌ای ثبت نشده است</p></div>
        <?php else: ?>
        <?php foreach ($hotels as $hotel): ?>
        <div class="h-card">
            <div class="h-top">
                <div class="h-ico"><i class="bi bi-building"></i></div>
                <div class="inf">
                    <h3>
                        <?php echo htmlspecialchars($hotel->hotel_name); ?>
                        <?php if ($hotel->star_rating): ?><span class="stars-badge"><?php for ($i=0;$i<$hotel->star_rating;$i++): ?><span>★</span><?php endfor; ?></span><?php endif; ?>
                        <?php if ($hotel->city): ?><span class="city-badge">📍 <?php echo htmlspecialchars($hotel->city); ?></span><?php endif; ?>
                    </h3>
                    <small><?php echo count($ratesByHotel[$hotel->id] ?? []); ?> نوع اتاق / بازه زمانی</small>
                </div>
            </div>

            <?php
            $descTags = !empty($hotel->description) ? array_filter(array_map('trim', explode(',', $hotel->description))) : [];
            $facTags = !empty($hotel->facilities) ? array_filter(array_map('trim', explode(',', $hotel->facilities))) : [];
            if ($descTags || $facTags):
            ?>
            <div class="tags">
                <span class="t-lbl"><i class="bi bi-info-circle"></i></span>
                <?php foreach ($descTags as $tag): ?><span class="t t-desc"><?php echo htmlspecialchars($tag); ?></span><?php endforeach; ?>
                <?php foreach ($facTags as $tag): ?><span class="t t-fac"><i class="bi bi-check2-circle"></i> <?php echo htmlspecialchars($tag); ?></span><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($ratesByHotel[$hotel->id])): ?>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>نوع اتاق</th>
                        <th>بازه تاریخ</th>
                        <th>فصل</th>
                        <th>🛏️ اقامت</th>
                        <th>🍳 اقامت+صبحانه</th>
                        <th>🥗 هافبرد</th>
                        <th>🍽️ فولبرد انتخابی</th>
                        <th>🥂 فولبرد بوفه</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ratesByHotel[$hotel->id] as $rate): ?>
                    <tr>
                        <td><span class="rb"><?php echo htmlspecialchars($rate->room_type); ?></span></td>
                        <td><span class="dr"><?php echo \Core\JDate::displayDate($rate->date_from); ?> — <?php echo \Core\JDate::displayDate($rate->date_to); ?></span></td>
                        <td><?php echo !empty($rate->season_label) ? '<span class="st">☀️ ' . htmlspecialchars($rate->season_label) . '</span>' : '-'; ?></td>
                        <td class="pc"><?php echo $rate->price_ekht > 0 ? number_format($rate->price_ekht) : '-'; ?></td>
                        <td class="pc"><?php echo $rate->price_sobhaneh > 0 ? number_format($rate->price_sobhaneh) : '-'; ?></td>
                        <td class="pc pc-best"><?php echo $rate->price_nahar > 0 ? number_format($rate->price_nahar) : '-'; ?></td>
                        <td class="pc"><?php echo $rate->price_entekhabifulboard > 0 ? number_format($rate->price_entekhabifulboard) : '-'; ?></td>
                        <td class="pc pc-hot"><?php echo $rate->price_fulboard_boufeh > 0 ? number_format($rate->price_fulboard_boufeh) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align:center;padding:20px;color:#94a3b8;font-size:12px;"><i class="bi bi-inbox"></i> نرخی ثبت نشده</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="ft">
            <?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?> — قیمت‌ها به تومان و ممکن است بدون اطلاع قبلی تغییر کند.
        </div>
    </div>

    <script>
    // Generate stars
    (function() {
        var c = document.getElementById('stars');
        if (!c) return;
        for (var i = 0; i < 60; i++) {
            var s = document.createElement('div');
            s.className = 'star';
            s.style.left = Math.random() * 100 + '%';
            s.style.top = Math.random() * 100 + '%';
            s.style.width = s.style.height = (1 + Math.random() * 2) + 'px';
            s.style.animationDuration = (2 + Math.random() * 4) + 's';
            s.style.animationDelay = (Math.random() * 5) + 's';
            c.appendChild(s);
        }
    })();
    </script>
</body>
</html>