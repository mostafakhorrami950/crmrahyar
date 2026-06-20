<?php $config = $GLOBALS['app_config']; $db = \Core\Database::getInstance(); ?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?php echo $config['url']; ?>/pipelines" class="btn btn-sm btn-secondary">← پایپ لاین‌ها</a>
        <h5 style="margin:0;">📋 <?php echo htmlspecialchars($pipeline->name); ?></h5>
        <!-- Pipeline Switcher -->
        <?php if (count($pipelines) > 1): ?>
        <select onchange="if(this.value)window.location='<?php echo $config['url']; ?>/pipelines/kanban/'+this.value" class="form-input" style="width:auto;font-size:13px;padding:4px 8px;">
            <?php foreach ($pipelines as $p): ?>
            <option value="<?php echo $p->id; ?>" <?php echo $p->id == $pipeline->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <input type="text" id="kanbanSearch" class="form-input" placeholder="🔍 جستجوی معامله..." style="width:200px;font-size:13px;padding:6px 10px;" oninput="filterKanbanCards(this.value)">
        <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-sm btn-primary">➕ معامله جدید</a>
    </div>
</div>

<!-- Kanban Summary -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <?php 
    $totalDeals = 0; $totalAmount = 0;
    foreach ($stages as $s): 
        $stageDeals = $deals[$s->id] ?? [];
        $stageTotal = array_sum(array_map(function($d){ return $d->amount ?? 0; }, $stageDeals));
        $totalDeals += count($stageDeals);
        $totalAmount += $stageTotal;
    endforeach;
    ?>
    <span style="background:var(--gray-100);padding:6px 14px;border-radius:20px;font-size:13px;">
        💼 <strong><?php echo $totalDeals; ?></strong> معامله
    </span>
    <span style="background:var(--gray-100);padding:6px 14px;border-radius:20px;font-size:13px;">
        💰 <strong><?php echo number_format($totalAmount); ?></strong> تومان
    </span>
    <span style="background:var(--gray-100);padding:6px 14px;border-radius:20px;font-size:13px;">
        📊 <strong><?php echo count($stages); ?></strong> مرحله
    </span>
</div>

<!-- Kanban Board -->
<div class="kanban-board" id="kanbanBoard">
    <?php foreach ($stages as $stage): 
        $stageDeals = $deals[$stage->id] ?? [];
        $stageTotal = array_sum(array_map(function($d){ return $d->amount ?? 0; }, $stageDeals));
    ?>
    <div class="kanban-column" data-stage-id="<?php echo $stage->id; ?>">
        <!-- Column Header -->
        <div class="kanban-column-header" style="border-top:4px solid <?php echo htmlspecialchars($stage->color ?? '#6B7280'); ?>;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <strong style="font-size:14px;"><?php echo htmlspecialchars($stage->name); ?></strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:2px;">
                        <?php echo count($stageDeals); ?> معامله • <?php echo number_format($stageTotal); ?> ت
                    </div>
                </div>
                <a href="<?php echo $config['url']; ?>/deals/create" style="font-size:18px;color:var(--gray-400);text-decoration:none;" title="افزودن معامله">➕</a>
            </div>
        </div>
        
        <!-- Drop Zone -->
        <div class="kanban-dropzone" data-stage-id="<?php echo $stage->id; ?>" 
             ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event, <?php echo $stage->id; ?>)">
            
            <?php if (empty($stageDeals)): ?>
            <div class="kanban-empty">معامله‌ای نیست</div>
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
                 ondragstart="handleDragStart(event)" ondragend="handleDragEnd(event)">
                
                <!-- Card Header -->
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
                    <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" style="font-weight:700;font-size:13px;color:var(--gray-900);text-decoration:none;line-height:1.5;">
                        <?php echo htmlspecialchars(mb_substr($deal->title, 0, 40)); ?>
                    </a>
                    <span style="font-size:11px;color:var(--gray-300);flex-shrink:0;">☰</span>
                </div>
                
                <!-- Amount -->
                <?php if ($deal->amount): ?>
                <div style="font-weight:800;font-size:15px;color:#059669;margin-bottom:6px;">
                    <?php echo number_format($deal->amount); ?> <small style="font-size:10px;font-weight:400;color:var(--gray-400);">تومان</small>
                </div>
                <?php endif; ?>
                
                <!-- Contact -->
                <?php if (!empty($deal->contact_name)): ?>
                <div style="display:flex;align-items:center;gap:4px;font-size:12px;color:var(--gray-600);margin-bottom:4px;">
                    👤 <?php echo htmlspecialchars(mb_substr($deal->contact_name, 0, 20)); ?>
                    <?php if (!empty($deal->contact_phone)): ?>
                    <span dir="ltr" style="color:var(--gray-400);font-size:11px;">(<?php echo htmlspecialchars($deal->contact_phone); ?>)</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Tags -->
                <?php if (!empty($deal->tags)): ?>
                <div style="display:flex;gap:3px;flex-wrap:wrap;margin-bottom:6px;">
                    <?php 
                    $tagsArr = array_filter(explode(',', $deal->tags));
                    foreach (array_slice($tagsArr, 0, 3) as $tag): 
                    ?>
                    <span style="background:#eef2ff;color:#4f46e5;padding:1px 6px;border-radius:6px;font-size:10px;">#<?php echo htmlspecialchars(trim($tag)); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Footer -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;padding-top:6px;border-top:1px solid var(--gray-100);">
                    <?php if (!empty($deal->assigned_name)): ?>
                    <span style="font-size:11px;color:var(--gray-400);">👨‍💼 <?php echo htmlspecialchars($deal->assigned_name); ?></span>
                    <?php else: ?>
                    <span></span>
                    <?php endif; ?>
                    <span style="font-size:10px;color:var(--gray-300);"><?php echo \Core\JDate::displayDate($deal->created_at); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick View Modal -->
