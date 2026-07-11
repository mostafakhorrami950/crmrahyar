<?php
ob_start();
?>
<div class="container" style="max-width: 900px; margin: 0 auto; padding: 40px 20px;">
    <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 24px;">📝 بلاگ</h1>
    <?php if (!empty($posts)): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
        <?php foreach ($posts as $post): ?>
        <a href="/blog/<?php echo htmlspecialchars($post->slug); ?>" style="background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; text-decoration: none; transition: 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 40px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div style="height: 140px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 40px;">📝</div>
            <div style="padding: 14px;">
                <?php if (!empty($post->category)): ?><div style="font-size: 10px; color: #4f46e5; font-weight: 700; margin-bottom: 6px; text-transform: uppercase;"><?php echo htmlspecialchars($post->category); ?></div><?php endif; ?>
                <h3 style="font-size: 15px; font-weight: 800; margin-bottom: 6px; color: #1e293b;"><?php echo htmlspecialchars($post->title); ?></h3>
                <?php if (!empty($post->excerpt)): ?><p style="font-size: 12px; color: #64748b; line-height: 1.5;"><?php echo mb_substr($post->excerpt, 0, 100); ?>...</p><?php endif; ?>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 8px;"><?php echo $post->published_at ? date('Y/m/d', strtotime($post->published_at)) : ''; ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 60px;"><p style="color: #94a3b8;">مقاله‌ای منتشر نشده است.</p></div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'بلاگ', 'description' => 'مقالات و اخبار'];
require __DIR__ . '/../layouts/main.php';
?>