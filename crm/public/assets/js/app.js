/**
 * CRM Travel Agency - Custom JavaScript
 * Pure JS, no dependencies required
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ========== FLASH MESSAGES AUTO-HIDE ==========
    document.querySelectorAll('.flash').forEach(function(flash) {
        setTimeout(function() {
            flash.style.opacity = '0';
            flash.style.transform = 'translateX(80px)';
            flash.style.transition = 'all 0.3s ease';
            setTimeout(function() { flash.remove(); }, 300);
        }, 5000);
    });

    // Close flash
    document.querySelectorAll('.flash .close-btn').forEach(function(btn) {
        btn.addEventListener('click', function() { this.parentElement.remove(); });
    });

    // ========== USER DROPDOWN ==========
    document.querySelectorAll('.user-dropdown-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            this.nextElementSibling.classList.toggle('show');
        });
    });
    document.addEventListener('click', function() {
        document.querySelectorAll('.user-dropdown-menu.show').forEach(function(m) { m.classList.remove('show'); });
    });

    // ========== MODAL ==========
    window.openModal = function(id) {
        var modal = document.getElementById(id);
        if (modal) { modal.classList.add('show'); document.body.style.overflow = 'hidden'; }
    };
    window.closeModal = function(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
            clearModalErrors(modal);
        }
    };
    function clearModalErrors(modal) {
        modal.querySelectorAll('.ajax-error').forEach(function(e) {
            e.style.display = 'none'; e.innerHTML = '';
        });
    }
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) { this.classList.remove('show'); document.body.style.overflow = ''; clearModalErrors(this); }
        });
    });
    document.querySelectorAll('.modal-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = this.closest('.modal-overlay');
            if (modal) { modal.classList.remove('show'); document.body.style.overflow = ''; clearModalErrors(modal); }
        });
    });

    // ========== MOBILE SIDEBAR ==========
    // Sidebar toggle handled in main layout inline script to avoid duplicate listeners

    // ========== COPY PUBLIC LINK ==========
    window.copyPublicLink = function() {
        var input = document.getElementById('publicPayLink');
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function() {
            var btn = input.nextElementSibling;
            if (btn) {
                var original = btn.innerHTML;
                btn.innerHTML = '✅ کپی شد!';
                btn.style.background = '#28a745';
                setTimeout(function() {
                    btn.innerHTML = original;
                    btn.style.background = '';
                }, 2000);
            }
        }).catch(function() {
            document.execCommand('copy');
        });
    };

    // ========== DATA-AJAX FORM HANDLER ==========
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form.matches('form[data-ajax="true"]')) return;
        e.preventDefault();

        var submitBtn = form.querySelector('[type="submit"]');
        var originalText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '⏳ ...'; }

        var formData = new FormData(form);
        var errorDiv = form.querySelector('.ajax-error');
        if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.innerHTML = ''; }

        fetch(form.action, {
            method: form.method || 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalText; }

            if (data.success) {
                // Contact created - auto-select in parent form
                if (data.contact) {
                    var contactSelect = document.getElementById('contactSelect');
                    if (contactSelect) {
                        var opt = document.createElement('option');
                        opt.value = data.contact.id;
                        opt.textContent = data.contact.full_name + ' - ' + data.contact.phone;
                        opt.selected = true;
                        contactSelect.appendChild(opt);
                    }
                    var modal = form.closest('.modal-overlay');
                    if (modal) { modal.classList.remove('show'); document.body.style.overflow = ''; clearModalErrors(modal); }
                    form.reset();
                    return;
                }

                // Handle public payment link (show on payment create page)
                if (data.public_link) {
                    var linkInput = document.getElementById('publicPayLink');
                    var linkSection = document.getElementById('publicLinkSection');
                    var directBtn = document.getElementById('directPayBtn');
                    if (linkInput && linkSection) {
                        linkInput.value = data.public_link;
                        linkSection.style.display = 'block';
                        if (data.redirect && directBtn) {
                            directBtn.onclick = function() {
                                window.location.href = data.redirect;
                            };
                        }
                        // Show success message
                        var successDiv = form.querySelector('.ajax-success');
                        if (successDiv && data.message) {
                            successDiv.innerHTML = '✅ ' + data.message;
                            successDiv.style.display = 'block';
                        }
                        // Enable submit button so user can create another
                        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalText; }
                        return;
                    }
                }

                // Redirect (fix base path)
                if (data.redirect) {
                    // If redirect doesn't start with /crm, prepend it
                    var basePath = '/crm';
                    var redirectUrl = data.redirect;
                    if (redirectUrl.indexOf(basePath) !== 0) {
                        redirectUrl = basePath + redirectUrl;
                    }
                    window.location.href = redirectUrl;
                    return;
                }
                location.reload();
            } else {
                var msg = data.message || 'خطا در اجرای درخواست';
                if (errorDiv) { errorDiv.innerHTML = msg; errorDiv.style.display = 'block'; }
                else { alert(msg); }
            }
        })
        .catch(function(err) {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalText; }
            if (errorDiv) { errorDiv.innerHTML = 'خطا در ارتباط با سرور'; errorDiv.style.display = 'block'; }
            else { alert('خطا در ارتباط با سرور'); }
            console.error(err);
        });
    });

});