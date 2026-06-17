<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?php echo $config['url']; ?>/deals" class="btn btn-sm btn-secondary">← بازگشت به لیست</a>
        <h5 style="margin:0;">🔍 جزییات معامله</h5>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button class="btn btn-sm btn-success" onclick="openModal('smsModal')">✉️ پیامک</button>
        <a href="<?php echo $config['url']; ?>/payment/create/<?php echo $deal->id; ?>" class="btn btn-sm btn-primary">💳 لینک پرداخت</a>
        <a href="<?php echo $config['url']; ?>/deals/edit/<?php echo $deal->id; ?>" class="btn btn-sm btn-secondary">✏️ ویرایش</a>
    </div>
</div>

<div class="row" style="margin-top:16px;">
    <!-- === MAIN CONTENT (7 cols) === -->
    <div class="col-md-7">
        <!-- Deal Header Card -->
        <div class="card" style="padding:24px;margin-bottom:16px;border-radius:16px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
                <div style="flex:1;min-width:200px;">
                    <h3 style="margin:0 0 10px 0;font-size:22px;font-weight:800;color:var(--gray-900);"><?php echo htmlspecialchars($deal->title); ?></h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <span class="badge-stage" style="background:<?php echo $deal->stage_color; ?>18;color:<?php echo $deal->stage_color; ?>;padding:5px 14px;border-radius:20px;font-size:13px;font-weight:600;">
                            ● <?php echo htmlspecialchars($deal->stage_name); ?>
                        </span>
                        <span style="background:var(--gray-100);padding:5px 14px;border-radius:20px;font-size:13px;color:var(--gray-600);font-weight:500;">
                            📋 <?php echo htmlspecialchars($deal->pipeline_name); ?>
                        </span>
                        <?php if ($deal->is_won): ?>
                        <span style="background:linear-gradient(135deg,#d4edda,#c3e6cb);padding:5px 14px;border-radius:20px;font-size:13px;color:#155724;font-weight:600;">✅ موفق</span>
                        <?php elseif ($deal->is_lost): ?>
                        <span style="background:linear-gradient(135deg,#f8d7da,#f5c6cb);padding:5px 14px;border-radius:20px;font-size:13px;color:#721c24;font-weight:600;">❌ ناموفق</span>
                        <?php else: ?>
                        <span style="background:linear-gradient(135deg,#fff3cd,#ffeaa7);padding:5px 14px;border-radius:20px;font-size:13px;color:#856404;font-weight:600;">⏳ در جریان</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="text-align:left;direction:ltr;background:linear-gradient(135deg,#667eea08,#764ba208);padding:12px 20px;border-radius:14px;border:1px solid #667eea15;">
                    <div style="font-size:30px;font-weight:900;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                        <?php echo number_format($deal->amount); ?>
                    </div>
                    <div style="font-size:12px;color:var(--gray-500);font-weight:500;text-align:center;">تومان</div>
                </div>
            </div>

            <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">

            <div class="row g-3">
                <div class="col-6 col-md-3"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">👤 مسئول</small><strong style="font-size:13px;"><?php echo htmlspecialchars($deal->assigned_name ?? 'تعیین نشده'); ?></strong></div></div>
                <div class="col-6 col-md-3"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">📅 ایجاد</small><strong style="font-size:13px;"><?php echo date('Y/m/d', strtotime($deal->created_at)); ?></strong></div></div>
                <div class="col-6 col-md-3"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">🎯 منبع</small><strong style="font-size:13px;"><?php echo htmlspecialchars($deal->source ?? '-'); ?></strong></div></div>
                <div class="col-6 col-md-3"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">👤 ایجادکننده</small><strong style="font-size:13px;"><?php echo htmlspecialchars($deal->creator_name ?? '-'); ?></strong></div></div>
                <?php if ($deal->expected_close_date): ?>
                <div class="col-6 col-md-3"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">📅 پیش‌بینی</small><strong style="font-size:13px;"><?php echo date('Y/m/d', strtotime($deal->expected_close_date)); ?></strong></div></div>
                <?php endif; ?>
                <div class="col-6 col-md-3"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">📊 احتمال</small><strong style="font-size:13px;"><?php echo $deal->probability; ?>%</strong></div></div>
                <?php if ($deal->is_lost && $deal->lost_reason): ?>
                <div class="col-12"><div style="background:#f8d7da;padding:10px 14px;border-radius:10px;border-right:3px solid #dc3545;"><small style="color:#721c24;display:block;font-size:11px;">❌ دلیل عدم موفقیت</small><strong style="font-size:13px;color:#721c24;"><?php echo htmlspecialchars($deal->lost_reason); ?></strong></div></div>
                <?php endif; ?>
            </div>

            <?php if ($deal->description): ?>
            <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">
            <div>
                <small style="color:var(--gray-500);display:block;margin-bottom:8px;">📝 توضیحات</small>
                <p style="margin:0;line-height:1.9;font-size:14px;color:var(--gray-700);">
                <?php 
                $desc = htmlspecialchars($deal->description);
                $desc = preg_replace('/#([\x{600}-\x{6FF}\x{FB8A}\x{067E}\x{0686}\x{06AF}\x{0698}\w]+)/u', '<a href="' . $config['url'] . '/deals/tag/$1" style="color:var(--primary);font-weight:bold;text-decoration:none;">#$1</a>', $desc);
                echo nl2br($desc); 
                ?>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Contact Card -->
        <?php if ($deal->contact_name): ?>
        <div class="card" style="padding:24px;margin-bottom:16px;border-radius:16px;">
            <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:15px;">👤 اطلاعات مخاطب</h5>
            <div class="row g-2">
                <div class="col-md-4"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">نام</small><strong style="font-size:13px;"><?php echo htmlspecialchars($deal->contact_name); ?></strong></div></div>
                <div class="col-md-4"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">📞 تلفن</small><strong style="font-size:13px;direction:ltr;display:inline-block;"><?php echo htmlspecialchars($deal->contact_phone ?? '-'); ?></strong></div></div>
                <div class="col-md-4"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">📧 ایمیل</small><strong style="font-size:13px;"><?php echo htmlspecialchars($deal->contact_email ?? '-'); ?></strong></div></div>
                <?php if ($deal->national_code): ?><div class="col-md-4"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">کد ملی</small><strong style="font-size:13px;"><?php echo htmlspecialchars($deal->national_code); ?></strong></div></div><?php endif; ?>
                <?php if ($deal->passport_number): ?><div class="col-md-4"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">🛂 پاسپورت</small><strong style="font-size:13px;"><?php echo htmlspecialchars($deal->passport_number); ?></strong></div></div><?php endif; ?>
                <?php if ($deal->company): ?><div class="col-md-4"><div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;"><small style="color:var(--gray-500);display:block;font-size:11px;">🏢 شرکت</small><strong style="font-size:13px;"><?php echo htmlspecialchars($deal->company); ?></strong></div></div><?php endif; ?>
            </div>
            <div style="margin-top:14px;display:flex;gap:8px;">
                <button class="btn btn-sm btn-success" onclick="openModal('smsModal')">✉️ ارسال پیامک به مخاطب</button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Activities -->
        <div class="card" style="padding:24px;margin-bottom:16px;border-radius:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h5 style="margin:0;font-weight:bold;font-size:15px;">📅 فعالیت‌ها</h5>
                <button class="btn btn-sm btn-primary" onclick="openModal('activityModal')">➕ ثبت فعالیت جدید</button>
            </div>
            <?php if (empty($activities)): ?>
            <div style="text-align:center;padding:40px 20px;color:var(--gray-400);">
                <div style="font-size:40px;margin-bottom:12px;">📋</div>
                <p>هیچ فعالیتی ثبت نشده است.</p>
            </div>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($activities as $act): ?>
                <div style="display:flex;align-items:flex-start;gap:12px;padding:14px;background:var(--gray-50);border-radius:12px;border-right:3px solid <?php echo $act->is_done ? '#28a745' : '#ffc107'; ?>;">
                    <div style="width:38px;height:38px;background:<?php echo $act->type == 'call' ? '#e3f2fd' : ($act->type == 'meeting' ? '#fce4ec' : ($act->type == 'sms' ? '#e8f5e9' : '#fff3e0')); ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <?php echo $act->type == 'call' ? '📞' : ($act->type == 'meeting' ? '🤝' : ($act->type == 'sms' ? '✉️' : ($act->type == 'email' ? '📧' : '📝'))); ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <strong style="font-size:14px;"><?php echo htmlspecialchars($act->subject ?? ($act->type == 'call' ? 'تماس تلفنی' : ($act->type == 'meeting' ? 'جلسه' : ($act->type == 'sms' ? 'پیامک' : ($act->type == 'email' ? 'ایمیل' : 'یادداشت'))))); ?></strong>
                        <br><small style="color:var(--gray-400);font-size:12px;"><?php echo htmlspecialchars($act->user_name ?? ''); ?> | <?php echo date('Y/m/d H:i', strtotime($act->created_at)); ?></small>
                        <?php if ($act->description): ?><p style="margin:6px 0 0 0;font-size:13px;color:var(--gray-600);line-height:1.7;"><?php echo nl2br(htmlspecialchars($act->description)); ?></p><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- === SIDEBAR (5 cols) === -->
    <div class="col-md-5">
        <!-- Quick Stats Card -->
        <div class="card" style="padding:20px;margin-bottom:16px;border-radius:16px;">
            <h5 style="margin:0 0 14px 0;font-weight:bold;font-size:14px;">⚡ خلاصه معامله</h5>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div style="display:flex;flex-direction:column;gap:4px;padding:10px 12px;background:var(--gray-50);border-radius:10px;">
                    <span style="font-size:11px;color:var(--gray-500);">مبلغ</span>
                    <strong style="font-size:15px;color:var(--primary);"><?php echo number_format($deal->amount); ?></strong>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;padding:10px 12px;background:var(--gray-50);border-radius:10px;">
                    <span style="font-size:11px;color:var(--gray-500);">مرحله</span>
                    <span style="font-size:13px;font-weight:700;color:<?php echo $deal->stage_color; ?>;"><?php echo htmlspecialchars($deal->stage_name); ?></span>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;padding:10px 12px;background:var(--gray-50);border-radius:10px;">
                    <span style="font-size:11px;color:var(--gray-500);">وضعیت</span>
                    <?php if ($deal->is_won): ?><span style="font-size:13px;font-weight:700;color:#155724;">✅ موفق</span>
                    <?php elseif ($deal->is_lost): ?><span style="font-size:13px;font-weight:700;color:#721c24;">❌ ناموفق</span>
                    <?php else: ?><span style="font-size:13px;font-weight:700;color:#856404;">⏳ در جریان</span>
                    <?php endif; ?>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;padding:10px 12px;background:var(--gray-50);border-radius:10px;">
                    <span style="font-size:11px;color:var(--gray-500);">احتمال</span>
                    <strong style="font-size:13px;"><?php echo $deal->probability; ?>%</strong>
                </div>
            </div>
        </div>

        <!-- Pipeline Progress -->
        <div class="card" style="padding:20px;margin-bottom:16px;border-radius:16px;">
            <h5 style="margin:0 0 14px 0;font-weight:bold;font-size:14px;">📊 پیشرفت در پایپ‌لاین</h5>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--gray-500);">
                    <span>مرحله <?php echo $currentOrder; ?> از <?php echo $totalStages; ?></span>
                    <span style="font-weight:700;color:var(--primary);">%<?php echo $progressPct; ?></span>
                </div>
                <div style="height:8px;background:var(--gray-100);border-radius:4px;overflow:hidden;">
                    <div style="height:100%;width:<?php echo $progressPct; ?>%;background:linear-gradient(90deg,#667eea,#764ba2);border-radius:4px;transition:width 0.5s;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:8px;">
                    <?php foreach ($allStages as $idx => $s): ?>
                    <div style="text-align:center;flex:1;">
                        <div style="width:10px;height:10px;border-radius:50%;margin:0 auto 4px;background:<?php echo ($idx + 1) <= $currentOrder ? $s->color : '#e0e0e0'; ?>;"></div>
                        <small style="font-size:9px;color:var(--gray-500);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($s->name); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Edit Deal Inline Card -->
        <div class="card" style="padding:20px;margin-bottom:16px;border-radius:16px;border:2px dashed var(--primary);">
            <h5 style="margin:0 0 12px 0;font-weight:bold;font-size:14px;color:var(--primary);">✏️ ویرایش سریع</h5>
            <form method="POST" action="<?php echo $config['url']; ?>/deals/update/<?php echo $deal->id; ?>" data-ajax="true" id="quickEditForm">
                <div class="row g-2">
                    <div class="col-12" style="margin-bottom:8px;">
                        <label class="form-label" style="font-size:12px;">عنوان</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($deal->title); ?>" style="font-size:13px;padding:8px 10px;">
                    </div>
                    <div class="col-6" style="margin-bottom:8px;">
                        <label class="form-label" style="font-size:12px;">مرحله</label>
                        <select name="stage_id" class="form-select" style="font-size:13px;padding:8px 10px;">
                            <?php foreach ($stages as $s): ?>
                            <option value="<?php echo $s->id; ?>" <?php echo $s->id == $deal->stage_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6" style="margin-bottom:8px;">
                        <label class="form-label" style="font-size:12px;">مبلغ (تومان)</label>
                        <input type="text" name="amount" class="form-control" value="<?php echo number_format($deal->amount); ?>" style="font-size:13px;padding:8px 10px;direction:ltr;text-align:left;">
                    </div>
                    <div class="col-6" style="margin-bottom:8px;">
                        <label class="form-label" style="font-size:12px;">👤 مسئول</label>
                        <select name="assigned_to" class="form-select" style="font-size:13px;padding:8px 10px;">
                            <option value="">انتخاب</option>
                            <?php foreach ($users ?? [] as $u): ?>
                            <option value="<?php echo $u->id; ?>" <?php echo $u->id == $deal->assigned_to ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6" style="margin-bottom:8px;">
                        <label class="form-label" style="font-size:12px;">وضعیت</label>
                        <select name="deal_status" class="form-select" style="font-size:13px;padding:8px 10px;">
                            <option value="open" <?php echo (!$deal->is_won && !$deal->is_lost) ? 'selected' : ''; ?>>⏳ در جریان</option>
                            <option value="won" <?php echo $deal->is_won ? 'selected' : ''; ?>>✅ موفق</option>
                            <option value="lost" <?php echo $deal->is_lost ? 'selected' : ''; ?>>❌ ناموفق</option>
                        </select>
                    </div>
                </div>
                <div class="ajax-error alert alert-danger" style="display:none;font-size:12px;padding:8px 12px;margin-top:8px;"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;padding:10px;font-size:14px;font-weight:bold;">💾 ذخیره تغییرات</button>
            </form>
        </div>

        <!-- Payments -->
        <div class="card" style="padding:20px;margin-bottom:16px;border-radius:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h5 style="margin:0;font-weight:bold;font-size:14px;">💳 پرداخت‌ها</h5>
                <a href="<?php echo $config['url']; ?>/payment/create/<?php echo $deal->id; ?>" class="btn btn-sm btn-primary" style="font-size:11px;">+ جدید</a>
            </div>
            <?php if (empty($payments)): ?>
            <p style="color:var(--gray-400);font-size:13px;text-align:center;padding:16px;">هیچ پرداختی ثبت نشده است.</p>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <?php foreach ($payments as $p): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:var(--gray-50);border-radius:10px;flex-wrap:wrap;gap:4px;">
                    <div>
                        <strong style="font-size:13px;"><?php echo number_format($p->amount); ?> تومان</strong>
                        <br><small style="color:var(--gray-400);font-size:11px;"><?php echo date('Y/m/d', strtotime($p->created_at)); ?></small>
                        <?php if (!empty($p->public_token) && $p->status == 'pending'): ?>
                        <br><a href="<?php echo $config['url']; ?>/pay/<?php echo htmlspecialchars($p->public_token); ?>" target="_blank" style="font-size:11px;color:var(--primary);font-weight:600;">🔗 لینک پرداخت</a>
                        <?php endif; ?>
                    </div>
                    <span style="padding:4px 10px;border-radius:12px;font-size:11px;font-weight:700;
                        <?php echo $p->status == 'success' ? 'background:#d4edda;color:#155724;' : ($p->status == 'pending' ? 'background:#fff3cd;color:#856404;' : 'background:#f8d7da;color:#721c24;'); ?>">
                        <?php echo $p->status == 'success' ? 'موفق' : ($p->status == 'pending' ? 'در انتظار' : 'ناموفق'); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- SMS History -->
        <?php if ($config['features']['sms'] && !empty($smsHistory)): ?>
        <div class="card" style="padding:20px;margin-bottom:16px;border-radius:16px;">
            <h5 style="margin:0 0 12px 0;font-weight:bold;font-size:14px;">✉️ آخرین پیامک‌ها</h5>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <?php foreach (array_slice($smsHistory, 0, 5) as $sms): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--gray-50);border-radius:10px;">
                    <div>
                        <strong style="font-size:13px;"><?php echo htmlspecialchars($sms->recipient); ?></strong>
                        <br><small style="color:var(--gray-400);font-size:11px;"><?php echo date('Y/m/d', strtotime($sms->created_at)); ?></small>
                    </div>
                    <span style="padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;<?php echo $sms->status == 'sent' ? 'background:#d4edda;color:#155724;' : 'background:#f8d7da;color:#721c24;'; ?>">
                        <?php echo $sms->status == 'sent' ? 'ارسال' : 'خطا'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- SMS Modal -->
