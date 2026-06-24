<?php $config = $GLOBALS['app_config']; ?>

<!-- Marked.js for Markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>تاریخچه تحلیل‌های هوش مصنوعی</h5>
    <div class="d-flex gap-2">
        <a href="<?php echo $config['url']; ?>/reports" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت به گزارشات</a>
        <a href="<?php echo $config['url']; ?>/reports" class="btn btn-warning btn-sm"><i class="bi bi-robot me-1"></i>تحلیل جدید</a>
    </div>
</div>

<?php if (empty($analyses)): ?>
<div class="card border-0 shadow-sm text-center py-5">
    <i class="bi bi-robot fs-1 text-muted d-block mb-3 opacity-25"></i>
    <h5 class="text-muted">هنوز هیچ تحلیلی انجام نشده</h5>
    <p class="text-muted">از صفحه گزارشات، دکمه «تحلیل با هوش مصنوعی» را بزنید</p>
    <a href="<?php echo $config['url']; ?>/reports" class="btn btn-warning mt-2"><i class="bi bi-robot me-1"></i>رفتن به گزارشات</a>
</div>
<?php else: ?>

<?php foreach ($analyses as $a): ?>
<div class="card border-0 shadow-sm mb-4 ai-analysis-card">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#ai-<?php echo $a->id; ?>">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);color:#fff;">
                <i class="bi bi-robot fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0"><?php echo \Core\JDate::displayDateTime($a->created_at); ?></h6>
                <small class="text-muted">
                    <i class="bi bi-cpu me-1"></i><?php echo htmlspecialchars($a->model ?? 'نامشخص'); ?>
                    <span class="mx-1">|</span>
                    <i class="bi bi-briefcase me-1"></i><?php echo number_format($a->deals_count); ?> معامله
                    <span class="mx-1">|</span>
                    <i class="bi bi-cash me-1"></i><?php echo number_format($a->total_amount); ?> ت
                    <?php if ($a->user_name): ?>
                    <span class="mx-1">|</span>
                    <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($a->user_name); ?>
                    <?php endif; ?>
                </small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary copy-ai-btn" data-id="<?php echo $a->id; ?>" title="کپی"><i class="bi bi-clipboard"></i></button>
            <i class="bi bi-chevron-down text-muted"></i>
        </div>
    </div>
    <div class="collapse <?php echo $a === reset($analyses) ? 'show' : ''; ?>" id="ai-<?php echo $a->id; ?>">
        <div class="card-body p-4">
            <div class="ai-markdown-content" id="ai-content-<?php echo $a->id; ?>" style="direction:rtl;line-height:2;font-size:14px;"></div>
            <textarea class="d-none" id="ai-raw-<?php echo $a->id; ?>"><?php echo htmlspecialchars($a->result); ?></textarea>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configure marked for RTL
    marked.setOptions({
        breaks: true,
        gfm: true
    });
    
    // Render all AI results as Markdown
    document.querySelectorAll('.ai-markdown-content').forEach(function(el) {
        var id = el.id.replace('ai-content-', '');
        var rawEl = document.getElementById('ai-raw-' + id);
        if (rawEl) {
            try {
                el.innerHTML = marked.parse(rawEl.value);
            } catch(e) {
                el.textContent = rawEl.value;
            }
        }
    });
    
    // Copy button
    document.querySelectorAll('.copy-ai-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var id = this.dataset.id;
            var rawEl = document.getElementById('ai-raw-' + id);
            if (rawEl) {
                navigator.clipboard.writeText(rawEl.value).then(function() {
                    btn.innerHTML = '<i class="bi bi-check"></i>';
                    setTimeout(function() { btn.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 2000);
                });
            }
        });
    });
});
</script>

<style>
.ai-markdown-content h1, .ai-markdown-content h2, .ai-markdown-content h3 { 
    color: #1e293b; 
    margin-top: 1.2rem; 
    margin-bottom: 0.6rem; 
    font-weight: 700;
}
.ai-markdown-content h1 { font-size: 1.4rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.4rem; }
.ai-markdown-content h2 { font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.3rem; }
.ai-markdown-content h3 { font-size: 1.05rem; }
.ai-markdown-content ul, .ai-markdown-content ol { padding-right: 1.5rem; padding-left: 0; }
.ai-markdown-content li { margin-bottom: 0.4rem; }
.ai-markdown-content strong { color: #0f172a; }
.ai-markdown-content code { 
    background: #f1f5f9; 
    padding: 2px 6px; 
    border-radius: 4px; 
    font-size: 13px;
    direction: ltr;
    display: inline-block;
}
.ai-markdown-content pre { 
    background: #1e293b; 
    color: #e2e8f0; 
    padding: 1rem; 
    border-radius: 8px; 
    overflow-x: auto;
    direction: ltr;
}
.ai-markdown-content pre code { background: transparent; color: inherit; }
.ai-markdown-content blockquote { 
    border-right: 4px solid #667eea; 
    padding-right: 1rem; 
    margin: 1rem 0; 
    color: #64748b;
    background: #f8fafc;
    padding: 0.8rem 1rem;
    border-radius: 0 8px 8px 0;
}
.ai-markdown-content table { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 1rem 0; 
    font-size: 13px;
}
.ai-markdown-content th, .ai-markdown-content td { 
    border: 1px solid #e2e8f0; 
    padding: 8px 12px; 
    text-align: right; 
}
.ai-markdown-content th { background: #f1f5f9; font-weight: 600; }
.ai-markdown-content hr { border: none; border-top: 2px solid #e2e8f0; margin: 1.5rem 0; }
.ai-markdown-content p { margin-bottom: 0.6rem; }
</style>