<div class="modal-overlay" id="dealQuickView" style="display:none;">
    <div class="modal-box" style="max-width:450px;">
        <div class="modal-header">
            <h5 id="qvTitle">-</h5>
            <button type="button" class="modal-close" onclick="closeQuickView()">&times;</button>
        </div>
        <div class="modal-body" id="qvBody">
            <div style="text-align:center;padding:20px;">⏳ در حال بارگذاری...</div>
        </div>
    </div>
</div>

<style>
.kanban-board {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 20px;
    min-height: 70vh;
    align-items: flex-start;
}
.kanban-column {
    min-width: 280px;
    max-width: 320px;
    flex: 1;
    background: var(--gray-50);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    max-height: 80vh;
}
.kanban-column-header {
    padding: 14px 16px;
    background: white;
    border-radius: 12px 12px 0 0;
    border-bottom: 1px solid var(--gray-200);
}
.kanban-dropzone {
    padding: 8px;
    flex: 1;
    overflow-y: auto;
    min-height: 100px;
    transition: background 0.2s;
}
.kanban-dropzone.drag-over {
    background: #dbeafe;
    border-radius: 0 0 12px 12px;
}
.kanban-empty {
    text-align: center;
    padding: 30px 10px;
    color: var(--gray-300);
    font-size: 13px;
}
.kanban-card {
    background: white;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    cursor: grab;
    transition: transform 0.15s, box-shadow 0.15s;
    border: 2px solid transparent;
}
.kanban-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transform: translateY(-1px);
    border-color: var(--primary);
}
.kanban-card.dragging {
    opacity: 0.4;
    transform: rotate(2deg);
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
    
    // Send AJAX update
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
            // Move card DOM
            var card = document.querySelector('.kanban-card[data-deal-id="' + dealId + '"]');
            if (card && zone) {
                // Remove empty message if exists
                var emptyMsg = zone.querySelector('.kanban-empty');
                if (emptyMsg) emptyMsg.remove();
                
                zone.appendChild(card);
                updateColumnStats();
            }
        } else {
            alert(d.message || 'خطا در انتقال');
        }
    })
    .catch(function() {
        alert('خطای شبکه');
    });
}

// ============ UPDATE COLUMN STATS ============
function updateColumnStats() {
    document.querySelectorAll('.kanban-column').forEach(function(col) {
        var cards = col.querySelectorAll('.kanban-card');
        var totalAmount = 0;
        cards.forEach(function(c) {
            totalAmount += parseInt(c.dataset.dealAmount || 0);
        });
        
        var header = col.querySelector('.kanban-column-header div div');
        if (header) {
            var countText = cards.length + ' معامله • ' + totalAmount.toLocaleString('en-US') + ' ت';
            var subDiv = header.querySelector('div');
            if (subDiv) subDiv.textContent = countText;
        }
        
        // Show/hide empty message
        var zone = col.querySelector('.kanban-dropzone');
        var emptyMsg = zone.querySelector('.kanban-empty');
        if (cards.length === 0 && !emptyMsg) {
            var div = document.createElement('div');
            div.className = 'kanban-empty';
            div.textContent = 'معامله‌ای نیست';
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

// ============ QUICK VIEW (double-click) ============
document.querySelectorAll('.kanban-card').forEach(function(card) {
    card.addEventListener('dblclick', function() {
        var dealId = this.dataset.dealId;
        openQuickView(dealId);
    });
});

function openQuickView(dealId) {
    document.getElementById('dealQuickView').style.display = 'flex';
    document.getElementById('qvBody').innerHTML = '<div style="text-align:center;padding:20px;">⏳ در حال بارگذاری...</div>';
    
    fetch('<?php echo $config['url']; ?>/deals/get-data/' + dealId)
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            var deal = d.deal;
            document.getElementById('qvTitle').textContent = deal.title || '-';
            var amount = deal.amount ? (parseInt(deal.amount).toLocaleString('en-US') + ' تومان') : '-';
            var status = deal.is_won ? '✅ موفق' : (deal.is_lost ? '❌ ناموفق' : '⏳ در حال بررسی');
            
            document.getElementById('qvBody').innerHTML = 
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">' +
                    '<div style="background:var(--gray-50);padding:10px;border-radius:8px;"><div style="font-size:11px;color:var(--gray-400);">💰 مبلغ</div><strong>' + amount + '</strong></div>' +
                    '<div style="background:var(--gray-50);padding:10px;border-radius:8px;"><div style="font-size:11px;color:var(--gray-400);">📊 وضعیت</div><strong>' + status + '</strong></div>' +
                '</div>' +
                '<div style="margin-top:12px;"><a href="<?php echo $config['url']; ?>/deals/view/' + dealId + '" class="btn btn-primary" style="width:100%;text-align:center;">مشاهده جزئیات کامل</a></div>';
        } else {
            document.getElementById('qvBody').innerHTML = '<div style="text-align:center;padding:20px;color:#EF4444;">خطا در بارگذاری</div>';
        }
    });
}

function closeQuickView() {
    document.getElementById('dealQuickView').style.display = 'none';
}
</script>