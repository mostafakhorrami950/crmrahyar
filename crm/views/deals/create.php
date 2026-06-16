<div class="page-header">
    <h5>ایجاد معامله جدید</h5>
    <a href="<?php echo $config['url']; ?>/deals" class="btn btn-secondary">بازگشت به لیست</a>
</div>

<div class="card">
    <form method="POST" action="<?php echo $config['url']; ?>/deals/store">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">عنوان معامله</label>
                <input type="text" name="title" class="form-input" required placeholder="مثال: تور استانبول">
            </div>
            <div class="form-group">
                <label class="form-label">مبلغ (تومان)</label>
                <input type="text" name="amount" class="form-input" data-format="number" required placeholder="مبلغ به تومان">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">پایپ لاین</label>
                <select name="pipeline_id" class="form-select" id="pipelineSelect" required>
                    <option value="">انتخاب پایپ لاین</option>
                    <?php foreach ($pipelines as $p): ?>
                    <option value="<?php echo $p->id; ?>" <?php echo ($selectedPipeline ?? 0) == $p->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">مرحله</label>
                <select name="stage_id" class="form-select" id="stageSelect" required>
                    <option value="">ابتدا پایپ لاین را انتخاب کنید</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">مخاطب</label>
                <div class="d-flex gap-8">
                    <select name="contact_id" class="form-select" id="contactSelect" style="flex:1;">
                        <option value="">انتخاب مخاطب موجود</option>
                        <?php foreach ($contacts as $c): ?>
                        <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->name . ' - ' . $c->phone); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('newContactModal')">+ جدید</button>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">مسئول</label>
                <select name="user_id" class="form-select">
                    <option value="">انتخاب مسئول</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>" <?php echo (\Core\Auth::id() == $u->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">توضیحات</label>
            <textarea name="description" class="form-textarea" placeholder="توضیحات معامله..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">ذخیره معامله</button>
    </form>
</div>

<!-- Modal: New Contact -->
<div class="modal-overlay" id="newContactModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5>مخاطب جدید</h5>
            <button type="button" class="modal-close" onclick="closeModal('newContactModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo $config['url']; ?>/contacts/store" class="ajax-form">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">نام و نام خانوادگی</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">شماره موبایل</label>
                    <input type="text" name="phone" class="form-input" required placeholder="0912xxxxxxx">
                </div>
                <div class="form-group">
                    <label class="form-label">ایمیل</label>
                    <input type="email" name="email" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">ذخیره</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('newContactModal')">انصراف</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('pipelineSelect')?.addEventListener('change', function() {
    var pipelineId = this.value;
    var stageSelect = document.getElementById('stageSelect');
    stageSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';
    
    fetch('<?php echo $config['url']; ?>/pipelines/get-stages/' + pipelineId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            stageSelect.innerHTML = '<option value="">انتخاب مرحله</option>';
            data.forEach(function(s) {
                stageSelect.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
            });
        });
});
</script>