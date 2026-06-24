<?php $config = $GLOBALS['app_config']; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $chartColors = ['#4361ee','#7209b7','#06d6a0','#ffd166','#ef476f','#118ab2','#8338ec','#ff6b6b','#48bfe3','#56cfe1']; ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>گزارشات و تحلیل‌ها</h5>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo $config['url']; ?>/reports/sales" class="btn btn-outline-primary btn-sm"><i class="bi bi-cash me-1"></i>فروش</a>
        <a href="<?php echo $config['url']; ?>/reports/pipeline" class="btn btn-outline-primary btn-sm"><i class="bi bi-kanban me-1"></i>پایپ لاین</a>
        <a href="<?php echo $config['url']; ?>/reports/contacts" class="btn btn-outline-primary btn-sm"><i class="bi bi-people me-1"></i>مخاطبان</a>
        <button type="button" class="btn btn-warning btn-sm fw-bold" id="aiAnalyzeBtn" onclick="runAIAnalysis()">
            <i class="bi bi-robot me-1"></i>تحلیل با هوش مصنوعی
        </button>
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
        <div id="aiContent" style="display:none;direction:rtl;white-space:pre-wrap;line-height:2;font-size:14px;"></div>
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

    fetch('<?php echo $config['url']; ?>/ai/analyze', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
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
            content.textContent = data.analysis;
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
</script>