<div class="modal-overlay" id="smsModal">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <h5 class="modal-title">✉️ ارسال پیامک</h5>
            <button type="button" class="modal-close" onclick="closeModal('smsModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/sms/send" data-ajax="true">
            <input type="hidden" name="deal_id" value="<?php echo $deal->id; ?>">
            <?php if ($deal->contact_id): ?>
            <input type="hidden" name="contact_id" value="<?php echo $deal->contact_id; ?>">
            <?php endif; ?>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">شماره گیرنده *</label>
                    <input type="text" name="recipient" class="form-input" required 
                           value="<?php echo htmlspecialchars($deal->contact_phone ?? ''); ?>" 
                           placeholder="0912xxxxxxx" style="direction:ltr;text-align:left;">
                </div>
                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">متن پیامک *</label>
                    <textarea name="message" class="form-textarea" rows="4" required 
                              placeholder="متن پیامک خود را وارد کنید..."
                              style="min-height:120px;"><?php echo "{$config['name']}"; ?> | 
معامله: <?php echo htmlspecialchars($deal->title); ?>
مبلغ: <?php echo number_format($deal->amount); ?> تومان</textarea>
                    <small style="color:var(--gray-400);font-size:11px;display:block;margin-top:4px;">⚠️ تعداد کاراکترها: <span id="smsCharCount">0</span></small>
                </div>
                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">⏰ ارسال در زمان مشخص (اختیاری)</label>
                    <input type="datetime-local" name="send_time" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">✉️ ارسال پیامک</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('smsModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<!-- Activity Modal -->
