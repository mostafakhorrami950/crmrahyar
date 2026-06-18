<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>📊 گزارشات و تحلیل‌ها</h5>
    <div style="display:flex;gap:8px;">
        <a href="<?php echo $config['url']; ?>/reports/sales" class="btn btn-secondary btn-sm">💰 فروش</a>
        <a href="<?php echo $config['url']; ?>/reports/pipeline" class="btn btn-secondary btn-sm">🔄 پایپ لاین</a>
        <a href="<?php echo $config['url']; ?>/reports/contacts" class="btn btn-secondary btn-sm">👥 مخاطبان</a>
        <a href="<?php echo $config['url']; ?>/activities" class="btn btn-secondary btn-sm">📅 فعالیت‌ها</a>
    </div>
</div>

<!-- KPI Cards -->
<div class="stats-row" style="margin-bottom:20px;">
    <div class="stat-box" style="background:linear-gradient(135deg,#667eea,#764ba2);">
        <div class="stat-value"><?php echo number_format($totalDeals->count ?? 0); ?></div>
        <div class="stat-label">💼 کل معاملات</div>
        <div style="font-size:12px;opacity:0.8;margin-top:4px;"><?php echo number_format($totalDeals->total ?? 0); ?> تومان</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#10B981,#059669);">
        <div class="stat-value"><?php echo number_format($wonDeals->count ?? 0); ?></div>
        <div class="stat-label">✅ موفق</div>
        <div style="font-size:12px;opacity:0.8;margin-top:4px;"><?php echo number_format($wonDeals->total ?? 0); ?> تومان</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#EF4444,#DC2626);">
        <div class="stat-value"><?php echo number_format($lostDeals->count ?? 0); ?></div>
        <div class="stat-label">❌ ناموفق</div>
        <div style="font-size:12px;opacity:0.8;margin-top:4px;"><?php echo number_format($lostDeals->total ?? 0); ?> تومان</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
        <div class="stat-value"><?php echo number_format($openDeals->count ?? 0); ?></div>
        <div class="stat-label">⏳ در حال بررسی</div>
        <div style="font-size:12px;opacity:0.8;margin-top:4px;"><?php echo number_format($openDeals->total ?? 0); ?> تومان</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#8B5CF6,#6D28D9);">
        <div class="stat-value"><?php echo $conversionRate; ?>%</div>
        <div class="stat-label">📈 نرخ تبدیل</div>
    </div>
</div>

<!-- Second row KPIs -->
<div class="stats-row" style="margin-bottom:20px;">
    <div class="stat-box" style="background:linear-gradient(135deg,#3B82F6,#1D4ED8);">
        <div class="stat-value"><?php echo number_format($todayDeals->count ?? 0); ?></div>
        <div class="stat-label">📌 معاملات امروز</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#EC4899,#BE185D);">
        <div class="stat-value"><?php echo number_format($contactStats->total ?? 0); ?></div>
        <div class="stat-label">👥 مخاطبین</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#14B8A6,#0D9488);">
        <div class="stat-value"><?php echo number_format($paymentStats->successful ?? 0); ?></div>
        <div class="stat-label">💳 پرداخت موفق</div>
        <div style="font-size:12px;opacity:0.8;margin-top:4px;"><?php echo number_format($paymentStats->total_paid ?? 0); ?> تومان</div>
    </div>
    <div class="stat-box" style="background:linear-gradient(135deg,#06B6D4,#0891B2);">
        <div class="stat-value"><?php echo number_format($smsStats->sent ?? 0); ?>/<?php echo number_format($smsStats->total ?? 0); ?></div>
        <div class="stat-label">📱 پیامک موفق</div>
    </div>
</div>

