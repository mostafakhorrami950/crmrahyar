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
        }
    };
    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
                document.body.style.overflow = '';
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
        // Remove formatting on focus for editing
        input.addEventListener('focus', function() {
            var value = this.value.replace(/[^0-9]/g, '');
            if (value) this.value = value;
        });
        // Add formatting on blur
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

    // ========== STAGE UPDATE (Drag & Drop simulation) ==========
    // For kanban cards, clicking changes stage via AJAX
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
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'deal_id=' + dealId + '&stage_id=' + stageId
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(function(err) { console.error(err); });
        });
    });

    // ========== AJAX FORM SUBMIT (for quick actions) ==========
    document.querySelectorAll('.ajax-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'خطا در اجرای درخواست');
                }
            })
            .catch(function(err) { console.error(err); });
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
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'feature=' + feature + '&enabled=' + enabled
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(function(err) { console.error(err); });
        });
    });

});