<?php $config = $GLOBALS['app_config']; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Marked.js for Markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<?php $chartColors = ['#4361ee','#7209b7','#06d6a0','#ffd166','#ef476f','#118ab2','#8338ec','#ff6b6b','#48bfe3','#56cfe1']; ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>گزارشات و تحلیل‌ها</h5>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo $config['url']; ?>/reports/sales" class="btn btn-outline-primary btn-sm"><i class="bi bi-cash me-1"></i>فروش</a>
        <a href="<?php echo $config['url']; ?>/reports/pipeline" class="btn btn-outline-primary btn-sm"><i class="bi bi-kanban me-1"></i>پایپ لاین</a>
        <a href="<?php echo $config['url']; ?>/reports/contacts" class="btn btn-outline-primary btn-sm"><i class="bi bi-people me-1"></i>مخاطبان</a>
        <a href="<?php echo $config['url']; ?>/ai/history" class="btn btn-outline-info btn-sm"><i class="bi bi-clock-history me-1"></i>تاریخچه AI</a>
        <button type="button" class="btn btn-warning btn-sm fw-bold" id="aiAnalyzeBtn" onclick="runAIAnalysis()">
            <i class="bi bi-robot me-1"></i>تحلیل با هوش مصنوعی
        </button>
    </div>
</div>

<!-- AI Data Selection -->
<div class="card border-0 shadow-sm mb-4" id="aiDataSelector">
    <div class="card-body py-3">
        <!-- Date Range Row -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="fw-bold small text-muted"><i class="bi bi-calendar3 me-1"></i>بازه زمانی:</span>
            <input type="date" class="form-control form-control-sm" id="aiDateFrom" style="width:150px;" placeholder="از تاریخ">
            <span class="text-muted">تا</span>
            <input type="date" class="form-control form-control-sm" id="aiDateTo" style="width:150px;" placeholder="تا تاریخ">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('aiDateFrom').value='';document.getElementById('aiDateTo').value='';"><i class="bi bi-x-circle me-1"></i>پاک کردن</button>
        </div>
        <!-- Categories Row -->
        <div class="d-flex flex-wrap align-items-center gap-3">
            <span class="fw-bold small text-muted"><i class="bi bi-funnel me-1"></i>اطلاعات ارسالی:</span>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catDeals" value="deals" checked disabled>
                <label class="form-check-label small" for="catDeals">📊 معاملات</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catStages" value="stages" checked>
                <label class="form-check-label small" for="catStages">📋 مراحل</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catSources" value="sources" checked>
                <label class="form-check-label small" for="catSources">🔗 منابع</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catTrends" value="trends" checked>
                <label class="form-check-label small" for="catTrends">📈 روندها</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catPipelines" value="pipelines" checked>
                <label class="form-check-label small" for="catPipelines">🔀 پایپ‌لاین</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catUsers" value="users" checked>
                <label class="form-check-label small" for="catUsers">👥 کاربران</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catActivities" value="activities" checked>
                <label class="form-check-label small" for="catActivities">📅 فعالیت‌ها</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catTargets" value="targets">
                <label class="form-check-label small" for="catTargets">🎯 اهداف</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catWinReasons" value="win_reasons">
                <label class="form-check-label small" for="catWinReasons">🏆 دلایل موفقیت</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catLoss" value="loss_reasons">
                <label class="form-check-label small" for="catLoss">❌ دلایل باخت</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input ai-cat" type="checkbox" id="catContacts" value="contacts">
                <label class="form-check-label small" for="catContacts">👤 مخاطبان</label>
            </div>
        </div>
    </div>
</div>

<!-- AI Analysis Result Card -->
<div class="card border-0 shadow mb-4" id="aiResultCard" style="display:none;">
    <div class="card-header bg-gradient d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff; border-radius: var(--radius) var(--radius) 0 0;">
        <h6 class="mb-0 fw-bold"><i class="bi bi-robot me-2"></i>تحلیل هوش مصنوعی</h6>
        <div>
            <span class="badge bg-white bg-opacity-25 me-2" id="aiModelBadge"></span>
            <span class="badge bg-white bg-opacity-25" id="aiTimeBadge"></span>
        </div>
    </div>
    <div class="card-body p-4">
        <div id="aiLoading" style="display:none;" class="text-center py-5">
            <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
            <p class="text-muted fw-medium">در حال تحلیل اطلاعات با هوش مصنوعی...<br><small>این فرآیند ممکن است ۳۰ تا ۶۰ ثانیه طول بکشد</small></p>
        </div>
        <div id="aiError" class="alert alert-danger" style="display:none;"></div>
        <div id="aiContent" class="ai-markdown-content" style="display:none;direction:rtl;line-height:2;font-size:14px;"></div>
    </div>
