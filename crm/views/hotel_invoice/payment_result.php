<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتیجه پرداخت - فاکتور هتل</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <style>
        body { font-family: Vazirmatn, sans-serif; background: #f5f7fa; }
        .result-container { max-width: 500px; margin: 80px auto; background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center; }
        .icon-success { color: #198754; font-size: 72px; }
        .icon-error { color: #dc3545; font-size: 72px; }
    </style>
</head>
<body>
    <div class="result-container">
        <?php if ($success): ?>
        <i class="bi bi-check-circle-fill icon-success"></i>
        <h3 class="mt-3 fw-bold text-success">پرداخت موفق</h3>
        <p class="text-muted mt-2"><?php echo htmlspecialchars($message); ?></p>
        <?php if ($refNumber): ?>
        <div class="bg-light rounded p-3 mt-3">
            <small class="text-muted">کد پیگیری</small><br>
            <strong class="fs-5" dir="ltr"><?php echo $refNumber; ?></strong>
        </div>
        <?php endif; ?>
        <?php if ($amount > 0): ?>
        <div class="mt-2">
            <small class="text-muted">مبلغ پرداختی</small><br>
            <strong class="text-success"><?php echo number_format($amount); ?> تومان</strong>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <i class="bi bi-x-circle-fill icon-error"></i>
        <h3 class="mt-3 fw-bold text-danger">پرداخت ناموفق</h3>
        <p class="text-muted mt-2"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>