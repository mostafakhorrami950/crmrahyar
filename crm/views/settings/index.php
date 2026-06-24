<?php $config = $GLOBALS['app_config']; ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-gear me-1"></i> تنظیمات سیستم</h5>
        <p style="margin:4px 0 0;color:var(--gray-500);font-size:13px;">مدیریت کلیه تنظیمات، امکانات و زیرسیستم‌های CRM</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <span style="padding:6px 14px;background:var(--gray-100);border-radius:20px;font-size:12px;font-weight:600;">
            <i class="bi bi-bar-chart me-1"></i> نسخه <?php echo htmlspecialchars($config['version'] ?? '1.0.0'); ?>
        </span>
    </div>
</div>

<!-- System Stats -->
<div class="row g-3" style="margin-bottom:24px;">
    <div class="col-6 col-md-2">
        <div class="card" style="padding:16px;text-align:center;border-radius:14px;border-top:3px solid #667eea;">
            <div style="font-size:28px;font-weight:900;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?php echo $stats['users']; ?></div>
            <div style="font-size:11px;color:var(--gray-500);margin-top:4px;"><i class="bi bi-person me-1"></i> کاربران</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card" style="padding:16px;text-align:center;border-radius:14px;border-top:3px solid #f093fb;">
            <div style="font-size:28px;font-weight:900;background:linear-gradient(135deg,#f093fb,#f5576c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?php echo $stats['contacts']; ?></div>
            <div style="font-size:11px;color:var(--gray-500);margin-top:4px;"><i class="bi bi-people me-1"></i> مخاطبان</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card" style="padding:16px;text-align:center;border-radius:14px;border-top:3px solid #4facfe;">
            <div style="font-size:28px;font-weight:900;background:linear-gradient(135deg,#4facfe,#00f2fe);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?php echo $stats['deals']; ?></div>
            <div style="font-size:11px;color:var(--gray-500);margin-top:4px;">💼 معاملات</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card" style="padding:16px;text-align:center;border-radius:14px;border-top:3px solid #43e97b;">
            <div style="font-size:28px;font-weight:900;background:linear-gradient(135deg,#43e97b,#38f9d7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?php echo $stats['payments']; ?></div>
            <div style="font-size:11px;color:var(--gray-500);margin-top:4px;"><i class="bi bi-credit-card me-1"></i> پرداخت‌ها</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card" style="padding:16px;text-align:center;border-radius:14px;border-top:3px solid #fa709a;">
            <div style="font-size:28px;font-weight:900;background:linear-gradient(135deg,#fa709a,#fee140);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?php echo $stats['sms_sent']; ?></div>
            <div style="font-size:11px;color:var(--gray-500);margin-top:4px;"><i class="bi bi-envelope me-1"></i> پیامک‌ها</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card" style="padding:16px;text-align:center;border-radius:14px;border-top:3px solid #a18cd1;">
            <div style="font-size:28px;font-weight:900;background:linear-gradient(135deg,#a18cd1,#fbc2eb);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?php echo $stats['pipelines']; ?></div>
            <div style="font-size:11px;color:var(--gray-500);margin-top:4px;"><i class="bi bi-list-task me-1"></i> پایپ‌لاین</div>
        </div>
    </div>
</div>

