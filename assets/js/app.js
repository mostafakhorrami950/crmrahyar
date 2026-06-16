/**
 * CRM Travel Agency - Custom JavaScript
 * Pure JS, no dependencies
 */
document.addEventListener('DOMContentLoaded', function() {
    // Flash auto-hide
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
    // Pipeline change
    document.getElementById('pipelineSelect')?.addEventListener('change', function() {
        var url = new URL(window.location.href);
        url.searchParams.set('pipeline_id', this.value);
        window.location.href = url.toString();
    });
    // Feature toggle
    document.querySelectorAll('.feature-toggle').forEach(function(t) {
        t.addEventListener('change', function() {
            fetch(this.dataset.url || '/settings/toggle-feature', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'feature=' + this.dataset.feature + '&enabled=' + (this.checked ? 1 : 0)
            }).then(function(r) { return r.json(); }).then(function(d) { if (d.success) location.reload(); });
        });
    });
});