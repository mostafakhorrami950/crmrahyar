<?php $config = $GLOBALS['app_config']; ?>

<div class="page-header">
    <h5>📥 ایمپورت مخاطبین</h5>
    <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-secondary">بازگشت به لیست</a>
</div>

<div class="card" style="max-width:700px;">
    <!-- راهنما -->
    <div style="background:#eff6ff;padding:16px;border-radius:10px;margin-bottom:20px;border:1px solid #bfdbfe;">
        <h6 style="margin-bottom:12px;">📋 راهنمای ایمپورت</h6>
        <ul style="font-size:13px;color:var(--gray-600);padding-right:20px;line-height:2;">
            <li>فرمت‌های مجاز: <strong>CSV</strong> و <strong>XLSX</strong> (اکسل)</li>
            <li>حداکثر حجم فایل: <strong>5 مگابایت</strong></li>
            <li>ردیف اول فایل باید <strong>عنوان ستون‌ها</strong> باشد</li>
            <li>پس از آپلود، ستون‌ها را به فیلدهای دلخواه نگاشت (map) می‌کنید</li>
            <li>مخاطبین تکراری (بر اساس شماره تلفن) نادیده گرفته می‌شوند</li>
            <li>حداقل یکی از «نام» یا «تلفن» باید مقدار داشته باشد</li>
        </ul>
    </div>

    <div style="background:#f0fdf4;padding:16px;border-radius:10px;margin-bottom:20px;border:1px solid #86efac;">
        <h6 style="margin-bottom:8px;">💡 نکته برای فایل‌های اکسل فارسی</h6>
        <p style="font-size:12px;color:var(--gray-600);margin:0;">
            اگر فایل اکسل شما فارسی است، آن را به صورت <strong>CSV با کدگذاری UTF-8</strong> ذخیره کنید تا کاراکترهای فارسی درست نمایش داده شوند.
            <br>در اکسل: File → Save As → CSV UTF-8 (Comma delimited)
        </p>
    </div>

    <form method="POST" action="<?php echo $config['url']; ?>/contacts/import/upload" enctype="multipart/form-data" id="importForm">
        <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label" style="font-size:14px;font-weight:600;">📁 فایل مخاطبین را انتخاب کنید</label>
            <div style="border:2px dashed var(--gray-300);border-radius:12px;padding:40px 20px;text-align:center;cursor:pointer;background:var(--gray-50);transition:all 0.2s;" id="dropZone" onclick="document.getElementById('fileInput').click()">
                <div style="font-size:48px;margin-bottom:12px;" id="dropIcon">📤</div>
                <p style="font-weight:600;color:var(--gray-700);margin:0;" id="dropText">فایل را اینجا بکشید یا کلیک کنید</p>
                <p style="font-size:12px;color:var(--gray-400);margin:8px 0 0;" id="dropHint">CSV, XLSX — حداکثر 5 مگابایت</p>
                <input type="file" name="import_file" id="fileInput" accept=".csv,.xlsx,.xls" style="display:none;" required>
            </div>
            <div id="fileInfo" style="display:none;margin-top:12px;padding:12px;background:#ecfdf5;border-radius:8px;border:1px solid #a7f3d0;">
                <span id="fileName"></span>
                <button type="button" onclick="clearFile()" style="float:left;background:none;border:none;color:#dc3545;cursor:pointer;font-size:16px;">✕</button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" id="uploadBtn" disabled style="width:100%;padding:14px;">
            📤 آپلود و ادامه →
        </button>
    </form>
</div>

<script>
var dropZone = document.getElementById('dropZone');
var fileInput = document.getElementById('fileInput');
var fileInfo = document.getElementById('fileInfo');
var fileName = document.getElementById('fileName');
var uploadBtn = document.getElementById('uploadBtn');

// Drag & Drop
dropZone.addEventListener('dragover', function(e) { 
    e.preventDefault(); 
    dropZone.style.borderColor = 'var(--primary)'; 
    dropZone.style.background = '#f0f4ff'; 
});
dropZone.addEventListener('dragleave', function(e) { 
    e.preventDefault(); 
    dropZone.style.borderColor = 'var(--gray-300)'; 
    dropZone.style.background = 'var(--gray-50)'; 
});
dropZone.addEventListener('drop', function(e) { 
    e.preventDefault(); 
    dropZone.style.borderColor = 'var(--gray-300)'; 
    dropZone.style.background = 'var(--gray-50)';
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        showFileInfo(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', function() {
    if (this.files.length) showFileInfo(this.files[0]);
});

function showFileInfo(file) {
    var ext = file.name.split('.').pop().toLowerCase();
    var icon = ext === 'csv' ? '📄' : '📊';
    fileName.textContent = icon + ' ' + file.name + ' (' + formatSize(file.size) + ')';
    fileInfo.style.display = 'block';
    uploadBtn.disabled = false;
    document.getElementById('dropIcon').textContent = icon;
    document.getElementById('dropText').textContent = file.name;
    document.getElementById('dropHint').textContent = formatSize(file.size);
}

function clearFile() {
    fileInput.value = '';
    fileInfo.style.display = 'none';
    uploadBtn.disabled = true;
    document.getElementById('dropIcon').textContent = '📤';
    document.getElementById('dropText').textContent = 'فایل را اینجا بکشید یا کلیک کنید';
    document.getElementById('dropHint').textContent = 'CSV, XLSX — حداکثر 5 مگابایت';
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

// Show loading on submit
document.getElementById('importForm').addEventListener('submit', function() {
    uploadBtn.textContent = '⏳ در حال آپلود...';
    uploadBtn.disabled = true;
});
</script>