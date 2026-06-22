<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header">
    <h5>📤 ارسال پیامک</h5>
    <a href="<?php echo $config['url']; ?>/sms/history" class="btn btn-secondary btn-sm">← تاریخچه</a>
</div>

<!-- Tabs -->
<div style="display:flex;gap:4px;margin-bottom:16px;">
    <button type="button" class="tab-btn active" onclick="switchTab('single')" id="tab-single">📞 ارسال تکی</button>
    <button type="button" class="tab-btn" onclick="switchTab('bulk')" id="tab-bulk">📱 ارسال انبوه</button>
</div>

<!-- Single SMS Tab -->
<div class="card tab-content" id="tab-single-content" style="padding:24px;">
    <h5 style="margin-bottom:16px;font-weight:bold;">ارسال پیامک تکی</h5>
    <div class="ajax-error alert alert-danger" style="display:none;"></div>
    <form method="POST" action="<?php echo $config['url']; ?>/sms/send" data-ajax="true" id="singleSmsForm">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">📞 شماره موبایل *</label>
                <input type="text" name="phone" class="form-input" placeholder="0912xxxxxxx" dir="ltr" style="text-align:left;" required>
            </div>
            <div class="form-group">
                <label class="form-label">📱 شماره ارسال‌کننده</label>
                <select name="from_number" class="form-input">
                    <?php foreach ($senderNumbers ?? ['+983000505'] as $num): ?>
                    <option value="<?php echo htmlspecialchars($num); ?>"><?php echo htmlspecialchars($num); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">📝 متن پیامک *</label>
            <textarea name="message" class="form-textarea" rows="5" required placeholder="متن پیامک خود را اینجا بنویسید..."></textarea>
            <div class="form-hint"><span id="charCount">0</span> کاراکتر</div>
        </div>
        <input type="hidden" name="deal_id" value="0">
        <button type="submit" class="btn btn-primary">📤 ارسال پیامک</button>
    </form>
</div>

<!-- Bulk SMS Tab -->
<div class="card tab-content" id="tab-bulk-content" style="padding:24px;display:none;">
    <h5 style="margin-bottom:16px;font-weight:bold;">📱 ارسال پیامک انبوه</h5>
    <div class="ajax-error alert alert-danger" style="display:none;"></div>
    <div id="bulkResult" style="display:none;"></div>
    <form id="bulkSmsForm" method="POST">
        <!-- Filter Section -->
        <div style="background:var(--gray-50);padding:16px;border-radius:12px;margin-bottom:16px;">
            <h6 style="margin-bottom:12px;font-weight:bold;">🎯 فیلتر مخاطبین</h6>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">نوع فیلتر</label>
                    <select name="filter_type" id="filterType" class="form-input" onchange="toggleFilters()">
                        <option value="">همه مخاطبین</option>
                        <option value="deal_status">بر اساس وضعیت معامله</option>
                        <option value="pipeline">بر اساس پایپ لاین</option>
                        <option value="date_range">بر اساس تاریخ ثبت</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">📂 دسته‌بندی (اختیاری)</label>
                    <select name="category_id" class="form-input">
                        <option value="">همه دسته‌بندی‌ها</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Deal Status Filter -->
            <div id="dealStatusFilter" style="display:none;">
                <div class="form-group">
                    <label class="form-label">وضعیت معامله</label>
                    <select name="deal_status" class="form-input">
                        <option value="">انتخاب کنید</option>
                        <option value="won">✅ موفق (معاملات برنده)</option>
                        <option value="lost">❌ ناموفق (معاملات باخته)</option>
                        <option value="open">⏳ در حال بررسی</option>
                    </select>
                </div>
            </div>

            <!-- Pipeline Filter -->
            <div id="pipelineFilter" style="display:none;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">پایپ لاین</label>
                        <select name="pipeline_id" id="bulkPipelineSelect" class="form-input" onchange="loadBulkStages(this.value)">
                            <option value="">انتخاب پایپ لاین</option>
                            <?php foreach ($pipelines as $p): ?>
                            <option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">مرحله (اختیاری)</label>
                        <select name="stage_id" id="bulkStageSelect" class="form-input">
                            <option value="">همه مراحل</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div id="dateRangeFilter" style="display:none;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">از تاریخ</label>
                        <input type="date" name="date_from" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">تا تاریخ</label>
                        <input type="date" name="date_to" class="form-input">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">📝 متن پیامک *</label>
            <textarea name="message" class="form-textarea" rows="5" required placeholder="متن پیامک انبوه خود را اینجا بنویسید..."></textarea>
            <div class="form-hint"><span id="bulkCharCount">0</span> کاراکتر</div>
        </div>

        <div style="display:flex;gap:8px;margin-top:12px;">
            <button type="button" class="btn btn-secondary" onclick="previewBulk()">👁️ پیش‌نمایش تعداد مخاطبین</button>
            <button type="button" class="btn btn-primary" id="bulkSendBtn" onclick="sendBulk()" disabled>📤 ارسال انبوه</button>
        </div>
        <div id="previewResult" style="margin-top:12px;display:none;"></div>
    </form>
