<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?php echo $config['url']; ?>/deals" class="btn btn-sm btn-secondary">← بازگشت</a>
        <h5 style="margin:0;">🔍 جزییات معامله</h5>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button class="btn btn-sm btn-success" onclick="openModal('smsModal')">✉️ ارسال پیامک</button>
        <a href="<?php echo $config['url']; ?>/payment/create/<?php echo $deal->id; ?>" class="btn btn-sm btn-primary">💳 ایجاد لینک پرداخت</a>
        <a href="<?php echo $config['url']; ?>/deals/edit/<?php echo $deal->id; ?>" class="btn btn-sm btn-secondary">✏️ ویرایش</a>
    </div>
</div>

<div class="row" style="margin-top:16px;">
    <!-- Main Content -->
    <div class="col-md-8">
        <!-- Deal Card -->
        <div class="card" style="padding:24px;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                <div style="flex:1;">
                    <h3 style="margin:0 0 8px 0;font-size:22px;font-weight:bold;"><?php echo htmlspecialchars($deal->title); ?></h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <span class="badge-stage" style="background:<?php echo $deal->stage_color; ?>20;color:<?php echo $deal->stage_color; ?>;padding:4px 12px;border-radius:20px;font-size:13px;">
                            ● <?php echo htmlspecialchars($deal->stage_name); ?>
                        </span>
                        <span style="background:var(--gray-100);padding:4px 12px;border-radius:20px;font-size:13px;color:var(--gray-600);">
                            📋 <?php echo htmlspecialchars($deal->pipeline_name); ?>
                        </span>
                        <?php if ($deal->is_won): ?>
                        <span style="background:#d4edda;padding:4px 12px;border-radius:20px;font-size:13px;color:#155724;">✅ موفق</span>
                        <?php elseif ($deal->is_lost): ?>
                        <span style="background:#f8d7da;padding:4px 12px;border-radius:20px;font-size:13px;color:#721c24;">❌ ناموفق</span>
                        <?php else: ?>
                        <span style="background:#fff3cd;padding:4px 12px;border-radius:20px;font-size:13px;color:#856404;">⏳ در جریان</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="text-align:left;direction:ltr;">
                    <div style="font-size:28px;font-weight:bold;color:var(--primary);"><?php echo number_format($deal->amount); ?></div>
                    <div style="font-size:12px;color:var(--gray-500);">تومان</div>
                </div>
            </div>

            <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">

            <div class="row g-3">
                <div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">👤 مسئول</small><strong><?php echo htmlspecialchars($deal->assigned_name ?? 'تعیین نشده'); ?></strong></div>
                <div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">📅 تاریخ ایجاد</small><strong><?php echo date('Y/m/d', strtotime($deal->created_at)); ?></strong></div>
                <div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">🎯 نحوه آشنایی</small><strong><?php echo htmlspecialchars($deal->source ?? '-'); ?></strong></div>
                <div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">👤 ایجاد کننده</small><strong><?php echo htmlspecialchars($deal->creator_name ?? '-'); ?></strong></div>
                <?php if ($deal->expected_close_date): ?>
                <div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">📅 پیش‌بینی</small><strong><?php echo date('Y/m/d', strtotime($deal->expected_close_date)); ?></strong></div>
                <?php endif; ?>
            </div>

            <?php if ($deal->description): ?>
            <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">
            <div>
                <small style="color:var(--gray-500);display:block;margin-bottom:8px;">📝 توضیحات</small>
                <p style="margin:0;line-height:1.8;">
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
        <div class="card" style="padding:24px;margin-bottom:16px;">
            <h5 style="margin:0 0 16px 0;font-weight:bold;">👤 اطلاعات مخاطب</h5>
            <div class="row g-3">
                <div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">نام</small><strong><?php echo htmlspecialchars($deal->contact_name); ?></strong></div>
                <div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">📞 تلفن</small><strong><?php echo htmlspecialchars($deal->contact_phone ?? '-'); ?></strong></div>
                <div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">📧 ایمیل</small><strong><?php echo htmlspecialchars($deal->contact_email ?? '-'); ?></strong></div>
                <?php if ($deal->national_code): ?><div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">کد ملی</small><strong><?php echo htmlspecialchars($deal->national_code); ?></strong></div><?php endif; ?>
                <?php if ($deal->passport_number): ?><div class="col-md-4"><small style="color:var(--gray-500);display:block;margin-bottom:2px;">🛂 پاسپورت</small><strong><?php echo htmlspecialchars($deal->passport_number); ?></strong></div><?php endif; ?>
            </div>
            <div style="margin-top:16px;display:flex;gap:8px;">
                <button class="btn btn-sm btn-success" onclick="openModal('smsModal')">✉️ ارسال پیامک</button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Activities -->
        <div class="card" style="padding:24px;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h5 style="margin:0;font-weight:bold;">📅 فعالیت‌ها</h5>
                <button class="btn btn-sm btn-primary" onclick="openModal('activityModal')">➕ ثبت فعالیت</button>
            </div>
            <?php if (empty($activities)): ?>
            <div style="text-align:center;padding:40px 20px;color:var(--gray-400);">
                <div style="font-size:40px;margin-bottom:12px;">📋</div>
                <p>هیچ فعالیتی ثبت نشده است.</p>
            </div>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($activities as $act): ?>
                <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;background:var(--gray-50);border-radius:12px;border-right:3px solid <?php echo $act->is_done ? '#28a745' : '#ffc107'; ?>;">
                    <div style="width:36px;height:36px;background:<?php echo $act->type == 'call' ? '#e3f2fd' : ($act->type == 'meeting' ? '#fce4ec' : ($act->type == 'sms' ? '#e8f5e9' : '#fff3e0')); ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <?php echo $act->type == 'call' ? '📞' : ($act->type == 'meeting' ? '🤝' : ($act->type == 'sms' ? '✉️' : ($act->type == 'email' ? '📧' : '📝'))); ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <strong style="font-size:14px;"><?php echo htmlspecialchars($act->subject ?? ($act->type == 'call' ? 'تماس تلفنی' : ($act->type == 'meeting' ? 'جلسه' : ($act->type == 'sms' ? 'پیامک' : ($act->type == 'email' ? 'ایمیل' : 'یادداشت'))))); ?></strong>
                        <br><small style="color:var(--gray-400);font-size:12px;"><?php echo htmlspecialchars($act->user_name ?? ''); ?> | <?php echo date('Y/m/d H:i', strtotime($act->created_at)); ?></small>
                        <?php if ($act->description): ?><p style="margin:4px 0 0 0;font-size:13px;color:var(--gray-600);"><?php echo nl2br(htmlspecialchars($act->description)); ?></p><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:14px;">⚡ خلاصه معامله</h5>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--gray-50);border-radius:8px;">
                    <span style="font-size:13px;color:var(--gray-600);">مبلغ</span>
                    <strong style="font-size:16px;color:var(--primary);"><?php echo number_format($deal->amount); ?> تومان</strong>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--gray-50);border-radius:8px;">
                    <span style="font-size:13px;color:var(--gray-600);">مرحله</span>
                    <span class="badge-stage" style="background:<?php echo $deal->stage_color; ?>20;color:<?php echo $deal->stage_color; ?>;font-size:12px;"><?php echo htmlspecialchars($deal->stage_name); ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--gray-50);border-radius:8px;">
                    <span style="font-size:13px;color:var(--gray-600);">وضعیت</span>
                    <?php if ($deal->is_won): ?><span style="background:#d4edda;padding:2px 8px;border-radius:12px;font-size:12px;color:#155724;">✅ موفق</span>
                    <?php elseif ($deal->is_lost): ?><span style="background:#f8d7da;padding:2px 8px;border-radius:12px;font-size:12px;color:#721c24;">❌ ناموفق</span>
                    <?php else: ?><span style="background:#fff3cd;padding:2px 8px;border-radius:12px;font-size:12px;color:#856404;">⏳ در جریان</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:14px;">🔧 عملیات</h5>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <button class="btn btn-success" style="width:100%;" onclick="openModal('smsModal')">✉️ ارسال پیامک به مخاطب</button>
                <a href="<?php echo $config['url']; ?>/payment/create/<?php echo $deal->id; ?>" class="btn btn-primary" style="width:100%;">💳 ایجاد لینک پرداخت</a>
                <a href="<?php echo $config['url']; ?>/deals/edit/<?php echo $deal->id; ?>" class="btn btn-secondary" style="width:100%;">✏️ ویرایش معامله</a>
            </div>
        </div>

        <!-- Payments -->
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:14px;">💳 پرداخت‌ها</h5>
            <?php if (empty($payments)): ?>
            <p style="color:var(--gray-400);font-size:13px;text-align:center;padding:16px;">هیچ پرداختی ثبت نشده است.</p>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($payments as $p): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:var(--gray-50);border-radius:8px;">
                    <div>
                        <strong style="font-size:13px;"><?php echo number_format($p->amount); ?> تومان</strong>
                        <br><small style="color:var(--gray-400);font-size:11px;"><?php echo date('Y/m/d', strtotime($p->created_at)); ?></small>
                    </div>
                    <span style="padding:3px 10px;border-radius:12px;font-size:11px;font-weight:bold;
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
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:14px;">✉️ آخرین پیامک‌ها</h5>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach (array_slice($smsHistory, 0, 5) as $sms): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--gray-50);border-radius:8px;">
                    <div>
                        <strong style="font-size:13px;"><?php echo htmlspecialchars($sms->recipient); ?></strong>
                        <br><small style="color:var(--gray-400);font-size:11px;"><?php echo date('Y/m/d', strtotime($sms->created_at)); ?></small>
                    </div>
                    <span style="padding:2px 8px;border-radius:12px;font-size:11px;<?php echo $sms->status == 'sent' ? 'background:#d4edda;color:#155724;' : 'background:#f8d7da;color:#721c24;'; ?>">
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
</script>