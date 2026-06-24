<?php $config = $GLOBALS['app_config']; $db = \Core\Database::getInstance(); ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2 gap-md-3 flex-wrap">
        <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>پایپ لاین‌ها
        </a>
        <div>
            <h5 class="fw-bold mb-0"><i class="bi bi-kanban me-2 text-primary"></i><?php echo htmlspecialchars($pipeline->name); ?></h5>
        </div>
        <!-- Pipeline Switcher -->
        <?php if (count($pipelines) > 1): ?>
        <select onchange="if(this.value)window.location='<?php echo $config['url']; ?>/pipelines/kanban/'+this.value" class="form-select form-select-sm" style="width:auto;font-size:13px;">
            <?php foreach ($pipelines as $p): ?>
            <option value="<?php echo $p->id; ?>" <?php echo $p->id == $pipeline->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <div class="input-group input-group-sm" style="width:200px;">
            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
            <input type="text" id="kanbanSearch" class="form-control" placeholder="جستجوی معامله..." oninput="filterKanbanCards(this.value)">
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="new bootstrap.Modal(document.getElementById('quickDealModal')).show()">
            <i class="bi bi-lightning me-1"></i>افزودن سریع
        </a>
    </div>
</div>

<!-- Kanban Summary -->
<?php 
$totalDeals = 0; $totalAmount = 0;
foreach ($stages as $s): 
    $stageDeals = $deals[$s->id] ?? [];
    $stageTotal = array_sum(array_map(function($d){ return $d->amount ?? 0; }, $stageDeals));
    $totalDeals += count($stageDeals);
    $totalAmount += $stageTotal;
endforeach;
?>
<div class="d-flex gap-2 flex-wrap mb-3">
    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 fs-7">
        <i class="bi bi-briefcase me-1"></i><strong><?php echo $totalDeals; ?></strong> معامله
    </span>
    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 fs-7">
        <i class="bi bi-cash me-1"></i><strong><?php echo number_format($totalAmount); ?></strong> ریال
    </span>
    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 fs-7">
        <i class="bi bi-layers me-1"></i><strong><?php echo count($stages); ?></strong> مرحله
    </span>
</div>

<!-- Kanban Board -->
<div class="kanban-board" id="kanbanBoard">
    <?php foreach ($stages as $stage): 
        $stageDeals = $deals[$stage->id] ?? [];
        $stageTotal = array_sum(array_map(function($d){ return $d->amount ?? 0; }, $stageDeals));
        $stageColor = htmlspecialchars($stage->color ?? '#6B7280');
    ?>
    <div class="kanban-column" data-stage-id="<?php echo $stage->id; ?>">
        <!-- Column Header -->
        <div class="kanban-col-header" style="border-bottom:3px solid <?php echo $stageColor; ?>;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="color-dot" style="background:<?php echo $stageColor; ?>;"></span>
                        <strong style="font-size:14px;"><?php echo htmlspecialchars($stage->name); ?></strong>
                    </div>
                    <div class="text-muted mt-1" style="font-size:11px;">
                        <span class="fw-semibold text-dark"><?php echo count($stageDeals); ?></span> معامله
                        <?php if ($stageTotal > 0): ?>
                        · <span class="text-success fw-semibold"><?php echo number_format($stageTotal); ?></span> ریال
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-sm btn-link text-muted p-0" title="افزودن معامله">
                    <i class="bi bi-plus-circle fs-5"></i>
                </a>
            </div>
        </div>
        
        <!-- Drop Zone -->
        <div class="kanban-dropzone" data-stage-id="<?php echo $stage->id; ?>" 
             ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event, <?php echo $stage->id; ?>)">
            
            <?php if (empty($stageDeals)): ?>
            <div class="kanban-empty">
                <i class="bi bi-inbox text-muted" style="font-size:24px;opacity:0.3;"></i>
                <div class="mt-1">معامله‌ای نیست</div>
            </div>
            <?php endif; ?>
            
            <?php foreach ($stageDeals as $deal): ?>
            <div class="kanban-card" 
                 draggable="true" 
                 data-deal-id="<?php echo $deal->id; ?>"
                 data-deal-title="<?php echo htmlspecialchars($deal->title); ?>"
                 data-deal-amount="<?php echo $deal->amount ?? 0; ?>"
                 data-deal-contact="<?php echo htmlspecialchars($deal->contact_name ?? ''); ?>"
                 data-deal-phone="<?php echo htmlspecialchars($deal->contact_phone ?? ''); ?>"
                 data-deal-assigned="<?php echo htmlspecialchars($deal->assigned_name ?? ''); ?>"
                 data-deal-created="<?php echo \Core\JDate::displayDate($deal->created_at); ?>"
                 ondragstart="handleDragStart(event)" ondragend="handleDragEnd(event)"
                 ondblclick="openQuickView(<?php echo $deal->id; ?>)">
                
                <!-- Card Header -->
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="fw-bold text-dark text-decoration-none" style="font-size:13px;line-height:1.6;">
                        <?php echo htmlspecialchars(mb_substr($deal->title, 0, 40)); ?>
                    </a>
                    <i class="bi bi-grip-vertical text-muted" style="font-size:12px;opacity:0.3;"></i>
                </div>
                
                <!-- Amount -->
                <?php if ($deal->amount): ?>
                <div class="fw-bold mb-1" style="font-size:15px;color:#059669;">
                    <?php echo number_format($deal->amount); ?>
                    <small class="text-muted fw-normal" style="font-size:10px;">ریال</small>
                </div>
                <?php endif; ?>
                
                <!-- Contact -->
                <?php if (!empty($deal->contact_name)): ?>
                <div class="d-flex align-items-center gap-1 text-muted mb-1" style="font-size:12px;">
                    <i class="bi bi-person"></i>
                    <?php echo htmlspecialchars(mb_substr($deal->contact_name, 0, 20)); ?>
                    <?php if (!empty($deal->contact_phone)): ?>
                    <span dir="ltr" style="font-size:11px;opacity:0.6;"><?php echo htmlspecialchars($deal->contact_phone); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Tags -->
                <?php if (!empty($deal->tags)): ?>
                <div class="d-flex gap-1 flex-wrap mb-1">
                    <?php 
                    $tagsArr = array_filter(explode(',', $deal->tags));
                    foreach (array_slice($tagsArr, 0, 3) as $tag): 
                    ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:10px;padding:1px 6px;">#<?php echo htmlspecialchars(trim($tag)); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Footer -->
                <div class="d-flex justify-content-between align-items-center mt-1 pt-1 border-top">
                    <?php if (!empty($deal->assigned_name)): ?>
                    <span class="text-muted" style="font-size:11px;">
                        <i class="bi bi-person-check me-1"></i><?php echo htmlspecialchars($deal->assigned_name); ?>
                    </span>
                    <?php else: ?>
                    <span></span>
                    <?php endif; ?>
                    <span class="text-muted" style="font-size:10px;"><?php echo \Core\JDate::displayDate($deal->created_at); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick View Modal (Bootstrap) -->
