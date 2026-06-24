<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-1"></i>ایجاد پایپ لاین جدید</h5>
    <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-outline-secondary">بازگشت به لیست</a>
</div>

<div class="card">
    <form method="POST" action="<?php echo $config['url']; ?>/pipelines/store" data-ajax="true">
        <div class="form-row">
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">نام پایپ لاین *</label>
                <input type="text" name="name" class="form-input" required placeholder="مثال: فروش تور">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-medium">توضیحات پایپ لاین</label>
                <textarea name="description" class="form-textarea" rows="3" placeholder="توضیح مختصری درباره این پایپ لاین..."></textarea>
            </div>
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">

        <h5 class="fw-bold mb-0"><i class="bi bi-pin me-1"></i> مراحل پایپ لاین</h5>
        <p style="font-size:13px;color:var(--gray-500);margin-bottom:16px;">
            برای هر مرحله از فرآیند فروش خود یک نام و توضیحات وارد کنید. می‌توانید مراحل را به ترتیب دلخواه مرتب کنید.
        </p>

        <div id="stagesContainer" style="display:flex;flex-direction:column;gap:12px;">
            <div class="stage-row" style="background:var(--gray-50);border-radius:10px;padding:14px;border:2px solid var(--gray-200);">
                <div class="form-row">
                    <div class="mb-3" style="flex:2;">
                        <label class="form-label text-muted small fw-medium">نام مرحله *</label>
                        <input type="text" name="stages[0][name]" class="form-input" required placeholder="مثال: مذاکره اولیه">
                    </div>
                    <div class="mb-3" style="flex:1;">
                        <label class="form-label text-muted small fw-medium">رنگ مرحله</label>
                        <input type="color" name="stages[0][color]" class="form-input" value="#4361ee" style="padding:4px;height:40px;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">توضیحات مرحله (اختیاری)</label>
                    <textarea name="stages[0][description]" class="form-textarea" rows="2" placeholder="توضیح دهید در این مرحله چه اتفاقی می‌افتد..."></textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" class="btn btn-danger btn-sm remove-stage" onclick="removeStage(this)" style="display:none;"><i class="bi bi-trash me-1"></i>حذف مرحله</button>
                </div>
            </div>
        </div>

        <div style="margin:16px 0;">
            <button type="button" class="btn btn-success" onclick="addStage()"><i class="bi bi-plus-circle me-1"></i> افزودن مرحله جدید</button>
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">
        
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle text-success me-1"></i> ایجاد پایپ لاین</button>
        <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-outline-secondary">لغو</a>
    </form>
</div>

<script>
var stageIndex = 1;

function addStage() {
    var container = document.getElementById('stagesContainer');
    var html = '<div class="stage-row" style="background:var(--gray-50);border-radius:10px;padding:14px;border:2px solid var(--gray-200);">';
    html += '<div class="form-row">';
    html += '<div class="mb-3" style="flex:2;"><label class="form-label text-muted small fw-medium">نام مرحله *</label><input type="text" name="stages[' + stageIndex + '][name]" class="form-input" required placeholder="مثال: مذاکره اولیه"></div>';
    html += '<div class="mb-3" style="flex:1;"><label class="form-label text-muted small fw-medium">رنگ مرحله</label><input type="color" name="stages[' + stageIndex + '][color]" class="form-input" value="#4361ee" style="padding:4px;height:40px;"></div>';
    html += '</div>';
    html += '<div class="mb-3"><label class="form-label text-muted small fw-medium">توضیحات مرحله (اختیاری)</label><textarea name="stages[' + stageIndex + '][description]" class="form-textarea" rows="2" placeholder="توضیح دهید در این مرحله چه اتفاقی می‌افتد..."></textarea></div>';
    html += '<div style="display:flex;justify-content:flex-end;gap:8px;"><button type="button" class="btn btn-danger btn-sm" onclick="removeStage(this)"><i class="bi bi-trash me-1"></i>حذف مرحله</button></div>';
    html += '</div>';
    
    var div = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
    stageIndex++;
}

function removeStage(btn) {
    var container = document.getElementById('stagesContainer');
    if (container.children.length > 1) {
        btn.closest('.stage-row').remove();
    } else {
        alert('حداقل یک مرحله باید وجود داشته باشد.');
    }
}
</script>