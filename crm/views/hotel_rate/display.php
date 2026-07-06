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
        /* ===== PRINT ===== */
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background: #fff !important; padding: 0 !important; display: block !important; }
            .no-print, .bg-mesh, .bg-grid { display: none !important; }
            .page { box-shadow: none !important; max-width: 100% !important; border-radius: 0 !important; animation: none !important; }
            .hotel-section { page-break-inside: avoid !important; break-inside: avoid !important; }
            .rate-card { page-break-inside: avoid !important; break-inside: avoid !important; }
            .hotel-section .rates { page-break-inside: avoid !important; }
            @page { margin: 4mm; size: A4 landscape; }
        }

        /* ===== RESET ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { height: 100%; }
        body {
            font-family: Vazirmatn, Tahoma, sans-serif;
            min-height: 100vh; background: #f0f2f5;
            display: flex; align-items: center; justify-content: center;
            padding: 20px; position: relative; overflow-x: hidden;
        }

        /* ===== BG ===== */
        .bg-mesh {
            position: fixed; inset: 0; z-index: -2;
            background:
                radial-gradient(at 0% 0%, <?php echo $pc; ?>14 0%, transparent 50%),
                radial-gradient(at 100% 0%, #ec48990f 0%, transparent 50%),
                radial-gradient(at 50% 100%, <?php echo $sc; ?>0f 0%, transparent 50%),
                #f0f2f5;
        }
        .bg-grid {
            position: fixed; inset: 0; z-index: -1;
            background-image: linear-gradient(rgba(0,0,0,0.015) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.015) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* ===== PAGE ===== */
        .page {
            max-width: 960px; width: 100%; background: #fff;
            border-radius: 20px; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 40px rgba(0,0,0,0.08);
            animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(30px); } }

        /* ===== HEADER ===== */
        .hdr {
            background: linear-gradient(135deg, #111827, <?php echo $pc; ?>);
            padding: 24px 28px; position: relative; overflow: hidden;
        }
        .hdr::before {
            content: ''; position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");
        }
        .hdr-in { display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 2; }
        .hdr h1 { color: #fff; font-size: 20px; font-weight: 900; display: flex; align-items: center; gap: 10px; }
        .hdr h1 .i { width: 36px; height: 36px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .hdr p { color: rgba(255,255,255,0.5); font-size: 11px; margin-top: 3px; }
        .hdr .meta { display: flex; gap: 8px; }
        .hdr .badge { background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.8); padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; }
        .glow { position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, #a78bfa, #c084fc, #a78bfa, transparent); background-size: 200% 100%; animation: glow 4s linear infinite; }
        @keyframes glow { 0% { background-position: -100% 0; } 100% { background-position: 200% 0; } }

        /* ===== FILTER ===== */
        .flt {
            padding: 12px 24px; background: #fafbfc;
            border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .flt label { font-size: 11px; color: #6b7280; font-weight: 700; }
        .flt select, .flt input[type="text"] {
            padding: 6px 12px; border: 1.5px solid #e5e7eb; border-radius: 8px;
            font: inherit; font-size: 12px; background: #fff; transition: all 0.2s;
            min-width: 120px;
        }
        .flt select:focus, .flt input[type="text"]:focus {
            border-color: <?php echo $pc; ?>; box-shadow: 0 0 0 3px <?php echo $pc; ?>26; outline: none;
        }
        .bf { padding: 6px 14px; border-radius: 8px; border: none; font: inherit; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px; }
        .bf:hover { transform: translateY(-1px); }
        .bf-s { background: <?php echo $pc; ?>; color: #fff; }
        .bf-s:hover { box-shadow: 0 4px 12px <?php echo $pc; ?>4d; }
        .bf-c { background: #f3f4f6; color: #6b7280; text-decoration: none; }
        .bf-c:hover { background: #e5e7eb; }
        .bf-p { background: #111827; color: #fff; margin-right: auto; }
        .bf-p:hover { background: #1f2937; }

        /* ===== HOTEL SECTION ===== */
        .hotel-section { padding: 0 24px; }
        .hotel-section:first-child { padding-top: 20px; }
        .hotel-section + .hotel-section { border-top: 1px solid #f3f4f6; }
        .hotel-head { display: flex; align-items: center; gap: 14px; padding: 18px 0 12px; }
        .hotel-ico {
            width: 48px; height: 48px; border-radius: 14px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 20px; flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(251,191,36,0.3);
        }
        .hotel-info h2 { font-size: 17px; font-weight: 800; color: #111827; }
        .hotel-info .si { display: flex; gap: 12px; margin-top: 2px; }
        .hotel-info .si span { font-size: 11px; color: #9ca3af; display: flex; align-items: center; gap: 3px; }
        .hotel-info .si .st { color: #f59e0b; }
        .tags { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 12px; }
        .tag { padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; transition: all 0.2s; cursor: default; }
        .tag:hover { transform: translateY(-1px); }
        .tag-d { background: <?php echo $pc; ?>0f; color: <?php echo $pc; ?>; border: 1px solid <?php echo $pc; ?>33; }
        .tag-f { background: <?php echo $sc; ?>0f; color: <?php echo $sc; ?>; border: 1px solid <?php echo $sc; ?>33; }

        /* ===== RATE CARDS ===== */
        .rates { display: flex; flex-direction: column; gap: 8px; padding-bottom: 20px; }
        .rc {
            display: grid; grid-template-columns: 140px 1fr;
            border: 1.5px solid #f3f4f6; border-radius: 12px; overflow: hidden;
            transition: all 0.3s; background: #fff;
        }
        .rc:hover { border-color: <?php echo $pc; ?>40; box-shadow: 0 4px 16px <?php echo $pc; ?>14; transform: translateX(-2px); }
        .rl {
            background: #f9fafb; padding: 12px 14px;
            display: flex; flex-direction: column; justify-content: center;
            border-left: 1.5px solid #f3f4f6;
        }
        .rc:hover .rl { border-left-color: <?php echo $pc; ?>40; }
        .rl .rm { font-size: 13px; font-weight: 800; color: #1f2937; }
        .rl .sn { font-size: 10px; color: #9ca3af; margin-top: 2px; }
        .rb { padding: 10px 16px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .pi {
            display: flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: 8px;
            background: #f9fafb; border: 1px solid #f3f4f6;
            transition: all 0.2s; font-size: 11px;
        }
        .pi:hover { background: <?php echo $pc; ?>0a; border-color: <?php echo $pc; ?>33; }
        .pi .lb { color: #6b7280; font-weight: 500; }
        .pi .vl { font-weight: 800; color: #111827; font-family: 'Courier New', monospace; direction: ltr; }
        .pi .dr { font-size: 9px; color: #9ca3af; direction: ltr; }

        /* ===== EMPTY ===== */
        .empty { text-align: center; padding: 60px 20px; }
        .empty .ei { width: 64px; height: 64px; margin: 0 auto 16px; background: #f3f4f6; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; color: #9ca3af; }
        .empty p { color: #9ca3af; font-size: 14px; }

        /* ===== FOOTER ===== */
        .ft { padding: 14px 24px; text-align: center; background: #fafbfc; border-top: 1px solid #f3f4f6; font-size: 10px; color: #9ca3af; }

        /* Jalali datepicker override */
        .datepicker-plot-area { z-index: 9999 !important; }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="bg-grid"></div>

    <div class="page">
        <div class="hdr">
            <div class="hdr-in">
                <div>
                    <h1><span class="i">🏨</span> نرخنامه هتل‌ها</h1>
                    <p><?php echo htmlspecialchars($company); ?> · <?php echo htmlspecialchars($sub); ?></p>
                </div>
                <div class="meta no-print">
                    <?php if (!empty($checkin)): ?><span class="badge">📅 ورود: <?php echo htmlspecialchars($checkin); ?></span><?php endif; ?>
                    <?php if (!empty($checkout)): ?><span class="badge">📅 خروج: <?php echo htmlspecialchars($checkout); ?></span><?php endif; ?>
                </div>
            </div>
            <div class="glow"></div>
        </div>

        <div class="flt no-print">
            <form method="get" id="filterForm" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;width:100%;">
                <label><i class="bi bi-funnel"></i></label>
                <select name="hotel">
                    <option value="">همه هتل‌ها</option>
                    <?php foreach ($allHotels as $h): ?>
                    <option value="<?php echo htmlspecialchars($h->hotel_name); ?>" <?php echo $hotelFilter === $h->hotel_name ? 'selected' : ''; ?>><?php echo htmlspecialchars($h->hotel_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <label>ورود:</label>
                <input type="text" name="checkin" id="flt_checkin" value="<?php echo htmlspecialchars($checkin); ?>" placeholder="۱۴۰۴/۰۴/۱۵" autocomplete="off" style="width:120px;">
                <label>خروج:</label>
                <input type="text" name="checkout" id="flt_checkout" value="<?php echo htmlspecialchars($checkout); ?>" placeholder="۱۴۰۴/۰۴/۲۰" autocomplete="off" style="width:120px;">
                <button type="submit" class="bf bf-s"><i class="bi bi-search"></i> جستجو</button>
                <a href="<?php echo $config['url'] ?? ''; ?>/hotel-rates/display" class="bf bf-c"><i class="bi bi-x-circle"></i> پاک</a>
                <button type="button" class="bf bf-p" onclick="window.print()"><i class="bi bi-printer"></i> چاپ</button>
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
                <?php foreach ($descTags as $t): ?><span class="tag tag-d"><?php echo htmlspecialchars($t); ?></span><?php endforeach; ?>
                <?php foreach ($facTags as $t): ?><span class="tag tag-f"><i class="bi bi-check2"></i> <?php echo htmlspecialchars($t); ?></span><?php endforeach; ?>
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
                        <div class="pi" style="background:<?php echo $sc; ?>0a;border-color:<?php echo $sc; ?>33;"><span class="lb">🥗 هافبرد:</span><span class="vl" style="color:<?php echo $sc; ?>;"><?php echo number_format($rate->price_nahar); ?> ت</span></div>
                        <?php endif; ?>
                        <?php if ($rate->price_entekhabifulboard > 0): ?>
                        <div class="pi"><span class="lb">🍽️ فولبرد انتخابی:</span><span class="vl"><?php echo number_format($rate->price_entekhabifulboard); ?> ت</span></div>
                        <?php endif; ?>
                        <?php if ($rate->price_fulboard_boufeh > 0): ?>
                        <div class="pi" style="background:#fef2f2;border-color:#fecaca;"><span class="lb">🥂 فولبرد بوفه:</span><span class="vl" style="color:#dc2626;"><?php echo number_format($rate->price_fulboard_boufeh); ?> ت</span></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align:center;padding:16px;color:#d1d5db;font-size:12px;">
                <?php echo ($checkin || $checkout) ? 'نرخی برای بازه زمانی انتخاب شده یافت نشد' : 'نرخی ثبت نشده'; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="ft"><?php echo htmlspecialchars($company); ?> | <?php echo htmlspecialchars($sub); ?> — قیمت‌ها به تومان و ممکن است بدون اطلاع قبلی تغییر کند.</div>
    </div>

    <!-- jQuery + Jalali Datepicker -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <script>
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.pDatepicker !== 'undefined') {
        var dpConfig = {
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false,
            calendar: { persian: { locale: 'fa' } },
            onSelect: function(unix) {
                var d = new Date(unix);
                var g = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                jQuery(this.inputEl).attr('data-gregorian', g);
            }
        };
        jQuery('#flt_checkin').pDatepicker(dpConfig);
        jQuery('#flt_checkout').pDatepicker(dpConfig);

        // Convert initial Gregorian values to Jalali display
        function toJalali(gDate) {
            if (!gDate) return '';
            var parts = gDate.split('-');
            if (parts.length !== 3) return gDate;
            try {
                var pd = new persianDate();
                pd.toCalendar('persian');
                var j = pd.convert(new Date(parseInt(parts[0]), parseInt(parts[1])-1, parseInt(parts[2])));
                return j.year() + '/' + String(j.month()).padStart(2,'0') + '/' + String(j.date()).padStart(2,'0');
            } catch(e) { return gDate; }
        }
        var ciVal = jQuery('#flt_checkin').val();
        var coVal = jQuery('#flt_checkout').val();
        if (ciVal && ciVal.indexOf('-') > -1) {
            jQuery('#flt_checkin').val(toJalali(ciVal)).attr('data-gregorian', ciVal);
        }
        if (coVal && coVal.indexOf('-') > -1) {
            jQuery('#flt_checkout').val(toJalali(coVal)).attr('data-gregorian', coVal);
        }

        // Before submit, set Gregorian values
        document.getElementById('filterForm').addEventListener('submit', function() {
            var ci = jQuery('#flt_checkin').attr('data-gregorian');
            var co = jQuery('#flt_checkout').attr('data-gregorian');
            if (ci) jQuery('#flt_checkin').val(ci);
            if (co) jQuery('#flt_checkout').val(co);
        });
    }
    </script>
</body>
</html>