<?php $config = $GLOBALS['app_config']; ?>

<!-- Mobile-First Deal Edit Page -->
<div class="deal-edit-page">
    
    <!-- Header -->
    <div class="deal-edit-header">
        <div class="deal-edit-header-top">
            <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-sm btn-secondary">← بازگشت</a>
            <span class="deal-status-badge <?php echo $deal->is_won ? 'status-won' : ($deal->is_lost ? 'status-lost' : 'status-open'); ?>">
                <?php echo $deal->is_won ? '✅ موفق' : ($deal->is_lost ? '❌ ناموفق' : '⏳ در جریان'); ?>
            </span>
        </div>
        <h5 class="deal-edit-title">✏️ ویرایش معامله #<?php echo $deal->id; ?></h5>
        <p class="deal-edit-subtitle"><?php echo htmlspecialchars($deal->title); ?></p>
    </div>

    <form method="POST" action="<?php echo $config['url']; ?>/deals/update/<?php echo $deal->id; ?>" id="dealEditForm">
        
        <!-- Section 1: Basic Info -->
        <div class="deal-section">
            <button type="button" class="deal-section-header" onclick="toggleSection(this)">
                <span>📋 اطلاعات اصلی</span>
                <span class="toggle-icon">▼</span>
            </button>
            <div class="deal-section-body open">
                <div class="deal-form-grid">
                    <div class="deal-form-field full">
                        <label>عنوان معامله *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($deal->title); ?>" required class="deal-input" placeholder="مثال: تور استانبول خانواده رضایی">
                    </div>
                    
                    <div class="deal-form-field">
                        <label>📋 پایپ لاین</label>
                        <select name="pipeline_id" id="editPipelineSelect" class="deal-input">
                            <?php foreach ($pipelines as $p): ?>
                            <option value="<?php echo $p->id; ?>" <?php echo $p->id == $deal->pipeline_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="deal-form-field">
                        <label>🏷️ مرحله</label>
                        <select name="stage_id" id="editStageSelect" class="deal-input">
                            <?php foreach ($stages as $s): ?>
                            <option value="<?php echo $s->id; ?>" <?php echo $s->id == $deal->stage_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="deal-form-field">
                        <label>💰 مبلغ (تومان)</label>
                        <input type="text" name="amount" id="amountInput" value="<?php echo $deal->amount ? number_format($deal->amount) : ''; ?>" class="deal-input amount-input" placeholder="0" dir="ltr">
                    </div>
                    
                    <div class="deal-form-field">
                        <label>📊 احتمال موفقیت</label>
                        <div class="deal-range-wrapper">
                            <input type="range" name="probability" min="0" max="100" value="<?php echo (int)$deal->probability; ?>" oninput="document.getElementById('probVal').textContent=this.value+'%'" class="deal-range">
                            <span id="probVal" class="deal-range-value"><?php echo (int)$deal->probability; ?>%</span>
                        </div>
                    </div>
                    
                    <div class="deal-form-field">
                        <label>👤 مخاطب</label>
                        <select name="contact_id" class="deal-input">
                            <option value="">— انتخاب مخاطب —</option>
                            <?php foreach ($contacts as $c): ?>
                            <option value="<?php echo $c->id; ?>" <?php echo $c->id == $deal->contact_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->full_name); ?> (<?php echo htmlspecialchars($c->phone); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="deal-form-field">
                        <?php if (\Core\Auth::canAccessAll('deals.edit')): ?>
                        <label>👨‍💼 مسئول</label>
                        <select name="assigned_to" class="deal-input">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u->id; ?>" <?php echo $u->id == $deal->assigned_to ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                        <input type="hidden" name="assigned_to" value="<?php echo $deal->assigned_to; ?>">
                        <label>👨‍💼 مسئول</label>
                        <div class="deal-readonly-field">
                            👤 <?php foreach ($users as $u) { if ($u->id == $deal->assigned_to) { echo htmlspecialchars($u->full_name); break; } } ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="deal-form-field">
                        <label>🎯 منبع</label>
                        <select name="source" class="deal-input">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($sources as $s): ?>
                            <option value="<?php echo htmlspecialchars($s->name); ?>" <?php echo $s->name == $deal->source ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="deal-form-field">
                        <label>📅 تاریخ پیش‌بینی بستن</label>
                        <input type="date" name="expected_close_date" value="<?php echo $deal->expected_close_date ?? ''; ?>" class="deal-input">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Status -->
        <div class="deal-section">
            <button type="button" class="deal-section-header" onclick="toggleSection(this)">
                <span>🔵 وضعیت معامله</span>
                <span class="toggle-icon">▼</span>
            </button>
            <div class="deal-section-body open">
                <div class="deal-status-options">
                    <label class="deal-status-card status-open-card <?php echo (!$deal->is_won && !$deal->is_lost) ? 'active' : ''; ?>">
                        <input type="radio" name="deal_status" value="open" <?php echo (!$deal->is_won && !$deal->is_lost) ? 'checked' : ''; ?>>
                        <span class="status-icon">⏳</span>
                        <span class="status-label">در جریان</span>
                    </label>
                    <label class="deal-status-card status-won-card <?php echo ($deal->is_won && !$deal->is_lost) ? 'active' : ''; ?>">
                        <input type="radio" name="deal_status" value="won" <?php echo ($deal->is_won && !$deal->is_lost) ? 'checked' : ''; ?>>
                        <span class="status-icon">✅</span>
                        <span class="status-label">موفق</span>
                    </label>
                    <label class="deal-status-card status-lost-card <?php echo $deal->is_lost ? 'active' : ''; ?>">
                        <input type="radio" name="deal_status" value="lost" <?php echo $deal->is_lost ? 'checked' : ''; ?>>
                        <span class="status-icon">❌</span>
                        <span class="status-label">ناموفق</span>
                    </label>
                </div>
                
                <!-- Lost reason (shown when status=lost) -->
                <div id="lostReasonBox" style="display:<?php echo $deal->is_lost ? 'block' : 'none'; ?>;">
                    <div class="deal-form-grid" style="margin-top:12px;">
                        <div class="deal-form-field">
                            <label>دلیل شکست</label>
                            <select name="loss_reason_id" class="deal-input">
                                <option value="">— انتخاب کنید —</option>
                                <?php
                                $lossReasons = \Core\Database::getInstance()->fetchAll("SELECT id, name, icon FROM deal_loss_reasons WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
                                foreach ($lossReasons as $lr):
                                ?>
                                <option value="<?php echo $lr->id; ?>" <?php echo ($deal->loss_reason_id ?? '') == $lr->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($lr->icon . ' ' . $lr->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="deal-form-field full">
                            <label>توضیحات شکست</label>
                            <textarea name="loss_reason_note" class="deal-input" rows="2" placeholder="دلیل عدم موفقیت..."><?php echo htmlspecialchars($deal->loss_reason_note ?? $deal->lost_reason ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Description -->
        <div class="deal-section">
            <button type="button" class="deal-section-header" onclick="toggleSection(this)">
                <span>📝 توضیحات</span>
                <span class="toggle-icon">▼</span>
            </button>
            <div class="deal-section-body open">
                <textarea name="description" id="dealDescription" class="deal-input deal-textarea" rows="5" placeholder="توضیحات معامله... از # برای هشتگ استفاده کنید"><?php echo htmlspecialchars($deal->description ?? ''); ?></textarea>
                <div class="deal-desc-meta">
                    <small>💡 از # برای هشتگ استفاده کنید</small>
                    <small id="descCharCount">0 کاراکتر</small>
                </div>
                
                <?php
                // Extract tags from description
                $tags = [];
                if ($deal->description) {
                    preg_match_all('/#([\x{600}-\x{6FF}\x{FB8A}\x{067E}\x{0686}\x{06AF}\x{0698}\w]+)/u', $deal->description, $matches);
                    $tags = $matches[1] ?? [];
                }
                if (!empty($tags)):
                ?>
                <div class="deal-tags">
                    <?php foreach (array_unique($tags) as $tag): ?>
                    <a href="<?php echo $config['url']; ?>/deals/tags/<?php echo urlencode($tag); ?>" class="deal-tag">#<?php echo htmlspecialchars($tag); ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section 4: Quick Info (read-only) -->
        <div class="deal-section">
            <button type="button" class="deal-section-header" onclick="toggleSection(this)">
                <span>⚡ اطلاعات تکمیلی</span>
                <span class="toggle-icon">▶</span>
            </button>
            <div class="deal-section-body">
                <div class="deal-info-grid">
                    <div class="deal-info-item">
                        <span class="info-label">شناسه</span>
                        <span class="info-value">#<?php echo $deal->id; ?></span>
                    </div>
                    <div class="deal-info-item">
                        <span class="info-label">تاریخ ایجاد</span>
                        <span class="info-value"><?php echo \Core\JDate::displayDate($deal->created_at); ?></span>
                    </div>
                    <div class="deal-info-item">
                        <span class="info-label">آخرین بروزرسانی</span>
                        <span class="info-value"><?php echo \Core\JDate::displayDate($deal->updated_at ?? $deal->created_at); ?></span>
                    </div>
                    <?php if ($deal->contact_name ?? false): ?>
                    <div class="deal-info-item">
                        <span class="info-label">مخاطب</span>
                        <span class="info-value"><?php echo htmlspecialchars($deal->contact_name); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Action Bar -->
        <div class="deal-action-bar">
            <button type="submit" class="btn btn-primary deal-save-btn">
                💾 ذخیره تغییرات
            </button>
            <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-secondary deal-cancel-btn">
                انصراف
            </a>
        </div>
    </form>
</div>

<style>
/* ========== DEAL EDIT PAGE STYLES ========== */
.deal-edit-page { padding-bottom: 80px; }

.deal-edit-header {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: #fff;
    padding: 16px;
    border-radius: var(--radius);
    margin-bottom: 16px;
}
.deal-edit-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.deal-edit-title { font-size: 18px; font-weight: 700; margin: 0; color: #fff; }
.deal-edit-subtitle { font-size: 13px; opacity: 0.85; margin: 4px 0 0; }
.deal-status-badge {
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-open { background: rgba(255,255,255,0.2); color: #fff; }
.status-won { background: #d4edda; color: #155724; }
.status-lost { background: #f8d7da; color: #721c24; }

/* Sections */
.deal-section {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    margin-bottom: 12px;
    overflow: hidden;
}
.deal-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 14px 16px;
    background: none;
    border: none;
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-700);
    cursor: pointer;
    font-family: inherit;
}
.deal-section-header:hover { background: var(--gray-50); }
.toggle-icon { font-size: 12px; color: var(--gray-400); transition: transform 0.2s; }
.deal-section-body { padding: 0 16px 16px; display: none; }
.deal-section-body.open { display: block; }
.deal-section-header.active .toggle-icon { transform: rotate(180deg); }

/* Form Grid */
.deal-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.deal-form-field { display: flex; flex-direction: column; gap: 4px; }
.deal-form-field.full { grid-column: 1 / -1; }
.deal-form-field label {
    font-size: 11px;
    font-weight: 600;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.deal-input {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-sm);
    font-size: 14px;
    color: var(--gray-800);
    background: #fff;
    transition: border-color 0.2s;
    font-family: inherit;
    outline: none;
}
.deal-input:focus { border-color: var(--primary); }
.deal-textarea { min-height: 100px; resize: vertical; line-height: 1.8; }
.amount-input { font-size: 18px !important; font-weight: 700 !important; text-align: left !important; }
.deal-readonly-field {
    padding: 10px 12px;
    background: var(--gray-50);
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 600;
    color: var(--primary);
}

/* Range */
.deal-range-wrapper { display: flex; align-items: center; gap: 10px; padding: 6px 0; }
.deal-range { flex: 1; accent-color: var(--primary); height: 6px; }
.deal-range-value { font-weight: 800; color: var(--primary); font-size: 16px; min-width: 40px; text-align: center; }

/* Status Options */
.deal-status-options { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
.deal-status-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 8px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
    text-align: center;
}
.deal-status-card input { display: none; }
.status-open-card { background: #e8f5e9; }
.status-won-card { background: #d4edda; }
.status-lost-card { background: #f8d7da; }
.deal-status-card.active.status-open-card { border-color: #4CAF50; }
.deal-status-card.active.status-won-card { border-color: #28a745; }
.deal-status-card.active.status-lost-card { border-color: #dc3545; }
.deal-status-card .status-icon { font-size: 24px; }
.deal-status-card .status-label { font-size: 12px; font-weight: 600; color: var(--gray-700); }

/* Description Meta */
.deal-desc-meta {
    display: flex;
    justify-content: space-between;
    margin-top: 6px;
    font-size: 11px;
    color: var(--gray-400);
}

/* Tags */
.deal-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.deal-tag {
    background: var(--primary-light);
    color: var(--primary);
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}
.deal-tag:hover { background: var(--primary); color: #fff; }

/* Info Grid */
.deal-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.deal-info-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 10px;
    background: var(--gray-50);
    border-radius: var(--radius-sm);
}
.info-label { font-size: 11px; color: var(--gray-400); }
.info-value { font-size: 13px; font-weight: 600; color: var(--gray-700); }

/* Sticky Action Bar */
.deal-action-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    padding: 12px 16px;
    box-shadow: 0 -2px 12px rgba(0,0,0,0.1);
    display: flex;
    gap: 8px;
    z-index: 100;
    direction: rtl;
}
.deal-save-btn {
    flex: 2;
    padding: 12px;
    font-size: 15px;
    font-weight: 700;
    border-radius: var(--radius-sm);
}
.deal-cancel-btn {
    flex: 1;
    padding: 12px;
    font-size: 14px;
    border-radius: var(--radius-sm);
    text-align: center;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .deal-form-grid { grid-template-columns: 1fr; }
    .deal-status-options { grid-template-columns: 1fr 1fr 1fr; gap: 6px; }
    .deal-status-card { padding: 10px 4px; }
    .deal-status-card .status-icon { font-size: 20px; }
    .deal-status-card .status-label { font-size: 11px; }
    .deal-info-grid { grid-template-columns: 1fr; }
    .deal-edit-title { font-size: 16px; }
    .deal-action-bar { padding: 10px 12px; }
    .deal-save-btn { font-size: 14px; padding: 10px; }
}

@media (max-width: 480px) {
    .deal-edit-header { padding: 12px; border-radius: 10px; }
    .deal-section { border-radius: 10px; }
    .deal-section-header { padding: 12px 14px; font-size: 13px; }
    .deal-input { padding: 10px; font-size: 16px; /* prevent iOS zoom */ }
    .amount-input { font-size: 16px !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sections
    window.toggleSection = function(btn) {
        var body = btn.nextElementSibling;
        var isOpen = body.classList.contains('open');
        body.classList.toggle('open');
        btn.classList.toggle('active');
        btn.querySelector('.toggle-icon').textContent = isOpen ? '▶' : '▼';
    };
    
    // Auto-open sections with open class
    document.querySelectorAll('.deal-section-header').forEach(function(header) {
        if (header.nextElementSibling.classList.contains('open')) {
            header.classList.add('active');
            header.querySelector('.toggle-icon').textContent = '▼';
        }
    });

    // Pipeline change -> update stages
    var pipelineSelect = document.getElementById('editPipelineSelect');
    var stageSelect = document.getElementById('editStageSelect');
    if (pipelineSelect) {
        pipelineSelect.addEventListener('change', function() {
            var pipelineId = this.value;
            if (!pipelineId || !stageSelect) return;
            fetch('<?php echo $config['url']; ?>/pipelines/' + pipelineId + '/stages')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    stageSelect.innerHTML = '';
                    if (data && data.length) {
                        data.forEach(function(s) {
                            var opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.name;
                            stageSelect.appendChild(opt);
                        });
                    }
                })
                .catch(function() {});
        });
    }

    // Amount formatting
    var amountInput = document.getElementById('amountInput');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            var v = this.value.replace(/[^0-9]/g, '');
            if (v) this.value = parseInt(v).toLocaleString('en');
        });
    }

    // Status toggle
    document.querySelectorAll('.deal-status-card').forEach(function(card) {
        card.addEventListener('click', function() {
            document.querySelectorAll('.deal-status-card').forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            var radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            document.getElementById('lostReasonBox').style.display = radio.value === 'lost' ? 'block' : 'none';
        });
    });

    // Description char counter
    var descArea = document.getElementById('dealDescription');
    var descCounter = document.getElementById('descCharCount');
    if (descArea && descCounter) {
        function updateCount() { descCounter.textContent = descArea.value.length + ' کاراکتر'; }
        descArea.addEventListener('input', updateCount);
        updateCount();
    }

    // Form submit - strip commas from amount
    document.getElementById('dealEditForm').addEventListener('submit', function() {
        if (amountInput) {
            amountInput.value = amountInput.value.replace(/[^0-9]/g, '');
        }
    });
});
</script>