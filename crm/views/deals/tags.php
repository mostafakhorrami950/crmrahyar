<div class="page-header">
    <h5>🏷️ همه هشتگ‌ها</h5>
    <a href="<?php echo $config['url']; ?>/deals" class="btn btn-secondary">بازگشت به معاملات</a>
</div>

<div class="card">
    <?php if (empty($tags)): ?>
    <div class="empty-state">
        <div class="empty-icon">🏷️</div>
        <h5>هیچ هشتگی وجود ندارد</h5>
        <p>برای استفاده از هشتگ‌ها، در توضیحات معاملات از <strong>#</strong> استفاده کنید.</p>
        <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-primary">➕ ایجاد معامله جدید</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-wrap:wrap;gap:10px;padding:20px;">
        <?php 
        $maxCount = 1;
        foreach ($tags as $t) { if ($t['count'] > $maxCount) $maxCount = $t['count']; }
        
        foreach ($tags as $t): 
            $size = 14 + (($t['count'] / $maxCount) * 20);
            $colors = ['#4361ee', '#7209b7', '#f72585', '#4cc9f0', '#f8961e', '#43aa8b', '#577590', '#e63946'];
            $color = $colors[array_rand($colors)];
        ?>
        <a href="<?php echo $config['url']; ?>/deals/tag/<?php echo urlencode($t['tag']); ?>" 
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:<?php echo $color; ?>15;color:<?php echo $color; ?>;border-radius:20px;text-decoration:none;font-size:<?php echo $size; ?>px;font-weight:bold;transition:all 0.2s;"
           onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
            #<?php echo htmlspecialchars($t['tag']); ?>
            <span style="background:<?php echo $color; ?>30;padding:2px 8px;border-radius:10px;font-size:12px;"><?php echo $t['count']; ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>