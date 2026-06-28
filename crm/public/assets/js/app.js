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
});

// ========== BULK SELECT & DELETE ==========
function toggleAll(checkbox) {
    var checks = document.querySelectorAll('.row-check');
    checks.forEach(function(c) { c.checked = checkbox.checked; });
    updateBulkBar();
}

function updateBulkBar() {
    var checked = document.querySelectorAll('.row-check:checked');
    var bar = document.getElementById('bulkBar');
    var count = document.getElementById('bulkCount');
    if (!bar) return;
    
    if (checked.length > 0) {
        bar.classList.remove('d-none');
        bar.style.display = 'flex';
        if (count) count.textContent = checked.length + ' مورد انتخاب شده';
    } else {
        bar.classList.add('d-none');
    }
}

function clearSelection() {
    document.querySelectorAll('.row-check').forEach(function(c) { c.checked = false; });
    var selectAll = document.getElementById('selectAll');
    if (selectAll) selectAll.checked = false;
    updateBulkBar();
}

function getSelectedIds() {
    var ids = [];
    document.querySelectorAll('.row-check:checked').forEach(function(c) { ids.push(c.value); });
    return ids;
}

function bulkDelete(entity) {
    var ids = getSelectedIds();
    if (ids.length === 0) { alert('آیتمی انتخاب نشده.'); return; }
    
    if (!confirm('آیا از حذف ' + ids.length + ' مورد اطمینان دارید؟ این عمل غیرقابل بازگشت است.')) return;
    
    var formData = new FormData();
    formData.append('entity', entity);
    ids.forEach(function(id) { formData.append('ids[]', id); });
    
    fetch(CRM_BASE_URL + '/bulk/delete', {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            // Remove deleted rows from table
            ids.forEach(function(id) {
                var row = document.querySelector('tr[data-id="' + id + '"]');
                if (row) row.remove();
            });
            clearSelection();
            showFlash('success', data.message);
        } else {
            alert(data.message);
        }
    })
    .catch(function(e) { alert('خطا: ' + e.message); });
}

function showFlash(type, message) {
    var flash = document.createElement('div');
    flash.className = 'flash flash-' + type;
    flash.innerHTML = '<span>' + message + '</span><button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:16px;">✕</button>';
    flash.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;padding:12px 20px;border-radius:10px;display:flex;gap:12px;align-items:center;font-size:14px;box-shadow:0 4px 20px rgba(0,0,0,0.15);animation:slideDown 0.3s ease;';
    if (type === 'success') { flash.style.background='#d4edda'; flash.style.color='#155724'; flash.style.border='1px solid #c3e6cb'; }
    else { flash.style.background='#f8d7da'; flash.style.color='#721c24'; flash.style.border='1px solid #f5c6cb'; }
    document.body.appendChild(flash);
    setTimeout(function() { flash.remove(); }, 3000);
}

// ========== IN-DOMCONTENTLOADED HELPERS ==========
document.addEventListener('DOMContentLoaded', function() {
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

    // ========== PWA INITIALIZATION ==========
    initPWA();
});

function initPWA() {
    var baseUrl = (typeof CRM_BASE_URL !== 'undefined') ? CRM_BASE_URL : '';
    
    // Inject manifest link
    if (!document.querySelector('link[rel="manifest"]')) {
        var link = document.createElement('link');
        link.rel = 'manifest';
        link.href = baseUrl + '/manifest.json';
        document.head.appendChild(link);
    }
    // Inject theme-color meta
    if (!document.querySelector('meta[name="theme-color"]')) {
        var m = document.createElement('meta');
        m.name = 'theme-color'; m.content = '#4361ee';
        document.head.appendChild(m);
    }
    // Inject apple meta tags
    var appleMetas = {'apple-mobile-web-app-capable':'yes','apple-mobile-web-app-status-bar-style':'black-translucent','apple-mobile-web-app-title':'سفیر اسمان'};
    Object.keys(appleMetas).forEach(function(n){
        if(!document.querySelector('meta[name="'+n+'"]')){
            var t=document.createElement('meta');t.name=n;t.content=appleMetas[n];document.head.appendChild(t);
        }
    });
    // Inject apple-touch-icon
    if(!document.querySelector('link[rel="apple-touch-icon"]')){
        var a=document.createElement('link');a.rel='apple-touch-icon';a.href=baseUrl+'/pwa/icon/icon-192x192.svg';document.head.appendChild(a);
    }

    // Register Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(baseUrl + '/sw.js').then(function(reg) {
            console.log('[PWA] SW registered');
            setInterval(function(){ reg.update(); }, 3600000);
            reg.addEventListener('updatefound', function() {
                var nw = reg.installing;
                nw.addEventListener('statechange', function() {
                    if (nw.state === 'activated') {
                        if (confirm('نسخه جدید سامانه در دسترس است. بروزرسانی شود؟')) location.reload();
                    }
                });
            });
        }).catch(function(e) { console.log('[PWA] SW failed:', e); });
    }

    // Install prompt
    var deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault(); deferredPrompt = e;
        if (document.getElementById('pwaInstallBanner')) return;
        var b = document.createElement('div');
        b.id = 'pwaInstallBanner';
        b.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);z-index:9999;background:linear-gradient(135deg,#4361ee,#7209b7);color:#fff;padding:12px 24px;border-radius:16px;box-shadow:0 8px 30px rgba(67,97,238,0.4);display:flex;align-items:center;gap:12px;font-size:14px;font-family:Vazirmatn,Tahoma,sans-serif;direction:rtl;max-width:90%;';
        b.innerHTML = '<span>📱</span><span>اپلیکیشن را نصب کنید</span>' +
            '<button onclick="installPWA()" style="background:#fff;color:#4361ee;border:none;padding:6px 16px;border-radius:8px;font-family:inherit;font-weight:600;cursor:pointer;">نصب</button>' +
            '<button onclick="dismissPWA()" style="background:transparent;color:rgba(255,255,255,0.8);border:none;padding:6px;cursor:pointer;font-size:18px;">✕</button>';
        document.body.appendChild(b);
    });
    window.installPWA = function() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function(r) { deferredPrompt = null; dismissPWA(); });
    };
    window.dismissPWA = function() {
        var b = document.getElementById('pwaInstallBanner');
        if (b) b.remove();
    };
    window.addEventListener('appinstalled', function() { dismissPWA(); deferredPrompt = null; });
}
