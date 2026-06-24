<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-journal-text me-1"></i> لاگ سیستم</h5>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="d-flex gap-8" style="flex-wrap:wrap;">
        <?php foreach ($files as $f): ?>
        <a href="<?php echo $config['url']; ?>/system/logs?file=<?php echo urlencode($f['name']); ?>" 
           class="btn btn-sm <?php echo $currentFile === $f['name'] ? 'btn-primary' : 'btn-secondary'; ?>">
            📄 <?php echo htmlspecialchars($f['name']); ?> (<?php echo $f['size']; ?>)
        </a>
        <?php endforeach; ?>
        <?php if (empty($files)): ?>
        <p class="text-muted">فایل لاگی یافت نشد</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($content): ?>
<div class="card">
    <div class="card-header">📄 <?php echo htmlspecialchars($currentFile); ?></div>
    <pre style="background:var(--gray-900);color:#a7f3d0;padding:16px;border-radius:8px;font-size:12px;line-height:1.6;overflow-x:auto;max-height:600px;overflow-y:auto;direction:ltr;text-align:left;font-family:'Courier New',monospace;white-space:pre-wrap;word-break:break-all;"><?php echo htmlspecialchars($content); ?></pre>
</div>
<?php endif; ?>