<div class="modal-overlay" id="activityModal">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <h5 class="modal-title">📅 ثبت فعالیت جدید</h5>
            <button type="button" class="modal-close" onclick="closeModal('activityModal')">&times;</button>
        </div>
        <div class="ajax-error alert alert-danger" style="display:none;"></div>
        <form method="POST" action="<?php echo $config['url']; ?>/deals/add-activity/<?php echo $deal->id; ?>" data-ajax="true">
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">نوع فعالیت</label>
                    <select name="type" class="form-input">
                        <option value="note">📝 یادداشت</option>
                        <option value="call">📞 تماس تلفنی</option>
                        <option value="meeting">🤝 جلسه</option>
                        <option value="email">📧 ایمیل</option>
                        <option value="follow_up">📌 پیگیری</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">موضوع</label>
                    <input type="text" name="subject" class="form-input" placeholder="مثال: پیگیری مدارک مشتری">
                </div>
                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-textarea" rows="3" placeholder="توضیحات بیشتر..."></textarea>
                </div>
                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">تاریخ فعالیت</label>
                    <input type="datetime-local" name="activity_date" class="form-input" value="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">⏰ یادآوری (اختیاری)</label>
                    <input type="datetime-local" name="reminder_at" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">✅ ثبت فعالیت</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('activityModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script>
// SMS character counter
document.getElementById('smsModal')?.addEventListener('input', function(e) {
    if (e.target.name === 'message') {
        document.getElementById('smsCharCount').textContent = e.target.value.length;
    }
});

// Quick edit form: on AJAX success, reload page to show updated data
document.addEventListener('DOMContentLoaded', function() {
    var quickForm = document.getElementById('quickEditForm');
    if (quickForm) {
        quickForm.addEventListener('ajax:success', function() {
            location.reload();
        });
    }
});
</script>