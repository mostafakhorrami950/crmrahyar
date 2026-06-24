<?php $config = $GLOBALS['app_config']; ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $config['url']; ?>/contacts" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i></a>
        <h5 class="fw-bold mb-0"><i class="bi bi-person me-2"></i><?php echo htmlspecialchars($contact->full_name); ?></h5>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $contact->id; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>ویرایش</a>
        <a href="<?php echo $config['url']; ?>/deals?search=<?php echo urlencode($contact->phone ?? $contact->full_name); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search me-1"></i>جستجوی معاملات</a>
        <?php if (\Core\Auth::hasPermission('contacts.delete')): ?>
        <form method="POST" action="<?php echo $config['url']; ?>/contacts/delete/<?php echo $contact->id; ?>" class="d-inline" onsubmit="return confirm('آیا از حذف مطمئنید؟')">
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>حذف</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <!-- LEFT COLUMN -->
    <div class="col-12 col-lg-7">
        <!-- Profile Card -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:28px;color:#fff;font-weight:800;flex-shrink:0;">
                        <?php echo mb_substr($contact->full_name, 0, 1); ?>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($contact->full_name); ?></h5>
                        <?php if (!empty($contact->category_name)): ?>
                        <span class="badge" style="background:<?php echo htmlspecialchars($contact->category_color ?? '#6B7280'); ?>;color:white;">
                            <i class="bi bi-folder me-1"></i><?php echo htmlspecialchars($contact->category_name); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row g-2">
                    <?php if ($contact->phone): ?>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted" style="font-size:11px;"><i class="bi bi-phone me-1"></i>موبایل</div>
                            <strong dir="ltr" style="font-size:14px;"><?php echo htmlspecialchars($contact->phone); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($contact->company_phone): ?>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted" style="font-size:11px;"><i class="bi bi-building me-1"></i>تلفن شرکت</div>
                            <strong dir="ltr" style="font-size:14px;"><?php echo htmlspecialchars($contact->company_phone); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($contact->email): ?>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted" style="font-size:11px;"><i class="bi bi-envelope me-1"></i>ایمیل</div>
                            <strong style="font-size:13px;" dir="ltr"><?php echo htmlspecialchars($contact->email); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($contact->company): ?>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted" style="font-size:11px;"><i class="bi bi-building me-1"></i>شرکت</div>
                            <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->company); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($contact->national_code): ?>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted" style="font-size:11px;"><i class="bi bi-card-heading me-1"></i>کد ملی</div>
                            <strong dir="ltr" style="font-size:13px;"><?php echo htmlspecialchars($contact->national_code); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($contact->passport_number): ?>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted" style="font-size:11px;"><i class="bi bi-pass me-1"></i>پاسپورت</div>
                            <strong dir="ltr" style="font-size:13px;"><?php echo htmlspecialchars($contact->passport_number); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($contact->source): ?>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted" style="font-size:11px;"><i class="bi bi-crosshair me-1"></i>نحوه آشنایی</div>
                            <strong style="font-size:13px;"><?php echo htmlspecialchars($contact->source); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted" style="font-size:11px;"><i class="bi bi-calendar me-1"></i>تاریخ ثبت</div>
                            <strong style="font-size:13px;"><?php echo \Core\JDate::displayDate($contact->created_at); ?></strong>
                        </div>
                    </div>
                </div>
                
                <?php if ($contact->address): ?>
                <div class="bg-light rounded p-2 mt-2">
                    <div class="text-muted" style="font-size:11px;"><i class="bi bi-geo-alt me-1"></i>آدرس</div>
                    <span style="font-size:13px;"><?php echo htmlspecialchars($contact->address); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($contact->notes): ?>
                <div class="rounded p-2 mt-2" style="background:#fff8e1;border-right:3px solid #ffc107;">
                    <div style="font-size:11px;color:#856404;"><i class="bi bi-journal-text me-1"></i>یادداشت</div>
                    <p class="mb-0 small" style="color:#5a4a00;line-height:1.7;"><?php echo nl2br(htmlspecialchars($contact->notes)); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Deals -->
        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                    <h6 class="fw-bold mb-0"><i class="bi bi-briefcase me-2"></i>معاملات (<?php echo count($deals); ?>)</h6>
                    <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                </div>
                <?php if (empty($deals)): ?>
                <div class="text-center text-muted py-4 small">معامله‌ای ثبت نشده</div>
                <?php else: ?>
                <div style="max-height:300px;overflow-y:auto;">
                    <?php foreach ($deals as $d): ?>
                    <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $d->id; ?>" class="d-flex align-items-center gap-2 p-3 border-bottom text-decoration-none text-dark">
                        <div class="flex-grow-1 min-width-0">
                            <strong class="small"><?php echo htmlspecialchars(mb_substr($d->title, 0, 30)); ?></strong>
                            <div class="d-flex gap-1 mt-1 flex-wrap">
                                <span class="badge" style="background:<?php echo $d->stage_color; ?>20;color:<?php echo $d->stage_color; ?>;font-size:10px;"><?php echo htmlspecialchars($d->stage_name); ?></span>
                                <?php if ($d->amount): ?><small class="text-muted"><?php echo number_format($d->amount); ?> ت</small><?php endif; ?>
                            </div>
                        </div>
                        <?php if ($d->is_won): ?><span class="fs-5">✅</span>
                        <?php elseif ($d->is_lost): ?><span class="fs-5">❌</span>
                        <?php else: ?><span class="fs-5">⏳</span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activities -->
        <?php if (!empty($activities)): ?>
        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2"></i>فعالیت‌های اخیر</h6>
                </div>
                <div style="max-height:250px;overflow-y:auto;">
                    <?php foreach ($activities as $act): ?>
                    <div class="d-flex align-items-start gap-2 p-3 border-bottom">
                        <span class="fs-5">
                            <?php if ($act->type == 'call'): ?><i class="bi bi-telephone text-success"></i>
                            <?php elseif ($act->type == 'meeting'): ?><i class="bi bi-people text-primary"></i>
                            <?php else: ?><i class="bi bi-pencil text-warning"></i>
                            <?php endif; ?>
                        </span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small"><?php echo htmlspecialchars($act->subject ?? '-'); ?></div>
                            <div class="text-muted" style="font-size:11px;">
                                <i class="bi bi-clock me-1"></i><?php echo \Core\JDate::displayDate($act->created_at); ?>
                                <?php if ($act->deal_title): ?> • <?php echo htmlspecialchars($act->deal_title); ?><?php endif; ?>
                            </div>
                        </div>
                        <?php if ($act->is_done): ?><i class="bi bi-check-circle-fill text-success"></i><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-12 col-lg-5">
        <!-- Stats -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-lightning me-2"></i>خلاصه آمار</h6>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="rounded p-3 text-center text-white" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                            <div class="fw-bold fs-4"><?php echo $contact->deals_count; ?></div>
                            <div style="font-size:12px;opacity:0.9;">معاملات</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded p-3 text-center text-white" style="background:linear-gradient(135deg,#10B981,#059669);">
                            <div class="fw-bold fs-5"><?php echo number_format($contact->total_purchases ?? 0); ?></div>
                            <div style="font-size:12px;opacity:0.9;">خرید موفق (ت)</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded p-3 text-center text-white" style="background:linear-gradient(135deg,#3B82F6,#2563EB);">
                            <div class="fw-bold fs-5"><?php echo number_format($contact->total_deals_amount ?? 0); ?></div>
                            <div style="font-size:12px;opacity:0.9;">مجموع معاملات (ت)</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded p-3 text-center text-white" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                            <div class="fw-bold"><?php echo \Core\JDate::displayDate($contact->created_at); ?></div>
                            <div style="font-size:12px;opacity:0.9;">عضو از</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments -->
        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                    <h6 class="fw-bold mb-0"><i class="bi bi-credit-card me-2"></i>پرداخت‌ها (<?php echo count($payments); ?>)</h6>
                </div>
                <?php if (empty($payments)): ?>
                <div class="text-center text-muted py-4 small">پرداختی ثبت نشده</div>
                <?php else: ?>
                <div style="max-height:200px;overflow-y:auto;">
                    <?php foreach ($payments as $p): ?>
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <div>
                            <strong class="small"><?php echo number_format($p->amount); ?> ت</strong>
                            <div class="text-muted" style="font-size:11px;"><?php echo htmlspecialchars($p->deal_title ?? ''); ?> • <?php echo \Core\JDate::displayDate($p->created_at); ?></div>
                        </div>
                        <span class="badge <?php echo $p->status == 'success' ? 'bg-success' : ($p->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger'); ?>" style="font-size:11px;">
                            <?php echo $p->status == 'success' ? '✅ موفق' : ($p->status == 'pending' ? '⏳ در انتظار' : '❌ ناموفق'); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge me-2"></i>اقدامات سریع</h6>
                <div class="d-grid gap-2">
                    <a href="<?php echo $config['url']; ?>/deals/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>ایجاد معامله</a>
                    <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $contact->id; ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>ویرایش مخاطب</a>
                </div>
            </div>
        </div>
    </div>
</div>