</div>

<style>
.tab-btn { padding:10px 20px;border:2px solid var(--gray-200);background:white;border-radius:10px 10px 0 0;font-weight:600;font-size:14px;cursor:pointer;color:var(--gray-500); }
.tab-btn.active { background:var(--primary);color:white;border-color:var(--primary); }
</style>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(function(b){b.classList.remove('active');});
    document.querySelectorAll('.tab-content').forEach(function(c){c.style.display='none';});
    document.getElementById('tab-'+tab).classList.add('active');
    document.getElementById('tab-'+tab+'-content').style.display='block';
}

function toggleFilters() {
    var type = document.getElementById('filterType').value;
    document.getElementById('dealStatusFilter').style.display = (type==='deal_status') ? 'block' : 'none';
    document.getElementById('pipelineFilter').style.display = (type==='pipeline') ? 'block' : 'none';
    document.getElementById('dateRangeFilter').style.display = (type==='date_range') ? 'block' : 'none';
}

function loadBulkStages(pid) {
    if (!pid) return;
    var sel = document.getElementById('bulkStageSelect');
    sel.innerHTML = '<option value="">در حال بارگذاری...</option>';
    fetch('<?php echo $config['url']; ?>/pipelines/'+pid+'/stages')
    .then(function(r){return r.json();})
    .then(function(d){
        sel.innerHTML = '<option value="">همه مراحل</option>';
        if(d.stages) d.stages.forEach(function(s){sel.innerHTML+='<option value="'+s.id+'">'+s.name+'</option>';});
    });
}

function previewBulk() {
    var form = document.getElementById('bulkSmsForm');
    var fd = new FormData(form);
    fd.append('preview', '1');
    
    fetch('<?php echo $config['url']; ?>/sms/send-bulk', {method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
        var el = document.getElementById('previewResult');
        el.style.display = 'block';
        if(d.success){
            el.innerHTML = '<div class="alert alert-success" style="background:#d1fae5;color:#065f46;padding:12px;border-radius:8px;">✅ '+d.message+'</div>';
            document.getElementById('bulkSendBtn').disabled = false;
        } else {
            el.innerHTML = '<div class="alert alert-danger" style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;">❌ '+d.message+'</div>';
        }
    });
}

function sendBulk() {
    if(!confirm('آیا از ارسال پیامک انبوه اطمینان دارید؟')) return;
    var form = document.getElementById('bulkSmsForm');
    var fd = new FormData(form);
    
    document.getElementById('bulkSendBtn').disabled = true;
    document.getElementById('bulkSendBtn').innerHTML = '⏳ در حال ارسال...';
    
    fetch('<?php echo $config['url']; ?>/sms/send-bulk', {method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
        var el = document.getElementById('bulkResult');
        el.style.display = 'block';
        if(d.failed > 0 && d.sent === 0){
            var errDetail = d.debug_error ? '<div style="margin-top:8px;font-size:12px;opacity:0.8;">جزئیات خطا: '+d.debug_error+'</div>' : '';
            el.innerHTML = '<div class="alert" style="background:#fee2e2;color:#991b1b;padding:16px;border-radius:8px;">❌ '+d.message+errDetail+'</div>';
        } else if(d.failed > 0) {
            var errDetail = d.debug_error ? '<div style="margin-top:8px;font-size:12px;opacity:0.8;">آخرین خطا: '+d.debug_error+'</div>' : '';
            el.innerHTML = '<div class="alert" style="background:#fef3c7;color:#92400e;padding:16px;border-radius:8px;font-weight:600;">⚠️ '+d.message+errDetail+'</div>';
        } else if(d.success) {
            el.innerHTML = '<div class="alert" style="background:#d1fae5;color:#065f46;padding:16px;border-radius:8px;font-weight:600;">✅ '+d.message+'</div>';
        } else {
            el.innerHTML = '<div class="alert" style="background:#fee2e2;color:#991b1b;padding:16px;border-radius:8px;">❌ '+d.message+'</div>';
        }
        document.getElementById('bulkSendBtn').innerHTML = '📤 ارسال انبوه';
    }).catch(function(err){
        var el = document.getElementById('bulkResult');
        el.style.display = 'block';
        el.innerHTML = '<div class="alert" style="background:#fee2e2;color:#991b1b;padding:16px;border-radius:8px;">❌ خطای شبکه: '+err.message+'</div>';
        document.getElementById('bulkSendBtn').innerHTML = '📤 ارسال انبوه';
        document.getElementById('bulkSendBtn').disabled = false;
    });
}

// Char count
document.querySelectorAll('textarea[name="message"]').forEach(function(ta){
    ta.addEventListener('input',function(){
        var id = this.closest('form').id==='singleSmsForm' ? 'charCount' : 'bulkCharCount';
        document.getElementById(id).textContent = this.value.length;
    });
});
</script>