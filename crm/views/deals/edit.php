<?php $config = $GLOBALS['app_config']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2"></i>ویرایش معامله: <?php echo htmlspecialchars($deal->title); ?></h5>
    <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="card mx-auto" style="max-width:850px;">
    <div class="card-body p-3 p-md-4">
        <form method="POST" action="<?php echo $config['url']; ?>/deals/update/<?php echo $deal->id; ?>" id="dealEditForm">
            <div class="row g-3">
                <div class="col-12"><label class="form-label text-muted small fw-medium">عنوان معامله <span class="text-danger">*</span></label><input type="text" name="title" value="<?php echo htmlspecialchars($deal->title); ?>" required class="form-control"></div>
                
                <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">پایپ لاین</label><select name="pipeline_id" id="editPipelineSelect" class="form-select"><?php foreach ($pipelines as $p): ?><option value="<?php echo $p->id; ?>" <?php echo $p->id == $deal->pipeline_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option><?php endforeach; ?></select></div>
                
                <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">مرحله</label><select name="stage_id" id="editStageSelect" class="form-select"><?php foreach ($stages as $s): ?><option value="<?php echo $s->id; ?>" <?php echo $s->id == $deal->stage_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->name); ?></option><?php endforeach; ?></select></div>
                
                <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">مبلغ (تومان)</label><input type="text" name="amount" id="amountInput" value="<?php echo $deal->amount ? number_format($deal->amount) : ''; ?>" class="form-control" dir="ltr" style="text-align:left;font-weight:700;"></div>
                
                <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">احتمال موفقیت</label><div class="d-flex align-items-center gap-2"><input type="range" name="probability" min="0" max="100" value="<?php echo (int)$deal->probability; ?>" oninput="document.getElementById('probVal').textContent=this.value+'%'" class="form-range flex-grow-1"><span id="probVal" class="fw-bold text-primary"><?php echo (int)$deal->probability; ?>%</span></div></div>
                
                <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">مخاطب</label><select name="contact_id" class="form-select"><option value="">انتخاب مخاطب</option><?php foreach ($contacts as $c): ?><option value="<?php echo $c->id; ?>" <?php echo $c->id == $deal->contact_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->full_name); ?> (<?php echo htmlspecialchars($c->phone); ?>)</option><?php endforeach; ?></select></div>
                
                <div class="col-12 col-md-6">
                    <?php if (\Core\Auth::canAccessAll('deals.edit')): ?>
                    <label class="form-label text-muted small fw-medium">مسئول</label><select name="assigned_to" class="form-select"><option value="">انتخاب</option><?php foreach ($users as $u): ?><option value="<?php echo $u->id; ?>" <?php echo $u->id == $deal->assigned_to ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option><?php endforeach; ?></select>
                    <?php else: ?>
                    <input type="hidden" name="assigned_to" value="<?php echo $deal->assigned_to; ?>"><label class="form-label text-muted small fw-medium">مسئول</label><div class="bg-light rounded p-2 text-primary fw-semibold"><i class="bi bi-person me-1"></i><?php foreach ($users as $u) { if ($u->id == $deal->assigned_to) { echo htmlspecialchars($u->full_name); break; } } ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">منبع</label><select name="source" class="form-select"><option value="">انتخاب</option><?php foreach ($sources as $s): ?><option value="<?php echo htmlspecialchars($s->name); ?>" <?php echo $s->name == $deal->source ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->icon . ' ' . $s->name); ?></option><?php endforeach; ?></select></div>
                
                <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">تاریخ پیش‌بینی بستن</label><input type="date" name="expected_close_date" value="<?php echo $deal->expected_close_date ?? ''; ?>" class="form-control"></div>
                
                <!-- Status -->
                <div class="col-12">
                    <label class="form-label text-muted small fw-medium">وضعیت معامله</label>
                    <div class="row g-2">
                        <div class="col-4"><label class="card p-3 text-center cursor-pointer border-2 <?php echo (!$deal->is_won && !$deal->is_lost) ? 'border-success bg-success bg-opacity-10' : 'border-light'; ?>" style="cursor:pointer;"><input type="radio" name="deal_status" value="open" <?php echo (!$deal->is_won && !$deal->is_lost) ? 'checked' : ''; ?> class="d-none"><div class="fs-4">⏳</div><small class="fw-semibold">در جریان</small></label></div>
                        <div class="col-4"><label class="card p-3 text-center cursor-pointer border-2 <?php echo $deal->is_won ? 'border-success bg-success bg-opacity-10' : 'border-light'; ?>" style="cursor:pointer;"><input type="radio" name="deal_status" value="won" <?php echo $deal->is_won ? 'checked' : ''; ?> class="d-none"><div class="fs-4">✅</div><small class="fw-semibold">موفق</small></label></div>
                        <div class="col-4"><label class="card p-3 text-center cursor-pointer border-2 <?php echo $deal->is_lost ? 'border-danger bg-danger bg-opacity-10' : 'border-light'; ?>" style="cursor:pointer;"><input type="radio" name="deal_status" value="lost" <?php echo $deal->is_lost ? 'checked' : ''; ?> class="d-none"><div class="fs-4">❌</div><small class="fw-semibold">ناموفق</small></label></div>
                    </div>
                </div>
                
                <!-- Win Reason -->
                <div class="col-12" id="winReasonBox" style="display:<?php echo $deal->is_won ? 'block' : 'none'; ?>;">
                    <div class="row g-2">
                        <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">دلیل موفقیت</label><select name="win_reason_id" class="form-select"><option value="">انتخاب</option><?php try { $wr=\Core\Database::getInstance()->fetchAll("SELECT id,name,icon FROM deal_win_reasons WHERE is_active=1 ORDER BY sort_order"); foreach($wr as $r): ?><option value="<?php echo $r->id; ?>" <?php echo ($deal->win_reason_id??'')==$r->id?'selected':''; ?>><?php echo htmlspecialchars($r->icon.' '.$r->name); ?></option><?php endforeach; } catch(\Exception $e){} ?></select></div>
                        <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">توضیحات</label><textarea name="win_reason_note" class="form-control" rows="2"><?php echo htmlspecialchars($deal->win_reason_note ?? ''); ?></textarea></div>
                    </div>
                </div>
                
                <!-- Lost Reason -->
                <div class="col-12" id="lostReasonBox" style="display:<?php echo $deal->is_lost ? 'block' : 'none'; ?>;">
                    <div class="row g-2">
                        <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">دلیل شکست</label><select name="loss_reason_id" class="form-select"><option value="">انتخاب</option><?php try { $lr=\Core\Database::getInstance()->fetchAll("SELECT id,name,icon FROM deal_loss_reasons WHERE is_active=1 ORDER BY sort_order"); foreach($lr as $r): ?><option value="<?php echo $r->id; ?>" <?php echo ($deal->loss_reason_id??'')==$r->id?'selected':''; ?>><?php echo htmlspecialchars($r->icon.' '.$r->name); ?></option><?php endforeach; } catch(\Exception $e){} ?></select></div>
                        <div class="col-12 col-md-6"><label class="form-label text-muted small fw-medium">توضیحات</label><textarea name="loss_reason_note" class="form-control" rows="2"><?php echo htmlspecialchars($deal->loss_reason_note ?? $deal->lost_reason ?? ''); ?></textarea></div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="col-12"><label class="form-label text-muted small fw-medium">توضیحات</label><textarea name="description" class="form-control" rows="4" placeholder="توضیحات... از # برای هشتگ استفاده کنید"><?php echo htmlspecialchars($deal->description ?? ''); ?></textarea></div>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ذخیره تغییرات</button>
                <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-outline-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pipeline change
    var pipelineSelect = document.getElementById('editPipelineSelect');
    var stageSelect = document.getElementById('editStageSelect');
    if (pipelineSelect) {
        pipelineSelect.addEventListener('change', function() {
            var pid = this.value; if (!pid) return;
            fetch('<?php echo $config['url']; ?>/pipelines/' + pid + '/stages').then(function(r){return r.json();}).then(function(data){
                stageSelect.innerHTML = '';
                if (data && data.length) data.forEach(function(s){var o=document.createElement('option');o.value=s.id;o.textContent=s.name;stageSelect.appendChild(o);});
            }).catch(function(){});
        });
    }
    // Amount formatting
    var ai = document.getElementById('amountInput');
    if (ai) ai.addEventListener('input', function(){var v=this.value.replace(/[^0-9]/g,'');if(v)this.value=parseInt(v).toLocaleString('en');});
    // Status toggle
    document.querySelectorAll('.card input[name="deal_status"]').forEach(function(r){
        r.closest('label').addEventListener('click',function(){
            document.querySelectorAll('.card input[name="deal_status"]').forEach(function(x){x.closest('label').classList.remove('border-success','border-danger','bg-success','bg-danger','bg-opacity-10');x.closest('label').classList.add('border-light');});
            this.classList.remove('border-light');
            var v=this.querySelector('input').value;
            if(v==='open'||v==='won') this.classList.add('border-success','bg-success','bg-opacity-10');
            else this.classList.add('border-danger','bg-danger','bg-opacity-10');
            var wb=document.getElementById('winReasonBox');var lb=document.getElementById('lostReasonBox');
            if(wb) wb.style.display=v==='won'?'block':'none';
            if(lb) lb.style.display=v==='lost'?'block':'none';
        });
    });
    // Form submit - strip commas
    document.getElementById('dealEditForm').addEventListener('submit',function(){if(ai)ai.value=ai.value.replace(/[^0-9]/g,'');});
});
</script>