<div class="modal fade" id="dealQuickView" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold" id="qvTitle">-</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="qvBody">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted small mt-2 mb-0">در حال بارگذاری...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.kanban-board {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    padding-bottom: 20px;
    min-height: 70vh;
    align-items: flex-start;
    -webkit-overflow-scrolling: touch;
}
.kanban-column {
    min-width: 290px;
    max-width: 320px;
    flex: 1;
    background: #f8f9fa;
    border-radius: var(--radius);
    display: flex;
    flex-direction: column;
    max-height: 80vh;
}
.kanban-col-header {
    padding: 14px 16px;
    background: #fff;
    border-radius: var(--radius) var(--radius) 0 0;
}
.kanban-dropzone {
    padding: 8px;
    flex: 1;
    overflow-y: auto;
    min-height: 100px;
    transition: background 0.2s;
    border-radius: 0 0 var(--radius) var(--radius);
}
.kanban-dropzone.drag-over {
    background: rgba(67, 97, 238, 0.08);
    outline: 2px dashed var(--primary);
    outline-offset: -4px;
}
.kanban-empty {
    text-align: center;
    padding: 30px 10px;
    color: #adb5bd;
    font-size: 13px;
}
.kanban-card {
    background: #fff;
    border-radius: var(--radius-sm);
    padding: 14px;
    margin-bottom: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    cursor: grab;
    transition: transform 0.15s, box-shadow 0.15s;
    border: 2px solid transparent;
    border-right: 4px solid var(--primary);
}
.kanban-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
    border-color: rgba(67, 97, 238, 0.2);
}
.kanban-card.dragging {
    opacity: 0.4;
    transform: rotate(2deg);
}

@media (max-width: 767.98px) {
    .kanban-column {
        min-width: 260px;
        max-width: 280px;
    }
    .kanban-card {
        padding: 10px;
    }
}
</style>

<script>
// ============ DRAG & DROP ============
var draggedCard = null;
var dragDealId = null;

function handleDragStart(e) {
    draggedCard = e.target.closest('.kanban-card');
    dragDealId = draggedCard.dataset.dealId;
    draggedCard.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', dragDealId);
}

