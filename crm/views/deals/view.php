<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/deals" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
        <h5 class="fw-bold mb-0"><i class="bi bi-briefcase me-2"></i>جزییات معامله</h5>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-outline-success btn-sm" onclick="new bootstrap.Modal(document.getElementById('smsModal')).show()"><i class="bi bi-envelope me-1"></i>پیامک</button>
        <a href="<?php echo $config['url']; ?>/payment/create/<?php echo $deal->id; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-credit-card me-1"></i>لینک پرداخت</a>
        <a href="<?php echo $config['url']; ?>/deals/edit/<?php echo $deal->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>ویرایش</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <!-- Deal Header -->
        <div class="card mb-3"><div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div class="flex-grow-1" style="min-width:200px;">
                    <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($deal->title); ?></h5>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge" style="background:<?php echo $deal->stage_color; ?>18;color:<?php echo $deal->stage_color; ?>;"><?php echo htmlspecialchars($deal->stage_name); ?></span>
                        <span class="badge bg-light text-dark"><i class="bi bi-kanban me-1"></i><?php echo htmlspecialchars($deal->pipeline_name); ?></span>
                        <?php if ($deal->is_won): ?><span class="badge bg-success">✅ موفق</span>
                        <?php elseif ($deal->is_lost): ?><span class="badge bg-danger">❌ ناموفق</span>
                        <?php else: ?><span class="badge bg-warning text-dark">⏳ در جریان</span><?php endif; ?>
                    </div>
                </div>
                <div class="text-center p-3 rounded" style="background:linear-gradient(135deg,#667eea08,#764ba208);direction:ltr;">
                    <div class="fw-bold" style="font-size:28px;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?php echo number_format($deal->amount ?? 0); ?></div>
                    <small class="text-muted">تومان</small>
                </div>
            </div>
            <hr>
            <div class="row g-2">
                <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-person me-1"></i>مسئول</small><strong class="small"><?php echo htmlspecialchars($deal->assigned_name ?? '-'); ?></strong></div></div>
                <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-calendar me-1"></i>ایجاد</small><strong class="small"><?php echo \Core\JDate::displayDate($deal->created_at); ?></strong></div></div>
                <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-crosshair me-1"></i>منبع</small><strong class="small"><?php echo htmlspecialchars($deal->source ?? '-'); ?></strong></div></div>
                <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-person me-1"></i>ایجادکننده</small><strong class="small"><?php echo htmlspecialchars($deal->creator_name ?? '-'); ?></strong></div></div>
                <?php if ($deal->expected_close_date): ?><div class="col-6 col-md-3"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-calendar-event me-1"></i>پیش‌بینی</small><strong class="small"><?php echo \Core\JDate::displayDate($deal->expected_close_date); ?></strong></div></div><?php endif; ?>
                <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-percent me-1"></i>احتمال</small><strong class="small"><?php echo $deal->probability; ?>%</strong></div></div>
                <?php if ($deal->is_lost && $deal->lost_reason): ?><div class="col-12"><div class="bg-danger bg-opacity-10 rounded p-2 border-end border-danger border-3"><small class="text-danger d-block" style="font-size:11px;"><i class="bi bi-x-circle me-1"></i>دلیل عدم موفقیت</small><strong class="small text-danger"><?php echo htmlspecialchars($deal->lost_reason); ?></strong></div></div><?php endif; ?>
            </div>
            <?php if ($deal->description): ?>
            <hr><small class="text-muted d-block mb-2"><i class="bi bi-card-text me-1"></i>توضیحات</small>
            <p class="small mb-0" style="line-height:1.9;color:#495057;"><?php echo nl2br(htmlspecialchars($deal->description)); ?></p>
            <?php endif; ?>
        </div></div>

        <!-- Contact -->
        <?php if ($deal->contact_name): ?>
        <div class="card mb-3"><div class="card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-person me-2"></i>اطلاعات مخاطب</h6>
            <div class="row g-2">
                <div class="col-6 col-md-4"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">نام</small><strong class="small"><?php echo htmlspecialchars($deal->contact_name); ?></strong></div></div>
                <div class="col-6 col-md-4"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-phone me-1"></i>تلفن</small><strong class="small" dir="ltr"><?php echo htmlspecialchars($deal->contact_phone ?? '-'); ?></strong></div></div>
                <div class="col-6 col-md-4"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;"><i class="bi bi-envelope me-1"></i>ایمیل</small><strong class="small"><?php echo htmlspecialchars($deal->contact_email ?? '-'); ?></strong></div></div>
            </div>
            <div class="mt-3 d-flex flex-wrap gap-1">
                <button class="btn btn-sm btn-outline-success" onclick="new bootstrap.Modal(document.getElementById('smsModal')).show()"><i class="bi bi-envelope me-1"></i>پیامک</button>
                <?php if (!empty($deal->contact_phone)):
                    $ph = preg_replace('/^0/','98',preg_replace('/[\s\-\(\)]+/','',$deal->contact_phone));
                    if(strpos($ph,'+')===0) $ph=substr($ph,1);
                ?>
                <a href="https://wa.me/<?php echo $ph; ?>" target="_blank" class="btn btn-sm" style="background:#25D366;color:#fff;"><i class="bi bi-whatsapp me-1"></i>واتساپ</a>
                <a href="https://t.me/+<?php echo $ph; ?>" target="_blank" class="btn btn-sm" style="background:#0088cc;color:#fff;"><i class="bi bi-telegram me-1"></i>تلگرام</a>
                <?php endif; ?>
            </div>
        </div></div>
        <?php endif; ?>

        <!-- Activities -->
        <div class="card mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2"></i>فعالیت‌ها</h6>
                <button class="btn btn-sm btn-primary" onclick="new bootstrap.Modal(document.getElementById('activityModal')).show()"><i class="bi bi-plus me-1"></i>ثبت فعالیت</button>
            </div>
            <?php if (empty($activities)): ?>
            <div class="text-center py-4 text-muted"><i class="bi bi-calendar-x" style="font-size:40px;opacity:0.3;"></i><p class="mt-2 small">هیچ فعالیتی ثبت نشده.</p></div>
            <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($activities as $act): ?>
                <div class="bg-light rounded p-3 border-end border-3 <?php echo $act->is_done ? 'border-success' : 'border-warning'; ?>">
                    <div class="d-flex align-items-start gap-2">
                        <div class="rounded p-2" style="background:<?php echo $act->type=='call'?'#e3f2fd':($act->type=='meeting'?'#fce4ec':($act->type=='sms'?'#e8f5e9':'#fff3e0')); ?>;">
                            <i class="bi bi-<?php echo $act->type=='call'?'telephone':($act->type=='meeting'?'people':($act->type=='sms'||$act->type=='email'?'envelope':'pencil')); ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <strong class="small"><?php echo htmlspecialchars($act->subject ?? '-'); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($act->user_name ?? ''); ?> | <?php echo \Core\JDate::displayDateTime($act->created_at); ?></small>
                            <?php if ($act->description): ?><p class="small text-secondary mt-1 mb-0"><?php echo nl2br(htmlspecialchars($act->description)); ?></p><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div></div>
    </div>

    <!-- Sidebar -->
    <div class="col-12 col-lg-5">
        <!-- Stats -->
        <div class="card mb-3"><div class="card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-lightning me-2"></i>خلاصه</h6>
            <div class="row g-2">
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">مبلغ</small><strong class="text-primary"><?php echo number_format($deal->amount ?? 0); ?></strong></div></div>
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">مرحله</small><strong class="small" style="color:<?php echo $deal->stage_color; ?>;"><?php echo htmlspecialchars($deal->stage_name); ?></strong></div></div>
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">وضعیت</small>
                    <?php if($deal->is_won): ?><strong class="small text-success">✅ موفق</strong><?php elseif($deal->is_lost): ?><strong class="small text-danger">❌ ناموفق</strong><?php else: ?><strong class="small text-warning">⏳ در جریان</strong><?php endif; ?>
                </div></div>
                <div class="col-6"><div class="bg-light rounded p-2"><small class="text-muted d-block" style="font-size:11px;">احتمال</small><strong><?php echo $deal->probability; ?>%</strong></div></div>
            </div>
        </div></div>

        <!-- Pipeline Progress -->
        <div class="card mb-3"><div class="card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2"></i>پیشرفت</h6>
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>مرحله <?php echo $currentOrder; ?> از <?php echo $totalStages; ?></span>
                <span class="fw-bold text-primary">%<?php echo $progressPct; ?></span>
            </div>
            <div class="progress mb-3" style="height:8px;"><div class="progress-bar" style="width:<?php echo $progressPct; ?>%;background:linear-gradient(90deg,#667eea,#764ba2);"></div></div>
            <div class="d-flex justify-content-between">
                <?php foreach ($allStages as $idx => $s): ?>
                <div class="text-center flex-grow-1">
                    <div class="rounded-circle mx-auto mb-1" style="width:10px;height:10px;background:<?php echo ($idx+1)<=$currentOrder?$s->color:'#e0e0e0'; ?>;"></div>
                    <small class="text-muted d-block" style="font-size:9px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($s->name); ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div></div>

        <!-- Quick Edit -->
        <div class="card mb-3"><div class="card-body">
            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-pencil me-2"></i>ویرایش سریع</h6>
            <form method="POST" action="<?php echo $config['url']; ?>/deals/update/<?php echo $deal->id; ?>" data-ajax="true">
                <div class="row g-2">
                    <div class="col-12"><label class="form-label text-muted small">عنوان</label><input type="text" name="title" class="form-control form-control-sm" value="<?php echo htmlspecialchars($deal->title); ?>"></div>
                    <div class="col-6"><label class="form-label text-muted small">مرحله</label><select name="stage_id" class="form-select form-select-sm"><?php foreach($stages as $s): ?><option value="<?php echo $s->id; ?>" <?php echo $s->id==$deal->stage_id?'selected':''; ?>><?php echo htmlspecialchars($s->name); ?></option><?php endforeach; ?></select></div>
                    <div class="col-6"><label class="form-label text-muted small">مبلغ</label><input type="text" name="amount" class="form-control form-control-sm" value="<?php echo number_format($deal->amount); ?>" dir="ltr" style="text-align:left;"></div>
                    <div class="col-6"><label class="form-label text-muted small">مسئول</label><select name="assigned_to" class="form-select form-select-sm"><option value="">انتخاب</option><?php foreach($users??[] as $u): ?><option value="<?php echo $u->id; ?>" <?php echo $u->id==$deal->assigned_to?'selected':''; ?>><?php echo htmlspecialchars($u->full_name); ?></option><?php endforeach; ?></select></div>
                    <div class="col-6"><label class="form-label text-muted small">وضعیت</label><select name="deal_status" class="form-select form-select-sm"><option value="open" <?php echo (!$deal->is_won&&!$deal->is_lost)?'selected':''; ?>>⏳ در جریان</option><option value="won" <?php echo $deal->is_won?'selected':''; ?>>✅ موفق</option><option value="lost" <?php echo $deal->is_lost?'selected':''; ?>>❌ ناموفق</option></select></div>
                </div>
                <div class="ajax-error alert alert-danger d-none mt-2 small p-2"></div>
                <button type="submit" class="btn btn-primary w-100 mt-2"><i class="bi bi-check-circle me-1"></i>ذخیره</button>
            </form>
        </div></div>

        <!-- Payments -->
        <div class="card mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-credit-card me-2"></i>پرداخت‌ها</h6>
                <a href="<?php echo $config['url']; ?>/payment/create/<?php echo $deal->id; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus"></i></a>
            </div>
            <?php if(empty($payments)): ?>
            <p class="text-muted text-center small py-3 mb-0">پرداختی ثبت نشده.</p>
            <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach($payments as $p): ?>
                <div class="bg-light rounded p-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                    <div>
                        <strong class="small"><?php echo number_format($p->amount); ?> تومان</strong>
                        <br><small class="text-muted"><?php echo \Core\JDate::displayDate($p->created_at); ?></small>
                        <?php if(!empty($p->public_token)&&$p->status=='pending'): ?>
                        <br><a href="<?php echo $config['url']; ?>/pay/<?php echo htmlspecialchars($p->public_token); ?>" target="_blank" class="small text-primary fw-bold"><i class="bi bi-link me-1"></i>لینک پرداخت</a>
                        <?php endif; ?>
                    </div>
                    <span class="badge <?php echo $p->status=='success'?'bg-success':($p->status=='pending'?'bg-warning text-dark':'bg-danger'); ?>" style="font-size:11px;"><?php echo $p->status=='success'?'موفق':($p->status=='pending'?'در انتظار':'ناموفق'); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<!-- SMS Modal -->
