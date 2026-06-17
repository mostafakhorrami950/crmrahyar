<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-sm btn-secondary">← بازگشت به لیست</a>
        <h5 style="margin:0;">👤 جزییات مخاطب</h5>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $contact->id; ?>" class="btn btn-sm btn-secondary">✏️ ویرایش مخاطب</a>
        <form method="POST" action="<?php echo $config['url']; ?>/contacts/delete/<?php echo $contact->id; ?>" style="display:inline;" onsubmit="return confirm('آیا از حذف «<?php echo htmlspecialchars($contact->full_name); ?>» اطمینان دارید؟')">
            <button type="submit" class="btn btn-sm btn-danger">🗑️ حذف</button>
        </form>
    </div>
</div>

<div class="row" style="margin-top:16px;">
    <!-- === MAIN CONTENT === -->
    <div class="col-md-7">
        <!-- Contact Profile Card -->
        <div class="card" style="padding:24px;border-radius:16px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                <!-- Avatar -->
                <div style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:36px;color:#fff;font-weight:800;flex-shrink:0;">
                    <?php echo mb_substr($contact->full_name, 0, 1); ?>
                </div>
                <div style="flex:1;min-width:200px;">
                    <h3 style="margin:0 0 6px 0;font-size:24px;font-weight:800;"><?php echo htmlspecialchars($contact->full_name); ?></h3>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:14px;color:var(--gray-500);">
                        <?php if ($contact->phone): ?>
                        <span>📞 <strong dir="ltr" style="display:inline-block;"><?php echo htmlspecialchars($contact->phone); ?></strong></span>
                        <?php endif; ?>
                        <?php if ($contact->company_phone): ?>
                        <span>🏢 <strong dir="ltr" style="display:inline-block;"><?php echo htmlspecialchars($contact->company_phone); ?></strong></span>
                        <?php endif; ?>
                        <?php if ($contact->email): ?>
                        <span>✉️ <?php echo htmlspecialchars($contact->email); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-200);">
            
            <div class="row g-3">
                <?php if ($contact->national_code): ?>
                <div class="col-6 col-md-4">
                    <div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;">
                        <small style="color:var(--gray-500);display:block;font-size:11px;">🪪 کد ملی</small>
                        <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->national_code); ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($contact->passport_number): ?>
                <div class="col-6 col-md-4">
                    <div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;">
                        <small style="color:var(--gray-500);display:block;font-size:11px;">🛂 پاسپورت</small>
                        <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->passport_number); ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($contact->company): ?>
                <div class="col-6 col-md-4">
                    <div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;">
                        <small style="color:var(--gray-500);display:block;font-size:11px;">🏢 شرکت</small>
                        <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->company); ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-6 col-md-4">
                    <div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;">
                        <small style="color:var(--gray-500);display:block;font-size:11px;">📅 تاریخ ثبت</small>
                        <strong style="font-size:13px;"><?php echo \Core\JDate::displayDate($contact->created_at); ?></strong>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;">
                        <small style="color:var(--gray-500);display:block;font-size:11px;">👤 ثبت توسط</small>
                        <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->created_by_name ?? '-'); ?></strong>
                    </div>
                </div>
                <?php if ($contact->source): ?>
                <div class="col-6 col-md-4">
                    <div style="background:var(--gray-50);padding:10px 14px;border-radius:10px;">
                        <small style="color:var(--gray-500);display:block;font-size:11px;">🎯 نحوه آشنایی</small>
                        <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->source); ?></strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($contact->address): ?>
            <div style="margin-top:16px;padding:14px;background:var(--gray-50);border-radius:10px;">
                <small style="color:var(--gray-500);display:block;font-size:11px;">📍 آدرس</small>
                <span style="font-size:14px;"><?php echo htmlspecialchars($contact->address); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($contact->notes): ?>
            <div style="margin-top:12px;padding:14px;background:#fff8e1;border-radius:10px;border-right:3px solid #ffc107;">
                <small style="color:#856404;display:block;font-size:11px;">📝 یادداشت</small>
                <p style="margin:4px 0 0;font-size:13px;color:#5a4a00;line-height:1.7;"><?php echo nl2br(htmlspecialchars($contact->notes)); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Deals Table -->
        <div class="card" style="padding:0;border-radius:16px;margin-bottom:16px;overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;">
                <h5 style="margin:0;font-weight:bold;font-size:15px;">💼 معاملات این مخاطب</h5>
                <span style="background:var(--primary);color:#fff;padding:3px 12px;border-radius:12px;font-size:12px;font-weight:600;"><?php echo $contact->deals_count; ?> مورد</span>
            </div>
            <?php if (empty($deals)): ?>
            <div style="text-align:center;padding:40px 20px;color:var(--gray-400);">
                <div style="font-size:40px;margin-bottom:8px;">💼</div>
                <p>هیچ معامله‌ای برای این مخاطب ثبت نشده است.</p>
                <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-sm btn-primary">➕ ایجاد معامله جدید</a>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>مرحله</th>
                            <th>مبلغ</th>
                            <th>تاریخ</th>
                            <th>وضعیت</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deals as $d): ?>
                        <tr>
                            <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $d->id; ?>" style="color:var(--primary);font-weight:500;"><?php echo htmlspecialchars(mb_substr($d->title, 0, 25)); ?></a></td>
                            <td><span style="background:<?php echo $d->stage_color; ?>20;color:<?php echo $d->stage_color; ?>;padding:3px 10px;border-radius:10px;font-size:12px;font-weight:600;"><?php echo htmlspecialchars($d->stage_name); ?></span></td>
                            <td class="amount-value"><?php echo number_format($d->amount); ?></td>
                            <td style="font-size:12px;color:var(--gray-500);"><?php echo \Core\JDate::displayDate($d->created_at); ?></td>
                            <td>
                                <?php if ($d->is_won): ?><span class="badge badge-success">✅</span>
                                <?php elseif ($d->is_lost): ?><span class="badge badge-danger">❌</span>
                                <?php else: ?><span class="badge badge-warning">⏳</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="<?php echo $config['url']; ?>/deals/view/<?php echo $d->id; ?>" class="btn btn-sm btn-primary">👁️</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Activities -->
        <?php if (!empty($activities)): ?>
        <div class="card" style="padding:20px;border-radius:16px;margin-bottom:16px;">
            <h5 style="margin:0 0 16px 0;font-weight:bold;font-size:15px;">📅 آخرین فعالیت‌ها</h5>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($activities as $act): ?>
                <div style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:var(--gray-50);border-radius:10px;border-right:3px solid <?php echo $act->is_done ? '#28a745' : '#ffc107'; ?>;">
                    <div style="width:32px;height:32px;background:<?php echo $act->type == 'call' ? '#e3f2fd' : ($act->type == 'meeting' ? '#fce4ec' : '#fff3e0'); ?>;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">
                        <?php echo $act->type == 'call' ? '📞' : ($act->type == 'meeting' ? '🤝' : '📝'); ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                            <strong style="font-size:13px;"><?php echo htmlspecialchars($act->subject ?? '-'); ?></strong>
                            <small style="color:var(--gray-400);font-size:11px;"><?php echo \Core\JDate::displayDate($act->created_at); ?></small>
                        </div>
                        <small style="color:var(--gray-500);font-size:12px;">
                            در معامله: <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $act->deal_id; ?>" style="color:var(--primary);"><?php echo htmlspecialchars($act->deal_title ?? ''); ?></a>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- === SIDEBAR === -->
    <div class="col-md-5">
        <!-- Stats Summary -->
        <div class="card" style="padding:20px;border-radius:16px;margin-bottom:16px;">
            <h5 style="margin:0 0 14px 0;font-weight:bold;font-size:14px;">⚡ خلاصه آماری</h5>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div style="background:var(--gray-50);padding:14px;border-radius:12px;text-align:center;">
                    <div style="font-size:24px;font-weight:800;color:var(--primary);"><?php echo $contact->deals_count; ?></div>
                    <div style="font-size:12px;color:var(--gray-500);">تعداد معاملات</div>
                </div>
                <div style="background:var(--gray-50);padding:14px;border-radius:12px;text-align:center;">
                    <div style="font-size:20px;font-weight:800;color:#28a745;"><?php echo number_format($contact->total_purchases ?? 0); ?></div>
                    <div style="font-size:12px;color:var(--gray-500);">موفق (تومان)</div>
                </div>
                <div style="background:var(--gray-50);padding:14px;border-radius:12px;text-align:center;">
                    <div style="font-size:20px;font-weight:800;color:var(--gray-700);"><?php echo number_format($contact->total_deals_amount ?? 0); ?></div>
                    <div style="font-size:12px;color:var(--gray-500);">مجموع معاملات</div>
                </div>
                <div style="background:var(--gray-50);padding:14px;border-radius:12px;text-align:center;">
                    <div style="font-size:20px;font-weight:800;color:#6f42c1;"><?php echo \Core\JDate::displayDate($contact->created_at); ?></div>
                    <div style="font-size:12px;color:var(--gray-500);">عضو از</div>
                </div>
            </div>
        </div>

        <!-- Payments -->
        <div class="card" style="padding:20px;border-radius:16px;margin-bottom:16px;">
            <h5 style="margin:0 0 14px 0;font-weight:bold;font-size:14px;">💳 تراکنش‌های پرداخت</h5>
            <?php if (empty($payments)): ?>
            <p style="color:var(--gray-400);font-size:13px;text-align:center;padding:16px;">هیچ تراکنشی ثبت نشده است.</p>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <?php foreach ($payments as $p): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:var(--gray-50);border-radius:10px;flex-wrap:wrap;gap:4px;">
                    <div>
                        <strong style="font-size:13px;"><?php echo number_format($p->amount); ?> تومان</strong>
                        <br><small style="color:var(--gray-400);font-size:11px;"><?php echo htmlspecialchars($p->deal_title ?? '-'); ?></small>
                        <br><small style="color:var(--gray-400);font-size:11px;"><?php echo \Core\JDate::displayDate($p->created_at); ?></small>
                    </div>
                    <span style="padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;
                        <?php echo $p->status == 'success' ? 'background:#d4edda;color:#155724;' : ($p->status == 'pending' ? 'background:#fff3cd;color:#856404;' : 'background:#f8d7da;color:#721c24;'); ?>">
                        <?php echo $p->status == 'success' ? '✅ موفق' : ($p->status == 'pending' ? '⏳ در انتظار' : '❌ ناموفق'); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="padding:20px;border-radius:16px;">
            <h5 style="margin:0 0 14px 0;font-weight:bold;font-size:14px;">🔗 اقدامات سریع</h5>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-primary" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;">
                    ➕ ایجاد معامله برای این مخاطب
                </a>
                <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $contact->id; ?>" class="btn btn-secondary" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;">
                    ✏️ ویرایش اطلاعات مخاطب
                </a>
                <a href="<?php echo $config['url']; ?>/deals?search=<?php echo urlencode($contact->phone ?? $contact->full_name); ?>" class="btn btn-secondary" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;">
                    🔍 جستجوی معاملات این مخاطب
                </a>
            </div>
        </div>
    </div>
</div>