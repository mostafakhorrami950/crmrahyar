<?php $config = $GLOBALS['app_config']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-bell me-1"></i> اعلان‌ها</h5>
    <form method="POST" action="<?php echo $config['url']; ?>/notifications/mark-all-read" style="display:inline;">
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-check-circle text-success me-1"></i> خواندن همه</button>
    </form>
</div>

<?php if (empty($notifications)): ?>
<div class="empty-state">
    <div class="empty-icon"><i class="bi bi-bell me-1"></i></div>
    <h5 class="fw-bold mb-0">اعلانی ندارید</h5>
</div>
<?php else: ?>
<div class="card">
    <?php foreach ($notifications as $n): ?>
    <div class="notif-item <?php echo !$n->is_read ? 'unread' : ''; ?>" style="padding:14px 16px;border-bottom:1px solid var(--gray-100);cursor:pointer;" onclick="markRead(<?php echo $n->id; ?>, '<?php echo $n->link; ?>')">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="notif-title"><?php echo $n->from_user_name ? htmlspecialchars($n->from_user_name) . ': ' : ''; ?><?php echo htmlspecialchars($n->title); ?></div>
                <?php if ($n->message): ?>
                <div class="notif-msg"><?php echo htmlspecialchars($n->message); ?></div>
                <?php endif; ?>
            </div>
            <span class="notif-time"><?php echo \Core\JDate::displayDate($n->created_at); ?></span>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if ($total > $perPage): ?>
    <div class="pagination" style="padding:16px;">
        <?php for ($i = 1; $i <= ceil($total / $perPage); $i++): ?>
        <a href="<?php echo $config['url']; ?>/notifications?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
function markRead(id, link) {
    fetch('<?php echo $config['url']; ?>/notifications/mark-read/' + id, {method:'POST'})
        .then(function() {
            if (link && link !== '') window.location.href = '<?php echo $config['url']; ?>' + link;
            else window.location.reload();
        });
}
</script>