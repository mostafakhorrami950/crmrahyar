<?php
ob_start();
?>
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">
    <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 20px;"><?php echo htmlspecialchars($page->title); ?></h1>
    <div style="font-size: 15px; color: #475569; line-height: 1.8;"><?php echo $page->content; ?></div>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => $page->title ?? 'صفحه', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>