<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM آژانس مسافرتی | سیستم مدیریت ارتباط با مشتریان</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $config['url']; ?>/assets/css/app.css">
</head>
<body>
    <!-- Hero Section -->
    <section class="landing-hero">
        <div class="container py-5">
            <div class="row align-items-center min-vh-100">
                <div class="col-12 col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                    <div class="mb-4">
                        <span style="font-size:48px;">✈️</span>
                    </div>
                    <h1 class="display-4 fw-bold text-white mb-3">سیستم مدیریت<br>ارتباط با مشتریان</h1>
                    <p class="lead text-white-50 mb-4">مدیریت هوشمند معاملات، مخاطبان و فعالیت‌های آژانس مسافرتی شما با ابزارهای حرفه‌ای CRM</p>
                    <div class="d-flex gap-3 justify-content-center justify-content-lg-start flex-wrap">
                        <a href="<?php echo $config['url']; ?>/login" class="btn btn-light btn-lg fw-bold px-4">
                            <i class="bi bi-box-arrow-in-left me-2"></i>ورود به سیستم
                        </a>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded-4 p-4 text-center text-white backdrop-blur">
                                <i class="bi bi-people fs-1 mb-2"></i>
                                <h5 class="fw-bold">مخاطبان</h5>
                                <p class="small text-white-50 mb-0">مدیریت اطلاعات مشتریان</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded-4 p-4 text-center text-white backdrop-blur">
                                <i class="bi bi-kanban fs-1 mb-2"></i>
                                <h5 class="fw-bold">پایپ لاین</h5>
                                <p class="small text-white-50 mb-0">پیگیری معاملات</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded-4 p-4 text-center text-white backdrop-blur">
                                <i class="bi bi-credit-card fs-1 mb-2"></i>
                                <h5 class="fw-bold">پرداخت</h5>
                                <p class="small text-white-50 mb-0">مدیریت مالی</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded-4 p-4 text-center text-white backdrop-blur">
                                <i class="bi bi-graph-up fs-1 mb-2"></i>
                                <h5 class="fw-bold">گزارشات</h5>
                                <p class="small text-white-50 mb-0">تحلیل عملکرد</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">امکانات سیستم</h2>
                <p class="text-muted">ابزارهای حرفه‌ای برای مدیریت کسب‌وکار شما</p>
            </div>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:64px;height:64px;">
                            <i class="bi bi-people text-primary fs-3"></i>
                        </div>
                        <h6 class="fw-bold">مدیریت مخاطبان</h6>
                        <p class="text-muted small mb-0">ثبت و مدیریت اطلاعات مشتریان با جزئیات کامل</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:64px;height:64px;">
                            <i class="bi bi-kanban text-success fs-3"></i>
                        </div>
                        <h6 class="fw-bold">پایپ لاین فروش</h6>
                        <p class="text-muted small mb-0">مدیریت مراحل معاملات با نمای کانبان</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:64px;height:64px;">
                            <i class="bi bi-calendar-check text-warning fs-3"></i>
                        </div>
                        <h6 class="fw-bold">تقویم و فعالیت‌ها</h6>
                        <p class="text-muted small mb-0">برنامه‌ریزی و پیگیری فعالیت‌های روزانه</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:64px;height:64px;">
                            <i class="bi bi-envelope text-info fs-3"></i>
                        </div>
                        <h6 class="fw-bold">پیامک و ارتباطات</h6>
                        <p class="text-muted small mb-0">ارسال پیامک انبوه و ارتباط مؤثر با مشتریان</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #4361ee, #7209b7);">
        <div class="container text-center">
            <h3 class="fw-bold text-white mb-3">آماده شروع هستید؟</h3>
            <p class="text-white-50 mb-4">همین حالا وارد سیستم شوید و کسب‌وکار خود را مدیریت کنید</p>
            <a href="<?php echo $config['url']; ?>/login" class="btn btn-light btn-lg fw-bold px-5">
                <i class="bi bi-box-arrow-in-left me-2"></i>ورود به سیستم
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-center">
        <p class="text-white-50 mb-0 small">CRM Travel Agency v2.0.0 | تمامی حقوق محفوظ است</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>