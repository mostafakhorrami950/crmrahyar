<?php
ob_start();
?>

<!-- JSON-LD Article Schema -->
<?php if (!empty($schema)): ?>
<script type="application/ld+json"><?php echo json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<?php endif; ?>

<div class="container" style="max-width: 750px; margin: 0 auto; padding: 40px 20px;">
    <!-- Breadcrumb -->
    <div style="font-size: 12px; color: #94a3b8; margin-bottom: 16px;">
        <a href="/" style="color: #4f46e5;">خانه</a>
        <span style="margin: 0 6px;">›</span>
        <a href="/blog" style="color: #4f46e5;">بلاگ</a>
        <span style="margin: 0 6px;">›</span>
        <span><?php echo htmlspecialchars($post->title); ?></span>
    </div>

    <?php if (!empty($post->category)): ?>
    <div style="font-size: 11px; color: #4f46e5; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;"><?php echo htmlspecialchars($post->category); ?></div>
    <?php endif; ?>

    <h1 style="font-size: 30px; font-weight: 900; margin-bottom: 12px; line-height: 1.3;"><?php echo htmlspecialchars($post->title); ?></h1>

    <div style="font-size: 12px; color: #94a3b8; margin-bottom: 24px;">
        <?php if (!empty($post->published_at)): ?>
        📅 <?php echo date('Y/m/d', strtotime($post->published_at)); ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($post->excerpt)): ?>
    <p style="font-size: 15px; color: #475569; line-height: 1.7; margin-bottom: 24px; font-style: italic; border-right: 3px solid #4f46e5; padding-right: 16px;"><?php echo htmlspecialchars($post->excerpt); ?></p>
    <?php endif; ?>

    <div style="font-size: 15px; color: #1e293b; line-height: 1.8;"><?php echo $post->content; ?></div>

    <!-- Tags -->
    <?php
    $tags = !empty($post->tags_json) ? json_decode($post->tags_json, true) : [];
    if (!empty($tags)):
    ?>
    <div style="margin-top: 24px; display: flex; gap: 6px; flex-wrap: wrap;">
        <?php foreach ($tags as $tag): ?>
        <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars($tag); ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => $post->title ?? 'مقاله', 'description' => $post->excerpt ?? ''];
require __DIR__ . '/../layouts/main.php';
?>