</div>

<!-- Summary Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-lg-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-primary mb-1"><i class="bi bi-briefcase fs-4"></i></div>
            <div class="fw-bold fs-5"><?php echo number_format($totalDeals->count); ?></div>
            <small class="text-muted">کل معاملات</small>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-success mb-1"><i class="bi bi-check-circle fs-4"></i></div>
            <div class="fw-bold fs-5 text-success"><?php echo number_format($wonDeals->count); ?></div>
            <small class="text-muted">معاملات موفق</small>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-danger mb-1"><i class="bi bi-x-circle fs-4"></i></div>
            <div class="fw-bold fs-5 text-danger"><?php echo number_format($lostDeals->count); ?></div>
            <small class="text-muted">معاملات ناموفق</small>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-warning mb-1"><i class="bi bi-clock fs-4"></i></div>
            <div class="fw-bold fs-5 text-warning"><?php echo number_format($openDeals->count); ?></div>
            <small class="text-muted">در جریان</small>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-info mb-1"><i class="bi bi-percent fs-4"></i></div>
            <div class="fw-bold fs-5 text-info"><?php echo $conversionRate; ?>%</div>
            <small class="text-muted">نرخ تبدیل</small>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-secondary mb-1"><i class="bi bi-cash-stack fs-4"></i></div>
            <div class="fw-bold fs-5" style="font-size:14px!important;"><?php echo number_format($avgDealAmount->avg_amount); ?></div>
            <small class="text-muted">میانگین مبلغ</small>
        </div>
    </div>
</div>

<!-- Financial Summary -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-right:4px solid var(--success)!important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">مجموع فروش موفق</small>
                    <div class="fw-bold fs-5 text-success"><?php echo number_format($wonDeals->total); ?> تومان</div>
                </div>
                <i class="bi bi-arrow-up-circle text-success fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-right:4px solid var(--danger)!important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">مجموع معاملات از دست رفته</small>
                    <div class="fw-bold fs-5 text-danger"><?php echo number_format($lostDeals->total); ?> تومان</div>
                </div>
                <i class="bi bi-arrow-down-circle text-danger fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-right:4px solid var(--info)!important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">پرداخت‌های موفق</small>
                    <div class="fw-bold fs-5 text-info"><?php echo number_format($paymentStats->total_paid ?? 0); ?> تومان</div>
                    <small class="text-muted"><?php echo number_format($paymentStats->successful ?? 0); ?> پرداخت از <?php echo number_format($paymentStats->total ?? 0); ?></small>
                </div>
                <i class="bi bi-credit-card text-info fs-3"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row g-3 mb-4">
    <!-- Deal Status Pie Chart -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2"></i>وضعیت معاملات</h6></div>
            <div class="card-body" style="height:280px;"><canvas id="dealStatusChart"></canvas></div>
        </div>
    </div>
    <!-- Stage Distribution -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2"></i>توزیع مراحل</h6></div>
            <div class="card-body" style="height:280px;"><canvas id="stageChart"></canvas></div>
        </div>
    </div>
    <!-- Deal Sources -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2"></i>منابع معاملات</h6></div>
            <div class="card-body" style="height:280px;"><canvas id="sourceChart"></canvas></div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row g-3 mb-4">
    <!-- Monthly Deals Bar Chart -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>روند ماهانه معاملات (۶ ماه اخیر)</h6></div>
            <div class="card-body" style="height:300px;"><canvas id="monthlyChart"></canvas></div>
        </div>
    </div>
    <!-- Contact Categories -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>دسته‌بندی مخاطبان</h6></div>
            <div class="card-body" style="height:300px;"><canvas id="categoryChart"></canvas></div>
        </div>
    </div>
</div>

<!-- Charts Row 3 -->
<div class="row g-3 mb-4">
    <!-- Pipeline Sales -->
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-kanban me-2"></i>فروش بر اساس پایپ لاین</h6></div>
            <div class="card-body" style="height:280px;"><canvas id="pipelineChart"></canvas></div>
        </div>
    </div>
    <!-- Activity Types -->
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2"></i>فعالیت‌ها (۳۰ روز اخیر)</h6></div>
            <div class="card-body" style="height:280px;"><canvas id="activityChart"></canvas></div>
        </div>
    </div>
