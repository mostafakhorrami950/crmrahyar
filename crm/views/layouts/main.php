<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $title ?? 'CRM Travel Agency'; ?> | CRM آژانس مسافرتی</title>
    <!-- Bootstrap 5.3 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Vazir Font -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <!-- Persian Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $config['url']; ?>/assets/css/app.css?v=3.0.0">
    <script>var CRM_BASE_URL = '<?php echo $config['url']; ?>';</script>
</head>
<body>
    <div class="d-flex" id="app-wrapper">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <span class="brand-icon">✈️</span>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">علائدین سفیر آسمان</h5>
                        <small class="text-white-50">آژانس مسافرتی</small>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-section">ناوبری اصلی</div>
            <nav class="sidebar-nav">
                <a href="<?php echo $config['url']; ?>/dashboard" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> <span>داشبورد</span>
                </a>
                
                <?php if (\Core\Auth::hasPermission('pipelines.view')): ?>
                <a href="<?php echo $config['url']; ?>/pipelines" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/pipelines') !== false && strpos($_SERVER['REQUEST_URI'], '/pipelines/kanban') === false ? 'active' : ''; ?>">
                    <i class="bi bi-kanban"></i> <span>پایپ لاین‌ها</span>
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('deals.view')): ?>
                <a href="<?php echo $config['url']; ?>/deals" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/deals') !== false && strpos($_SERVER['REQUEST_URI'], '/deals/tag') === false && strpos($_SERVER['REQUEST_URI'], '/deals/tags') === false) ? 'active' : ''; ?>">
                    <i class="bi bi-briefcase"></i> <span>معاملات</span>
                </a>
                <a href="<?php echo $config['url']; ?>/deals/tags" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/deals/tag') !== false || strpos($_SERVER['REQUEST_URI'], '/deals/tags') !== false) ? 'active' : ''; ?>">
                    <i class="bi bi-tags"></i> <span>هشتگ‌ها</span>
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('contacts.view')): ?>
                <a href="<?php echo $config['url']; ?>/contacts" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/contacts') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i> <span>مخاطبان</span>
                </a>
                <?php endif; ?>
                
                <?php if ($config['features']['payment_gateway'] && \Core\Auth::hasPermission('payments.view')): ?>
                <a href="<?php echo $config['url']; ?>/payment/history" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/payment') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-credit-card"></i> <span>پرداخت‌ها</span>
                </a>
                <?php endif; ?>
                
                <?php if ($config['features']['sms'] && \Core\Auth::hasPermission('sms.send')): ?>
                <a href="<?php echo $config['url']; ?>/sms/history" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/sms') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-envelope"></i> <span>پیامک‌ها</span>
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('activities.view')): ?>
                <a href="<?php echo $config['url']; ?>/activities" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/activities') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-check"></i> <span>فعالیت‌ها</span>
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('calendar.view')): ?>
                <a href="<?php echo $config['url']; ?>/calendar" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/calendar') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-calendar3"></i> <span>تقویم</span>
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('reports.view')): ?>
                <a href="<?php echo $config['url']; ?>/targets" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/targets') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-bullseye"></i> <span>هدف‌گذاری</span>
                </a>
                <div class="sidebar-section">گزارشات</div>
                <a href="<?php echo $config['url']; ?>/reports" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/reports') !== false && strpos($_SERVER['REQUEST_URI'], '/reports/sales') === false && strpos($_SERVER['REQUEST_URI'], '/reports/pipeline') === false && strpos($_SERVER['REQUEST_URI'], '/reports/activities') === false && strpos($_SERVER['REQUEST_URI'], '/reports/contacts') === false) ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i> <span>داشبورد گزارشات</span>
                </a>
                <?php endif; ?>
                
                <div class="sidebar-section">مدیریت</div>
                
                <?php if (\Core\Auth::hasPermission('users.manage')): ?>
                <a href="<?php echo $config['url']; ?>/users" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/users') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-person"></i> <span>کاربران</span>
                </a>
                <a href="<?php echo $config['url']; ?>/teams" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/teams') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i> <span>تیم‌ها</span>
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('roles.manage')): ?>
                <a href="<?php echo $config['url']; ?>/roles" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/roles') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-shield-check"></i> <span>نقش‌ها</span>
                </a>
                <?php endif; ?>
                
                <?php if (\Core\Auth::hasPermission('settings.manage')): ?>
                <a href="<?php echo $config['url']; ?>/settings" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/settings') !== false && strpos($_SERVER['REQUEST_URI'], '/custom-fields') === false ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i> <span>تنظیمات</span>
                </a>
                <a href="<?php echo $config['url']; ?>/custom-fields" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/custom-fields') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-ui-checks"></i> <span>فیلدهای اختصاصی</span>
                </a>
                <a href="<?php echo $config['url']; ?>/automation" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/automation') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-robot"></i> <span>اتوماسیون</span>
                </a>
                <a href="<?php echo $config['url']; ?>/backup" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/backup') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-cloud-arrow-down"></i> <span>بکاپ</span>
                </a>
                <a href="<?php echo $config['url']; ?>/system/repair" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/system/repair') !== false) ? 'active' : ''; ?>">
                    <i class="bi bi-tools"></i> <span>تعمیر دیتابیس</span>
                </a>
                <a href="<?php echo $config['url']; ?>/system/error-logs" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/system/error-logs') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-exclamation-triangle"></i> <span>گزارش خطاها</span>
                </a>
                <a href="<?php echo $config['url']; ?>/system/logs" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/system/logs') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-journal-text"></i> <span>لاگ سیستم</span>
                </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="<?php echo $config['url']; ?>/logout"><i class="bi bi-box-arrow-right"></i> خروج</a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content flex-grow-1">
            <!-- Top Header -->
            <header class="top-header">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-link text-dark d-lg-none p-0 me-2" id="sidebarToggle" type="button">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <!-- Global Search -->
                    <form action="<?php echo $config['url']; ?>/search" method="GET" class="global-search-form d-none d-md-block">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 bg-light" placeholder="جستجوی سراسری..." autocomplete="off" id="globalSearchInput">
                        </div>
                        <div class="search-suggestions" id="searchSuggestions" style="display:none;"></div>
                    </form>
                </div>
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <!-- Export Links -->
                    <a href="<?php echo $config['url']; ?>/export/deals" title="خروجی اکسل معاملات" class="btn btn-link text-decoration-none d-none d-lg-inline-block p-1">
                        <i class="bi bi-file-earmark-excel text-success fs-5"></i>
                    </a>
                    <!-- Notification Bell -->
                    <div class="dropdown" id="notificationBell">
                        <a href="<?php echo $config['url']; ?>/notifications" class="btn btn-link text-decoration-none p-1 position-relative" id="notifDropdownBtn">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display:none;font-size:10px;">0</span>
                        </a>
                    </div>
                    <span class="text-muted small d-none d-xl-inline-block"><i class="bi bi-calendar3"></i> <?php echo \Core\JDate::date('l'); ?> - <?php echo \Core\JDate::displayDate(date('Y-m-d')); ?></span>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span class="d-none d-md-inline"><?php echo \Core\Auth::user()->full_name ?? ''; ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-start">
                            <li><span class="dropdown-item-text text-muted small"><?php echo \Core\Auth::user()->role_name ?? ''; ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $config['url']; ?>/logout"><i class="bi bi-box-arrow-right"></i> خروج</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Flash Messages -->
                <?php 
                $flashes = \Core\Session::getFlashes();
                foreach ($flashes as $flash): 
                ?>
                    <div class="alert alert-<?php echo $flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info')); ?> alert-dismissible fade show" role="alert">
                        <?php 
                            $icons = ['success'=>'bi-check-circle', 'danger'=>'bi-x-circle', 'warning'=>'bi-exclamation-triangle', 'info'=>'bi-info-circle'];
                            $bsType = $flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'));
                        ?>
                        <i class="bi <?php echo $icons[$flash['type']] ?? 'bi-info-circle'; ?> me-2"></i>
                        <?php echo htmlspecialchars($flash['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>

                <?php echo $content ?? ''; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery + Persian Datepicker -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="<?php echo $config['url']; ?>/assets/js/app.js?v=2.0.0" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle
        var toggleBtn = document.getElementById('sidebarToggle');
        var sidebarEl = document.getElementById('sidebar');
        var overlayEl = document.getElementById('sidebarOverlay');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (sidebarEl) sidebarEl.classList.toggle('open');
                if (overlayEl) overlayEl.classList.toggle('show');
            });
        }
        if (overlayEl) {
            overlayEl.addEventListener('click', function() {
                if (sidebarEl) sidebarEl.classList.remove('open');
                overlayEl.classList.remove('show');
            });
        }

        // ===== NOTIFICATION BELL =====
        var baseUrl = '<?php echo $config['url']; ?>';
        var notifBadge = document.getElementById('notifBadge');
        
        function loadNotifications() {
            fetch(baseUrl + '/notifications/unread')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.count > 0) {
                        notifBadge.textContent = data.count;
                        notifBadge.style.display = 'inline-flex';
                    } else {
                        notifBadge.style.display = 'none';
                    }
                })
                .catch(function() {});
        }
        
        loadNotifications();
        setInterval(loadNotifications, 60000);

        // ===== GLOBAL SEARCH AUTOCOMPLETE =====
        var searchInput = document.getElementById('globalSearchInput');
        var suggestions = document.getElementById('searchSuggestions');
        var searchTimer = null;
        if (searchInput && suggestions) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                var q = this.value.trim();
                if (q.length < 2) { suggestions.style.display = 'none'; return; }
                searchTimer = setTimeout(function() {
                    fetch(baseUrl + '/search/api?q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            var html = '';
                            if (data.deals && data.deals.length) {
                                html += '<div class="search-section-label">معاملات</div>';
                                data.deals.forEach(function(d) {
                                    html += '<a href="' + baseUrl + '/deals/view/' + d.id + '" class="search-suggestion-item"><div class="ss-title"><i class="bi bi-briefcase me-1"></i>' + d.title + '</div></a>';
                                });
                            }
                            if (data.contacts && data.contacts.length) {
                                html += '<div class="search-section-label">مخاطبان</div>';
                                data.contacts.forEach(function(c) {
                                    html += '<a href="' + baseUrl + '/contacts/view/' + c.id + '" class="search-suggestion-item"><div class="ss-title"><i class="bi bi-person me-1"></i>' + c.full_name + '</div><div class="ss-sub">' + (c.phone||'') + '</div></a>';
                                });
                            }
                            if (!html) html = '<div class="text-center text-muted py-4">نتیجه‌ای یافت نشد</div>';
                            suggestions.innerHTML = html;
                            suggestions.style.display = 'block';
                        })
                        .catch(function() { suggestions.style.display = 'none'; });
                }, 300);
            });
            searchInput.addEventListener('focus', function() {
                if (suggestions.innerHTML.trim()) suggestions.style.display = 'block';
            });
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
                    suggestions.style.display = 'none';
                }
            });
        }

        // ===== JALALI DATE PICKER - Global =====
        // Convert all type="date" inputs to Persian datepicker
        if (typeof jQuery !== 'undefined' && typeof $.fn.pDatepicker !== 'undefined') {
            jQuery('input[type="date"]').each(function() {
                var $input = jQuery(this);
                var name = $input.attr('name');
                var id = $input.attr('id') || '';
                var gregorianValue = $input.val(); // Current Gregorian value
                
                // Create hidden field to store Gregorian value
                var $hidden = jQuery('<input>', {
                    type: 'hidden',
                    name: name,
                    id: id + '_gregorian',
                    value: gregorianValue
                });
                
                // Convert visible input to text
                $input.attr('type', 'text');
                $input.removeAttr('name'); // Remove name to avoid duplicate submission
                $input.attr('autocomplete', 'off');
                $input.attr('placeholder', 'تاریخ را انتخاب کنید');
                $input.addClass('jalali-date-input');
                
                // Insert hidden field after visible input
                $input.after($hidden);
                
                // If there's a Gregorian value, convert to Jalali for display
                if (gregorianValue) {
                    try {
                        var parts = gregorianValue.split('-');
                        if (parts.length === 3) {
                            var pd = new persianDate();
                            pd.toCalendar('persian');
                            var jalali = pd.convert(new Date(parseInt(parts[0]), parseInt(parts[1])-1, parseInt(parts[2])));
                            var jStr = jalali.year() + '/' + String(jalali.month()).padStart(2,'0') + '/' + String(jalali.date()).padStart(2,'0');
                            $input.val(jStr);
                        }
                    } catch(e) {}
                }
                
                // Initialize persian-datepicker
                $input.pDatepicker({
                    format: 'YYYY/MM/DD',
                    initialValue: false,
                    autoClose: true,
                    calendar: {
                        persian: {
                            locale: 'fa'
                        }
                    },
                    onSelect: function(unix) {
                        // Convert unix timestamp to Gregorian YYYY-MM-DD
                        var d = new Date(unix);
                        var gy = d.getFullYear();
                        var gm = String(d.getMonth() + 1).padStart(2, '0');
                        var gd = String(d.getDate()).padStart(2, '0');
                        $hidden.val(gy + '-' + gm + '-' + gd);
                    }
                });
            });
        }
    });
    </script>
</body>
</html>
