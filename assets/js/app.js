/**
 * CRM Travel Agency - Custom JavaScript
 * Pure JS, no dependencies
 */

// ========== UTILITY FUNCTIONS ==========
function showError(container, message) {
    var el = document.getElementById(container);
    if (!el) {
        el = document.createElement('div');
        el.id = container;
        el.className = 'alert alert-danger';
        var form = document.querySelector('form[data-ajax="true"]');
        if (form) form.prepend(el);
        else document.querySelector('.page-content')?.prepend(el);
    }
    el.textContent = message;
    el.style.display = 'block';
    setTimeout(function() { el.style.display = 'none'; }, 10000);
}

function showSuccess(message) {
    var el = document.createElement('div');
    el.className = 'alert alert-success';
    el.textContent = message;
    var container = document.querySelector('.page-content');
    if (container) container.prepend(el);
    setTimeout(function() { el.remove(); }, 5000);
}

function serializeForm(form) {
    var data = [];
    var elements = form.querySelectorAll('input, textarea, select');
    for (var i = 0; i < elements.length; i++) {
        var el = elements[i];
        if (el.disabled || el.type === 'submit' || el.type === 'button') continue;
        if (el.type === 'checkbox' || el.type === 'radio') {
            if (el.checked) data.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value));
        } else {
            data.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value));
        }
    }
    return data.join('&');
}

document.addEventListener('DOMContentLoaded', function() {
    // Flash auto-hide
    document.querySelectorAll('.flash').forEach(function(flash) {
        setTimeout(function() {
            flash.style.opacity = '0';
            flash.style.transform = 'translateX(80px)';
            flash.style.transition = 'all 0.3s ease';
            setTimeout(function() { flash.remove(); }, 300);
        }, 7000);
    });
    document.querySelectorAll('.flash .close-btn').forEach(function(btn) {
        btn.addEventListener('click', function() { this.parentElement.remove(); });
    });
    // User dropdown
    document.querySelectorAll('.user-dropdown-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            this.nextElementSibling.classList.toggle('show');
        });
    });
    document.addEventListener('click', function() {
        document.querySelectorAll('.user-dropdown-menu.show').forEach(function(m) { m.classList.remove('show'); });
    });
    // Modal
    window.openModal = function(id) {
        var m = document.getElementById(id);
        if (m) { m.classList.add('show'); document.body.style.overflow = 'hidden'; }
    };
    window.closeModal = function(id) {
        var m = document.getElementById(id);
        if (m) { m.classList.remove('show'); document.body.style.overflow = ''; }
    };
    document.querySelectorAll('.modal-overlay').forEach(function(o) {
        o.addEventListener('click', function(e) {
            if (e.target === this) { this.classList.remove('show'); document.body.style.overflow = ''; }
        });
    });
    document.querySelectorAll('.modal-close').forEach(function(b) {
        b.addEventListener('click', function() {
            var m = this.closest('.modal-overlay');
            if (m) { m.classList.remove('show'); document.body.style.overflow = ''; }
        });
    });
    // Sidebar toggle
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.getElementById('sidebar')?.classList.toggle('open');
        document.getElementById('sidebarOverlay')?.classList.toggle('show');
    });
    document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
        document.getElementById('sidebar')?.classList.remove('open');
        this.classList.remove('show');
    });
    // Number format
    document.querySelectorAll('[data-format="number"]').forEach(function(i) {
        i.addEventListener('input', function() {
            var v = this.value.replace(/[^0-9]/g, '');
            if (v) this.value = new Intl.NumberFormat('fa-IR').format(parseInt(v));
        });
        i.addEventListener('focus', function() { this.value = this.value.replace(/[^0-9]/g, ''); });
        i.addEventListener('blur', function() {
            var v = this.value.replace(/[^0-9]/g, '');
            if (v) this.value = new Intl.NumberFormat('fa-IR').format(parseInt(v));
        });
    });
    // Confirm dialogs
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'آیا اطمینان دارید؟')) e.preventDefault();
        });
    });
    // Feature toggle
    document.querySelectorAll('.feature-toggle').forEach(function(t) {
        t.addEventListener('change', function() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', this.dataset.url || '/settings/toggle-feature', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() { try { var d = JSON.parse(xhr.responseText); if (d.success) location.reload(); } catch(e) {} };
            xhr.send('feature=' + t.dataset.feature + '&enabled=' + (t.checked ? 1 : 0));
        });
    });
    // Pipeline stages loading
    document.getElementById('pipelineSelect')?.addEventListener('change', function() {
        var pipelineId = this.value;
        var stageSelect = document.getElementById('stageSelect');
        if (!stageSelect) return;
        stageSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';
        var base = document.querySelector('base')?.getAttribute('href') || '';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', base + '/pipelines/' + pipelineId + '/stages', true);
        xhr.onload = function() {
            try {
                var data = JSON.parse(xhr.responseText);
                stageSelect.innerHTML = '<option value="">انتخاب مرحله</option>';
                if (data.success && data.stages) {
                    data.stages.forEach(function(s) {
                        stageSelect.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
                    });
                }
            } catch(e) { stageSelect.innerHTML = '<option value="">خطا در بارگذاری</option>'; }
        };
        xhr.send();
    });
    // ========== UNIVERSAL AJAX FORM HANDLER ==========
    document.querySelectorAll('form[data-ajax="true"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = '⏳ ...'; }
            
            var errorContainer = form.querySelector('.ajax-error');
            if (errorContainer) errorContainer.style.display = 'none';
            
            var xhr = new XMLHttpRequest();
            xhr.open(form.method || 'POST', form.action, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.original || submitBtn.textContent; }
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else if (data.reload) {
                            location.reload();
                        } else {
                            showSuccess(data.message || 'عملیات با موفقیت انجام شد.');
                            // If form is in a modal, close it
                            var modal = form.closest('.modal-overlay');
                            if (modal) closeModal(modal.id);
                        }
                    } else {
                        if (errorContainer) {
                            errorContainer.textContent = data.message || 'خطا در انجام عملیات';
                            errorContainer.style.display = 'block';
                        } else {
                            showError('ajax-error-global', data.message || 'خطا در انجام عملیات');
                        }
                    }
                } catch(e) {
                    if (errorContainer) {
                        errorContainer.textContent = 'خطا: ' + xhr.responseText;
                        errorContainer.style.display = 'block';
                    } else {
                        showError('ajax-error-global', 'خطا: ' + xhr.responseText);
                    }
                }
            };
            xhr.onerror = function() {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.original || 'ارسال'; }
                showError('ajax-error-global', 'خطا در ارتباط با سرور');
            };
            xhr.send(serializeForm(form));
        });
        // Save original button text
        var btn = form.querySelector('[type="submit"]');
        if (btn) btn.dataset.original = btn.textContent;
    });
});
