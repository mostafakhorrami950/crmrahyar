<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>ویرایش پایپ لاین: <?php echo htmlspecialchars($pipeline->name); ?></h5>
    <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?php echo $config['url']; ?>/pipelines/update/<?php echo $pipeline->id; ?>" data-ajax="true">
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium">نام پایپ لاین <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($pipeline->name); ?>" placeholder="مثال: فروش تور">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-medium">توضیحات</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="توضیح مختصر..."><?php echo htmlspecialchars($pipeline->description ?? ''); ?></textarea>
                </div>
            </div>

            <h6 class="fw-bold mb-1"><i class="bi bi-pin me-1 text-primary"></i>مراحل پایپ لاین</h6>
            <p class="text-muted small mb-3">برای هر مرحله یک نام، رنگ و توضیحات وارد کنید.</p>

            <div id="stagesContainer" class="d-flex flex-column gap-3 mb-3">
                <?php $editIndex = 0; ?>
                <?php foreach ($stages as $stage): ?>
                <div class="stage-row p-3 rounded-3 border" style="background:#f8f9fa;">
                    <div class="row g-2">
                        <div class="col-12 col-md-7">
                            <label class="form-label text-muted small fw-medium">نام مرحله <span class="text-danger">*</span></label>
                            <input type="hidden" name="stages[<?php echo $editIndex; ?>][id]" value="<?php echo $stage->id; ?>">
                            <input type="text" name="stages[<?php echo $editIndex; ?>][name]" class="form-control" required value="<?php echo htmlspecialchars($stage->name); ?>" placeholder="مثال: مذاکره اولیه">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label text-muted small fw-medium">رنگ</label>
                            <input type="color" name="stages[<?php echo $editIndex; ?>][color]" class="form-control form-control-color w-100" value="<?php echo $stage->color; ?>">
                        </div>
                        <div class="col-6 col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeStage(this)" <?php echo count($stages) <= 1 ? 'style="display:none;"' : ''; ?>><i class="bi bi-trash me-1"></i>حذف</button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label text-muted small fw-medium">توضیحات مرحله</label>
                        <textarea name="stages[<?php echo $editIndex; ?>][description]" class="form-control" rows="2" placeholder="توضیح دهید در این مرحله چه اتفاقی می‌افتد..."><?php echo htmlspecialchars($stage->description ?? ''); ?></textarea>
                    </div>
                </div>
                <?php $editIndex++; ?>
                <?php endforeach; ?>
            </div>

            <button type="button" class="btn btn-success btn-sm mb-4" onclick="addStage()"><i class="bi bi-plus-circle me-1"></i>افزودن مرحله جدید</button>

            <div class="ajax-error alert alert-danger d-none mb-3"></div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>بروزرسانی</button>
                <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-outline-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>

<script>
var stageIndex = <?php echo $editIndex; ?>;

function addStage() {
    var container = document.getElementById('stagesContainer');
    var html = '<div class="stage-row p-3 rounded-3 border" style="background:#f8f9fa;">';
    html += '<div class="row g-2">';
    html += '<div class="col-12 col-md-7"><label class="form-label text-muted small fw-medium">نام مرحله <span class="text-danger">*</span></label><input type="text" name="stages[' + stageIndex + '][name]" class="form-control" required placeholder="مثال: مذاکره اولیه"></div>';
    html += '<div class="col-6 col-md-2"><label class="form-label text-muted small fw-medium">رنگ</label><input type="color" name="stages[' + stageIndex + '][color]" class="form-control form-control-color w-100" value="#4361ee"></div>';
    html += '<div class="col-6 col-md-3 d-flex align-items-end"><button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeStage(this)"><i class="bi bi-trash me-1"></i>حذف</button></div>';
    html += '</div>';
    html += '<div class="mt-2"><label class="form-label text-muted small fw-medium">توضیحات مرحله</label><textarea name="stages[' + stageIndex + '][description]" class="form-control" rows="2" placeholder="توضیح دهید..."></textarea></div>';
    html += '</div>';
    
    var div = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
    stageIndex++;
    
    var rows = container.querySelectorAll('.stage-row');
    if (rows.length > 1) {
        rows.forEach(function(r) {
            var btn = r.querySelector('.btn-outline-danger');
            if (btn) btn.style.display = '';
        });
    }
}

function removeStage(btn) {
    var container = document.getElementById('stagesContainer');
    if (container.children.length > 1) {
        btn.closest('.stage-row').remove();
        var rows = container.querySelectorAll('.stage-row');
        if (rows.length <= 1) {
            var lastBtn = rows[0]?.querySelector('.btn-outline-danger');
            if (lastBtn) lastBtn.style.display = 'none';
        }
    } else {
        alert('حداقل یک مرحله باید وجود داشته باشد.');
    }
}
</script>