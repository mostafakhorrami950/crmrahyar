<div class="page-header">
    <h5>👥 مدیریت مخاطبان</h5>
    <a href="<?php echo $config['url']; ?>/contacts/create" class="btn btn-primary">➕ مخاطب جدید</a>
</div>

<!-- Stats Row -->
<div class="stats-grid" style="margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-label">کل مخاطبان</div>
        <div class="stat-value"><?php echo count($contacts); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">💰 مجموع خرید</div>
        <div class="stat-value" style="color:var(--primary);font-size:18px;">
            <?php 
            $totalPurchases = 0;
            foreach ($contacts as $c) $totalPurchases += $c->total_purchases ?? 0;
            echo number_format($totalPurchases);
            ?>
        </div>
    </div>
</div>

<!-- Search Filter -->
<div class="filter-section card">
    <form method="GET" action="<?php echo parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="text" name="search" class="form-input" style="flex:2;min-width:200px;" placeholder="🔍 جستجو در مخاطبان با نام، شماره تماس یا ایمیل..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">🔍 جستجو</button>
    </form>
</div>

<!-- Contacts Grid -->
<?php if (empty($contacts)): ?>
<div class="card">
    <div style="text-align:center;padding:60px 20px;color:var(--gray-400);">
        <div style="font-size:64px;margin-bottom:16px;">👥</div>
        <h3 style="color:var(--gray-500);margin-bottom:8px;">هیچ مخاطبی یافت نشد</h3>
        <p style="margin-bottom:20px;">اولین مخاطب را ایجاد کنید</p>
        <a href="<?php echo $config['url']; ?>/contacts/create" class="btn btn-primary">➕ ایجاد مخاطب جدید</a>
    </div>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:12px;">
    <?php foreach ($contacts as $c): ?>
    <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $c->id; ?>" style="text-decoration:none;color:inherit;">
        <div class="card contact-card" style="padding:16px;border-radius:14px;transition:all 0.2s;cursor:pointer;border:1px solid var(--gray-200);">
            <div style="display:flex;align-items:center;gap:14px;">
                <!-- Avatar -->
                <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;font-weight:700;flex-shrink:0;">
                    <?php echo mb_substr($c->full_name, 0, 1); ?>
                </div>
                <!-- Info -->
                <div style="flex:1;min-width:0;">
                    <strong style="font-size:15px;color:var(--gray-900);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?php echo htmlspecialchars($c->full_name); ?>
                    </strong>
                    <?php if ($c->phone): ?>
                    <span style="font-size:13px;color:var(--gray-500);direction:ltr;display:inline-block;">📞 <?php echo htmlspecialchars($c->phone); ?></span>
                    <?php endif; ?>
                    <?php if ($c->email): ?>
                    <br><span style="font-size:12px;color:var(--gray-400);">✉️ <?php echo htmlspecialchars($c->email); ?></span>
                    <?php endif; ?>
                </div>
                <!-- Stats Badge -->
                <div style="text-align:center;flex-shrink:0;">
                    <div style="font-size:20px;font-weight:800;color:var(--primary);"><?php echo $c->deals_count; ?></div>
                    <div style="font-size:10px;color:var(--gray-400);">معامله</div>
                </div>
            </div>
            <!-- Bottom Row -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:12px;border-top:1px solid var(--gray-100);">
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <?php if ($c->national_code): ?>
                    <span style="font-size:11px;color:var(--gray-400);background:var(--gray-50);padding:3px 8px;border-radius:6px;">🪪 <?php echo htmlspecialchars($c->national_code); ?></span>
                    <?php endif; ?>
                    <?php if ($c->company): ?>
                    <span style="font-size:11px;color:var(--gray-400);background:var(--gray-50);padding:3px 8px;border-radius:6px;">🏢 <?php echo htmlspecialchars($c->company); ?></span>
                    <?php endif; ?>
                </div>
                <div style="display:flex;gap:4px;">
                    <a href="<?php echo $config['url']; ?>/contacts/view/<?php echo $c->id; ?>" class="btn btn-sm btn-primary" onclick="event.stopPropagation();" title="مشاهده">👁️</a>
                    <a href="<?php echo $config['url']; ?>/contacts/edit/<?php echo $c->id; ?>" class="btn btn-sm btn-secondary" onclick="event.stopPropagation();" title="ویرایش">✏️</a>
                    <form method="POST" action="<?php echo $config['url']; ?>/contacts/delete/<?php echo $c->id; ?>" style="display:inline;" onsubmit="event.stopPropagation();return confirm('آیا از حذف «<?php echo htmlspecialchars($c->full_name); ?>» اطمینان دارید؟')">
                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">🗑️</button>
                    </form>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:11px;color:var(--gray-400);">
                <span>📅 <?php echo \Core\JDate::displayDate($c->created_at); ?></span>
                <span>💰 <?php echo number_format($c->total_purchases ?? 0); ?></span>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.contact-card:hover {
    border-color: var(--primary) !important;
    box-shadow: 0 4px 16px rgba(102,126,234,0.15);
    transform: translateY(-2px);
}
</style>