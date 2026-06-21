<?php $config = $GLOBALS['app_config']; ?>

<div class="page-header">
    <h5>🗺️ نگاشت ستون‌ها — <?php echo htmlspecialchars($fileName); ?></h5>
    <a href="<?php echo $config['url']; ?>/contacts/import" class="btn btn-secondary">بازگشت</a>
</div>

<!-- Progress Steps -->
<div style="display:flex;gap:8px;margin-bottom:20px;justify-content:center;">
    <div style="background:#d4edda;color:#155724;padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;">✅ ۱. آپلود فایل</div>
    <div style="color:var(--gray-400);padding:8px 4px;">→</div>
    <div style="background:var(--primary);color:#fff;padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;">🗺️ ۲. نگاشت ستون‌ها</div>
    <div style="color:var(--gray-400);padding:8px 4px;">→</div>
    <div style="background:var(--gray-200);color:var(--gray-600);padding:8px 16px;border-radius:20px;font-size:13px;">✅ ۳. تایید و ایمپورت</div>
</div>

<div class="card">
    <p style="font-size:13px;color:var(--gray-600);margin-bottom:16px;">
        📊 فایل <strong><?php echo htmlspecialchars($fileName); ?></strong> — 
        <strong><?php echo number_format($totalRows); ?></strong> ردیف داده شناسایی شد.
        <br>ستون‌های فایل خود را به فیلدهای مخاطب نگاشت کنید. سیستم به صورت خودکار ستون‌ها را شناسایی کرده است.
    </p>

    <form id="mappingForm">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:30px;">#</th>
                        <th>عنوان ستون در فایل</th>
                        <th style="min-width:200px;">← نگاشت به فیلد</th>
                        <th>نمونه داده (ردیف ۱)</th>
                        <th>نمونه داده (ردیف ۲)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($headers as $i => $header): ?>
                    <tr>
                        <td style="color:var(--gray-400);font-size:12px;"><?php echo $i + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($header ?: "(ستون خالی {$i})"); ?></strong>
                        </td>
                        <td>
                            <select name="mapping[<?php echo $i; ?>]" class="form-select" style="font-size:13px;" onchange="highlightMapping(this)">
                                <?php foreach ($contactFields as $fieldKey => $fieldLabel): ?>
                                <option value="<?php echo $fieldKey; ?>" <?php echo (($autoMap[$i] ?? 'skip') === $fieldKey) ? 'selected' : ''; ?>>
                                    <?php echo $fieldLabel; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td style="font-size:12px;color:var(--gray-600);max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?php echo htmlspecialchars($sampleRows[0][$i] ?? '-'); ?>
                        </td>
                        <td style="font-size:12px;color:var(--gray-600);max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?php echo htmlspecialchars($sampleRows[1][$i] ?? '-'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Extra sample rows toggle -->
        <?php if (count($sampleRows) > 2): ?>
        <details style="margin-top:12px;">
            <summary style="cursor:pointer;font-size:12px;color:var(--gray-500);">🔍 نمایش نمونه‌های بیشتر...</summary>
            <div class="table-wrapper" style="margin-top:8px;">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($headers as $h): ?>
                            <th style="font-size:11px;"><?php echo htmlspecialchars($h); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($r = 2; $r < count($sampleRows); $r++): ?>
                        <tr>
                            <?php for ($c = 0; $c < count($headers); $c++): ?>
                            <td style="font-size:11px;"><?php echo htmlspecialchars($sampleRows[$r][$c] ?? '-'); ?></td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </details>
        <?php endif; ?>
    </form>
</div>

<!-- Preview & Import Actions -->
<div class="card" style="margin-top:12px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <span style="font-size:13px;color:var(--gray-600);" id="mappedCount">۰ فیلد نگاشت شده</span>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" class="btn btn-secondary" onclick="doPreview()">👁️ پیش‌نمایش</button>
            <button type="button" class="btn btn-primary btn-lg" onclick="doImport()" id="importBtn">✅ تایید و ایمپورت <?php echo number_format($totalRows); ?> ردیف</button>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;overflow-y:auto;padding:20px;">
    <div style="max-width:900px;margin:0 auto;background:#fff;border-radius:16px;padding:24px;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h5>👁️ پیش‌نمایش ایمپورت</h5>
            <button onclick="closePreview()" style="background:none;border:none;font-size:20px;cursor:pointer;">✕</button>
        </div>
        <div id="previewContent"></div>
        <div style="display:flex;gap:8px;margin-top:16px;justify-content:flex-end;">
            <button class="btn btn-secondary" onclick="closePreview()">بازگشت</button>
            <button class="btn btn-primary btn-lg" onclick="doImport(); closePreview();">✅ تایید و ایمپورت</button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="resultModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;overflow-y:auto;padding:20px;">
    <div style="max-width:500px;margin:100px auto;background:#fff;border-radius:16px;padding:24px;text-align:center;">
        <div id="resultIcon" style="font-size:64px;margin-bottom:16px;">⏳</div>
        <h5 id="resultTitle">در حال ایمپورت...</h5>
        <p id="resultMessage" style="color:var(--gray-600);"></p>
        <div id="resultDetails" style="text-align:right;font-size:13px;background:var(--gray-50);padding:12px;border-radius:8px;margin:12px 0;"></div>
        <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-primary" style="width:100%;">بازگشت به لیست مخاطبین</a>
    </div>