<div class="modal fade" id="smsModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title fw-bold"><i class="bi bi-envelope me-2"></i>ارسال پیامک</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="ajax-error alert alert-danger d-none mb-0 rounded-0"></div>
    <form method="POST" action="<?php echo $config['url']; ?>/sms/send" data-ajax="true">
        <input type="hidden" name="deal_id" value="<?php echo $deal->id; ?>">
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-12"><label class="form-label text-muted small">شماره گیرنده</label><input type="text" name="phone" class="form-control" required value="<?php echo htmlspecialchars($deal->contact_phone ?? ''); ?>" dir="ltr" style="text-align:left;"></div>
                <div class="col-12"><label class="form-label text-muted small">متن پیامک</label><textarea name="message" class="form-control" rows="4" required>معامله: <?php echo htmlspecialchars($deal->title); ?> | مبلغ: <?php echo number_format($deal->amount); ?> تومان</textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i>ارسال</button><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button></div>
    </form>
</div></div></div>

<!-- Activity Modal -->
<div class="modal fade" id="activityModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2"></i>ثبت فعالیت</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="ajax-error alert alert-danger d-none mb-0 rounded-0"></div>
    <form method="POST" action="<?php echo $config['url']; ?>/deals/add-activity/<?php echo $deal->id; ?>" data-ajax="true">
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-6"><label class="form-label text-muted small">نوع</label><select name="type" class="form-select"><option value="note">📝 یادداشت</option><option value="call">📞 تماس</option><option value="meeting">🤝 جلسه</option></select></div>
                <div class="col-6"><label class="form-label text-muted small">موضوع</label><input type="text" name="subject" class="form-control" placeholder="موضوع"></div>
                <div class="col-12"><label class="form-label text-muted small">توضیحات</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="col-6"><label class="form-label text-muted small">تاریخ</label><input type="datetime-local" name="activity_date" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>"></div>
                <div class="col-6"><label class="form-label text-muted small">یادآوری</label><input type="datetime-local" name="reminder_at" class="form-control"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>ثبت</button><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">لغو</button></div>
    </form>
</div></div></div>

<script>
function copyLink(btn){
    var inp=btn.previousElementSibling;inp.select();document.execCommand('copy');
    btn.innerHTML='<i class="bi bi-check"></i>';setTimeout(function(){btn.innerHTML='<i class="bi bi-clipboard"></i>';},1500);
}
</script>