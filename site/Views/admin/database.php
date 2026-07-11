<?php
ob_start();
?>
<div class="container" style="max-width: 900px; margin: 0 auto; padding: 30px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 style="font-size: 22px; font-weight: 900;">🗄️ تعمیرات دیتابیس</h1>
        <a href="/admin" style="background: #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none;">بازگشت</a>
    </div>

    <?php if (!empty($message)): ?>
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
        <a href="?action=migrate" style="background: #4f46e5; color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none; transition: 0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            ▶️ اجرای مایگریشن
        </a>
        <a href="?action=rollback" style="background: #f59e0b; color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none; transition: 0.2s;" onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'">
            ↩️ بازگشت مایگریشن
        </a>
        <a href="?action=repair" style="background: #059669; color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none; transition: 0.2s;" onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
            🔧 تعمیر و بررسی جداول
        </a>
    </div>

    <!-- Results -->
    <?php if (!empty($results)): ?>
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
        <div style="background: #1e293b; color: #fff; padding: 10px 16px; font-weight: 700; font-size: 13px;">نتایج عملیات</div>
        <div style="padding: 12px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 8px; text-align: right; font-weight: 700;">جدول / مایگریشن</th>
                        <th style="padding: 8px; text-align: center; font-weight: 700;">وضعیت</th>
                        <th style="padding: 8px; text-align: right; font-weight: 700;">پیام</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 6px 8px;"><?php echo htmlspecialchars($r['table'] ?? $r['name'] ?? ''); ?></td>
                        <td style="padding: 6px 8px; text-align: center;">
                            <?php if (($r['status'] ?? '') === 'ok'): ?>
                            <span style="color: #059669; font-weight: 700;">✅ سالم</span>
                            <?php elseif (($r['status'] ?? '') === 'applied' || ($r['status'] ?? '') === 'created'): ?>
                            <span style="color: #4f46e5; font-weight: 700;">✅ اجرا شد</span>
                            <?php elseif (($r['status'] ?? '') === 'skipped'): ?>
                            <span style="color: #94a3b8; font-weight: 700;">⏭️ رد شد</span>
                            <?php elseif (($r['status'] ?? '') === 'rolled_back'): ?>
                            <span style="color: #f59e0b; font-weight: 700;">↩️ بازگشت</span>
                            <?php elseif (($r['status'] ?? '') === 'missing'): ?>
                            <span style="color: #dc2626; font-weight: 700;">❌ ناقص</span>
                            <?php elseif (($r['status'] ?? '') === 'error'): ?>
                            <span style="color: #dc2626; font-weight: 700;">❌ خطا</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 6px 8px; color: #64748b; font-size: 11px;"><?php echo htmlspecialchars($r['error'] ?? $r['message'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Migration History -->
    <?php if (!empty($applied)): ?>
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
        <div style="background: #1e293b; color: #fff; padding: 10px 16px; font-weight: 700; font-size: 13px;">تاریخچه مایگریشن‌ها</div>
        <div style="padding: 12px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 8px; text-align: right; font-weight: 700;">نام</th>
                        <th style="padding: 8px; text-align: center; font-weight: 700;">بچ</th>
                        <th style="padding: 8px; text-align: right; font-weight: 700;">تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applied as $a): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 6px 8px;"><?php echo htmlspecialchars($a->migration); ?></td>
                        <td style="padding: 6px 8px; text-align: center;"><?php echo $a->batch; ?></td>
                        <td style="padding: 6px 8px; color: #64748b;"><?php echo $a->applied_at; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$meta = $meta ?? ['title' => 'تعمیرات دیتابیس', 'description' => ''];
require __DIR__ . '/../layouts/main.php';
?>