<!-- Quick Navigation Cards -->
<div style="margin-bottom:24px;">
    <h5 class="fw-bold mb-0">🔗 دسترسی سریع</h5>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/users" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#667eea';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;"><i class="bi bi-person me-1"></i></div>
                    <strong style="font-size:14px;color:var(--gray-800);">کاربران</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;"><?php echo $stats['users']; ?> کاربر</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/roles" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#8B5CF6';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;">🛡️</div>
                    <strong style="font-size:14px;color:var(--gray-800);">نقش‌ها و دسترسی‌ها</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;"><?php echo $stats['roles']; ?> نقش</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/pipelines" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#3B82F6';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;"><i class="bi bi-list-task me-1"></i></div>
                    <strong style="font-size:14px;color:var(--gray-800);">پایپ‌لاین‌ها</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;"><?php echo $stats['pipelines']; ?> پایپ‌لاین</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/settings/categories" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#F59E0B';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;">📂</div>
                    <strong style="font-size:14px;color:var(--gray-800);">دسته‌بندی مخاطبان</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;"><?php echo $stats['categories']; ?> دسته</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/settings/sources" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#10B981';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;"><i class="bi bi-crosshair me-1"></i></div>
                    <strong style="font-size:14px;color:var(--gray-800);">منابع معاملات</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;"><?php echo $stats['sources']; ?> منبع</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/settings/loss-reasons" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#8B5CF6';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;">😞</div>
                    <strong style="font-size:14px;color:var(--gray-800);">دلایل شکست</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;">مدیریت دلایل شکست معاملات</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/settings/win-reasons" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#10B981';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;">🏆</div>
                    <strong style="font-size:14px;color:var(--gray-800);">دلایل موفقیت</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;">مدیریت دلایل موفقیت معاملات</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/custom-fields" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#EC4899';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;"><i class="bi bi-tag me-1"></i></div>
                    <strong style="font-size:14px;color:var(--gray-800);">فیلدهای سفارشی</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;"><?php echo $stats['custom_fields']; ?> فیلد</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/system/repair" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#EF4444';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;">🗄️</div>
                    <strong style="font-size:14px;color:var(--gray-800);">تعمیر دیتابیس</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;">بررسی و تعمیر</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo $config['url']; ?>/system/error-logs" style="text-decoration:none;">
                <div class="card" style="padding:20px;text-align:center;border-radius:14px;cursor:pointer;transition:all 0.2s;border:2px solid transparent;" onmouseover="this.style.borderColor='#F97316';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform='none'">
                    <div style="font-size:32px;margin-bottom:8px;"><i class="bi bi-journal-text me-1"></i></div>
                    <strong style="font-size:14px;color:var(--gray-800);">لاگ خطاها</strong>
                    <div style="font-size:11px;color:var(--gray-400);margin-top:4px;">مشاهده خطاها</div>
                </div>
            </a>
        </div>
    </div>
</div>

