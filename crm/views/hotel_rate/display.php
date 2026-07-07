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
    $company = $invSet['invoice_company_name'] ?? 'علاءالدین سفیر اسمان';
    $sub = $invSet['invoice_subtitle'] ?? 'آژانس مسافرتی';
    $logo = $invSet['invoice_logo_url'] ?? '';
    $pc = $invSet['invoice_primary_color'] ?? '#4f46e5';
    $sc = $invSet['invoice_success_color'] ?? '#059669';
    ?>
    <style>
        /* ===== PRINT - Professional Office Style ===== */
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background: #fff !important; padding: 0 !important; display: block !important; font-size: 8pt !important; }
            .screen-only { display: none !important; }
            .print-only { display: block !important; }
            .print-wrap { max-width: 100%; margin: 0; padding: 4mm; }
            .print-accent { height: 3px; background: #1e3a5f; margin-bottom: 4mm; }
            .print-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 3mm; margin-bottom: 3mm; border-bottom: 2px solid #1e3a5f; }
            .print-header .ph-right h1 { font-size: 14pt; font-weight: 900; color: #1e3a5f; margin: 0; }
            .print-header .ph-right p { font-size: 8pt; color: #6b7280; margin: 1mm 0 0; }
            .print-header .ph-left { text-align: left; }
            .print-header .ph-left .co { font-size: 11pt; font-weight: 900; color: #1e3a5f; }
            .print-header .ph-left .sub { font-size: 7pt; color: #6b7280; }
            .print-meta { display: flex; flex-wrap: wrap; gap: 3px; margin-bottom: 3mm; padding-bottom: 2mm; border-bottom: 1px solid #e5e7eb; }
            .print-chip { display: inline-flex; align-items: center; gap: 2px; padding: 1px 6px; border-radius: 2px; font-size: 7pt; font-weight: 600; background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
            .print-hotel { page-break-inside: avoid; break-inside: avoid; margin-bottom: 4mm; }
            .print-hotel-name { font-size: 11pt; font-weight: 800; color: #1e3a5f; padding: 2mm 0; margin-bottom: 2mm; border-bottom: 1.5px solid #1e3a5f; }
            .print-hotel-name small { font-weight: 400; font-size: 8pt; color: #6b7280; }
            .print-tbl { width: 100%; border-collapse: collapse; font-size: 8pt; margin-bottom: 2mm; }
            .print-tbl th { background: #1e3a5f; color: #fff; padding: 2.5mm 3mm; border: 1px solid #1e3a5f; font-weight: 700; text-align: center; font-size: 7.5pt; }
            .print-tbl th:first-child { text-align: right; min-width: 70px; }
            .print-tbl td { padding: 2mm 3mm; border: 1px solid #d1d5db; text-align: center; font-size: 8pt; }
            .print-tbl td:first-child { text-align: right; font-weight: 700; }
            .print-tbl tr:nth-child(even) { background: #f9fafb; }
            .print-tbl tbody tr:last-child td { border-bottom: 2px solid #1e3a5f; }
            .print-tbl .price { font-family: 'Courier New', monospace; font-weight: 800; direction: ltr; color: #111827; }
            .print-tbl .empty-cell { color: #d1d5db; }
            .print-tags { display: flex; flex-wrap: wrap; gap: 3px; margin-bottom: 2mm; }
            .print-tag { padding: 1px 6px; border-radius: 2px; font-size: 6.5pt; font-weight: 600; border: 1px solid #d1d5db; background: #f9fafb; color: #4b5563; }
            .print-footer { text-align: center; border-top: 2px solid #1e3a5f; padding-top: 2mm; margin-top: 4mm; font-size: 7pt; color: #9ca3af; }
            @page { margin: 6mm 8mm; size: A4 landscape; }
        }

        /* ===== SCREEN ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .print-only { display: none; }
        html { height: 100%; }
        body {
            font-family: Vazirmatn, Tahoma, sans-serif;
            min-height: 100vh; background: #f0f2f5;
            display: flex; align-items: center; justify-content: center;
            padding: 20px; position: relative; overflow-x: hidden;
        }

        .bg-mesh {
            position: fixed; inset: 0; z-index: -2;
            background:
                radial-gradient(at 0% 0%, #3b82f6 0%, transparent 50%),
                radial-gradient(at 100% 100%, #8b5cf6 0%, transparent 50%),
                radial-gradient(at 50% 50%, #06b6d4 0%, transparent 40%),
                #f0f2f5;
        }
        .bg-grid {
            position: fixed; inset: 0; z-index: -1;
            background-image: linear-gradient(rgba(0,0,0,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.02) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .page {
            max-width: 960px; width: 100%; background: #fff;
            border-radius: 20px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 20px 60px rgba(0,0,0,0.1);
            animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(30px); } }

        /* Header */
        .hdr {
            background: linear-gradient(135deg, #1e3a5f, #2563eb, #7c3aed);
            padding: 28px 30px; position: relative; overflow: hidden;
        }
        .hdr::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1), transparent); }
        .hdr-in { display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 2; }
        .hdr h1 { color: #fff; font-size: 22px; font-weight: 900; display: flex; align-items: center; gap: 10px; }
        .hdr h1 .ico { width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; backdrop-filter: blur(8px); }
        .hdr p { color: rgba(255,255,255,0.6); font-size: 12px; margin-top: 4px; }
        .hdr .meta { display: flex; gap: 8px; }
        .hdr .badge { background: rgba(255,255,255,0.18); color: #fff; padding: 5px 14px; border-radius: 10px; font-size: 11px; font-weight: 600; backdrop-filter: blur(8px); }
        .glow { position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #06b6d4, #3b82f6, #8b5cf6, #ec4899, #f59e0b, #06b6d4); background-size: 300% 100%; animation: glow 5s linear infinite; }
        @keyframes glow { 0% { background-position: 0% 0; } 100% { background-position: 300% 0; } }

        /* Filter */
        .flt { padding: 14px 24px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .flt label { font-size: 12px; color: #475569; font-weight: 700; }
        .flt select, .flt input[type="text"] { padding: 7px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font: inherit; font-size: 13px; background: #fff; transition: all 0.2s; min-width: 130px; }
        .flt select:focus, .flt input[type="text"]:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.2); outline: none; }
        .bf { padding: 8px 18px; border-radius: 10px; border: none; font: inherit; font-size: 13px; font-weight: 800; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .bf:hover { transform: translateY(-2px); }
        .bf-s { background: linear-gradient(135deg, #2563eb, #7c3aed); color: #fff; box-shadow: 0 2px 8px rgba(37,99,235,0.3); }
        .bf-s:hover { box-shadow: 0 6px 20px rgba(37,99,235,0.4); }
        .bf-c { background: #f1f5f9; color: #475569; text-decoration: none; border: 2px solid #e2e8f0; }
        .bf-c:hover { background: #e2e8f0; }
        .bf-p { background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; margin-right: auto; box-shadow: 0 2px 8px rgba(15,23,42,0.3); }
        .bf-p:hover { box-shadow: 0 6px 20px rgba(15,23,42,0.4); }

        /* Hotel */
        .hotel-section { padding: 0 24px; }
        .hotel-section:first-child { padding-top: 24px; }
        .hotel-section + .hotel-section { border-top: 2px solid #e2e8f0; margin-top: 4px; }
        .hotel-head { display: flex; align-items: center; gap: 16px; padding: 20px 0 14px; }
        .hotel-ico { width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(135deg, #f59e0b, #ef4444); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 16px rgba(245,158,11,0.35); }
        .hotel-info h2 { font-size: 18px; font-weight: 900; color: #0f172a; }
        .hotel-info .si { display: flex; gap: 14px; margin-top: 3px; }
        .hotel-info .si span { font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 4px; font-weight: 500; }
        .hotel-info .si .st { color: #f59e0b; font-size: 13px; }
        .tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
        .tag { padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; cursor: default; }
        .tag:hover { transform: translateY(-2px); }
        .tag-d { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .tag-f { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }

        /* Rates */
        .rates { display: flex; flex-direction: column; gap: 10px; padding-bottom: 24px; }
        .rc { display: grid; grid-template-columns: 150px 1fr; border: 2px solid #e2e8f0; border-radius: 14px; overflow: hidden; transition: all 0.3s; background: #fff; }
        .rc:hover { border-color: #3b82f6; box-shadow: 0 8px 24px rgba(59,130,246,0.12); transform: translateX(-3px); }
        .rl { background: linear-gradient(180deg, #f8fafc, #f1f5f9); padding: 14px 16px; display: flex; flex-direction: column; justify-content: center; border-left: 2px solid #e2e8f0; }
        .rc:hover .rl { border-left-color: #3b82f6; background: linear-gradient(180deg, #eff6ff, #dbeafe); }
        .rl .rm { font-size: 14px; font-weight: 900; color: #0f172a; }
        .rl .sn { font-size: 11px; color: #64748b; margin-top: 3px; font-weight: 500; }
        .rb { padding: 12px 18px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .pi { display: flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 10px; background: #f8fafc; border: 1.5px solid #e2e8f0; transition: all 0.2s; font-size: 12px; }
        .pi:hover { background: #eff6ff; border-color: #93c5fd; }
        .pi .lb { color: #475569; font-weight: 600; }
        .pi .vl { font-weight: 900; color: #0f172a; font-family: 'Courier New', monospace; direction: ltr; }
        .pi .dr { font-size: 10px; color: #64748b; direction: ltr; font-weight: 600; }

        .pi-hb { background: #ecfdf5; border-color: #6ee7b7; }
        .pi-hb:hover { background: #d1fae5; border-color: #34d399; }
        .pi-hb .vl { color: #059669; }
        .pi-fb { background: #fef2f2; border-color: #fca5a5; }
        .pi-fb:hover { background: #fee2e2; border-color: #f87171; }
        .pi-fb .vl { color: #dc2626; }

        .empty { text-align: center; padding: 60px 20px; }
        .empty .ei { width: 72px; height: 72px; margin: 0 auto 16px; background: #f1f5f9; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #94a3b8; border: 2px solid #e2e8f0; }
        .empty p { color: #94a3b8; font-size: 15px; font-weight: 600; }

        .ft { padding: 16px 24px; text-align: center; background: #f8fafc; border-top: 2px solid #e2e8f0; font-size: 11px; color: #94a3b8; font-weight: 500; }

        .datepicker-plot-area { z-index: 9999 !important; }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="bg-grid"></div>

    <!-- ===== PRINT VERSION - Professional Office Style ===== -->
    <div class="print-only">
        <div class="print-wrap">
            <div class="print-accent"></div>
            <div class="print-header">
                <div class="ph-right">
                    <h1>📋 نرخنامه هتل‌ها</h1>
                    <p><?php echo \Core\JDate::displayDate(date('Y-m-d')); ?></p>
                </div>
                <div class="ph-left">
                    <div class="co"><?php echo htmlspecialchars($company); ?></div>
                    <div class="sub"><?php echo htmlspecialchars($sub); ?></div>
                </div>
            </div>

            <?php if (!empty($hotels)): ?>
            <?php foreach ($hotels as $hotel): ?>
            <div class="print-hotel">
                <div class="print-hotel-name">
                    <?php echo htmlspecialchars($hotel->hotel_name); ?>
                    <?php if ($hotel->star_rating): ?> <small>(<?php echo str_repeat('★', $hotel->star_rating); ?>)</small><?php endif; ?>
                    <?php if ($hotel->city): ?> — <small>📍 <?php echo htmlspecialchars($hotel->city); ?></small><?php endif; ?>
                </div>
                <?php
                $descTags = !empty($hotel->description) ? array_filter(array_map('trim', explode(',', $hotel->description))) : [];
                $facTags = !empty($hotel->facilities) ? array_filter(array_map('trim', explode(',', $hotel->facilities))) : [];
                if ($descTags || $facTags):
                ?>
                <div class="print-tags">
                    <?php foreach ($descTags as $t): ?><span class="print-tag"><?php echo htmlspecialchars($t); ?></span><?php endforeach; ?>
                    <?php foreach ($facTags as $t): ?><span class="print-tag" style="background:#eff6ff;border-color:#93c5fd;color:#1e40af;"><?php echo htmlspecialchars($t); ?></span><?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($ratesByHotel[$hotel->id])): ?>
                <table class="print-tbl">
                    <thead>
                        <tr>
                            <th>نوع اتاق</th>
                            <th>از تاریخ</th>
                            <th>تا تاریخ</th>
                            <th>فصل</th>
                            <th>اقامت (تومان)</th>
                            <th>اقامت+صبحانه (تومان)</th>
                            <th>هافبرد (تومان)</th>
                            <th>فولبرد انتخابی (تومان)</th>
                            <th>فولبرد بوفه (تومان)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ratesByHotel[$hotel->id] as $rate): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rate->room_type); ?></td>
                            <td dir="ltr"><?php echo \Core\JDate::displayDate($rate->date_from); ?></td>
                            <td dir="ltr"><?php echo \Core\JDate::displayDate($rate->date_to); ?></td>
                            <td><?php echo !empty($rate->season_label) ? htmlspecialchars($rate->season_label) : '-'; ?></td>
                            <td class="price"><?php echo $rate->price_ekht > 0 ? number_format($rate->price_ekht) : '-'; ?></td>
                            <td class="price"><?php echo $rate->price_sobhaneh > 0 ? number_format($rate->price_sobhaneh) : '-'; ?></td>
                            <td class="price"><?php echo $rate->price_nahar > 0 ? number_format($rate->price_nahar) : '-'; ?></td>
                            <td class="price"><?php echo $rate->price_entekhabifulboard > 0 ? number_format($rate->price_entekhabifulboard) : '-'; ?></td>
                            <td class="price"><?php echo $rate->price_fulboard_boufeh > 0 ? number_format($rate->price_fulboard_boufeh) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align:center;padding:4mm;color:#9ca3af;font-size:8pt;">نرخی ثبت نشده</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <div class="print-footer">
                <?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?> — قیمت‌ها به تومان و ممکن است بدون اطلاع قبلی تغییر کند.
            </div>
        </div>
    </div>

    <!-- ===== SCREEN VERSION ===== -->
    <div class="screen-only">
        <div class="page">
            <div class="hdr">
                <div class="hdr-in">
                    <div>
                        <h1><span class="ico">🏨</span> نرخنامه هتل‌ها</h1>
                        <p><?php echo htmlspecialchars($company); ?> · <?php echo htmlspecialchars($sub); ?></p>
                    </div>
                    <div class="meta">
                        <?php if (!empty($checkin)): ?><span class="badge">📅 ورود: <?php echo htmlspecialchars($checkin); ?></span><?php endif; ?>
                        <?php if (!empty($checkout)): ?><span class="badge">📅 خروج: <?php echo htmlspecialchars($checkout); ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="glow"></div>
            </div>

            <div class="flt">
                <form method="get" id="filterForm" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;width:100%;">
                    <label><i class="bi bi-funnel-fill"></i></label>
                    <select name="hotel">
                        <option value="">همه هتل‌ها</option>
                        <?php foreach ($allHotels as $h): ?>
                        <option value="<?php echo htmlspecialchars($h->hotel_name); ?>" <?php echo $hotelFilter === $h->hotel_name ? 'selected' : ''; ?>><?php echo htmlspecialchars($h->hotel_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>ورود:</label>
                    <input type="text" name="checkin" id="flt_checkin" value="<?php echo htmlspecialchars($checkin); ?>" placeholder="۱۴۰۴/۰۴/۱۵" autocomplete="off" style="width:130px;">
                    <label>خروج:</label>
                    <input type="text" name="checkout" id="flt_checkout" value="<?php echo htmlspecialchars($checkout); ?>" placeholder="۱۴۰۴/۰۴/۲۰" autocomplete="off" style="width:130px;">
                    <button type="submit" class="bf bf-s"><i class="bi bi-search"></i> جستجو</button>
                    <a href="<?php echo $config['url'] ?? ''; ?>/hotel-rates/display" class="bf bf-c"><i class="bi bi-x-circle"></i> پاک</a>
                    <button type="button" class="bf bf-p" onclick="window.print()"><i class="bi bi-printer-fill"></i> چاپ</button>
                </form>
            </div>

            <?php if (empty($hotels)): ?>
            <div class="empty"><div class="ei">📋</div><p>نرخنامه‌ای ثبت نشده است</p></div>
            <?php else: ?>
            <?php foreach ($hotels as $hotel): ?>
            <div class="hotel-section">
                <div class="hotel-head">
                    <div class="hotel-ico">🏨</div>
                    <div class="hotel-info">
                        <h2><?php echo htmlspecialchars($hotel->hotel_name); ?></h2>
                        <div class="si">
                            <?php if ($hotel->star_rating): ?><span class="st"><?php echo str_repeat('★', $hotel->star_rating); echo str_repeat('☆', 5 - $hotel->star_rating); ?></span><?php endif; ?>
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
                <div class="tags">
                    <?php foreach ($descTags as $t): ?><span class="tag tag-d"><i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($t); ?></span><?php endforeach; ?>
                    <?php foreach ($facTags as $t): ?><span class="tag tag-f"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($t); ?></span><?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($ratesByHotel[$hotel->id])): ?>
                <div class="rates">
                    <?php foreach ($ratesByHotel[$hotel->id] as $rate): ?>
                    <div class="rc">
                        <div class="rl">
                            <div class="rm"><?php echo htmlspecialchars($rate->room_type); ?></div>
                            <?php if (!empty($rate->season_label)): ?><div class="sn">☀️ <?php echo htmlspecialchars($rate->season_label); ?></div><?php endif; ?>
                        </div>
                        <div class="rb">
                            <div class="pi">
                                <span class="lb">از</span>
                                <span class="dr"><?php echo \Core\JDate::displayDate($rate->date_from); ?></span>
                                <span class="lb">تا</span>
                                <span class="dr"><?php echo \Core\JDate::displayDate($rate->date_to); ?></span>
                            </div>
                            <?php if ($rate->price_ekht > 0): ?>
                            <div class="pi"><span class="lb">🛏️ اقامت:</span><span class="vl"><?php echo number_format($rate->price_ekht); ?> ت</span></div>
                            <?php endif; ?>
                            <?php if ($rate->price_sobhaneh > 0): ?>
                            <div class="pi"><span class="lb">🍳 اقامت+صبحانه:</span><span class="vl"><?php echo number_format($rate->price_sobhaneh); ?> ت</span></div>
                            <?php endif; ?>
                            <?php if ($rate->price_nahar > 0): ?>
                            <div class="pi pi-hb"><span class="lb">🥗 هافبرد:</span><span class="vl"><?php echo number_format($rate->price_nahar); ?> ت</span></div>
                            <?php endif; ?>
                            <?php if ($rate->price_entekhabifulboard > 0): ?>
                            <div class="pi"><span class="lb">🍽️ فولبرد انتخابی:</span><span class="vl"><?php echo number_format($rate->price_entekhabifulboard); ?> ت</span></div>
                            <?php endif; ?>
                            <?php if ($rate->price_fulboard_boufeh > 0): ?>
                            <div class="pi pi-fb"><span class="lb">🥂 فولبرد بوفه:</span><span class="vl"><?php echo number_format($rate->price_fulboard_boufeh); ?> ت</span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:20px;color:#cbd5e1;font-size:13px;font-weight:600;">
                    <?php echo ($checkin || $checkout) ? 'نرخی برای بازه زمانی انتخاب شده یافت نشد' : 'نرخی ثبت نشده'; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <div class="ft"><?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?> — قیمت‌ها به تومان و ممکن است بدون اطلاع قبلی تغییر کند.</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <script>
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.pDatepicker !== 'undefined') {
        var dpConfig = {
            format: 'YYYY/MM/DD', autoClose: true, initialValue: false,
            calendar: { persian: { locale: 'fa' } },
            onSelect: function(unix) {
                var d = new Date(unix);
                var g = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                jQuery(this.inputEl).attr('data-gregorian', g);
            }
        };
        jQuery('#flt_checkin').pDatepicker(dpConfig);
        jQuery('#flt_checkout').pDatepicker(dpConfig);
        function toJalali(gDate) {
            if (!gDate) return '';
            var p = gDate.split('-'); if (p.length !== 3) return gDate;
            try { var pd = new persianDate(); pd.toCalendar('persian'); var j = pd.convert(new Date(+p[0], +p[1]-1, +p[2])); return j.year()+'/'+String(j.month()).padStart(2,'0')+'/'+String(j.date()).padStart(2,'0'); } catch(e) { return gDate; }
        }
        var ci = jQuery('#flt_checkin').val(), co = jQuery('#flt_checkout').val();
        if (ci && ci.indexOf('-') > -1) jQuery('#flt_checkin').val(toJalali(ci)).attr('data-gregorian', ci);
        if (co && co.indexOf('-') > -1) jQuery('#flt_checkout').val(toJalali(co)).attr('data-gregorian', co);
        document.getElementById('filterForm').addEventListener('submit', function() {
            var c = jQuery('#flt_checkin').attr('data-gregorian'), o = jQuery('#flt_checkout').attr('data-gregorian');
            if (c) jQuery('#flt_checkin').val(c); if (o) jQuery('#flt_checkout').val(o);
        });
    }
    </script>
</body>
</html>