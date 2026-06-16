<div class="page-header">
    <h5>🔧 تعمیر دیتابیس</h5>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="table-container" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
            <h6 style="font-weight:bold;margin-bottom:10px;color:#fff;">وضعیت جداول</h6>
            <div style="font-size:28px;font-weight:bold;"><?php echo $existsCount; ?> / <?php echo $totalExpected; ?></div>
            <small>جدول موجود</small>
            <?php if (!empty($missingTables)): ?>
            <div style="background:rgba(255,255,255,0.2);border-radius:8px;padding:8px;margin-top:10px;">
                <small>جداول缺失: <?php echo implode(', ', $missingTables); ?></small>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-8">
        <div class="table-container">
            <h6 style="font-weight:bold;margin-bottom:15px;">عملیات تعمیر</h6>
            <p style="font-size:14px;color:#666;margin-bottom:15px;">
                این ابزار به صورت خودکار جداول و فیلدهای مورد نیاز را بررسی و ایجاد می‌کند.
            </p>
            <button type="button" class="btn btn-primary btn-lg" onclick="runRepair()">
                🛠️ شروع تعمیر خودکار
            </button>
            <div id="repairResult" style="margin-top:15px;display:none;"></div>
        </div>
    </div>
</div>

<div class="table-container mt-4">
    <h6 style="font-weight:bold;margin-bottom:15px;">📋 گزارش تعمیرات قبلی</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr><th>جدول</th><th>عملیات</th><th>توضیحات</th><th>وضعیت</th><th>تاریخ</th></tr>
            </thead>
            <tbody>
                <?php if (empty($repairLog)): ?>
                <tr><td colspan="5" class="text-center py-3">هنوز تعمیری انجام نشده</td></tr>
                <?php else: ?>
                <?php foreach ($repairLog as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log->table_name); ?></td>
                    <td><?php echo htmlspecialchars($log->action); ?></td>
                    <td><?php echo htmlspecialchars($log->description); ?></td>
                    <td><span class="badge badge-<?php echo $log->status === 'success' ? 'success' : 'danger'; ?>"><?php echo $log->status; ?></span></td>
                    <td><?php echo $log->created_at; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function runRepair() {
    var btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '⏳ در حال تعمیر...';
    document.getElementById('repairResult').style.display = 'block';
    document.getElementById('repairResult').innerHTML = '<div class="alert alert-info">در حال اجرا...</div>';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo $config['url']; ?>/database/repair/run', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        btn.disabled = false;
        btn.innerHTML = '🛠️ شروع تعمیر خودکار';
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.success) {
                document.getElementById('repairResult').innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                setTimeout(function() { location.reload(); }, 2000);
            } else {
                document.getElementById('repairResult').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        } catch(e) {
            document.getElementById('repairResult').innerHTML = '<div class="alert alert-danger">خطا: ' + xhr.responseText + '</div>';
        }
    };
    xhr.onerror = function() {
        btn.disabled = false;
        btn.innerHTML = '🛠️ شروع تعمیر خودکار';
        document.getElementById('repairResult').innerHTML = '<div class="alert alert-danger">خطا در ارتباط با سرور</div>';
    };
    xhr.send('action=repair');
}
</script>