</div>

<!-- Top Users & Contacts Tables -->
<div class="row g-3 mb-4">
    <!-- Top Users -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>برترین کاربران</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>#</th><th>نام</th><th>معاملات</th><th>مجموع فروش</th></tr></thead>
                        <tbody>
                        <?php foreach ($topUsers as $i => $u): ?>
                        <tr>
                            <td><span class="badge bg-<?php echo $i < 3 ? 'warning' : 'secondary'; ?>"><?php echo $i + 1; ?></span></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($u->full_name); ?></td>
                            <td><?php echo number_format($u->deals_count); ?></td>
                            <td class="text-success fw-bold"><?php echo number_format($u->total); ?> ت</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topUsers)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">داده‌ای موجود نیست</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Top Contacts -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-star me-2 text-primary"></i>برترین مخاطبان</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>#</th><th>نام</th><th>معاملات</th><th>مجموع خرید</th></tr></thead>
                        <tbody>
                        <?php foreach ($topContacts as $i => $c): ?>
                        <tr>
                            <td><span class="badge bg-<?php echo $i < 3 ? 'primary' : 'secondary'; ?>"><?php echo $i + 1; ?></span></td>
                            <td>
                                <span class="fw-semibold"><?php echo htmlspecialchars($c->full_name); ?></span>
                                <?php if ($c->phone): ?><br><small class="text-muted" dir="ltr"><?php echo htmlspecialchars($c->phone); ?></small><?php endif; ?>
                            </td>
                            <td><?php echo number_format($c->deals_count); ?></td>
                            <td class="text-primary fw-bold"><?php echo number_format($c->total_amount); ?> ت</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topContacts)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">داده‌ای موجود نیست</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Extra Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-primary mb-1"><i class="bi bi-people fs-4"></i></div>
            <div class="fw-bold fs-5"><?php echo number_format($contactStats->total); ?></div>
            <small class="text-muted">کل مخاطبان</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-success mb-1"><i class="bi bi-envelope fs-4"></i></div>
            <div class="fw-bold fs-5"><?php echo number_format($smsStats->total ?? 0); ?></div>
            <small class="text-muted">پیامک ارسالی</small>
            <br><small class="text-success"><?php echo number_format($smsStats->sent ?? 0); ?> موفق</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-info mb-1"><i class="bi bi-airplane fs-4"></i></div>
            <div class="fw-bold fs-5"><?php echo number_format($travelStats->total ?? 0); ?></div>
            <small class="text-muted">سفرها</small>
            <br><small class="text-info"><?php echo number_format($travelStats->total_passengers ?? 0); ?> مسافر</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-warning mb-1"><i class="bi bi-calendar-day fs-4"></i></div>
            <div class="fw-bold fs-5"><?php echo number_format($todayDeals->count); ?></div>
            <small class="text-muted">معاملات امروز</small>
            <br><small class="text-success"><?php echo number_format($todayWon->count); ?> موفق</small>
        </div>
    </div>
</div>