</div>

<script>
var baseUrl = '<?php echo $config['url']; ?>';

function getMapping() {
    var mapping = {};
    var selects = document.querySelectorAll('#mappingForm select[name^="mapping"]');
    selects.forEach(function(sel) {
        var index = sel.name.match(/\[(\d+)\]/)[1];
        if (sel.value !== 'skip') {
            mapping[index] = sel.value;
        }
    });
    return mapping;
}

function highlightMapping(sel) {
    // Update mapped count
    var mapped = 0;
    document.querySelectorAll('#mappingForm select').forEach(function(s) {
        if (s.value !== 'skip') mapped++;
    });
    document.getElementById('mappedCount').textContent = mapped + ' فیلد نگاشت شده';
    
    // Highlight selected row
    var row = sel.closest('tr');
    row.style.background = sel.value !== 'skip' ? '#f0fdf4' : '';
}

// Initial count
setTimeout(function() { 
    var mapped = 0;
    document.querySelectorAll('#mappingForm select').forEach(function(s) {
        if (s.value !== 'skip') { mapped++; highlightMapping(s); }
    });
    document.getElementById('mappedCount').textContent = mapped + ' فیلد نگاشت شده';
}, 100);

function doPreview() {
    var mapping = getMapping();
    if (Object.keys(mapping).length === 0) {
        alert('حداقل یک ستون را نگاشت کنید.');
        return;
    }

    var formData = new FormData();
    for (var k in mapping) formData.append('mapping[' + k + ']', mapping[k]);

    fetch(baseUrl + '/contacts/import/preview', { 
        method: 'POST', 
        body: formData,
        headers: { 'Accept': 'application/json' }
    })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { alert(data.message); return; }
            showPreview(data.preview, data.total);
        })
        .catch(function(e) { alert('خطا: ' + e.message); });
}

function showPreview(preview, total) {
    var html = '<p style="font-size:13px;color:var(--gray-600);margin-bottom:12px;">📊 ' + total + ' ردیف در فایل — نمایش ۱۰ ردیف اول:</p>';
    html += '<div class="table-wrapper"><table><thead><tr>';
    
    var fields = Object.values(getMapping());
    var fieldLabels = <?php echo json_encode($contactFields); ?>;
    for (var k in getMapping()) {
        html += '<th>' + (fieldLabels[getMapping()[k]] || getMapping()[k]) + '</th>';
    }
    html += '</tr></thead><tbody>';
    
    preview.forEach(function(row) {
        html += '<tr>';
        for (var k in getMapping()) {
            var field = getMapping()[k];
            var val = row[field] || '<span style="color:var(--gray-300);">-</span>';
            html += '<td style="font-size:12px;">' + val + '</td>';
        }
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    document.getElementById('previewContent').innerHTML = html;
    document.getElementById('previewModal').style.display = 'block';
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
}

function doImport() {
    var mapping = getMapping();
    if (Object.keys(mapping).length === 0) {
        alert('حداقل یک ستون را نگاشت کنید.');
        return;
    }

    if (!confirm('آیا از ایمپورت <?php echo number_format($totalRows); ?> ردیف اطمینان دارید؟')) return;

    var btn = document.getElementById('importBtn');
    btn.textContent = '⏳ در حال ایمپورت...';
    btn.disabled = true;

    // Show loading modal
    document.getElementById('resultIcon').textContent = '⏳';
    document.getElementById('resultTitle').textContent = 'در حال ایمپورت...';
    document.getElementById('resultMessage').textContent = 'لطفاً صبر کنید';
    document.getElementById('resultDetails').textContent = '';
    document.getElementById('resultModal').style.display = 'block';

    var formData = new FormData();
    for (var k in mapping) formData.append('mapping[' + k + ']', mapping[k]);

    fetch(baseUrl + '/contacts/import/execute', { 
        method: 'POST', 
        body: formData,
        headers: { 'Accept': 'application/json' }
    })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('resultIcon').textContent = '✅';
                document.getElementById('resultTitle').textContent = 'ایمپورت با موفقیت انجام شد!';
                document.getElementById('resultMessage').textContent = data.message;
                
                var details = '<strong>✅ ایمپورت شده:</strong> ' + data.imported + '<br>';
                if (data.skipped > 0) details += '<strong>⏭️ نادیده گرفته شده:</strong> ' + data.skipped + '<br>';
                if (data.errors && data.errors.length) {
                    details += '<strong>❌ خطاها:</strong><br>';
                    data.errors.forEach(function(e) { details += '• ' + e + '<br>'; });
                }
                document.getElementById('resultDetails').innerHTML = details;
            } else {
                document.getElementById('resultIcon').textContent = '❌';
                document.getElementById('resultTitle').textContent = 'خطا در ایمپورت';
                document.getElementById('resultMessage').textContent = data.message;
                document.getElementById('resultDetails').textContent = '';
            }
        })
        .catch(function(e) {
            document.getElementById('resultIcon').textContent = '❌';
            document.getElementById('resultTitle').textContent = 'خطا در ارتباط';
            document.getElementById('resultMessage').textContent = e.message;
        });
}
</script>