<form method="POST" action="<?php echo $config['url']; ?>/settings/update">
<div class="row">
    <div class="col-md-8">
        <!-- Feature Toggles -->
        <div class="card" style="padding:24px;margin-bottom:20px;border-radius:16px;">
            <h5 class="fw-bold mb-0">🔌 فعال/غیرفعال کردن امکانات</h5>
            <div class="row g-3">
                <?php
                $featureMeta = [
                    'payment_gateway' => ['icon' => '<i class="bi bi-credit-card me-1"></i>', 'name' => 'درگاه پرداخت', 'desc' => 'اتصال به زیبال برای پرداخت آنلاین', 'color' => '#3B82F6'],
                    'sms' => ['icon' => '<i class="bi bi-envelope me-1"></i>', 'name' => 'پیامک', 'desc' => 'ارسال پیامک از طریق IPPanel', 'color' => '#10B981'],
                    'pipelines' => ['icon' => '<i class="bi bi-list-task me-1"></i>', 'name' => 'پایپ‌لاین‌ها', 'desc' => 'مدیریت مراحل معاملات', 'color' => '#8B5CF6'],
                    'reports' => ['icon' => '📈', 'name' => 'گزارشات', 'desc' => 'داشبورد و گزارش‌های تحلیلی', 'color' => '#F59E0B'],
                    'activity_log' => ['icon' => '<i class="bi bi-journal-text me-1"></i>', 'name' => 'لاگ فعالیت', 'desc' => 'ثبت و پیگیری فعالیت‌ها', 'color' => '#EF4444'],
                ];
                foreach ($features as $feature => $enabled):
                    $meta = $featureMeta[$feature] ?? ['icon' => '<i class="bi bi-gear me-1"></i>', 'name' => $feature, 'desc' => '', 'color' => '#6B7280'];
                ?>
                <div class="col-md-6">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;background:<?php echo $enabled ? $meta['color'] . '08' : 'var(--gray-50)'; ?>;border-radius:14px;border:2px solid <?php echo $enabled ? $meta['color'] . '30' : 'var(--gray-200)'; ?>;transition:all 0.3s;" id="feature-card-<?php echo $feature; ?>">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:44px;height:44px;background:<?php echo $meta['color']; ?>18;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;"><?php echo $meta['icon']; ?></div>
                            <div>
                                <strong style="font-size:14px;color:var(--gray-800);display:block;"><?php echo $meta['name']; ?></strong>
                                <small style="font-size:11px;color:var(--gray-500);"><?php echo $meta['desc']; ?></small>
                            </div>
                        </div>
                        <div style="position:relative;">
                            <input type="checkbox" class="feature-toggle" data-feature="<?php echo $feature; ?>" <?php echo $enabled ? 'checked' : ''; ?> style="display:none;" id="toggle-<?php echo $feature; ?>">
                            <label for="toggle-<?php echo $feature; ?>" style="display:block;width:52px;height:28px;background:<?php echo $enabled ? $meta['color'] : 'var(--gray-300)'; ?>;border-radius:14px;cursor:pointer;position:relative;transition:background 0.3s;" id="toggle-label-<?php echo $feature; ?>">
                                <span style="display:block;width:22px;height:22px;background:white;border-radius:50%;position:absolute;top:3px;<?php echo $enabled ? 'right:3px;' : 'left:3px;'; ?>box-shadow:0 2px 4px rgba(0,0,0,0.2);transition:all 0.3s;" id="toggle-knob-<?php echo $feature; ?>"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- System Settings -->
        <?php foreach ($groupedSettings as $group => $settings): ?>
        <?php if ($group === 'features') continue; ?>
        <div class="card" style="padding:24px;margin-bottom:20px;border-radius:16px;">
            <h5 class="fw-bold mb-0">
                <?php 
                $groupMeta = [
                    'general' => ['icon' => '<i class="bi bi-gear me-1"></i>', 'name' => 'تنظیمات عمومی', 'color' => '#667eea'],
                    'payment' => ['icon' => '<i class="bi bi-credit-card me-1"></i>', 'name' => 'تنظیمات درگاه پرداخت (زیبال)', 'color' => '#3B82F6'],
                    'sms' => ['icon' => '<i class="bi bi-envelope me-1"></i>', 'name' => 'تنظیمات پیامک (IPPanel)', 'color' => '#10B981'],
                ];
                $meta = $groupMeta[$group] ?? ['icon' => '🔧', 'name' => $group, 'color' => '#6B7280'];
                echo $meta['icon'] . ' ' . $meta['name'];
                ?>
            </h5>
            <div class="row g-3">
                <?php foreach ($settings as $s): ?>
                <div class="col-md-6">
                    <div style="margin-bottom:4px;">
                        <label class="form-label text-muted small fw-medium" style="font-size:12px;font-weight:600;color:var(--gray-600);">
                            <?php echo htmlspecialchars($s->description ?: $s->setting_key); ?>
                        </label>
                        <?php if (stripos($s->setting_key, 'key') !== false || stripos($s->setting_key, 'token') !== false || stripos($s->setting_key, 'secret') !== false || stripos($s->setting_key, 'password') !== false): ?>
                            <div style="position:relative;">
                                <input type="password" name="settings[<?php echo $s->setting_key; ?>]" class="form-control" value="<?php echo htmlspecialchars($s->setting_value); ?>" style="padding:10px 40px 10px 14px;border-radius:10px;font-size:13px;direction:ltr;" id="field-<?php echo $s->setting_key; ?>">
                                <button type="button" onclick="togglePassword('<?php echo $s->setting_key; ?>')" style="position:absolute;left:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--gray-400);padding:4px;">👁️</button>
                            </div>
                        <?php elseif (stripos($s->setting_key, 'url') !== false || stripos($s->setting_key, 'callback') !== false): ?>
                            <input type="url" name="settings[<?php echo $s->setting_key; ?>]" class="form-control" value="<?php echo htmlspecialchars($s->setting_value); ?>" style="padding:10px 14px;border-radius:10px;font-size:13px;direction:ltr;text-align:left;">
                        <?php elseif (stripos($s->setting_key, 'port') !== false): ?>
                            <input type="number" name="settings[<?php echo $s->setting_key; ?>]" class="form-control" value="<?php echo htmlspecialchars($s->setting_value); ?>" style="padding:10px 14px;border-radius:10px;font-size:13px;direction:ltr;text-align:left;">
                        <?php else: ?>
                            <input type="text" name="settings[<?php echo $s->setting_key; ?>]" class="form-control" value="<?php echo htmlspecialchars($s->setting_value); ?>" style="padding:10px 14px;border-radius:10px;font-size:13px;">
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="col-md-4">
        <!-- Save Button -->
        <div class="card" style="padding:20px;border-radius:16px;margin-bottom:20px;">
            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:16px;font-weight:bold;border-radius:12px;">
                <i class="bi bi-check-circle me-1"></i>ذخیره تنظیمات
            </button>
            <p style="text-align:center;font-size:11px;color:var(--gray-400);margin:10px 0 0;">تغییرات بلافاصله اعمال می‌شوند</p>
        </div>

        <!-- System Info -->
        <div class="card" style="padding:20px;border-radius:16px;margin-bottom:20px;">
            <h5 class="fw-bold mb-0">ℹ️ اطلاعات سیستم</h5>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--gray-50);border-radius:10px;font-size:13px;">
                    <span style="color:var(--gray-500);">نام سیستم</span>
                    <strong><?php echo htmlspecialchars($config['name']); ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--gray-50);border-radius:10px;font-size:13px;">
                    <span style="color:var(--gray-500);">نسخه</span>
                    <strong><?php echo htmlspecialchars($config['version'] ?? '1.0.0'); ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--gray-50);border-radius:10px;font-size:13px;">
                    <span style="color:var(--gray-500);">PHP</span>
                    <strong style="direction:ltr;"><?php echo phpversion(); ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--gray-50);border-radius:10px;font-size:13px;">
                    <span style="color:var(--gray-500);">سرور</span>
                    <strong style="direction:ltr;font-size:12px;"><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'); ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--gray-50);border-radius:10px;font-size:13px;">
                    <span style="color:var(--gray-500);">منطقه زمانی</span>
                    <strong><?php echo $config['timezone']; ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--gray-50);border-radius:10px;font-size:13px;">
                    <span style="color:var(--gray-500);">دیباگ</span>
                    <span style="padding:2px 8px;border-radius:8px;font-size:11px;font-weight:700;<?php echo ($config['debug'] ?? false) ? 'background:#fef3c7;color:#92400e;' : 'background:#d4edda;color:#155724;'; ?>">
                        <?php echo ($config['debug'] ?? false) ? 'روشن' : 'خاموش'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Feature Status Summary -->
        <div class="card" style="padding:20px;border-radius:16px;">
            <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart me-1"></i> وضعیت امکانات</h5>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($features as $feature => $enabled):
                    $fMeta = $featureMeta[$feature] ?? ['icon' => '<i class="bi bi-gear me-1"></i>', 'name' => $feature, 'color' => '#6B7280'];
                ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--gray-50);border-radius:10px;">
                    <span style="font-size:13px;"><?php echo $fMeta['icon']; ?> <?php echo $fMeta['name']; ?></span>
                    <span style="padding:2px 10px;border-radius:12px;font-size:11px;font-weight:700;<?php echo $enabled ? 'background:#d4edda;color:#155724;' : 'background:#f8d7da;color:#721c24;'; ?>">
                        <?php echo $enabled ? 'فعال' : 'غیرفعال'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</form>