<!-- Lost Reasons -->
<?php if (!empty($lostReasons)): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>دلایل عدم موفقیت</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>دلیل</th><th>تعداد</th><th>مجموع مبلغ</th><th>درصد</th></tr></thead>
                        <tbody>
                        <?php foreach ($lostReasons as $lr): 
                            $lrPct = $lostDeals->count > 0 ? round(($lr->count / $lostDeals->count) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($lr->lost_reason); ?></td>
                            <td><span class="badge bg-danger"><?php echo number_format($lr->count); ?></span></td>
                            <td class="text-danger"><?php echo number_format($lr->total); ?> ت</td>
                            <td>
                                <div class="progress" style="height:6px;width:80px;">
                                    <div class="progress-bar bg-danger" style="width:<?php echo $lrPct; ?>%"></div>
                                </div>
                                <small><?php echo $lrPct; ?>%</small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Color palette
var colors = ['#4361ee','#7209b7','#06d6a0','#ffd166','#ef476f','#118ab2','#8338ec','#ff6b6b','#48bfe3','#56cfe1'];
Chart.defaults.font.family = "'Vazirmatn', Tahoma, sans-serif";
Chart.defaults.font.size = 11;

// 1. Deal Status Pie
new Chart(document.getElementById('dealStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['موفق', 'ناموفق', 'در جریان'],
        datasets: [{
            data: [<?php echo $wonDeals->count; ?>, <?php echo $lostDeals->count; ?>, <?php echo $openDeals->count; ?>],
            backgroundColor: ['#06d6a0', '#ef476f', '#ffd166'],
            borderWidth: 0
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

// 2. Stage Distribution Bar
new Chart(document.getElementById('stageChart'), {
    type: 'bar',
    data: {
        labels: [<?php foreach ($stageDistribution as $s) echo "'" . htmlspecialchars($s->name) . "',"; ?>],
        datasets: [{
            label: 'تعداد معاملات',
            data: [<?php foreach ($stageDistribution as $s) echo $s->count . ','; ?>],
            backgroundColor: [<?php foreach ($stageDistribution as $s) echo "'" . ($s->color ?: '#4361ee') . "',"; ?>],
            borderRadius: 6
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

// 3. Deal Sources Doughnut
<?php $srcLabels = ''; $srcData = ''; $srcColors = ''; $si = 0; foreach ($dealSources as $src) { $srcLabels .= "'" . htmlspecialchars($src->source) . "',"; $srcData .= $src->count . ','; $srcColors .= "'" . $chartColors[$si % count($chartColors)] . "',"; $si++; } ?>
new Chart(document.getElementById('sourceChart'), {
    type: 'doughnut',
    data: {
        labels: [<?php echo $srcLabels; ?>],
        datasets: [{ data: [<?php echo $srcData; ?>], backgroundColor: [<?php echo $srcColors; ?>], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

// 4. Monthly Deals Stacked Bar
<?php $mLabels = ''; $mCreated = ''; $mWon = ''; $mLost = ''; foreach ($dealsByMonth as $m) { $mLabels .= "'" . $m->month . "',"; $mCreated .= $m->created . ','; $mWon .= $m->won . ','; $mLost .= $m->lost . ','; } ?>
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: [<?php echo $mLabels; ?>],
        datasets: [
            { label: 'ایجاد شده', data: [<?php echo $mCreated; ?>], backgroundColor: '#4361ee', borderRadius: 4 },
            { label: 'موفق', data: [<?php echo $mWon; ?>], backgroundColor: '#06d6a0', borderRadius: 4 },
            { label: 'ناموفق', data: [<?php echo $mLost; ?>], backgroundColor: '#ef476f', borderRadius: 4 }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});

// 5. Contact Categories Doughnut
<?php $catLabels = ''; $catData = ''; $catColors = ''; foreach ($contactCategories as $cc) { $catLabels .= "'" . htmlspecialchars($cc->name) . "',"; $catData .= $cc->count . ','; $catColors .= "'" . ($cc->color ?: '#4361ee') . "',"; } ?>
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: [<?php echo $catLabels ?: "''"; ?>],
        datasets: [{ data: [<?php echo $catData ?: '0'; ?>], backgroundColor: [<?php echo $catColors ?: "'#ccc'"; ?>], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

// 6. Pipeline Sales Horizontal Bar
<?php $pLabels = ''; $pData = ''; $pColors = ''; $pi = 0; foreach ($salesByPipeline as $p) { $pLabels .= "'" . htmlspecialchars($p->name) . "',"; $pData .= $p->total . ','; $pColors .= "'" . $chartColors[$pi % count($chartColors)] . "',"; $pi++; } ?>
new Chart(document.getElementById('pipelineChart'), {
    type: 'bar',
    data: {
        labels: [<?php echo $pLabels; ?>],
        datasets: [{ label: 'مجموع فروش (تومان)', data: [<?php echo $pData; ?>], backgroundColor: [<?php echo $pColors; ?>], borderRadius: 6 }]
    },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

// 7. Activity Types Polar Area
<?php $aLabels = ''; $aData = ''; $aColors = ''; $ai = 0; 
$activityTypeNames = ['note'=>'یادداشت','call'=>'تماس','meeting'=>'جلسه','email'=>'ایمیل','sms'=>'پیامک','follow_up'=>'پیگیری','other'=>'سایر'];
foreach ($activityStats as $a) { 
    $aLabels .= "'" . ($activityTypeNames[$a->type] ?? $a->type) . "',"; 
    $aData .= $a->count . ','; 
    $aColors .= "'" . $chartColors[$ai % count($chartColors)] . "',"; 
    $ai++; 
} ?>
new Chart(document.getElementById('activityChart'), {
    type: 'polarArea',
    data: {
        labels: [<?php echo $aLabels ?: "''"; ?>],
        datasets: [{ data: [<?php echo $aData ?: '0'; ?>], backgroundColor: [<?php echo $aColors ?: "'#ccc'"; ?>] }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

// ===== AI ANALYSIS =====
function runAIAnalysis() {
    var card = document.getElementById('aiResultCard');
    var loading = document.getElementById('aiLoading');
    var error = document.getElementById('aiError');
    var content = document.getElementById('aiContent');
    var btn = document.getElementById('aiAnalyzeBtn');

    card.style.display = 'block';
    loading.style.display = 'block';
    error.style.display = 'none';
    content.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>در حال تحلیل...';

    // Collect selected categories
    var cats = [];
    document.querySelectorAll('.ai-cat:checked').forEach(function(cb) { cats.push(cb.value); });
    
    var formData = new FormData();
    formData.append('categories', cats.join(','));
    
    // Date range
    var dateFrom = document.getElementById('aiDateFrom').value;
    var dateTo = document.getElementById('aiDateTo').value;
    if (dateFrom) formData.append('date_from', dateFrom);
    if (dateTo) formData.append('date_to', dateTo);
    
    fetch('<?php echo $config['url']; ?>/ai/analyze', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: formData
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        var ct = r.headers.get('content-type') || '';
        if (ct.indexOf('json') === -1) throw new Error('پاسخ نامعتبر - لطفاً صفحه را رفرش کنید');
        return r.json();
    })
    .then(function(data) {
        loading.style.display = 'none';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-robot me-1"></i>تحلیل با هوش مصنوعی';
        if (data.success) {
            try {
                content.innerHTML = marked.parse(data.analysis);
            } catch(e) {
                content.textContent = data.analysis;
            }
            content.style.display = 'block';
            document.getElementById('aiModelBadge').textContent = 'مدل: ' + (data.model || '');
            document.getElementById('aiTimeBadge').textContent = data.timestamp || '';
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            error.textContent = data.message || 'خطا در تحلیل';
            error.style.display = 'block';
        }
    })
    .catch(function(err) {
        loading.style.display = 'none';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-robot me-1"></i>تحلیل با هوش مصنوعی';
        error.textContent = err.message || 'خطا در ارتباط با سرور';
        error.style.display = 'block';
    });
}

// Markdown CSS styles for AI content
(function() {
    var style = document.createElement('style');
    style.textContent = '.ai-markdown-content h1,.ai-markdown-content h2,.ai-markdown-content h3{color:#1e293b;margin-top:1.2rem;margin-bottom:.6rem;font-weight:700}.ai-markdown-content h1{font-size:1.4rem;border-bottom:2px solid #e2e8f0;padding-bottom:.4rem}.ai-markdown-content h2{font-size:1.2rem;border-bottom:1px solid #e2e8f0;padding-bottom:.3rem}.ai-markdown-content h3{font-size:1.05rem}.ai-markdown-content ul,.ai-markdown-content ol{padding-right:1.5rem;padding-left:0}.ai-markdown-content li{margin-bottom:.4rem}.ai-markdown-content strong{color:#0f172a}.ai-markdown-content code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:13px;direction:ltr;display:inline-block}.ai-markdown-content pre{background:#1e293b;color:#e2e8f0;padding:1rem;border-radius:8px;overflow-x:auto;direction:ltr}.ai-markdown-content pre code{background:0 0;color:inherit}.ai-markdown-content blockquote{border-right:4px solid #667eea;padding:0.8rem 1rem;margin:1rem 0;color:#64748b;background:#f8fafc;border-radius:0 8px 8px 0}.ai-markdown-content table{width:100%;border-collapse:collapse;margin:1rem 0;font-size:13px}.ai-markdown-content th,.ai-markdown-content td{border:1px solid #e2e8f0;padding:8px 12px;text-align:right}.ai-markdown-content th{background:#f1f5f9;font-weight:600}.ai-markdown-content hr{border:none;border-top:2px solid #e2e8f0;margin:1.5rem 0}.ai-markdown-content p{margin-bottom:.6rem}';
    document.head.appendChild(style);
})();
</script>
