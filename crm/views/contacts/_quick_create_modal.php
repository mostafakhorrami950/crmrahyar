<?php $config = $GLOBALS['app_config']; ?>

<!-- Quick Create Contact Modal -->
<div class="modal fade" id="quickContactModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>افزودن سریع مخاطب</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo $config['url']; ?>/contacts/store" data-ajax="true" id="quickContactForm">
                <div class="modal-body">
                    <div class="ajax-error alert alert-danger d-none mb-3"></div>
                    <div class="ajax-success alert alert-success d-none mb-3"></div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-person me-1"></i>نام و نام خانوادگی <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required placeholder="مثال: علی محمدی" id="qcFullName">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-phone me-1"></i>شماره موبایل</label>
                        <input type="text" name="phone" class="form-control" placeholder="09123456789" dir="ltr" style="text-align:left;" id="qcPhone">
                        <small class="text-muted">در صورت تکراری بودن، هشدار داده می‌شود</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-envelope me-1"></i>ایمیل</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" dir="ltr" style="text-align:left;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-building me-1"></i>شرکت / سازمان</label>
                        <input type="text" name="company" class="form-control" placeholder="نام شرکت">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-medium"><i class="bi bi-card-text me-1"></i>یادداشت</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="توضیحات مختصر..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره و ادامه</button>
                    <button type="button" class="btn btn-success" id="qcSaveAndNew"><i class="bi bi-plus-circle me-1"></i>ذخیره و جدید</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var quickContactForm = document.getElementById('quickContactForm');
    var saveAndNewBtn = document.getElementById('qcSaveAndNew');
    
    if (saveAndNewBtn) {
        saveAndNewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            quickContactForm.setAttribute('data-action-mode', 'new');
            quickContactForm.requestSubmit();
        });
    }
    
    // Handle AJAX success for quick contact
    if (quickContactForm) {
        quickContactForm.addEventListener('submit', function(e) {
            var form = this;
            var actionMode = form.getAttribute('data-action-mode') || 'close';
            form.setAttribute('data-action-mode', 'close');
            
            // Don't prevent default - let app.js handle AJAX
            // But listen for the custom response
            var originalOnSuccess = null;
            
            // Use fetch for AJAX
            e.preventDefault();
            var formData = new FormData(form);
            var successDiv = form.querySelector('.ajax-success');
            var errorDiv = form.querySelector('.ajax-error');
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    // Add new contact to all contact select dropdowns on the page
                    var contactId = (data.contact && data.contact.id) || data.contact_id || data.id;
                    var contactName = (data.contact && data.contact.full_name) || formData.get('full_name');
                    var contactPhone = (data.contact && data.contact.phone) || formData.get('phone');
                    var selects = document.querySelectorAll('select[name="contact_id"], select[name="contact"]');
                    selects.forEach(function(sel) {
                        var opt = document.createElement('option');
                        opt.value = contactId;
                        opt.textContent = contactName + (contactPhone ? ' (' + contactPhone + ')' : '');
                        sel.appendChild(opt);
                        sel.value = opt.value;
                        sel.dispatchEvent(new Event('change'));
                    });
                    
                    if (actionMode === 'new') {
                        // Reset form for new entry
                        form.reset();
                        document.getElementById('qcFullName').focus();
                        if (successDiv) {
                            successDiv.classList.remove('d-none');
                            successDiv.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + (data.message || 'مخاطب با موفقیت ذخیره شد!');
                            setTimeout(function() { successDiv.classList.add('d-none'); }, 3000);
                        }
                        if (errorDiv) errorDiv.classList.add('d-none');
                    } else {
                        // Close modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('quickContactModal'));
                        if (modal) modal.hide();
                        form.reset();
                    }
                } else {
                    if (errorDiv) {
                        errorDiv.classList.remove('d-none');
                        errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' + (data.message || 'خطا در ذخیره مخاطب');
                    }
                    if (successDiv) successDiv.classList.add('d-none');
                }
            })
            .catch(function() {
                if (errorDiv) {
                    errorDiv.classList.remove('d-none');
                    errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>خطای شبکه';
                }
            });
        });
    }
    
    // Reset form when modal is hidden
    var modal = document.getElementById('quickContactModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            quickContactForm.reset();
            var successDiv = quickContactForm.querySelector('.ajax-success');
            var errorDiv = quickContactForm.querySelector('.ajax-error');
            if (successDiv) successDiv.classList.add('d-none');
            if (errorDiv) errorDiv.classList.add('d-none');
        });
    }
});
</script>