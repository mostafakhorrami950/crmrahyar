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

    // ========== CLOSE FLASH ==========
    document.querySelectorAll('.flash .close-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.parentElement.remove();
        });
    });

    // ========== USER DROPDOWN ==========
    var dropdownBtns = document.querySelectorAll('.user-dropdown-btn');
    dropdownBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var menu = this.nextElementSibling;
            menu.classList.toggle('show');
        });
    });
    document.addEventListener('click', function() {
        document.querySelectorAll('.user-dropdown-menu.show').forEach(function(m) {
            m.classList.remove('show');
        });
    });

    // ========== MODAL ==========
    window.openModal = function(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };
    window.closeModal = function(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
            // Clear errors
            var errDiv = modal.querySelector('.ajax-error');
            if (errDiv) { errDiv.style.display = 'none'; errDiv.innerHTML = ''; }
        }
    };
    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
                document.body.style.overflow = '';
                // Clear errors
                this.querySelectorAll('.ajax-error').forEach(function(e) {
                    e.style.display = 'none'; e.innerHTML = '';
                });
            }
        });
    });
    // Close modal on close button
    document.querySelectorAll('.modal-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = this.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
                modal.querySelectorAll('.ajax-error').forEach(function(e) {
                    e.style.display = 'none'; e.innerHTML = '';
                });
            }
        });
    });

    // ========== MOBILE SIDEBAR ==========
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('show');
        });
    }
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            this.classList.remove('show');
        });
    }

    // ========== NUMBER FORMAT ==========
    document.querySelectorAll('[data-format="number"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            var value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                this.value = new Intl.NumberFormat('fa-IR').format(parseInt(value));
            }
        });
        input.addEventListener('focus', function() {
            var value = this.value.replace(/[^0-9]/g, '');
            if (value) this.value = value;
        });
        input.addEventListener('blur', function() {
            var value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                this.value = new Intl.NumberFormat('fa-IR').format(parseInt(value));
            }
        });
    });

    // ========== CONFIRM DIALOGS ==========
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'آیا اطمینان دارید؟')) {
                e.preventDefault();
            }
        });
    });

    // ========== STAGE UPDATE ==========
    document.querySelectorAll('.kanban-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            var dealUrl = this.dataset.url;
            if (dealUrl && !e.target.closest('.stage-select')) {
                window.location.href = dealUrl;
            }
        });
    });

    // ========== STAGE SELECT CHANGE ==========
    document.querySelectorAll('.stage-select').forEach(function(select) {
        select.addEventListener('change', function() {
            var dealId = this.dataset.dealId;
            var stageId = this.value;
            if (!dealId || !stageId) return;
            
            fetch(this.dataset.url || '/deals/update-stage', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'deal_id=' + dealId + '&stage_id=' + stageId
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) { location.reload(); }
            })
            .catch(function(err) { console.error(err); });
        });
    });

    // ========== DATA-AJAX FORM HANDLER ==========
    // Handles forms with data-ajax="true" attribute
    // Supports: modal forms (auto-close on success), inline forms (show errors)
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
                // Check if this is a contact create modal form
                if (data.contact) {
                    // Auto-select the contact in the parent form
                    var contactSelect = document.getElementById('contactSelect');
                    if (contactSelect) {
                        var opt = document.createElement('option');
                        opt.value = data.contact.id;
                        opt.textContent = data.contact.full_name + ' - ' + data.contact.phone;
                        opt.selected = true;
                        contactSelect.appendChild(opt);
                    }
                    // Close the modal
                    var modal = form.closest('.modal-overlay');
                    if (modal) {
                        modal.classList.remove('show');
                        document.body.style.overflow = '';
                        modal.querySelectorAll('.ajax-error').forEach(function(e) {
                            e.style.display = 'none'; e.innerHTML = '';
                        });
                    }
                    // Reset form
                    form.reset();
                    return;
                }

                // If has redirect, go there
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                // Otherwise reload
                location.reload();
            } else {
                // Show error
                var msg = data.message || 'خطا در اجرای درخواست';
                if (errorDiv) {
                    errorDiv.innerHTML = msg;
                    errorDiv.style.display = 'block';
                } else {
                    alert(msg);
                }
            }
        })
        .catch(function(err) {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalText; }
            if (errorDiv) {
                errorDiv.innerHTML = 'خطا در ارتباط با سرور';
                errorDiv.style.display = 'block';
            } else {
                alert('خطا در ارتباط با سرور');
            }
            console.error(err);
        });
    });

    // ========== PIPELINE CHANGE ==========
    var pipelineSelect = document.getElementById('pipelineSelect');
    if (pipelineSelect) {
        pipelineSelect.addEventListener('change', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('pipeline_id', this.value);
            window.location.href = url.toString();
        });
    }

    // ========== SEARCH INPUT ==========
    var searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(function(input) {
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                var url = new URL(window.location.href);
                url.searchParams.set('search', this.value);
                window.location.href = url.toString();
            }
        });
    });

    // ========== TOGGLE FEATURE (Settings) ==========
    document.querySelectorAll('.feature-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            var feature = this.dataset.feature;
            var enabled = this.checked ? 1 : 0;
            
            fetch(this.dataset.url || '/settings/toggle-feature', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'feature=' + feature + '&enabled=' + enabled
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) { location.reload(); }
            })
            .catch(function(err) { console.error(err); });
        });
    });

    // ========== ACTIVITY TOGGLE ==========
    // Already handled by data-ajax handler above

});