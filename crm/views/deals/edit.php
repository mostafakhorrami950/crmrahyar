<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-sm btn-secondary">← بازگشت</a>
        <h5 style="margin:0;">✏️ ویرایش معامله</h5>
    </div>
    <span style="padding:6px 16px;border-radius:20px;font-size:13px;font-weight:bold;background:<?php 
        echo $deal->is_won ? '#d4edda;color:#155724;' : ($deal->is_lost ? '#f8d7da;color:#721c24;' : '#fff3cd;color:#856404;'); ?>">
        <?php echo $deal->is_won ? '✅ موفق' : ($deal->is_lost ? '❌ ناموفق' : '⏳ در جریان'); ?>
    </span>
</div>

<form method="POST" action="<?php echo $config['url']; ?>/deals/update/<?php echo $deal->id; ?>" style="margin-top:16px;">
    <div class="row">
        <div class="col-md-8">
            <!-- Main Info Card -->
            <div class="card" style="padding:24px;margin-bottom:16px;">
                <h5 style="margin:0 0 20px 0;font-weight:bold;font-size:15px;color:var(--gray-700);">📋 اطلاعات اصلی معامله</h5>
                
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">عنوان معامله *</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($deal->title); ?>" required style="font-size:16px;font-weight:bold;">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">پایپ لاین</label>
                        <select name="pipeline_id" class="form-select" id="editPipelineSelect">
                            <?php foreach ($pipelines as $p): ?>
                            <option value="<?php echo $p->id; ?>" <?php echo $p->id == $deal->pipeline_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">مرحله</label>
                        <select name="stage_id" class="form-select" id="editStageSelect">
                            <?php foreach ($stages as $s): ?>
                            <option value="<?php echo $s->id; ?>" <?php echo $s->id == $deal->stage_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">💰 مبلغ (تومان)</label>
                        <input type="text" name="amount" class="form-control" value="<?php echo number_format($deal->amount); ?>" style="font-size:16px;font-weight:bold;direction:ltr;text-align:left;">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">📊 درصد احتمال موفقیت</label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="range" name="probability" class="form-range" min="0" max="100" value="<?php echo $deal->probability; ?>" style="flex:1;" oninput="document.getElementById('probVal').textContent=this.value+'%'">
                            <span id="probVal" style="font-weight:bold;color:var(--primary);min-width:40px;text-align:center;"><?php echo $deal->probability; ?>%</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">👤 مخاطب</label>
                        <select name="contact_id" class="form-select">
                            <option value="">انتخاب مخاطب</option>
                            <?php foreach ($contacts as $c): ?>
                            <option value="<?php echo $c->id; ?>" <?php echo $c->id == $deal->contact_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->full_name); ?> (<?php echo htmlspecialchars($c->phone); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">👤 مسئول</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">انتخاب کنید</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u->id; ?>" <?php echo $u->id == $deal->assigned_to ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">🎯 نحوه آشنایی</label>
                        <input type="text" name="source" class="form-control" value="<?php echo htmlspecialchars($deal->source ?? ''); ?>" placeholder="مثال: اینستاگرام, معرفی دوستان, گوگل">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">📅 تاریخ پیش‌بینی بسته شدن</label>
                        <input type="date" name="expected_close_date" class="form-control" value="<?php echo $deal->expected_close_date; ?>">
                    </div>
                </div>
            </div>

            <!-- Description Card -->
            <div class="card" style="padding:24px;margin-bottom:16px;">
                <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:15px;color:var(--gray-700);">📝 توضیحات و هشتگ‌ها</h5>
                <textarea name="description" class="form-control" rows="5" style="line-height:1.8;"><?php echo htmlspecialchars($deal->description ?? ''); ?></textarea>
                <small style="color:var(--gray-400);font-size:12px;display:block;margin-top:6px;">💡 برای هشتگ از # استفاده کنید. مثال: #تور_کیش #ویزای_شنگن</small>
            </div>

        </div>

        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card" style="padding:20px;margin-bottom:16px;">
                <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:14px;color:var(--gray-700);">🔵 وضعیت معامله</h5>
                
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <label style="display:flex;align-items:center;gap:10px;padding:12px;background:#e8f5e9;border-radius:12px;cursor:pointer;border:2px solid <?php echo (!$deal->is_won && !$deal->is_lost) ? '#28a745' : 'transparent'; ?>;">
                        <input type="radio" name="deal_status" value="open" <?php echo (!$deal->is_won && !$deal->is_lost) ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:#28a745;">
                        <div>
                            <strong style="font-size:14px;color:#155724;">⏳ در جریان</strong>
                            <br><small style="color:#155724;font-size:12px;">معامله هنوز در حال پیگیری است</small>
                        </div>
                    </label>

                    <label style="display:flex;align-items:center;gap:10px;padding:12px;background:#d4edda;border-radius:12px;cursor:pointer;border:2px solid <?php echo $deal->is_won ? '#28a745' : 'transparent'; ?>;">
                        <input type="radio" name="deal_status" value="won" <?php echo $deal->is_won ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:#28a745;">
                        <div>
                            <strong style="font-size:14px;color:#155724;">✅ موفق (برنده شد)</strong>
                            <br><small style="color:#155724;font-size:12px;">معامله با موفقیت به پایان رسید</small>
                        </div>
                    </label>

                    <label style="display:flex;align-items:center;gap:10px;padding:12px;background:#f8d7da;border-radius:12px;cursor:pointer;border:2px solid <?php echo $deal->is_lost ? '#dc3545' : 'transparent'; ?>;">
                        <input type="radio" name="deal_status" value="lost" <?php echo $deal->is_lost ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:#dc3545;">
                        <div>
                            <strong style="font-size:14px;color:#721c24;">❌ ناموفق</strong>
                            <br><small style="color:#721c24;font-size:12px;">معامله به نتیجه نرسید</small>
                        </div>
                    </label>
                </div>

                <?php if ($deal->is_lost || true): ?>
                <div style="margin-top:16px;">
                    <label class="form-label">📝 دلیل (در صورت ناموفق بودن)</label>
                    <textarea name="lost_reason" class="form-control" rows="2" placeholder="دلیل عدم موفقیت..."><?php echo htmlspecialchars($deal->lost_reason ?? ''); ?></textarea>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quick Stats -->
            <div class="card" style="padding:20px;margin-bottom:16px;">
                <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:14px;color:var(--gray-700);">⚡ خلاصه</h5>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--gray-50);border-radius:8px;font-size:13px;">
                        <span style="color:var(--gray-500);">ایجاد شده توسط</span>
                        <strong><?php echo htmlspecialchars($deal->created_by ? 'کاربر #'.$deal->created_by : '-'); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--gray-50);border-radius:8px;font-size:13px;">
                        <span style="color:var(--gray-500);">تاریخ ایجاد</span>
                        <strong><?php echo date('Y/m/d', strtotime($deal->created_at)); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--gray-50);border-radius:8px;font-size:13px;">
                        <span style="color:var(--gray-500);">آخرین بروزرسانی</span>
                        <strong><?php echo date('Y/m/d', strtotime($deal->updated_at)); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="card" style="padding:20px;">
                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:16px;font-weight:bold;">
                    💾 ذخیره تغییرات
                </button>
                <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-secondary" style="width:100%;margin-top:8px;">
                    🔄 بازگشت به معامله
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

    // Lost reason toggle based on status
    document.querySelectorAll('input[name="deal_status"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('input[name="deal_status"]').forEach(function(r) {
                var label = r.closest('label');
                if (label) {
                    if (r.checked) label.style.borderColor = r.value === 'won' ? '#28a745' : (r.value === 'lost' ? '#dc3545' : '#28a745');
                    else label.style.borderColor = 'transparent';
                }
            });
        });
    });
});
</script>