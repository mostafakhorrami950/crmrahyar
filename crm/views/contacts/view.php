<?php $config = $GLOBALS['app_config']; ?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-sm btn-secondary">← لیست مخاطبین</a>
        <h5 style="margin:0;">👤 <?php echo htmlspecialchars($contact->full_name); ?></h5>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $contact->id; ?>" class="btn btn-sm btn-secondary">✏️ ویرایش</a>
        <a href="<?php echo $config['url']; ?>/deals?search=<?php echo urlencode($contact->phone ?? $contact->full_name); ?>" class="btn btn-sm btn-secondary">🔍 جستجوی معاملات</a>
        <form method="POST" action="<?php echo $config['url']; ?>/contacts/delete/<?php echo $contact->id; ?>" style="display:inline;" onsubmit="return confirm('آیا از حذف مطمئنید؟')">
            <button type="submit" class="btn btn-sm btn-danger">🗑️ حذف</button>
        </form>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
    
    <!-- ===== LEFT COLUMN ===== -->
    <div>
        <!-- Profile Card -->
        <div class="card" style="padding:24px;border-radius:16px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
                <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:28px;color:#fff;font-weight:800;flex-shrink:0;">
                    <?php echo mb_substr($contact->full_name, 0, 1); ?>
                </div>
                <div>
                    <h3 style="margin:0 0 4px;font-size:20px;font-weight:800;"><?php echo htmlspecialchars($contact->full_name); ?></h3>
                    <?php if (!empty($contact->category_name)): ?>
                    <span style="background:<?php echo htmlspecialchars($contact->category_color ?? '#6B7280'); ?>;color:white;padding:3px 12px;border-radius:12px;font-size:12px;font-weight:600;">📂 <?php echo htmlspecialchars($contact->category_name); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <?php if ($contact->phone): ?>
                <div style="background:var(--gray-50);padding:10px 12px;border-radius:10px;">
                    <div style="font-size:11px;color:var(--gray-400);">📞 موبایل</div>
                    <strong dir="ltr" style="font-size:14px;"><?php echo htmlspecialchars($contact->phone); ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($contact->company_phone): ?>
                <div style="background:var(--gray-50);padding:10px 12px;border-radius:10px;">
                    <div style="font-size:11px;color:var(--gray-400);">🏢 تلفن شرکت</div>
                    <strong dir="ltr" style="font-size:14px;"><?php echo htmlspecialchars($contact->company_phone); ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($contact->email): ?>
                <div style="background:var(--gray-50);padding:10px 12px;border-radius:10px;">
                    <div style="font-size:11px;color:var(--gray-400);">✉️ ایمیل</div>
                    <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->email); ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($contact->company): ?>
                <div style="background:var(--gray-50);padding:10px 12px;border-radius:10px;">
                    <div style="font-size:11px;color:var(--gray-400);">🏢 شرکت</div>
                    <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->company); ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($contact->national_code): ?>
                <div style="background:var(--gray-50);padding:10px 12px;border-radius:10px;">
                    <div style="font-size:11px;color:var(--gray-400);">🪪 کد ملی</div>
                    <strong dir="ltr" style="font-size:13px;"><?php echo htmlspecialchars($contact->national_code); ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($contact->passport_number): ?>
                <div style="background:var(--gray-50);padding:10px 12px;border-radius:10px;">
                    <div style="font-size:11px;color:var(--gray-400);">🛂 پاسپورت</div>
                    <strong dir="ltr" style="font-size:13px;"><?php echo htmlspecialchars($contact->passport_number); ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($contact->source): ?>
                <div style="background:var(--gray-50);padding:10px 12px;border-radius:10px;">
                    <div style="font-size:11px;color:var(--gray-400);">🎯 نحوه آشنایی</div>
                    <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->source); ?></strong>
                </div>
                <?php endif; ?>
                <div style="background:var(--gray-50);padding:10px 12px;border-radius:10px;">
                    <div style="font-size:11px;color:var(--gray-400);">📅 تاریخ ثبت</div>
                    <strong style="font-size:13px;"><?php echo \Core\JDate::displayDate($contact->created_at); ?></strong>
                </div>
            </div>
            
            <?php if ($contact->address): ?>
            <div style="margin-top:12px;background:var(--gray-50);padding:10px 12px;border-radius:10px;">
                <div style="font-size:11px;color:var(--gray-400);">📍 آدرس</div>
                <span style="font-size:13px;"><?php echo htmlspecialchars($contact->address); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($contact->notes): ?>
            <div style="margin-top:8px;background:#fff8e1;padding:10px 12px;border-radius:10px;border-right:3px solid #ffc107;">
                <div style="font-size:11px;color:#856404;">📝 یادداشت</div>
                <p style="margin:4px 0 0;font-size:13px;color:#5a4a00;line-height:1.7;"><?php echo nl2br(htmlspecialchars($contact->notes)); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Deals -->
        <div class="card" style="padding:0;border-radius:16px;overflow:hidden;margin-bottom:16px;">
            <div style="padding:14px 20px;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;">
                <h5 style="margin:0;font-size:15px;font-weight:700;">💼 معاملات (<?php echo count($deals); ?>)</h5>
                <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-sm btn-primary">➕ جدید</a>
            </div>
            <?php if (empty($deals)): ?>
            <div style="text-align:center;padding:30px;color:var(--gray-400);">معامله‌ای ثبت نشده</div>
            <?php else: ?>
            <div style="max-height:300px;overflow-y:auto;">
                <?php foreach ($deals as $d): ?>
                <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $d->id; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 20px;border-bottom:1px solid var(--gray-100);text-decoration:none;color:inherit;">
                    <div style="flex:1;min-width:0;">
                        <strong style="font-size:13px;"><?php echo htmlspecialchars(mb_substr($d->title, 0, 30)); ?></strong>
                        <div style="display:flex;gap:6px;margin-top:4px;">
                            <span style="background:<?php echo $d->stage_color; ?>20;color:<?php echo $d->stage_color; ?>;padding:2px 8px;border-radius:8px;font-size:10px;font-weight:600;"><?php echo htmlspecialchars($d->stage_name); ?></span>
                            <?php if ($d->amount): ?><span style="font-size:11px;color:var(--gray-500);"><?php echo number_format($d->amount); ?> ت</span><?php endif; ?>
                        </div>
                    </div>
                    <?php if ($d->is_won): ?><span style="font-size:18px;">✅</span>
                    <?php elseif ($d->is_lost): ?><span style="font-size:18px;">❌</span>
                    <?php else: ?><span style="font-size:18px;">⏳</span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Activities -->
        <?php if (!empty($activities)): ?>
        <div class="card" style="padding:0;border-radius:16px;overflow:hidden;">
            <div style="padding:14px 20px;border-bottom:1px solid var(--gray-200);">
                <h5 style="margin:0;font-size:15px;font-weight:700;">📅 فعالیت‌های اخیر</h5>
            </div>
            <div style="max-height:250px;overflow-y:auto;">
                <?php foreach ($activities as $act): ?>
                <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 20px;border-bottom:1px solid var(--gray-100);">
                    <span style="font-size:18px;"><?php echo $act->type == 'call' ? '📞' : ($act->type == 'meeting' ? '🤝' : '📝'); ?></span>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;"><?php echo htmlspecialchars($act->subject ?? '-'); ?></div>
                        <div style="font-size:11px;color:var(--gray-400);margin-top:2px;">
                            <?php echo \Core\JDate::displayDate($act->created_at); ?> • <?php echo htmlspecialchars($act->deal_title ?? ''); ?>
                        </div>
                    </div>
                    <?php if ($act->is_done): ?><span style="font-size:14px;">✅</span><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== RIGHT COLUMN ===== -->
    <div>
        <!-- Stats -->
        <div class="card" style="padding:20px;border-radius:16px;margin-bottom:16px;">
            <h5 style="margin:0 0 12px;font-size:14px;font-weight:700;">⚡ خلاصه آمار</h5>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;padding:16px;border-radius:12px;text-align:center;">
                    <div style="font-size:28px;font-weight:800;"><?php echo $contact->deals_count; ?></div>
                    <div style="font-size:12px;opacity:0.9;">معاملات</div>
                </div>
                <div style="background:linear-gradient(135deg,#10B981,#059669);color:white;padding:16px;border-radius:12px;text-align:center;">
                    <div style="font-size:20px;font-weight:800;"><?php echo number_format($contact->total_purchases ?? 0); ?></div>
                    <div style="font-size:12px;opacity:0.9;">خرید موفق (ت)</div>
                </div>
                <div style="background:linear-gradient(135deg,#3B82F6,#2563EB);color:white;padding:16px;border-radius:12px;text-align:center;">
                    <div style="font-size:20px;font-weight:800;"><?php echo number_format($contact->total_deals_amount ?? 0); ?></div>
                    <div style="font-size:12px;opacity:0.9;">مجموع معاملات (ت)</div>
                </div>
                <div style="background:linear-gradient(135deg,#F59E0B,#D97706);color:white;padding:16px;border-radius:12px;text-align:center;">
                    <div style="font-size:14px;font-weight:800;"><?php echo \Core\JDate::displayDate($contact->created_at); ?></div>
                    <div style="font-size:12px;opacity:0.9;">عضو از</div>
                </div>
            </div>
        </div>

        <!-- Payments -->
        <div class="card" style="padding:0;border-radius:16px;overflow:hidden;margin-bottom:16px;">
            <div style="padding:14px 20px;border-bottom:1px solid var(--gray-200);">
                <h5 style="margin:0;font-size:14px;font-weight:700;">💳 پرداخت‌ها (<?php echo count($payments); ?>)</h5>
            </div>
            <?php if (empty($payments)): ?>
            <div style="text-align:center;padding:24px;color:var(--gray-400);font-size:13px;">پرداختی ثبت نشده</div>
            <?php else: ?>
            <div style="max-height:200px;overflow-y:auto;">
                <?php foreach ($payments as $p): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 16px;border-bottom:1px solid var(--gray-100);">
                    <div>
                        <strong style="font-size:13px;"><?php echo number_format($p->amount); ?> ت</strong>
                        <div style="font-size:11px;color:var(--gray-400);"><?php echo htmlspecialchars($p->deal_title ?? ''); ?> • <?php echo \Core\JDate::displayDate($p->created_at); ?></div>
                    </div>
                    <span style="padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;<?php echo $p->status == 'success' ? 'background:#d1fae5;color:#065f46;' : ($p->status == 'pending' ? 'background:#fef3c7;color:#92400e;' : 'background:#fee2e2;color:#991b1b;'); ?>">
                        <?php echo $p->status == 'success' ? '✅' : ($p->status == 'pending' ? '⏳' : '❌'); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="padding:16px;border-radius:16px;">
            <h5 style="margin:0 0 10px;font-size:14px;font-weight:700;">🔗 اقدامات سریع</h5>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-primary" style="text-align:center;padding:10px;">➕ ایجاد معامله</a>
                <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $contact->id; ?>" class="btn btn-secondary" style="text-align:center;padding:10px;">✏️ ویرایش مخاطب</a>
            </div>
        </div>
    </div>
</div>