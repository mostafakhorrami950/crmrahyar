<div class="page-header">
    <h5>💳 ایجاد لینک پرداخت</h5>
    <a href="<?php echo $config['url']; ?>/deals/view/<?php echo $deal->id; ?>" class="btn btn-secondary">← بازگشت به معامله</a>
</div>

<div class="row" style="margin-top:16px;">
    <div class="col-md-6">
        <!-- Create Payment Form -->
        <div class="card" style="padding:24px;">
            <h5 style="margin:0 0 20px 0;font-weight:bold;">🔗 ایجاد لینک اختصاصی پرداخت</h5>
            
            <!-- Deal Info -->
            <div style="display:flex;gap:16px;padding:16px;background:var(--gray-50);border-radius:12px;margin-bottom:20px;">
                <div style="width:48px;height:48px;background:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;color:white;flex-shrink:0;">💰</div>
                <div>
                    <strong style="font-size:16px;"><?php echo htmlspecialchars($deal->title); ?></strong>
                    <br><small style="color:var(--gray-500);"><?php echo htmlspecialchars($deal->contact_name ?? ''); ?> | <?php echo htmlspecialchars($deal->contact_phone ?? ''); ?></small>
                    <br><strong style="color:var(--primary);font-size:15px;"><?php echo number_format($deal->amount); ?> تومان</strong>
                </div>
            </div>

            <div class="ajax-error alert alert-danger" style="display:none;"></div>
            <div class="ajax-success alert alert-success" style="display:none;"></div>
            <form method="POST" action="<?php echo $config['url']; ?>/payment/request" data-ajax="true">
                <input type="hidden" name="deal_id" value="<?php echo $deal->id; ?>">
                
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">مبلغ (تومان) *</label>
                    <input type="number" name="amount" class="form-input" value="<?php echo $deal->amount ?: 0; ?>" required min="1000" step="1000" style="font-size:18px;font-weight:bold;direction:ltr;text-align:left;">
                    <small style="color:var(--gray-400);font-size:11px;">مبلغ پیش‌فرض: <?php echo number_format($deal->amount); ?> تومان</small>
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">📱 شماره موبایل پرداخت کننده (برای نمایش کارت‌ها)</label>
                    <input type="text" name="mobile" class="form-input" placeholder="09120000000" value="<?php echo htmlspecialchars($deal->contact_phone ?? ''); ?>" style="direction:ltr;text-align:left;">
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">📝 توضیحات (نمایش در صفحه پرداخت)</label>
                    <textarea name="description" class="form-textarea" rows="2" placeholder="توضیحات این پرداخت..."><?php echo htmlspecialchars($deal->title); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:16px;font-weight:bold;" id="submitPayBtn">
                    💳 اتصال به درگاه پرداخت زیبال
                </button>
                <p style="text-align:center;color:var(--gray-400);font-size:12px;margin-top:8px;">
                    🔒 پرداخت امن توسط درگاه زیبال
                </p>
            </form>

            <!-- Public Payment Link Section (hidden by default) -->
            <div id="publicLinkSection" style="display:none;margin-top:20px;padding:16px;background:#d4edda;border-radius:12px;border:2px dashed #28a745;">
                <h6 style="margin:0 0 10px 0;color:#155724;">✅ لینک پرداخت اختصاصی مشتری</h6>
                <p style="font-size:13px;color:#155724;margin:0 0 10px 0;">این لینک را برای مشتری ارسال کنید. مشتری با کلیک روی آن می‌تواند مستقیماً پرداخت کند:</p>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="publicPayLink" style="flex:1;padding:10px;border:1px solid #28a745;border-radius:8px;font-size:13px;direction:ltr;text-align:left;background:#fff;" readonly>
                    <button type="button" class="btn btn-success" onclick="copyPublicLink()" style="white-space:nowrap;">📋 کپی</button>
                </div>
                <p style="font-size:12px;color:#155724;margin:10px 0 0 0;">
                    همچنین می‌توانید مستقیماً به درگاه پرداخت متصل شوید:
                    <button type="button" class="btn btn-sm btn-primary" id="directPayBtn" style="margin-top:6px;">💳 پرداخت مستقیم</button>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <!-- Previous Payments -->
        <div class="card" style="padding:24px;">
            <h5 style="margin:0 0 16px 0;font-weight:bold;">📋 تاریخچه پرداخت‌های این معامله</h5>
            <?php 
            $db = \Core\Database::getInstance();
            $payments = $db->fetchAll("SELECT * FROM payments WHERE deal_id = :id ORDER BY created_at DESC", [':id' => $deal->id]);
            ?>
            <?php if (empty($payments)): ?>
            <div style="text-align:center;padding:40px 20px;color:var(--gray-400);">
                <div style="font-size:48px;margin-bottom:12px;">💳</div>
                <p>هیچ پرداختی برای این معامله ثبت نشده است.</p>
            </div>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($payments as $p): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:var(--gray-50);border-radius:10px;">
                    <div>
                        <strong style="font-size:14px;"><?php echo number_format($p->amount); ?> تومان</strong>
                        <br><small style="color:var(--gray-400);font-size:11px;"><?php echo \Core\JDate::displayDateTime($p->created_at); ?></small>
                        <?php if ($p->track_id): ?><br><small style="color:var(--gray-400);font-size:11px;">کد: <?php echo $p->track_id; ?></small><?php endif; ?>
                    </div>
                    <div style="text-align:left;">
                        <span style="padding:3px 12px;border-radius:12px;font-size:11px;font-weight:bold;display:inline-block;
                            <?php echo $p->status == 'success' ? 'background:#d4edda;color:#155724;' : ($p->status == 'pending' ? 'background:#fff3cd;color:#856404;' : 'background:#f8d7da;color:#721c24;'); ?>">
                            <?php echo $p->status == 'success' ? '✅ موفق' : ($p->status == 'pending' ? '⏳ در انتظار' : '❌ ناموفق'); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>