<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نرخنامه هتل‌ها</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <?php
    $invSet = $invoiceSettings ?? [];
    $company = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $sub = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logo = $invSet['invoice_logo_url'] ?? '';
    $pc = $invSet['invoice_primary_color'] ?? '#4f46e5';
    ?>
    <style>
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background: #fff !important; }
            .no-print, .bg-mesh, .bg-grid { display: none !important; }
            .page { box-shadow: none !important; max-width: 100% !important; border-radius: 0 !important; }
            @page { margin: 3mm; size: A4 landscape; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { height: 100%; }
        body {
            font-family: Vazirmatn, 'Plus Jakarta Sans', Tahoma, sans-serif;
            min-height: 100vh;
            background: #f0f2f5;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            position: relative; overflow-x: hidden;
        }

        /* ===== BACKGROUND ===== */
        .bg-mesh {
            position: fixed; inset: 0; z-index: -2;
            background:
                radial-gradient(at 0% 0%, rgba(79,70,229,0.08) 0%, transparent 50%),
                radial-gradient(at 100% 0%, rgba(236,72,153,0.06) 0%, transparent 50%),
                radial-gradient(at 50% 100%, rgba(16,185,129,0.06) 0%, transparent 50%),
                #f0f2f5;
        }
        .bg-grid {
            position: fixed; inset: 0; z-index: -1;
            background-image:
                linear-gradient(rgba(0,0,0,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.02) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* ===== PAGE ===== */
        .page {
            max-width: 960px; width: 100%;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 40px rgba(0,0,0,0.08);
            animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(30px); } }

        /* ===== TOP BAR ===== */
        .top-bar {
            background: linear-gradient(135deg, #1e1b4b, #312e81, #4338ca);
            padding: 24px 28px;
            display: flex; align-items: center; justify-content: space-between;
            position: relative; overflow: hidden;
        }
        .top-bar::before {
            content: ''; position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .top-bar .brand { position: relative; z-index: 2; }
        .top-bar .brand h1 { color: #fff; font-size: 20px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
        .top-bar .brand h1 .ico {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; background: rgba(255,255,255,0.15);
            border-radius: 10px; font-size: 16px;
        }
        .top-bar .brand p { color: rgba(255,255,255,0.5); font-size: 11px; margin-top: 2px; }
        .top-bar .meta { position: relative; z-index: 2; display: flex; gap: 8px; }
        .top-bar .meta .badge {
            background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.8);
            padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 600;
            backdrop-filter: blur(4px);
        }
        .glow {
            position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, #818cf8, #a78bfa, #c084fc, #a78bfa, #818cf8);
            background-size: 200% 100%; animation: glow 4s linear infinite;
        }
        @keyframes glow { 0% { background-position: -100% 0; } 100% { background-position: 200% 0; } }

        /* ===== FILTER ===== */
        .filter-bar {
            padding: 12px 24px; background: #fafbfc;
            border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .filter-bar label { font-size: 11px; color: #6b7280; font-weight: 700; }
        .filter-bar select, .filter-bar input[type="date"] {
            padding: 6px 12px; border: 1.5px solid #e5e7eb; border-radius: 8px;
            font: inherit; font-size: 12px; background: #fff; transition: all 0.2s;
        }
        .filter-bar select:focus, .filter-bar input[type="date"]:focus {
            border-color: #818cf8; box-shadow: 0 0 0 3px rgba(129,140,248,0.15); outline: none;
        }
        .btn-f {
            padding: 6px 14px; border-radius: 8px; border: none; font: inherit;
            font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .btn-f:hover { transform: translateY(-1px); }
        .btn-search { background: #4f46e5; color: #fff; }
        .btn-search:hover { background: #4338ca; box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
        .btn-clear { background: #f3f4f6; color: #6b7280; text-decoration: none; }
        .btn-clear:hover { background: #e5e7eb; }
        .btn-print { background: #1e1b4b; color: #fff; margin-right: auto; }
        .btn-print:hover { background: #312e81; }

        /* ===== HOTEL SECTION ===== */
        .hotel { padding: 0 24px; }
        .hotel:first-child { padding-top: 20px; }
        .hotel + .hotel { border-top: 1px solid #f3f4f6; }

        .hotel-head {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 0 12px;
        }
        .hotel-logo {
            width: 48px; height: 48px; border-radius: 14px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 20px; flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(251,191,36,0.3);
        }
        .hotel-info h2 { font-size: 17px; font-weight: 800; color: #111827; }
        .hotel-info .sub-info { display: flex; gap: 12px; margin-top: 2px; }
        .hotel-info .sub-info span { font-size: 11px; color: #9ca3af; display: flex; align-items: center; gap: 3px; }
        .hotel-info .sub-info .stars { color: #f59e0b; }

        /* Tags */
        .tag-row { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 12px; }
        .tag {
            padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 3px;
            transition: all 0.2s; cursor: default;
        }
        .tag:hover { transform: translateY(-1px); }
        .tag-desc { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .tag-fac { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

        /* ===== RATE CARDS ===== */
        .rates { display: flex; flex-direction: column; gap: 8px; padding-bottom: 20px; }

        .rate-card {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 0;
            border: 1.5px solid #f3f4f6;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            background: #fff;
        }
        .rate-card:hover {
            border-color: #c7d2fe;
            box-shadow: 0 4px 16px rgba(79,70,229,0.08);
            transform: translateX(-2px);
        }

        .rate-label {
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            padding: 12px 14px;
            display: flex; flex-direction: column; justify-content: center;
            border-left: 1.5px solid #f3f4f6;
        }
        .rate-card:hover .rate-label { border-left-color: #e0e7ff; }
        .rate-label .room { font-size: 13px; font-weight: 800; color: #1f2937; }
        .rate-label .season {
            font-size: 10px; color: #9ca3af; margin-top: 2px;
            display: flex; align-items: center; gap: 3px;
        }

        .rate-body { padding: 10px 16px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .price-item {
            display: flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: 8px;
            background: #f9fafb; border: 1px solid #f3f4f6;
            transition: all 0.2s; font-size: 11px;
        }
        .price-item:hover { background: #eef2ff; border-color: #e0e7ff; }
        .price-item .label { color: #6b7280; font-weight: 500; }
        .price-item .val { font-weight: 800; color: #111827; font-family: 'Courier New', monospace; direction: ltr; }
        .price-item .val.hot { color: #dc2626; }
        .price-item .val.best { color: #059669; }
        .price-item .date-range {
            font-size: 9px; color: #9ca3af; direction: ltr; text-align: left;
            display: flex; align-items: center; gap: 3px;
        }

        /* ===== EMPTY ===== */
        .empty { text-align: center; padding: 60px 20px; }
        .empty .ico {
            width: 64px; height: 64px; margin: 0 auto 16px;
            background: #f3f4f6; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; color: #9ca3af;
        }
        .empty p { color: #9ca3af; font-size: 14px; }

        /* ===== FOOTER ===== */
        .footer {
            padding: 14px 24px; text-align: center;
            background: #fafbfc; border-top: 1px solid #f3f4f6;
            font-size: 10px; color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="bg-grid"></div>

    <div class="page">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="brand">
                <h1>
                    <span class="ico">🏨</span>
                    نرخنامه هتل‌ها
                </h1>
                <p><?php echo htmlspecialchars($company); ?> · <?php echo htmlspecialchars($sub); ?></p>
            </div>
            <div class="meta no-print">
                <?php if (!empty($checkin)): ?><span class="badge">📅 ورود: <?php echo htmlspecialchars($checkin); ?></span><?php endif; ?>
                <?php if (!empty($checkout)): ?><span class="badge">📅 خروج: <?php echo htmlspecialchars($checkout); ?></span><?php endif; ?>
            </div>
            <div class="glow"></div>
        </div>

        <!-- Filter -->
        <div class="filter-bar no-print">
            <form method="get" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;width:100%;">
                <label><i class="bi bi-funnel"></i></label>
                <select name="hotel">
                    <option value="">همه هتل‌ها</option>
                    <?php foreach ($allHotels as $h): ?>
                    <option value="<?php echo htmlspecialchars($h->hotel_name); ?>" <?php echo $hotelFilter === $h->hotel_name ? 'selected' : ''; ?>><?php echo htmlspecialchars($h->hotel_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <label>ورود:</label>
                <input type="date" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
                <label>خروج:</label>
                <input type="date" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
                <button type="submit" class="btn-f btn-search"><i class="bi bi-search"></i> جستجو</button>
                <a href="<?php echo $config['url'] ?? ''; ?>/hotel-rates/display" class="btn-f btn-clear"><i class="bi bi-x-circle"></i> پاک</a>
                <button type="button" class="btn-f btn-print" onclick="window.print()"><i class="bi bi-printer"></i> چاپ</button>
            </form>
        </div>

        <?php if (empty($hotels)): ?>
        <div class="empty">
            <div class="ico">📋</div>
            <p>نرخنامه‌ای ثبت نشده است</p>
        </div>
        <?php else: ?>
        <?php foreach ($hotels as $hotel): ?>
        <div class="hotel">
            <div class="hotel-head">
                <div class="hotel-logo">🏨</div>
                <div class="hotel-info">
                    <h2><?php echo htmlspecialchars($hotel->hotel_name); ?></h2>
                    <div class="sub-info">
                        <?php if ($hotel->star_rating): ?><span class="stars"><?php echo str_repeat('★', $hotel->star_rating); echo str_repeat('☆', 5 - $hotel->star_rating); ?></span><?php endif; ?>
                        <?php if ($hotel->city): ?><span>📍 <?php echo htmlspecialchars($hotel->city); ?></span><?php endif; ?>
                        <span><i class="bi bi-clock"></i> <?php echo count($ratesByHotel[$hotel->id] ?? []); ?> بازه زمانی</span>
                    </div>
                </div>
            </div>

            <?php
            $descTags = !empty($hotel->description) ? array_filter(array_map('trim', explode(',', $hotel->description))) : [];
            $facTags = !empty($hotel->facilities) ? array_filter(array_map('trim', explode(',', $hotel->facilities))) : [];
            if ($descTags || $facTags):
            ?>
            <div class="tag-row">
                <?php foreach ($descTags as $t): ?><span class="tag tag-desc"><?php echo htmlspecialchars($t); ?></span><?php endforeach; ?>
                <?php foreach ($facTags as $t): ?><span class="tag tag-fac"><i class="bi bi-check2"></i> <?php echo htmlspecialchars($t); ?></span><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($ratesByHotel[$hotel->id])): ?>
            <div class="rates">
                <?php foreach ($ratesByHotel[$hotel->id] as $rate): ?>
                <div class="rate-card">
                    <div class="rate-label">
                        <div class="room"><?php echo htmlspecialchars($rate->room_type); ?></div>
                        <?php if (!empty($rate->season_label)): ?><div class="season">☀️ <?php echo htmlspecialchars($rate->season_label); ?></div><?php endif; ?>
                    </div>
                    <div class="rate-body">
                        <div class="price-item">
                            <span class="label">از</span>
                            <span class="date-range"><?php echo \Core\JDate::displayDate($rate->date_from); ?></span>
                            <span class="label">تا</span>
                            <span class="date-range"><?php echo \Core\JDate::displayDate($rate->date_to); ?></span>
                        </div>
                        <?php if ($rate->price_ekht > 0): ?>
                        <div class="price-item">
                            <span class="label">🛏️ اقامت:</span>
                            <span class="val"><?php echo number_format($rate->price_ekht); ?> ت</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($rate->price_sobhaneh > 0): ?>
                        <div class="price-item">
                            <span class="label">🍳 اقامت+صبحانه:</span>
                            <span class="val"><?php echo number_format($rate->price_sobhaneh); ?> ت</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($rate->price_nahar > 0): ?>
                        <div class="price-item">
                            <span class="label">🥗 هافبرد:</span>
                            <span class="val best"><?php echo number_format($rate->price_nahar); ?> ت</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($rate->price_entekhabifulboard > 0): ?>
                        <div class="price-item">
                            <span class="label">🍽️ فولبرد انتخابی:</span>
                            <span class="val"><?php echo number_format($rate->price_entekhabifulboard); ?> ت</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($rate->price_fulboard_boufeh > 0): ?>
                        <div class="price-item">
                            <span class="label">🥂 فولبرد بوفه:</span>
                            <span class="val hot"><?php echo number_format($rate->price_fulboard_boufeh); ?> ت</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align:center;padding:16px;color:#d1d5db;font-size:12px;">
                <?php if ($checkin || $checkout): ?>
                نرخی برای بازه زمانی انتخاب شده یافت نشد
                <?php else: ?>
                نرخی ثبت نشده
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="footer">
            <?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?> — قیمت‌ها به تومان و ممکن است بدون اطلاع قبلی تغییر کند.
        </div>
    </div>
</body>
</html>