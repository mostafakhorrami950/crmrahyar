<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>ایجاد پایپ لاین جدید</h5>
    <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-outline-secondary"><i class="bi bi-arrow-right me-1"></i>بازگشت به لیست</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo $config['url']; ?>/pipelines/store" data-ajax="true">
            
            <!-- Pipeline Info -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label class="form-label text-muted small fw-medium">نام پایپ لاین *</label>
                    <input type="text" name="name" class="form-control" required placeholder="مثال: فروش تور">
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small fw-medium">توضیحات پایپ لاین</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="توضیح مختصری درباره این پایپ لاین..."></textarea>
                </div>
            </div>

            <hr class="my-4">

            <!-- Stages Section -->
            <div class="mb-3">
                <h5 class="fw-bold mb-1"><i class="bi bi-pin-angle me-1"></i> مراحل پایپ لاین</h5>
                <p class="text-muted small mb-0">
                    برای هر مرحله از فرآیند فروش خود یک نام و توضیحات وارد کنید. می‌توانید مراحل را به ترتیب دلخواه مرتب کنید.
                </p>
            </div>

            <div id="stagesContainer" class="d-flex flex-column gap-3">
                <!-- Stage Row Template -->
                <div class="stage-row rounded-3 p-3 p-md-4" style="background:#f8f9fa;border:2px solid #e9ecef;">
                    <div class="row g-3">
                        <div class="col-12 col-sm-8">
                            <label class="form-label text-muted small fw-medium">نام مرحله *</label>
                            <input type="text" name="stages[0][name]" class="form-control" required placeholder="مثال: مذاکره اولیه">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label text-muted small fw-medium">رنگ مرحله</label>
                            <input type="color" name="stages[0][color]" class="form-control form-control-color w-100" value="#4361ee" title="رنگ مرحله">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-medium">توضیحات مرحله (اختیاری)</label>
                            <textarea name="stages[0][description]" class="form-control" rows="2" placeholder="توضیح دهید در این مرحله چه اتفاقی می‌افتد..."></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-stage d-none" onclick="removeStage(this)">
                            <i class="bi bi-trash me-1"></i>حذف مرحله
                        </button>
                    </div>
                </div>
            </div>

            <div class="my-3">
                <button type="button" class="btn btn-success" onclick="addStage()"><i class="bi bi-plus-circle me-1"></i> افزودن مرحله جدید</button>
            </div>

            <hr class="my-4">
            
            <!-- Error Display -->
            <div class="ajax-error alert alert-danger d-none"></div>

            <!-- Submit -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-1"></i> ایجاد پایپ لاین</button>
                <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-outline-secondary">لغو</a>
            </div>
        </form>
    </div>
</div>

<script>
var stageIndex = 1;

function addStage() {
    var container = document.getElementById('stagesContainer');
    var html = '' +
        '<div class="stage-row rounded-3 p-3 p-md-4" style="background:#f8f9fa;border:2px solid #e9ecef;">' +
            '<div class="row g-3">' +
                '<div class="col-12 col-sm-8">' +
                    '<label class="form-label text-muted small fw-medium">نام مرحله *</label>' +
                    '<input type="text" name="stages[' + stageIndex + '][name]" class="form-control" required placeholder="مثال: مذاکره اولیه">' +
                '</div>' +
                '<div class="col-12 col-sm-4">' +
                    '<label class="form-label text-muted small fw-medium">رنگ مرحله</label>' +
                    '<input type="color" name="stages[' + stageIndex + '][color]" class="form-control form-control-color w-100" value="#4361ee" title="رنگ مرحله">' +
                '</div>' +
                '<div class="col-12">' +
                    '<label class="form-label text-muted small fw-medium">توضیحات مرحله (اختیاری)</label>' +
                    '<textarea name="stages[' + stageIndex + '][description]" class="form-control" rows="2" placeholder="توضیح دهید در این مرحله چه اتفاقی می‌افتد..."></textarea>' +
                '</div>' +
            '</div>' +
            '<div class="d-flex justify-content-end mt-2">' +
                '<button type="button" class="btn btn-outline-danger btn-sm" onclick="removeStage(this)">' +
                    '<i class="bi bi-trash me-1"></i>حذف مرحله' +
                '</button>' +
            '</div>' +
        '</div>';

    var div = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
    
    // Show remove buttons when there are multiple stages
    updateRemoveButtons();
    stageIndex++;
}

function removeStage(btn) {
    var container = document.getElementById('stagesContainer');
    if (container.children.length > 1) {
        btn.closest('.stage-row').remove();
        updateRemoveButtons();
    } else {
        alert('حداقل یک مرحله باید وجود داشته باشد.');
    }
}

function updateRemoveButtons() {
    var container = document.getElementById('stagesContainer');
    var buttons = container.querySelectorAll('.remove-stage');
    buttons.forEach(function(btn) {
        if (container.children.length > 1) {
            btn.classList.remove('d-none');
        } else {
            btn.classList.add('d-none');
        }
    });
}
</script>