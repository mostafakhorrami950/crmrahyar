<?php
ob_start();
$baseUrl = \Shared\Core\Config::getInstance()->url();
?>

<!-- JSON-LD Article Schema (SEO 2026) -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": <?php echo json_encode($post->title ?? '', JSON_UNESCAPED_UNICODE); ?>,
    "description": <?php echo json_encode($post->excerpt ?? $post->meta_description ?? '', JSON_UNESCAPED_UNICODE); ?>,
    "datePublished": <?php echo json_encode($post->published_at ?? $post->created_at ?? '', JSON_UNESCAPED_UNICODE); ?>,
    "dateModified": <?php echo json_encode($post->updated_at ?? $post->published_at ?? '', JSON_UNESCAPED_UNICODE); ?>,
    "author": { "@type": "Organization", "name": <?php echo json_encode($company ?? 'آژانس مسافرتی رهیار', JSON_UNESCAPED_UNICODE); ?> },
    "publisher": { "@type": "Organization", "name": <?php echo json_encode($company ?? 'آژانس مسافرتی رهیار', JSON_UNESCAPED_UNICODE); ?> },
    "mainEntityOfPage": { "@type": "WebPage", "@id": "<?php echo $baseUrl; ?>/blog/<?php echo urlencode($post->slug ?? ''); ?>" }
    <?php if (!empty($post->featured_image)): ?>
    ,"image": "<?php echo $baseUrl . htmlspecialchars($post->featured_image); ?>"
    <?php endif; ?>
}
</script>

<!-- BreadcrumbList Schema -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "خانه", "item": "<?php echo $baseUrl; ?>/" },
        { "@type": "ListItem", "position": 2, "name": "بلاگ", "item": "<?php echo $baseUrl; ?>/blog" },
        { "@type": "ListItem", "position": 3, "name": <?php echo json_encode($post->title ?? '', JSON_UNESCAPED_UNICODE); ?>, "item": "<?php echo $baseUrl; ?>/blog/<?php echo urlencode($post->slug ?? ''); ?>" }
    ]
}
</script>

<div class="container" style="max-width: 750px; margin: 0 auto; padding: 40px 20px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" style="font-size: 12px; color: #94a3b8; margin-bottom: 16px;">
        <a href="/" style="color: #4f46e5;">خانه</a>
        <span style="margin: 0 6px;">›</span>
        <a href="/blog" style="color: #4f46e5;">بلاگ</a>
        <span style="margin: 0 6px;">›</span>
        <span><?php echo htmlspecialchars($post->title ?? ''); ?></span>
    </nav>

    <article>
        <h1 style="font-size: 30px; font-weight: 900; margin-bottom: 12px; line-height: 1.3;"><?php echo htmlspecialchars($post->title ?? ''); ?></h1>

        <div style="font-size: 12px; color: #94a3b8; margin-bottom: 24px;">
            <?php if (!empty($post->published_at)): ?>
            <time datetime="<?php echo $post->published_at; ?>">📅 <?php echo date('Y/m/d', strtotime($post->published_at)); ?></time>
            <?php endif; ?>
            <?php if (!empty($post->focus_keyword)): ?>
            <span style="margin-right: 12px; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">🏷️ <?php echo htmlspecialchars($post->focus_keyword); ?></span>
            <?php endif; ?>
        </div>

        <?php if (!empty($post->featured_image)): ?>
        <figure style="margin-bottom: 24px;">
            <img src="<?php echo htmlspecialchars($post->featured_image); ?>" alt="<?php echo htmlspecialchars($post->image_alt ?? $post->title ?? ''); ?>" style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 12px;">
        </figure>
        <?php endif; ?>

        <?php if (!empty($post->excerpt)): ?>
        <p style="font-size: 15px; color: #475569; line-height: 1.7; margin-bottom: 24px; font-style: italic; border-right: 3px solid #4f46e5; padding-right: 16px;"><?php echo htmlspecialchars($post->excerpt); ?></p>
        <?php endif; ?>

        <div class="article-content" style="font-size: 15px; color: #1e293b; line-height: 1.8;"><?php echo $post->content ?? ''; ?></div>
    </article>

    <!-- Back to blog -->
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
        <a href="/blog" style="color: #4f46e5; font-weight: 700; font-size: 14px;">← بازگشت به بلاگ</a>
    </div>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? [
    'title' => $post->meta_title ?: ($post->title ?? 'مقاله'),
    'description' => $post->meta_description ?: ($post->excerpt ?? ''),
    'canonical' => $baseUrl . '/blog/' . ($post->slug ?? ''),
    'og_title' => $post->meta_title ?: ($post->title ?? ''),
    'og_description' => $post->meta_description ?: ($post->excerpt ?? ''),
    'og_image' => !empty($post->featured_image) ? $baseUrl . $post->featured_image : '',
    'og_type' => 'article',
];
require __DIR__ . '/../layouts/main.php';
?>