<script>
// Custom toggle switches
document.querySelectorAll('.feature-toggle').forEach(function(checkbox) {
    var feature = checkbox.dataset.feature;
    var label = document.getElementById('toggle-label-' + feature);
    var knob = document.getElementById('toggle-knob-' + feature);
    var card = document.getElementById('feature-card-' + feature);
    
    if (label) {
        label.addEventListener('click', function() {
            checkbox.checked = !checkbox.checked;
            var enabled = checkbox.checked ? 1 : 0;
            var colors = {
                'payment_gateway': '#3B82F6',
                'sms': '#10B981',
                'pipelines': '#8B5CF6',
                'reports': '#F59E0B',
                'activity_log': '#EF4444'
            };
            var color = colors[feature] || '#6B7280';
            
            // Update UI
            label.style.background = enabled ? color : 'var(--gray-300)';
            knob.style.right = enabled ? '3px' : 'auto';
            knob.style.left = enabled ? 'auto' : '3px';
            card.style.borderColor = enabled ? color + '30' : 'var(--gray-200)';
            card.style.background = enabled ? color + '08' : 'var(--gray-50)';
            
            // AJAX call
            fetch('<?php echo $config['url']; ?>/settings/toggle-feature', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'feature=' + feature + '&enabled=' + enabled
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    // Show toast notification
                    var toast = document.createElement('div');
                    toast.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#1f2937;color:white;padding:12px 24px;border-radius:12px;font-size:13px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
                    toast.textContent = enabled ? '<i class="bi bi-check-circle text-success me-1"></i> فعال شد' : '<i class="bi bi-x-circle text-danger me-1"></i> غیرفعال شد';
                    document.body.appendChild(toast);
                    setTimeout(function() { toast.remove(); }, 2000);
                }
            })
            .catch(function() {});
        });
    }
});

// Toggle password visibility
function togglePassword(key) {
    var field = document.getElementById('field-' + key);
    if (field) {
        field.type = field.type === 'password' ? 'text' : 'password';
    }
}
</script>