<!-- Main Reports Grid -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

    <!-- Monthly Sales -->
    <div class="card" style="padding:0;border-radius:16px;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--gray-200);">
            <h5 style="margin:0;font-size:15px;font-weight:700;">📈 فروش ماهانه</h5>
        </div>
        <?php if (empty($monthlySales)): ?>
        <div style="text-align:center;padding:30px;color:var(--gray-400);">داده‌ای موجود نیست</div>
        <?php else: ?>
        <div style="max-height:300px;overflow-y:auto;">
            <?php 
            $maxSales = max(array_map(function($m){ return $m->total; }, $monthlySales));
            foreach ($monthlySales as $m): 
                $barWidth = $maxSales > 0 ? round(($m->total / $maxSales) * 100) : 0;
            ?>
            <div style="padding:8px 20px;border-bottom:1px solid var(--gray-100);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span style="font-size:13px;font-weight:600;"><?php echo $m->month; ?></span>
                    <span style="font-size:13px;font-weight:700;color:var(--primary);"><?php echo number_format($m->total); ?> ت</span>
                </div>
                <div style="background:var(--gray-100);border-radius:4px;height:8px;overflow:hidden;">
                    <div style="background:linear-gradient(90deg,#667eea,#764ba2);width:<?php echo $barWidth; ?>%;height:100%;border-radius:4px;"></div>
                </div>
                <span style="font-size:11px;color:var(--gray-400);"><?php echo $m->deals_count; ?> معامله</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pipeline Sales -->
    <div class="card" style="padding:0;border-radius:16px;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--gray-200);">
            <h5 style="margin:0;font-size:15px;font-weight:700;">🔄 فروش بر اساس پایپ لاین</h5>
        </div>
        <?php if (empty($salesByPipeline)): ?>
        <div style="text-align:center;padding:30px;color:var(--gray-400);">داده‌ای موجود نیست</div>
        <?php else: ?>
        <div style="max-height:300px;overflow-y:auto;">
            <?php 
            $colors = ['#667eea','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899'];
            $maxPipe = max(array_map(function($s){ return $s->total; }, $salesByPipeline));
            foreach ($salesByPipeline as $idx => $s): 
                $color = $colors[$idx % count($colors)];
                $barWidth = $maxPipe > 0 ? round(($s->total / $maxPipe) * 100) : 0;
            ?>
            <div style="padding:8px 20px;border-bottom:1px solid var(--gray-100);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span style="font-size:13px;font-weight:600;"><?php echo htmlspecialchars($s->name); ?></span>
                    <span style="font-size:13px;font-weight:700;color:<?php echo $color; ?>;"><?php echo number_format($s->total); ?> ت</span>
                </div>
                <div style="background:var(--gray-100);border-radius:4px;height:8px;overflow:hidden;">
                    <div style="background:<?php echo $color; ?>;width:<?php echo $barWidth; ?>%;height:100%;border-radius:4px;"></div>
                </div>
                <span style="font-size:11px;color:var(--gray-400);"><?php echo $s->deals_count; ?> معامله</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Users -->
    <div class="card" style="padding:0;border-radius:16px;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--gray-200);">
            <h5 style="margin:0;font-size:15px;font-weight:700;">🏆 کاربران برتر فروش</h5>
        </div>
        <?php if (empty($topUsers)): ?>
        <div style="text-align:center;padding:30px;color:var(--gray-400);">داده‌ای موجود نیست</div>
        <?php else: ?>
        <div style="max-height:300px;overflow-y:auto;">
            <?php 
            $medals = ['🥇','🥈','🥉'];
            foreach ($topUsers as $idx => $u): 
            ?>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 20px;border-bottom:1px solid var(--gray-100);">
                <span style="font-size:20px;"><?php echo $medals[$idx] ?? '👤'; ?></span>
                <div style="flex:1;">
                    <strong style="font-size:13px;"><?php echo htmlspecialchars($u->full_name); ?></strong>
                    <div style="font-size:11px;color:var(--gray-400);"><?php echo $u->deals_count; ?> معامله</div>
                </div>
                <strong style="color:#059669;font-size:14px;"><?php echo number_format($u->total); ?> ت</strong>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Deal Sources -->
    <div class="card" style="padding:0;border-radius:16px;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--gray-200);">
            <h5 style="margin:0;font-size:15px;font-weight:700;">🎯 منابع جذب معاملات</h5>
        </div>
        <?php if (empty($dealSources)): ?>
        <div style="text-align:center;padding:30px;color:var(--gray-400);">داده‌ای موجود نیست</div>
        <?php else: ?>
        <div style="max-height:300px;overflow-y:auto;">
            <?php 
            $totalSources = array_sum(array_map(function($s){ return $s->count; }, $dealSources));
            $sourceColors = ['#667eea','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#3B82F6'];
            foreach ($dealSources as $idx => $s): 
                $pct = $totalSources > 0 ? round(($s->count / $totalSources) * 100) : 0;
                $color = $sourceColors[$idx % count($sourceColors)];
            ?>
            <div style="display:flex;align-items:center;gap:10px;padding:8px 20px;border-bottom:1px solid var(--gray-100);">
                <div style="width:10px;height:10px;border-radius:50%;background:<?php echo $color; ?>;flex-shrink:0;"></div>
                <div style="flex:1;">
                    <span style="font-size:13px;"><?php echo htmlspecialchars($s->source ?: 'نامشخص'); ?></span>
                </div>
                <span style="font-size:12px;color:var(--gray-500);"><?php echo $s->count; ?> (<?php echo $pct; ?>%)</span>
                <span style="font-size:12px;font-weight:600;color:var(--gray-700);"><?php echo number_format($s->total); ?> ت</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Contact Categories -->
    <div class="card" style="padding:0;border-radius:16px;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--gray-200);">
            <h5 style="margin:0;font-size:15px;font-weight:700;">📂 دسته‌بندی مخاطبین</h5>
        </div>
        <?php if (empty($contactCategories)): ?>
        <div style="text-align:center;padding:30px;color:var(--gray-400);">داده‌ای موجود نیست</div>
        <?php else: ?>
        <div style="max-height:300px;overflow-y:auto;">
            <?php foreach ($contactCategories as $cat): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:8px 20px;border-bottom:1px solid var(--gray-100);">
                <span style="background:<?php echo htmlspecialchars($cat->color ?? '#6B7280'); ?>;color:white;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:600;"><?php echo htmlspecialchars($cat->name); ?></span>
                <div style="flex:1;">
                    <div style="background:var(--gray-100);border-radius:4px;height:6px;overflow:hidden;">
                        <div style="background:<?php echo htmlspecialchars($cat->color ?? '#6B7280'); ?>;width:<?php echo min(100, ($cat->count / max(1, $contactStats->total)) * 100); ?>%;height:100%;border-radius:4px;"></div>
                    </div>
                </div>
                <strong style="font-size:14px;color:var(--gray-700);"><?php echo $cat->count; ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Lost Reasons -->
    <div class="card" style="padding:0;border-radius:16px;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--gray-200);">
            <h5 style="margin:0;font-size:15px;font-weight:700;">❌ دلایل عدم موفقیت</h5>
        </div>
        <?php if (empty($lostReasons)): ?>
        <div style="text-align:center;padding:30px;color:var(--gray-400);">داده‌ای موجود نیست</div>
        <?php else: ?>
        <div style="max-height:300px;overflow-y:auto;">
            <?php foreach ($lostReasons as $l): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:8px 20px;border-bottom:1px solid var(--gray-100);">
                <span style="font-size:16px;">❌</span>
                <div style="flex:1;">
                    <span style="font-size:13px;"><?php echo htmlspecialchars($l->lost_reason); ?></span>
                </div>
                <span style="font-size:12px;color:var(--gray-500);"><?php echo $l->count; ?> مورد</span>
                <span style="font-size:12px;font-weight:600;color:#EF4444;"><?php echo number_format($l->total); ?> ت</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<style>
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:10px; }
.stat-box { color:white; padding:16px; border-radius:12px; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.stat-value { font-weight:800; font-size:26px; }
.stat-label { font-size:12px; opacity:0.9; }
</style>