function handleDragEnd(e) {
    if (draggedCard) draggedCard.classList.remove('dragging');
    draggedCard = null;
    dragDealId = null;
    document.querySelectorAll('.kanban-dropzone').forEach(function(z) {
        z.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    var zone = e.target.closest('.kanban-dropzone');
    if (zone) zone.classList.add('drag-over');
}

function handleDragLeave(e) {
    var zone = e.target.closest('.kanban-dropzone');
    if (zone) zone.classList.remove('drag-over');
}

function handleDrop(e, stageId) {
    e.preventDefault();
    var zone = e.target.closest('.kanban-dropzone');
    if (zone) zone.classList.remove('drag-over');
    
    var dealId = e.dataTransfer.getData('text/plain');
    if (!dealId) return;
    
    var fd = new FormData();
    fd.append('deal_id', dealId);
    fd.append('stage_id', stageId);
    
    fetch('<?php echo $config['url']; ?>/pipelines/update-stage', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            var card = document.querySelector('.kanban-card[data-deal-id="' + dealId + '"]');
            if (card && zone) {
                var emptyMsg = zone.querySelector('.kanban-empty');
                if (emptyMsg) emptyMsg.remove();
                zone.appendChild(card);
                updateColumnStats();
                
                // Show success toast
                showToast('معامله با موفقیت منتقل شد', 'success');
            }
        } else {
            showToast(d.message || 'خطا در انتقال', 'danger');
        }
    })
    .catch(function() {
        showToast('خطای شبکه', 'danger');
    });
}

// ============ TOAST NOTIFICATION ============
function showToast(message, type) {
    var toast = document.createElement('div');
    toast.className = 'alert alert-' + (type || 'info') + ' position-fixed shadow-sm';
    toast.style.cssText = 'bottom:20px;left:50%;transform:translateX(-50%);z-index:9999;min-width:250px;text-align:center;font-size:13px;border-radius:12px;';
    toast.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-1"></i>' + message;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}

// ============ UPDATE COLUMN STATS ============
function updateColumnStats() {
    document.querySelectorAll('.kanban-column').forEach(function(col) {
        var cards = col.querySelectorAll('.kanban-card');
        var totalAmount = 0;
        cards.forEach(function(c) {
            totalAmount += parseInt(c.dataset.dealAmount || 0);
        });
        
        var statsDiv = col.querySelector('.kanban-col-header .text-muted');
        if (statsDiv) {
            var html = '<span class="fw-semibold text-dark">' + cards.length + '</span> معامله';
            if (totalAmount > 0) {
                html += ' · <span class="text-success fw-semibold">' + totalAmount.toLocaleString('en-US') + '</span> ریال';
            }
            statsDiv.innerHTML = html;
        }
        
        var zone = col.querySelector('.kanban-dropzone');
        var emptyMsg = zone.querySelector('.kanban-empty');
        if (cards.length === 0 && !emptyMsg) {
            var div = document.createElement('div');
            div.className = 'kanban-empty';
            div.innerHTML = '<i class="bi bi-inbox text-muted" style="font-size:24px;opacity:0.3;"></i><div class="mt-1">معامله‌ای نیست</div>';
            zone.appendChild(div);
        } else if (cards.length > 0 && emptyMsg) {
            emptyMsg.remove();
        }
    });
}

// ============ SEARCH ============
function filterKanbanCards(query) {
    query = query.trim().toLowerCase();
    document.querySelectorAll('.kanban-card').forEach(function(card) {
        var title = (card.dataset.dealTitle || '').toLowerCase();
        var contact = (card.dataset.dealContact || '').toLowerCase();
        var phone = (card.dataset.dealPhone || '').toLowerCase();
        
        if (!query || title.indexOf(query) !== -1 || contact.indexOf(query) !== -1 || phone.indexOf(query) !== -1) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// ============ QUICK VIEW ============
function openQuickView(dealId) {
    var modal = new bootstrap.Modal(document.getElementById('dealQuickView'));
    document.getElementById('qvBody').innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="text-muted small mt-2 mb-0">در حال بارگذاری...</p></div>';
    modal.show();
    
    fetch('<?php echo $config['url']; ?>/deals/get-data/' + dealId)
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            var deal = d.deal;
            document.getElementById('qvTitle').textContent = deal.title || '-';
            var amount = deal.amount ? (parseInt(deal.amount).toLocaleString('en-US') + ' ریال') : '-';
            var status = deal.is_won ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>موفق</span>' : 
                        (deal.is_lost ? '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>ناموفق</span>' : 
                        '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>در حال بررسی</span>');
            
            document.getElementById('qvBody').innerHTML = 
                '<div class="row g-2">' +
                    '<div class="col-6"><div class="bg-light rounded-3 p-3"><div class="text-muted small mb-1"><i class="bi bi-cash me-1"></i>مبلغ</div><strong>' + amount + '</strong></div></div>' +
                    '<div class="col-6"><div class="bg-light rounded-3 p-3"><div class="text-muted small mb-1"><i class="bi bi-bar-chart me-1"></i>وضعیت</div>' + status + '</div></div>' +
                '</div>' +
                '<div class="mt-3"><a href="<?php echo $config['url']; ?>/deals/view/' + dealId + '" class="btn btn-primary w-100"><i class="bi bi-eye me-1"></i>مشاهده جزئیات کامل</a></div>';
        } else {
            document.getElementById('qvBody').innerHTML = '<div class="text-center py-3 text-danger"><i class="bi bi-exclamation-circle fs-3"></i><p class="mt-2">خطا در بارگذاری</p></div>';
        }
    });
}
</script>

<?php include __DIR__ . '/../deals/_quick_create_modal.php'; ?>
