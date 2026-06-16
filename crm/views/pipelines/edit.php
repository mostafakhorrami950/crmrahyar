<div class="page-header">
    <h5>✏️ ویرایش پایپ لاین: <?php echo htmlspecialchars($pipeline->name); ?></h5>
    <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-secondary">بازگشت به لیست</a>
</div>

<div class="card">
    <form method="POST" action="<?php echo $config['url']; ?>/pipelines/update/<?php echo $pipeline->id; ?>" data-ajax="true">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">نام پایپ لاین *</label>
                <input type="text" name="name" class="form-input" required value="<?php echo htmlspecialchars($pipeline->name); ?>" placeholder="مثال: فروش تور">
            </div>
            <div class="form-group">
                <label class="form-label">توضیحات پایپ لاین</label>
                <textarea name="description" class="form-textarea" rows="3" placeholder="توضیح مختصری درباره این پایپ لاین..."><?php echo htmlspecialchars($pipeline->description ?? ''); ?></textarea>
            </div>
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">

        <h5 style="font-weight:bold;margin-bottom:15px;">📌 مراحل پایپ لاین</h5>
        <p style="font-size:13px;color:var(--gray-500);margin-bottom:16px;">
            برای هر مرحله یک نام، رنگ و توضیحات وارد کنید.
        </p>

        <div id="stagesContainer" style="display:flex;flex-direction:column;gap:12px;">
            <?php $editIndex = 0; ?>
            <?php foreach ($stages as $stage): ?>
            <div class="stage-row" style="background:var(--gray-50);border-radius:10px;padding:14px;border:2px solid var(--gray-200);">
                <div class="form-row">
                    <div class="form-group" style="flex:2;">
                        <label class="form-label">نام مرحله *</label>
                        <input type="text" name="stages[<?php echo $editIndex; ?>][name]" class="form-input" required value="<?php echo htmlspecialchars($stage->name); ?>" placeholder="مثال: مذاکره اولیه">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">رنگ مرحله</label>
                        <input type="color" name="stages[<?php echo $editIndex; ?>][color]" class="form-input" value="<?php echo $stage->color; ?>" style="padding:4px;height:40px;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">توضیحات مرحله (اختیاری)</label>
                    <textarea name="stages[<?php echo $editIndex; ?>][description]" class="form-textarea" rows="2" placeholder="توضیح دهید در این مرحله چه اتفاقی می‌افتد..."><?php echo htmlspecialchars($stage->description ?? ''); ?></textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeStage(this)" <?php echo count($stages) <= 1 ? 'style="display:none;"' : ''; ?>>🗑️ حذف مرحله</button>
                </div>
            </div>
            <?php $editIndex++; ?>
            <?php endforeach; ?>
        </div>

        <div style="margin:16px 0;">
            <button type="button" class="btn btn-success" onclick="addStage()">➕ افزودن مرحله جدید</button>
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">
        
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <button type="submit" class="btn btn-primary btn-lg">✅ بروزرسانی پایپ لاین</button>
        <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-secondary">لغو</a>
    </form>
</div>

<script>
var stageIndex = <?php echo $editIndex; ?>;

function addStage() {
    var container = document.getElementById('stagesContainer');
    var html = '<div class="stage-row" style="background:var(--gray-50);border-radius:10px;padding:14px;border:2px solid var(--gray-200);">';
    html += '<div class="form-row">';
    html += '<div class="form-group" style="flex:2;"><label class="form-label">نام مرحله *</label><input type="text" name="stages[' + stageIndex + '][name]" class="form-input" required placeholder="مثال: مذاکره اولیه"></div>';
    html += '<div class="form-group" style="flex:1;"><label class="form-label">رنگ مرحله</label><input type="color" name="stages[' + stageIndex + '][color]" class="form-input" value="#4361ee" style="padding:4px;height:40px;"></div>';
    html += '</div>';
    html += '<div class="form-group"><label class="form-label">توضیحات مرحله (اختیاری)</label><textarea name="stages[' + stageIndex + '][description]" class="form-textarea" rows="2" placeholder="توضیح دهید در این مرحله چه اتفاقی می‌افتد..."></textarea></div>';
    html += '<div style="display:flex;justify-content:flex-end;gap:8px;"><button type="button" class="btn btn-danger btn-sm" onclick="removeStage(this)">🗑️ حذف مرحله</button></div>';
    html += '</div>';
    
    var div = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
    stageIndex++;
    
    // Show remove buttons if more than 1
    var rows = container.querySelectorAll('.stage-row');
    if (rows.length > 1) {
        rows.forEach(function(r) {
            var btn = r.querySelector('.btn-danger');
            if (btn) btn.style.display = '';
        });
    }
}

function removeStage(btn) {
    var container = document.getElementById('stagesContainer');
    if (container.children.length > 1) {
        btn.closest('.stage-row').remove();
        // Hide remove button if only 1 left
        var rows = container.querySelectorAll('.stage-row');
        if (rows.length <= 1) {
            var lastBtn = rows[0]?.querySelector('.btn-danger');
            if (lastBtn) lastBtn.style.display = 'none';
        }
    } else {
        alert('حداقل یک مرحله باید وجود داشته باشد.');
    }
}
</script>