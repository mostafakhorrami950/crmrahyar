<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-sm btn-secondary">← بازگشت</a>
        <h5 style="margin:0;">✏️ ویرایش معامله</h5>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <span style="padding:6px 16px;border-radius:20px;font-size:13px;font-weight:bold;background:<?php 
            echo $deal->is_won ? '#d4edda;color:#155724;' : ($deal->is_lost ? '#f8d7da;color:#721c24;' : '#fff3cd;color:#856404;'); ?>">
            <?php echo $deal->is_won ? '✅ موفق' : ($deal->is_lost ? '❌ ناموفق' : '⏳ در جریان'); ?>
        </span>
        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-sm btn-primary">👁️ مشاهده</a>
    </div>
</div>

<form method="POST" action="<?php echo $config['url']; ?>/deals/update/<?php echo $deal->id; ?>" style="margin-top:16px;">
    <div class="row">
        <div class="col-md-8">
            <!-- Main Info Card -->
            <div class="card" style="padding:24px;margin-bottom:16px;border-radius:16px;">
                <h5 style="margin:0 0 20px 0;font-weight:bold;font-size:15px;">📋 اطلاعات اصلی</h5>
                
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" style="font-size:12px;font-weight:600;">عنوان معامله *</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($deal->title); ?>" required style="font-size:16px;font-weight:bold;padding:12px;border-radius:12px;">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:600;">📋 پایپ لاین</label>
                        <select name="pipeline_id" class="form-select" id="editPipelineSelect" style="padding:10px;border-radius:10px;">
                            <?php foreach ($pipelines as $p): ?>
                            <option value="<?php echo $p->id; ?>" <?php echo $p->id == $deal->pipeline_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:600;">🏷️ مرحله</label>
                        <select name="stage_id" class="form-select" id="editStageSelect" style="padding:10px;border-radius:10px;">
                            <?php foreach ($stages as $s): ?>
                            <option value="<?php echo $s->id; ?>" <?php echo $s->id == $deal->stage_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:600;">💰 مبلغ (تومان)</label>
                        <input type="text" name="amount" id="amountInput" class="form-control" value="<?php echo $deal->amount ? number_format($deal->amount) : ''; ?>" placeholder="0" style="font-size:18px;font-weight:bold;direction:ltr;text-align:left;padding:12px;border-radius:12px;">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:600;">📊 احتمال موفقیت</label>
                        <div style="display:flex;align-items:center;gap:10px;padding:8px;">
                            <input type="range" name="probability" min="0" max="100" value="<?php echo (int)$deal->probability; ?>" style="flex:1;accent-color:var(--primary);" oninput="document.getElementById('probVal').textContent=this.value+'%';this.style.setProperty('--val',this.value)">
                            <span id="probVal" style="font-weight:800;color:var(--primary);min-width:45px;text-align:center;font-size:18px;"><?php echo (int)$deal->probability; ?>%</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:600;">👤 مخاطب</label>
                        <select name="contact_id" class="form-select" style="padding:10px;border-radius:10px;">
                            <option value="">— انتخاب مخاطب —</option>
                            <?php foreach ($contacts as $c): ?>
                            <option value="<?php echo $c->id; ?>" <?php echo $c->id == $deal->contact_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->full_name); ?> (<?php echo htmlspecialchars($c->phone); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:600;">👨‍💼 مسئول</label>
                        <select name="assigned_to" class="form-select" style="padding:10px;border-radius:10px;">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u->id; ?>" <?php echo $u->id == $deal->assigned_to ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:600;">🎯 منبع</label>
                        <select name="source" class="form-select" style="padding:10px;border-radius:10px;">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($sources as $s): ?>
                            <option value="<?php echo htmlspecialchars($s->name); ?>" <?php echo $s->name == $deal->source ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px;font-weight:600;">📅 تاریخ پیش‌بینی</label>
                        <input type="date" name="expected_close_date" class="form-control" value="<?php echo $deal->expected_close_date ?? ''; ?>" style="padding:10px;border-radius:10px;">
                    </div>
                </div>
            </div>

            <!-- Description Card -->
            <div class="card" style="padding:24px;margin-bottom:16px;border-radius:16px;">
                <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:15px;">📝 توضیحات</h5>
                <textarea name="description" class="form-control" rows="6" style="line-height:2;border-radius:12px;padding:14px;font-size:14px;" placeholder="توضیحات معامله..."><?php echo htmlspecialchars($deal->description ?? ''); ?></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                    <small style="color:var(--gray-400);font-size:12px;">💡 از # برای هشتگ استفاده کنید</small>
                    <small id="descCharCount" style="color:var(--gray-400);font-size:12px;">0 کاراکتر</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card" style="padding:20px;margin-bottom:16px;border-radius:16px;">
                <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:14px;">🔵 وضعیت معامله</h5>
                
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <label class="status-option" style="display:flex;align-items:center;gap:10px;padding:14px;background:#e8f5e9;border-radius:12px;cursor:pointer;border:2px solid <?php echo (!$deal->is_won && !$deal->is_lost) ? '#4CAF50' : 'transparent'; ?>;transition:all 0.2s;">
                        <input type="radio" name="deal_status" value="open" <?php echo (!$deal->is_won && !$deal->is_lost) ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:#4CAF50;">
                        <div>
                            <strong style="font-size:14px;color:#155724;">⏳ در جریان</strong>
                            <div style="font-size:11px;color:#2e7d32;">معامله فعال و در حال پیگیری</div>
                        </div>
                    </label>

                    <label class="status-option" style="display:flex;align-items:center;gap:10px;padding:14px;background:#d4edda;border-radius:12px;cursor:pointer;border:2px solid <?php echo $deal->is_won ? '#28a745' : 'transparent'; ?>;transition:all 0.2s;">
                        <input type="radio" name="deal_status" value="won" <?php echo $deal->is_won ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:#28a745;">
                        <div>
                            <strong style="font-size:14px;color:#155724;">✅ موفق</strong>
                            <div style="font-size:11px;color:#2e7d32;">معامله با موفقیت بسته شد</div>
                        </div>
                    </label>

                    <label class="status-option" style="display:flex;align-items:center;gap:10px;padding:14px;background:#f8d7da;border-radius:12px;cursor:pointer;border:2px solid <?php echo $deal->is_lost ? '#dc3545' : 'transparent'; ?>;transition:all 0.2s;">
                        <input type="radio" name="deal_status" value="lost" <?php echo $deal->is_lost ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:#dc3545;">
                        <div>
                            <strong style="font-size:14px;color:#721c24;">❌ ناموفق</strong>
                            <div style="font-size:11px;color:#c62828;">معامله به نتیجه نرسید</div>
                        </div>
                    </label>
                </div>

                <div id="lostReasonBox" style="margin-top:14px;display:<?php echo $deal->is_lost ? 'block' : 'none'; ?>;">
                    <label class="form-label" style="font-size:12px;font-weight:600;">📝 دلیل ناموفق بودن</label>
                    <textarea name="lost_reason" class="form-control" rows="2" placeholder="دلیل عدم موفقیت..." style="border-radius:10px;"><?php echo htmlspecialchars($deal->lost_reason ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card" style="padding:20px;margin-bottom:16px;border-radius:16px;">
                <h5 style="margin:0 0 14px 0;font-weight:bold;font-size:14px;">⚡ اطلاعات تکمیلی</h5>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;justify-content:space-between;padding:10px 12px;background:var(--gray-50);border-radius:10px;font-size:13px;">
                        <span style="color:var(--gray-500);">🆔 شناسه</span>
                        <strong>#<?php echo $deal->id; ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:10px 12px;background:var(--gray-50);border-radius:10px;font-size:13px;">
                        <span style="color:var(--gray-500);">📅 ایجاد</span>
                        <strong><?php echo \Core\JDate::displayDate($deal->created_at); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:10px 12px;background:var(--gray-50);border-radius:10px;font-size:13px;">
                        <span style="color:var(--gray-500);">🔄 بروزرسانی</span>
                        <strong><?php echo \Core\JDate::displayDate($deal->updated_at ?? $deal->created_at); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="card" style="padding:20px;border-radius:16px;">
                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:16px;font-weight:bold;border-radius:12px;">
                    💾 ذخیره تغییرات
                </button>
                <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-secondary" style="width:100%;margin-top:8px;padding:12px;border-radius:12px;">
                    ← انصراف و بازگشت
                </a>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pipeline change -> update stages
    document.getElementById('editPipelineSelect')?.addEventListener('change', function() {
        var pipelineId = this.value;
        var stageSelect = document.getElementById('editStageSelect');
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

    // Amount formatting
    var amountInput = document.getElementById('amountInput');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            var v = this.value.replace(/[^0-9]/g, '');
            if (v) this.value = parseInt(v).toLocaleString('en');
        });
    }

    // Status toggle - show/hide lost reason
    document.querySelectorAll('input[name="deal_status"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.getElementById('lostReasonBox').style.display = this.value === 'lost' ? 'block' : 'none';
            document.querySelectorAll('.status-option').forEach(function(label) {
                var r = label.querySelector('input[type="radio"]');
                if (r.checked) {
                    if (r.value === 'lost') label.style.borderColor = '#dc3545';
                    else if (r.value === 'won') label.style.borderColor = '#28a745';
                    else label.style.borderColor = '#4CAF50';
                } else {
                    label.style.borderColor = 'transparent';
                }
            });
        });
    });

    // Description char counter
    var descArea = document.querySelector('textarea[name="description"]');
    var descCounter = document.getElementById('descCharCount');
    if (descArea && descCounter) {
        function updateCount() { descCounter.textContent = descArea.value.length + ' کاراکتر'; }
        descArea.addEventListener('input', updateCount);
        updateCount();
    }
});
</script>