<?php
namespace Controllers;

/**
 * PWA Controller - Complete standalone PWA frontend
 * All platform features available for mobile operators
 */
class PWAController
{
    public function index(array $params = []): void
    {
        if (\Core\Auth::check()) {
            header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/app');
        } else {
            header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/login');
        }
        exit;
    }

    public function loginForm(array $params = []): void
    {
        if (\Core\Auth::check()) { header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/app'); exit; }
        $error = '';
        include __DIR__ . '/../views/pwa/login.php';
    }

    public function login(array $params = []): void
    {
        $config = $GLOBALS['app_config'];
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($username) || empty($password)) {
            $error = 'نام کاربری و رمز عبور الزامی است';
            include __DIR__ . '/../views/pwa/login.php';
            return;
        }
        $db = \Core\Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
        if (!$user || !password_verify($password, $user->password)) {
            $error = 'نام کاربری یا رمز عبور اشتباه است';
            include __DIR__ . '/../views/pwa/login.php';
            return;
        }
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['logged_in'] = true;
        header('Location: ' . $config['url'] . '/pwa/app');
        exit;
    }

    public function logout(array $params = []): void
    {
        session_destroy();
        header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/login');
        exit;
    }

    public function app(array $params = []): void
    {
        \Core\Auth::requireAuth();
        include __DIR__ . '/../views/pwa/app.php';
    }

    public function more(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $user = \Core\Auth::user();
        $pwaContent = $this->renderSection(function() use ($config, $user) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center justify-content-between">
            <h5>بیشتر</h5>
            <a href="<?php echo $config['url']; ?>/pwa/logout" style="color:var(--pwa-danger);text-decoration:none;font-size:13px;"><i class="bi bi-box-arrow-right me-1"></i>خروج</a>
        </div></div>
        <div class="pwa-content pwa-fade">
            <div class="pwa-card" style="text-align:center;">
                <div style="width:60px;height:60px;margin:0 auto 10px;background:linear-gradient(135deg,#4361ee,#7209b7);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff;font-weight:700;"><?php echo mb_substr($user->full_name ?? '?', 0, 1); ?></div>
                <div style="font-weight:700;font-size:15px;"><?php echo htmlspecialchars($user->full_name ?? ''); ?></div>
                <div style="color:var(--pwa-muted);font-size:12px;"><?php echo htmlspecialchars($user->role_name ?? ''); ?></div>
            </div>
            <div class="pwa-card" style="padding:0;">
                <a href="<?php echo $config['url']; ?>/pwa/pipelines" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);"><div class="pwa-list-icon" style="background:rgba(67,97,238,0.1);color:var(--pwa-primary);"><i class="bi bi-kanban"></i></div><span class="pwa-list-title">پایپ لاین‌ها</span><div class="pwa-list-right"><i class="bi bi-chevron-left" style="color:var(--pwa-muted);"></i></div></a>
                <a href="<?php echo $config['url']; ?>/pwa/calendar" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);"><div class="pwa-list-icon" style="background:rgba(247,37,133,0.1);color:var(--pwa-accent);"><i class="bi bi-calendar3"></i></div><span class="pwa-list-title">تقویم</span><div class="pwa-list-right"><i class="bi bi-chevron-left" style="color:var(--pwa-muted);"></i></div></a>
                <a href="<?php echo $config['url']; ?>/dashboard" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);"><div class="pwa-list-icon" style="background:rgba(6,214,160,0.1);color:var(--pwa-success);"><i class="bi bi-globe"></i></div><span class="pwa-list-title">نسخه وب کامل</span><div class="pwa-list-right"><i class="bi bi-chevron-left" style="color:var(--pwa-muted);"></i></div></a>
                <a href="<?php echo $config['url']; ?>/pwa/logout" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-danger);"><div class="pwa-list-icon" style="background:rgba(239,71,111,0.1);color:var(--pwa-danger);"><i class="bi bi-box-arrow-right"></i></div><span class="pwa-list-title">خروج از حساب</span></a>
            </div>
            <p style="text-align:center;color:var(--pwa-muted);font-size:11px;margin-top:20px;">علاءالدین سفیر اسمان - PWA<br>v1.0.0</p>
        </div>
        <?php $this->renderBottomNav($config, 'more');
        });
        $pageTitle = 'بیشتر';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    // ========== DEALS ==========

    public function deals(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $userId = \Core\Auth::id();
        $search = trim($_GET['q'] ?? '');
        $where = "WHERE d.assigned_to = ?";
        $qp = [$userId];
        if ($search) { $where .= " AND (d.title LIKE ? OR c.full_name LIKE ?)"; $qp[] = "%$search%"; $qp[] = "%$search%"; }
        $deals = $db->fetchAll("SELECT d.*, s.name as stage_name, s.color as stage_color, c.full_name as contact_name FROM deals d LEFT JOIN stages s ON d.stage_id = s.id LEFT JOIN contacts c ON d.contact_id = c.id {$where} ORDER BY d.created_at DESC LIMIT 50", $qp);
        $pwaContent = $this->renderSection(function() use ($config, $deals, $search) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/app" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5>معاملات</h5></div>
            <a href="<?php echo $config['url']; ?>/pwa/deals/create" style="color:var(--pwa-primary);font-size:22px;text-decoration:none;"><i class="bi bi-plus-circle-fill"></i></a>
        </div></div>
        <div class="pwa-content pwa-fade">
            <form method="GET" style="margin-bottom:12px;"><input type="text" name="q" class="pwa-input" placeholder="🔍 جستجو در معاملات..." value="<?php echo htmlspecialchars($search); ?>" style="font-size:13px;"></form>
            <?php if (empty($deals)): ?><div style="text-align:center;padding:40px;color:var(--pwa-muted);"><i class="bi bi-inbox" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i><p>معامله‌ای یافت نشد</p></div>
            <?php else: foreach ($deals as $deal): ?>
            <a href="<?php echo $config['url']; ?>/pwa/deals/view/<?php echo $deal->id; ?>" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);">
                <div class="pwa-list-icon" style="background:rgba(67,97,238,0.1);color:var(--pwa-primary);"><i class="bi bi-briefcase"></i></div>
                <div style="flex:1;min-width:0;"><div class="pwa-list-title"><?php echo htmlspecialchars(mb_substr($deal->title,0,35)); ?></div>
                    <div class="pwa-list-sub"><?php if($deal->contact_name): ?><i class="bi bi-person me-1"></i><?php echo htmlspecialchars(mb_substr($deal->contact_name,0,20)); endif; ?>
                    <?php if($deal->stage_name): ?> <span class="pwa-badge pwa-badge-primary" style="font-size:9px;"><?php echo htmlspecialchars($deal->stage_name); ?></span><?php endif; ?></div></div>
                <div class="pwa-list-right"><div style="font-size:13px;font-weight:700;"><?php echo $deal->is_won?'✅':($deal->is_lost?'❌':number_format($deal->amount)); ?></div></div>
            </a>
            <?php endforeach; endif; ?>
        </div>
        <?php $this->renderBottomNav($config, 'deals');
        });
        $pageTitle = 'معاملات';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function dealCreate(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $contacts = $db->fetchAll("SELECT id, full_name, phone FROM contacts ORDER BY full_name LIMIT 200");
        $pipelines = $db->fetchAll("SELECT * FROM pipelines WHERE is_active = 1 ORDER BY name");
        $stages = $db->fetchAll("SELECT s.id, s.name, s.pipeline_id FROM stages s JOIN pipelines p ON s.pipeline_id = p.id WHERE p.is_active = 1 ORDER BY s.pipeline_id, s.order_index");
        $pwaContent = $this->renderSection(function() use ($config, $contacts, $pipelines, $stages) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/deals" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5>ایجاد معامله</h5></div></div>
        <div class="pwa-content pwa-fade">
            <form method="POST" action="<?php echo $config['url']; ?>/pwa/deals/store">
                <div style="margin-bottom:14px;"><label class="pwa-label">عنوان معامله *</label><input type="text" name="title" class="pwa-input" required placeholder="مثال: تور استانبول"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">مخاطب</label><select name="contact_id" class="pwa-select"><option value="">انتخاب کنید</option><?php foreach($contacts as $c): ?><option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->full_name); ?></option><?php endforeach; ?></select></div>
                <div class="row g-2" style="margin-bottom:14px;"><div class="col-6"><label class="pwa-label">مبلغ (تومان)</label><input type="number" name="amount" class="pwa-input" placeholder="0"></div>
                <div class="col-6"><label class="pwa-label">احتمال %</label><input type="number" name="probability" class="pwa-input" min="0" max="100" value="0"></div></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">پایپ لاین *</label><select name="pipeline_id" id="pwaPipe" class="pwa-select" required onchange="pwaLoadStages()"><option value="">انتخاب</option><?php foreach($pipelines as $p): ?><option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name); ?></option><?php endforeach; ?></select></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">مرحله *</label><select name="stage_id" id="pwaStage" class="pwa-select" required><option value="">ابتدا پایپ لاین را انتخاب کنید</option></select></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">توضیحات</label><textarea name="description" class="pwa-input" rows="3" placeholder="توضیحات..."></textarea></div>
                <button type="submit" class="pwa-btn pwa-btn-primary pwa-btn-block"><i class="bi bi-check-circle"></i> ثبت معامله</button>
            </form>
        </div>
        <script>var pwaStages=<?php echo json_encode($stages); ?>;function pwaLoadStages(){var pid=document.getElementById('pwaPipe').value;var sel=document.getElementById('pwaStage');sel.innerHTML='<option value="">انتخاب</option>';pwaStages.forEach(function(s){if(String(s.pipeline_id)===pid) sel.innerHTML+='<option value="'+s.id+'">'+s.name+'</option>';});}</script>
        <?php $this->renderBottomNav($config, 'deals');
        });
        $pageTitle = 'ایجاد معامله';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function dealStore(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $db = \Core\Database::getInstance();
        $db->query("INSERT INTO deals (title, description, amount, pipeline_id, stage_id, contact_id, assigned_to, source, probability, created_by) VALUES (?,?,?,?,?,?,?,?,?,?)", [
            trim($_POST['title'] ?? ''), $_POST['description'] ?? '', $_POST['amount'] ?: 0, $_POST['pipeline_id'], $_POST['stage_id'], $_POST['contact_id'] ?: null, \Core\Auth::id(), $_POST['source'] ?? '', $_POST['probability'] ?: 0, \Core\Auth::id()
        ]);
        header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/deals');
        exit;
    }

    public function dealView(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $id = $params['id'];
        $deal = $db->fetch("SELECT d.*, s.name as stage_name, s.color as stage_color, c.full_name as contact_name, c.phone as contact_phone, c.id as c_id FROM deals d LEFT JOIN stages s ON d.stage_id = s.id LEFT JOIN contacts c ON d.contact_id = c.id WHERE d.id = ?", [$id]);
        if (!$deal) { header('Location: ' . $config['url'] . '/pwa/deals'); exit; }
        $activities = $db->fetchAll("SELECT * FROM deal_activities WHERE deal_id = ? ORDER BY activity_date DESC", [$id]);
        $payments = $db->fetchAll("SELECT * FROM payments WHERE deal_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);
$pwaContent = $this->renderSection(function() use ($config, $deal, $activities, $payments, $id) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/deals" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5 style="font-size:14px;"><?php echo htmlspecialchars(mb_substr($deal->title,0,30)); ?></h5></div>
            <a href="<?php echo $config['url']; ?>/pwa/deals/edit/<?php echo $id; ?>" style="color:var(--pwa-primary);font-size:16px;text-decoration:none;"><i class="bi bi-pencil"></i></a>
        </div></div>
        <div class="pwa-content pwa-fade">
            <div class="pwa-card">
                <div class="row g-2 text-center">
                    <div class="col-4"><div class="pwa-stat"><div class="pwa-stat-value" style="color:var(--pwa-primary);font-size:16px;"><?php echo number_format($deal->amount); ?></div><div class="pwa-stat-label">مبلغ</div></div></div>
                    <div class="col-4"><div class="pwa-stat"><div class="pwa-stat-value" style="font-size:14px;"><?php echo $deal->probability; ?>%</div><div class="pwa-stat-label">احتمال</div></div></div>
                    <div class="col-4"><div class="pwa-stat"><div class="pwa-stat-value" style="font-size:12px;"><?php echo $deal->is_won?'✅ موفق':($deal->is_lost?'❌ ناموفق':'🔄 باز'); ?></div><div class="pwa-stat-label">وضعیت</div></div></div>
                </div>
                <?php if($deal->stage_name): ?><div style="margin-top:10px;text-align:center;"><span class="pwa-badge pwa-badge-primary"><?php echo htmlspecialchars($deal->stage_name); ?></span></div><?php endif; ?>
            </div>
            <?php if($deal->contact_name): ?>
            <a href="<?php echo $config['url']; ?>/pwa/contacts/view/<?php echo $deal->c_id; ?>" class="pwa-card" style="display:flex;align-items:center;gap:12px;text-decoration:none;color:var(--pwa-text);">
                <div class="pwa-list-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:700;"><?php echo mb_substr($deal->contact_name,0,1); ?></div>
                <div><div style="font-weight:600;font-size:13px;"><?php echo htmlspecialchars($deal->contact_name); ?></div><?php if($deal->contact_phone): ?><div style="color:var(--pwa-muted);font-size:11px;" dir="ltr"><?php echo htmlspecialchars($deal->contact_phone); ?></div><?php endif; ?></div>
                <i class="bi bi-chevron-left" style="margin-right:auto;color:var(--pwa-muted);"></i>
            </a>
            <?php endif; ?>
            <?php if($deal->description): ?><div class="pwa-card"><div class="pwa-section-title">توضیحات</div><p style="font-size:13px;color:var(--pwa-muted);"><?php echo nl2br(htmlspecialchars($deal->description)); ?></p></div><?php endif; ?>
            <!-- Add Activity -->
            <div class="pwa-card"><div class="pwa-card-header"><span class="pwa-card-title"><i class="bi bi-plus-circle me-1"></i>افزودن فعالیت</span></div>
                <form method="POST" action="<?php echo $config['url']; ?>/pwa/deals/add-activity/<?php echo $id; ?>">
                    <div class="row g-2"><div class="col-6" style="margin-bottom:8px;"><select name="type" class="pwa-select" style="font-size:12px;"><option value="call">📞 تماس</option><option value="meeting">🤝 جلسه</option><option value="email">📧 ایمیل</option><option value="follow_up">🔔 پیگیری</option></select></div>
                    <div class="col-6" style="margin-bottom:8px;"><input type="text" name="subject" class="pwa-input" placeholder="موضوع" style="font-size:12px;"></div></div>
                    <div class="row g-2"><div class="col-8" style="margin-bottom:8px;"><input type="datetime-local" name="activity_date" class="pwa-input" style="font-size:12px;"></div>
                    <div class="col-4"><button type="submit" class="pwa-btn pwa-btn-primary pwa-btn-block" style="font-size:12px;padding:10px;"><i class="bi bi-plus"></i> ثبت</button></div></div>
                </form>
            </div>
            <!-- Activities -->
            <?php if(!empty($activities)): ?><div class="pwa-card"><div class="pwa-card-header"><span class="pwa-card-title"><i class="bi bi-clock-history me-1"></i>فعالیت‌ها</span></div>
                <?php foreach($activities as $act): ?>
                <div class="pwa-list-item" style="<?php echo $act->is_done?'opacity:0.5;':''; ?>">
                    <div class="pwa-list-icon" style="background:rgba(255,209,102,0.1);color:var(--pwa-warning);"><i class="bi bi-<?php echo $act->type==='call'?'telephone':($act->type==='meeting'?'people':'pin'); ?>"></i></div>
                    <div style="flex:1;"><div class="pwa-list-title" style="<?php echo $act->is_done?'text-decoration:line-through;':''; ?>"><?php echo htmlspecialchars($act->subject ?? '-'); ?></div>
                        <div class="pwa-list-sub"><?php echo $act->activity_date?\Core\JDate::displayDate($act->activity_date):''; ?></div></div>
                    <form method="POST" action="<?php echo $config['url']; ?>/pwa/activities/toggle/<?php echo $act->id; ?>"><button type="submit" style="background:none;border:none;font-size:18px;cursor:pointer;"><?php echo $act->is_done?'✅':'⬜'; ?></button></form>
                </div>
                <?php endforeach; ?></div>
            <?php endif; ?>
            <!-- Payments -->
            <?php if(!empty($payments)): ?><div class="pwa-card"><div class="pwa-section-title">پرداخت‌ها</div>
                <?php foreach($payments as $p): ?><div class="pwa-list-item"><div class="pwa-list-icon" style="background:rgba(6,214,160,0.1);color:var(--pwa-success);"><i class="bi bi-credit-card"></i></div>
                    <div style="flex:1;"><div class="pwa-list-title"><?php echo number_format($p->amount); ?> تومان</div><div class="pwa-list-sub"><?php echo $p->status; ?></div></div></div><?php endforeach; ?></div>
            <?php endif; ?>
        </div>
        <?php $this->renderBottomNav($config, 'deals');
        });
        $pageTitle = 'مشاهده معامله';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function dealEdit(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $id = $params['id'];
        $deal = $db->fetch("SELECT * FROM deals WHERE id = ?", [$id]);
        if (!$deal) { header('Location: ' . $config['url'] . '/pwa/deals'); exit; }
        $contacts = $db->fetchAll("SELECT id, full_name FROM contacts ORDER BY full_name LIMIT 200");
        $pipelines = $db->fetchAll("SELECT * FROM pipelines WHERE is_active = 1");
        $stages = $db->fetchAll("SELECT * FROM stages WHERE is_active = 1 ORDER BY pipeline_id, order_index");
$pwaContent = $this->renderSection(function() use ($config, $deal, $contacts, $pipelines, $stages, $id) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/deals/view/<?php echo $id; ?>" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5 style="font-size:15px;">ویرایش معامله</h5></div></div>
        <div class="pwa-content pwa-fade">
            <form method="POST" action="<?php echo $config['url']; ?>/pwa/deals/update/<?php echo $id; ?>">
                <div style="margin-bottom:14px;"><label class="pwa-label">عنوان *</label><input type="text" name="title" class="pwa-input" required value="<?php echo htmlspecialchars($deal->title); ?>"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">مخاطب</label><select name="contact_id" class="pwa-select"><option value="">انتخاب</option><?php foreach($contacts as $c): ?><option value="<?php echo $c->id; ?>" <?php echo $deal->contact_id==$c->id?'selected':''; ?>><?php echo htmlspecialchars($c->full_name); ?></option><?php endforeach; ?></select></div>
                <div class="row g-2"><div class="col-6" style="margin-bottom:14px;"><label class="pwa-label">مبلغ</label><input type="number" name="amount" class="pwa-input" value="<?php echo $deal->amount; ?>"></div>
                <div class="col-6" style="margin-bottom:14px;"><label class="pwa-label">احتمال %</label><input type="number" name="probability" class="pwa-input" value="<?php echo $deal->probability; ?>"></div></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">پایپ لاین</label><select name="pipeline_id" id="pwaPipeline" class="pwa-select" onchange="pwaLoadStages()"><option value="">انتخاب</option><?php foreach($pipelines as $p): ?><option value="<?php echo $p->id; ?>" <?php echo $deal->pipeline_id==$p->id?'selected':''; ?>><?php echo htmlspecialchars($p->name); ?></option><?php endforeach; ?></select></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">مرحله</label><select name="stage_id" id="pwaStage" class="pwa-select"></select></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">توضیحات</label><textarea name="description" class="pwa-input" rows="3"><?php echo htmlspecialchars($deal->description ?? ''); ?></textarea></div>
                <button type="submit" class="pwa-btn pwa-btn-primary pwa-btn-block"><i class="bi bi-check-circle"></i> بروزرسانی</button>
            </form>
        </div>
        <script>
        var pwaStages=<?php echo json_encode($stages); ?>; var currentStage=<?php echo $deal->stage_id; ?>;
        function pwaLoadStages(){var pid=document.getElementById('pwaPipeline').value;var sel=document.getElementById('pwaStage');sel.innerHTML='<option value="">انتخاب</option>';pwaStages.forEach(function(s){if(String(s.pipeline_id)===pid){var opt='<option value="'+s.id+'"'+(s.id==currentStage?' selected':'')+'>'+s.name+'</option>';sel.innerHTML+=opt;}});}
        pwaLoadStages();
        </script>
        <?php $this->renderBottomNav($config, 'deals');
        });
        $pageTitle = 'ویرایش معامله';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function dealUpdate(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $id = $params['id'];
        $db = \Core\Database::getInstance();
        $db->query("UPDATE deals SET title=?, description=?, amount=?, pipeline_id=?, stage_id=?, contact_id=?, source=?, probability=?, updated_at=NOW() WHERE id=?", [
            trim($_POST['title'] ?? ''), $_POST['description'] ?? '', $_POST['amount'] ?: 0, $_POST['pipeline_id'], $_POST['stage_id'], $_POST['contact_id'] ?: null, $_POST['source'] ?? '', $_POST['probability'] ?: 0, $id
        ]);
        header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/deals/view/' . $id);
        exit;
    }

    public function addActivity(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $dealId = $params['id'];
        $db = \Core\Database::getInstance();
        $db->query("INSERT INTO deal_activities (deal_id, user_id, type, subject, activity_date) VALUES (?,?,?,?,?)", [
            $dealId, \Core\Auth::id(), $_POST['type'] ?? 'note', trim($_POST['subject'] ?? ''), $_POST['activity_date'] ?: null
        ]);
        header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/deals/view/' . $dealId);
        exit;
    }

    // ========== CONTACTS ==========

    public function contacts(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $search = trim($_GET['q'] ?? '');
        $where = $search ? "WHERE full_name LIKE ? OR phone LIKE ?" : "";
        $qp = $search ? ["%$search%", "%$search%"] : [];
        $contacts = $db->fetchAll("SELECT * FROM contacts {$where} ORDER BY created_at DESC LIMIT 50", $qp);
$pwaContent = $this->renderSection(function() use ($config, $contacts, $search) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/app" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5 style="font-size:15px;">مخاطبان</h5></div>
            <a href="<?php echo $config['url']; ?>/pwa/contacts/create" style="color:var(--pwa-success);font-size:18px;text-decoration:none;"><i class="bi bi-person-plus"></i></a>
        </div></div>
        <div class="pwa-content pwa-fade">
            <form method="GET" style="margin-bottom:12px;"><input type="text" name="q" class="pwa-input" placeholder="🔍 جستجو..." value="<?php echo htmlspecialchars($search); ?>" style="font-size:13px;"></form>
            <?php if(empty($contacts)): ?><div style="text-align:center;padding:30px;color:var(--pwa-muted);"><i class="bi bi-people" style="font-size:40px;display:block;margin-bottom:8px;opacity:0.3;"></i><p>مخاطبی یافت نشد</p></div>
            <?php else: foreach($contacts as $c): ?>
            <a href="<?php echo $config['url']; ?>/pwa/contacts/view/<?php echo $c->id; ?>" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);">
                <div class="pwa-list-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:14px;font-weight:700;"><?php echo mb_substr($c->full_name,0,1); ?></div>
                <div style="flex:1;"><div class="pwa-list-title"><?php echo htmlspecialchars($c->full_name); ?></div><div class="pwa-list-sub"><?php if($c->phone): ?><i class="bi bi-phone me-1"></i><span dir="ltr"><?php echo htmlspecialchars($c->phone); ?></span><?php endif; ?></div></div>
                <?php if($c->phone): ?><a href="tel:<?php echo htmlspecialchars($c->phone); ?>" style="color:var(--pwa-success);font-size:18px;text-decoration:none;" onclick="event.stopPropagation();"><i class="bi bi-telephone-fill"></i></a><?php endif; ?>
            </a>
            <?php endforeach; endif; ?>
        </div>
        <?php $this->renderBottomNav($config, 'contacts');
        });
        $pageTitle = 'مخاطبان';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function contactCreate(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
$pwaContent = $this->renderSection(function() use ($config) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/contacts" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5 style="font-size:15px;">ایجاد مخاطب</h5></div></div>
        <div class="pwa-content pwa-fade">
            <form method="POST" action="<?php echo $config['url']; ?>/pwa/contacts/store">
                <div style="margin-bottom:14px;"><label class="pwa-label">نام کامل *</label><input type="text" name="full_name" class="pwa-input" required></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">تلفن</label><input type="text" name="phone" class="pwa-input" dir="ltr" placeholder="09xxxxxxxxx"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">ایمیل</label><input type="email" name="email" class="pwa-input" dir="ltr"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">شرکت</label><input type="text" name="company" class="pwa-input"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">یادداشت</label><textarea name="notes" class="pwa-input" rows="3"></textarea></div>
                <button type="submit" class="pwa-btn pwa-btn-primary pwa-btn-block"><i class="bi bi-check-circle"></i> ثبت مخاطب</button>
            </form>
        </div>
        <?php $this->renderBottomNav($config, 'contacts');
        });
        $pageTitle = 'ایجاد مخاطب';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function contactStore(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $db = \Core\Database::getInstance();
        $db->query("INSERT INTO contacts (full_name, phone, email, company, notes, created_by) VALUES (?,?,?,?,?,?)", [
            trim($_POST['full_name'] ?? ''), trim($_POST['phone'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['company'] ?? ''), $_POST['notes'] ?? '', \Core\Auth::id()
        ]);
        header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/contacts');
        exit;
    }

    public function contactView(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $id = $params['id'];
        $contact = $db->fetch("SELECT * FROM contacts WHERE id = ?", [$id]);
        if (!$contact) { header('Location: ' . $config['url'] . '/pwa/contacts'); exit; }
        $deals = $db->fetchAll("SELECT d.*, s.name as stage_name FROM deals d LEFT JOIN stages s ON d.stage_id = s.id WHERE d.contact_id = ? ORDER BY d.created_at DESC LIMIT 10", [$id]);
$pwaContent = $this->renderSection(function() use ($config, $contact, $deals, $id) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/contacts" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5 style="font-size:14px;"><?php echo htmlspecialchars($contact->full_name); ?></h5></div>
            <a href="<?php echo $config['url']; ?>/pwa/contacts/edit/<?php echo $id; ?>" style="color:var(--pwa-primary);font-size:16px;text-decoration:none;"><i class="bi bi-pencil"></i></a>
        </div></div>
        <div class="pwa-content pwa-fade">
            <div class="pwa-card" style="text-align:center;">
                <div style="width:60px;height:60px;margin:0 auto 10px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff;font-weight:700;"><?php echo mb_substr($contact->full_name,0,1); ?></div>
                <h6 style="font-weight:700;"><?php echo htmlspecialchars($contact->full_name); ?></h6>
                <?php if($contact->company): ?><div style="color:var(--pwa-muted);font-size:12px;"><?php echo htmlspecialchars($contact->company); ?></div><?php endif; ?>
            </div>
            <div class="pwa-card" style="padding:0;">
                <?php if($contact->phone): ?><a href="tel:<?php echo htmlspecialchars($contact->phone); ?>" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);"><div class="pwa-list-icon" style="background:rgba(6,214,160,0.1);color:var(--pwa-success);"><i class="bi bi-telephone"></i></div><div style="flex:1;"><div class="pwa-list-title" dir="ltr"><?php echo htmlspecialchars($contact->phone); ?></div><div class="pwa-list-sub">تماس</div></div><i class="bi bi-chevron-left" style="color:var(--pwa-muted);"></i></a><?php endif; ?>
                <?php if($contact->email): ?><a href="mailto:<?php echo htmlspecialchars($contact->email); ?>" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);"><div class="pwa-list-icon" style="background:rgba(67,97,238,0.1);color:var(--pwa-primary);"><i class="bi bi-envelope"></i></div><div style="flex:1;"><div class="pwa-list-title" dir="ltr"><?php echo htmlspecialchars($contact->email); ?></div><div class="pwa-list-sub">ایمیل</div></div><i class="bi bi-chevron-left" style="color:var(--pwa-muted);"></i></a><?php endif; ?>
                <?php if($contact->national_code): ?><div class="pwa-list-item"><div class="pwa-list-icon" style="background:rgba(255,209,102,0.1);color:var(--pwa-warning);"><i class="bi bi-card-text"></i></div><div style="flex:1;"><div class="pwa-list-title" dir="ltr"><?php echo htmlspecialchars($contact->national_code); ?></div><div class="pwa-list-sub">کد ملی</div></div></div><?php endif; ?>
            </div>
            <?php if($contact->address || $contact->notes): ?><div class="pwa-card"><?php if($contact->address): ?><div style="margin-bottom:8px;"><span style="font-size:11px;color:var(--pwa-muted);">آدرس:</span><div style="font-size:13px;"><?php echo htmlspecialchars($contact->address); ?></div></div><?php endif; ?><?php if($contact->notes): ?><div><span style="font-size:11px;color:var(--pwa-muted);">یادداشت:</span><div style="font-size:13px;"><?php echo nl2br(htmlspecialchars($contact->notes)); ?></div></div><?php endif; ?></div><?php endif; ?>
            <?php if(!empty($deals)): ?><div class="pwa-card"><div class="pwa-section-title">معاملات</div>
                <?php foreach($deals as $d): ?><a href="<?php echo $config['url']; ?>/pwa/deals/view/<?php echo $d->id; ?>" class="pwa-list-item" style="text-decoration:none;color:var(--pwa-text);"><div class="pwa-list-icon" style="background:rgba(67,97,238,0.1);color:var(--pwa-primary);"><i class="bi bi-briefcase"></i></div><div style="flex:1;"><div class="pwa-list-title"><?php echo htmlspecialchars($d->title); ?></div><div class="pwa-list-sub"><?php echo $d->stage_name?htmlspecialchars($d->stage_name):''; ?></div></div><div style="font-size:12px;font-weight:700;"><?php echo number_format($d->amount); ?></div></a><?php endforeach; ?></div>
            <?php endif; ?>
        </div>
        <?php $this->renderBottomNav($config, 'contacts');
        });
        $pageTitle = 'مشاهده مخاطب';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function contactEdit(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $id = $params['id'];
        $c = $db->fetch("SELECT * FROM contacts WHERE id = ?", [$id]);
        if (!$c) { header('Location: ' . $config['url'] . '/pwa/contacts'); exit; }
$pwaContent = $this->renderSection(function() use ($config, $c, $id) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/contacts/view/<?php echo $id; ?>" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5 style="font-size:15px;">ویرایش مخاطب</h5></div></div>
        <div class="pwa-content pwa-fade">
            <form method="POST" action="<?php echo $config['url']; ?>/pwa/contacts/update/<?php echo $id; ?>">
                <div style="margin-bottom:14px;"><label class="pwa-label">نام کامل *</label><input type="text" name="full_name" class="pwa-input" required value="<?php echo htmlspecialchars($c->full_name); ?>"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">تلفن</label><input type="text" name="phone" class="pwa-input" dir="ltr" value="<?php echo htmlspecialchars($c->phone ?? ''); ?>"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">ایمیل</label><input type="email" name="email" class="pwa-input" dir="ltr" value="<?php echo htmlspecialchars($c->email ?? ''); ?>"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">شرکت</label><input type="text" name="company" class="pwa-input" value="<?php echo htmlspecialchars($c->company ?? ''); ?>"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">کد ملی</label><input type="text" name="national_code" class="pwa-input" dir="ltr" value="<?php echo htmlspecialchars($c->national_code ?? ''); ?>"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">شماره پاسپورت</label><input type="text" name="passport_number" class="pwa-input" dir="ltr" value="<?php echo htmlspecialchars($c->passport_number ?? ''); ?>"></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">آدرس</label><textarea name="address" class="pwa-input" rows="2"><?php echo htmlspecialchars($c->address ?? ''); ?></textarea></div>
                <div style="margin-bottom:14px;"><label class="pwa-label">یادداشت</label><textarea name="notes" class="pwa-input" rows="2"><?php echo htmlspecialchars($c->notes ?? ''); ?></textarea></div>
                <button type="submit" class="pwa-btn pwa-btn-primary pwa-btn-block"><i class="bi bi-check-circle"></i> بروزرسانی</button>
            </form>
        </div>
        <?php $this->renderBottomNav($config, 'contacts');
        });
        $pageTitle = 'ویرایش مخاطب';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function contactUpdate(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $id = $params['id'];
        $db = \Core\Database::getInstance();
        $db->query("UPDATE contacts SET full_name=?, phone=?, email=?, company=?, national_code=?, passport_number=?, address=?, notes=?, updated_at=NOW() WHERE id=?", [
            trim($_POST['full_name'] ?? ''), trim($_POST['phone'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['company'] ?? ''), trim($_POST['national_code'] ?? ''), trim($_POST['passport_number'] ?? ''), $_POST['address'] ?? '', $_POST['notes'] ?? '', $id
        ]);
        header('Location: ' . $GLOBALS['app_config']['url'] . '/pwa/contacts/view/' . $id);
        exit;
    }

    // ========== ACTIVITIES ==========

    public function activities(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $userId = \Core\Auth::id();
        $activities = $db->fetchAll("SELECT al.*, d.title as deal_title FROM deal_activities al LEFT JOIN deals d ON al.deal_id = d.id WHERE al.user_id = ? ORDER BY al.activity_date DESC LIMIT 50", [$userId]);
        $pwaContent = $this->renderSection(function() use ($config, $activities) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/app" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5>فعالیت‌ها</h5></div>
        </div></div>
        <div class="pwa-content pwa-fade">
            <?php if (empty($activities)): ?><div style="text-align:center;padding:40px;color:var(--pwa-muted);"><i class="bi bi-calendar-check" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i><p>فعالیتی یافت نشد</p></div>
            <?php else: foreach ($activities as $act): ?>
            <?php $isOverdue = !$act->is_done && $act->activity_date && strtotime($act->activity_date) < time(); ?>
            <div class="pwa-list-item" style="<?php echo $act->is_done ? 'opacity:0.5;' : ''; ?>">
                <form method="POST" action="<?php echo $config['url']; ?>/pwa/activities/toggle/<?php echo $act->id; ?>" style="display:inline;">
                    <button type="submit" style="background:none;border:none;font-size:20px;cursor:pointer;padding:0;"><?php echo $act->is_done ? '✅' : '⬜'; ?></button>
                </form>
                <div style="flex:1;min-width:0;">
                    <div class="pwa-list-title" style="<?php echo $act->is_done ? 'text-decoration:line-through;' : ''; ?>"><?php echo htmlspecialchars(mb_substr($act->subject ?? '-', 0, 35)); ?></div>
                    <div class="pwa-list-sub"><?php if($act->deal_title): ?><i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars(mb_substr($act->deal_title, 0, 25)); endif; ?></div>
                </div>
                <div class="pwa-list-right">
                    <small style="color:<?php echo $isOverdue ? 'var(--pwa-danger)' : 'var(--pwa-muted)'; ?>;font-size:10px;font-weight:<?php echo $isOverdue ? '700' : '400'; ?>;">
                        <?php echo $act->activity_date ? \Core\JDate::displayDate($act->activity_date) : '-'; ?>
                    </small>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <?php $this->renderBottomNav($config, 'activities');
        });
        $pageTitle = 'فعالیت‌ها';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function activityToggle(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $db = \Core\Database::getInstance();
        $id = (int)($params['id'] ?? 0);
        if ($id) {
            $act = $db->fetch("SELECT * FROM deal_activities WHERE id = ?", [$id]);
            if ($act) {
                $newStatus = $act->is_done ? 0 : 1;
                $db->query("UPDATE deal_activities SET is_done = ? WHERE id = ?", [$newStatus, $id]);
            }
        }
        $back = $_SERVER['HTTP_REFERER'] ?? ($GLOBALS['app_config']['url'] . '/pwa/activities');
        header('Location: ' . $back);
        exit;
    }

    // ========== CALENDAR ==========

    public function calendar(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $userId = \Core\Auth::id();
        $activities = $db->fetchAll("SELECT al.*, d.title as deal_title FROM deal_activities al LEFT JOIN deals d ON al.deal_id = d.id WHERE al.user_id = ? AND al.activity_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY al.activity_date ASC LIMIT 30", [$userId]);
        $pwaContent = $this->renderSection(function() use ($config, $activities) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/app" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5>تقویم</h5></div></div>
        <div class="pwa-content pwa-fade">
            <?php if (empty($activities)): ?><div style="text-align:center;padding:40px;color:var(--pwa-muted);"><i class="bi bi-calendar3" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i><p>رویدادی در ۷ روز آینده ندارید</p></div>
            <?php else: $currentDate = ''; foreach ($activities as $act): $actDate = $act->activity_date ? date('Y-m-d', strtotime($act->activity_date)) : ''; if ($actDate !== $currentDate): $currentDate = $actDate; ?>
            <div class="pwa-section-title" style="margin-top:12px;"><i class="bi bi-calendar-event me-1"></i><?php echo $act->activity_date ? \Core\JDate::displayDate($act->activity_date) : '-'; ?></div>
            <?php endif; ?>
            <div class="pwa-list-item">
                <div class="pwa-list-icon" style="background:<?php echo $act->is_done ? 'rgba(6,214,160,0.1)' : 'rgba(255,209,102,0.1)'; ?>;color:<?php echo $act->is_done ? 'var(--pwa-success)' : 'var(--pwa-warning)'; ?>;">
                    <i class="bi bi-<?php echo $act->type === 'call' ? 'telephone' : ($act->type === 'meeting' ? 'people' : 'pin'); ?>"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="pwa-list-title" style="<?php echo $act->is_done ? 'text-decoration:line-through;opacity:0.5;' : ''; ?>"><?php echo htmlspecialchars(mb_substr($act->subject ?? '-', 0, 30)); ?></div>
                    <div class="pwa-list-sub"><?php echo $act->deal_title ? htmlspecialchars(mb_substr($act->deal_title, 0, 25)) : ''; ?></div>
                </div>
                <div class="pwa-list-right">
                    <small style="color:var(--pwa-muted);font-size:10px;"><?php echo $act->activity_date ? date('H:i', strtotime($act->activity_date)) : ''; ?></small>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <?php $this->renderBottomNav($config, 'more');
        });
        $pageTitle = 'تقویم';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function calendarEvents(array $params = []): void
    {
        header('Content-Type: application/json');
        \Core\Auth::requireAuth();
        $db = \Core\Database::getInstance();
        $events = $db->fetchAll("SELECT al.id, al.subject as title, al.activity_date as start, al.type, al.is_done, d.title as deal_title FROM deal_activities al LEFT JOIN deals d ON al.deal_id = d.id WHERE al.user_id = ? AND al.activity_date IS NOT NULL", [\Core\Auth::id()]);
        echo json_encode($events);
        exit;
    }

    // ========== PIPELINES ==========

    public function pipelines(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $pipelines = $db->fetchAll("SELECT p.*, (SELECT COUNT(*) FROM deals d WHERE d.pipeline_id = p.id AND d.is_won = 0 AND d.is_lost = 0) as deals_count FROM pipelines p WHERE p.is_active = 1 ORDER BY is_default DESC");
        $pwaContent = $this->renderSection(function() use ($config, $pipelines) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/app" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5>پایپ لاین‌ها</h5></div></div>
        <div class="pwa-content pwa-fade">
            <?php foreach ($pipelines as $p): ?>
            <a href="<?php echo $config['url']; ?>/pwa/pipeline/kanban/<?php echo $p->id; ?>" style="text-decoration:none;color:var(--pwa-text);">
            <div class="pwa-card"><div class="d-flex justify-content-between align-items-center">
                <div><div style="font-weight:700;font-size:14px;"><?php echo htmlspecialchars($p->name); ?></div><div style="color:var(--pwa-muted);font-size:11px;"><?php echo $p->deals_count; ?> معامله فعال</div></div>
                <i class="bi bi-chevron-left" style="color:var(--pwa-muted);"></i>
            </div></div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php $this->renderBottomNav($config, 'more');
        });
        $pageTitle = 'پایپ لاین‌ها';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    public function pipelineKanban(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $pipelineId = (int)($params['id'] ?? 0);
        $pipeline = $db->fetch("SELECT * FROM pipelines WHERE id = ?", [$pipelineId]);
        if (!$pipeline) { header('Location: ' . $config['url'] . '/pwa/pipelines'); exit; }
        $stages = $db->fetchAll("SELECT * FROM stages WHERE pipeline_id = ? ORDER BY order_index", [$pipelineId]);
        $pwaContent = $this->renderSection(function() use ($config, $pipeline, $stages, $db) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/pipelines" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5><?php echo htmlspecialchars($pipeline->name); ?></h5></div></div>
        <div class="pwa-content pwa-fade" style="overflow-x:auto;white-space:nowrap;padding-bottom:80px;">
            <div style="display:inline-flex;gap:10px;align-items:flex-start;">
            <?php foreach ($stages as $stage): $stageDeals = $db->fetchAll("SELECT d.*, c.full_name as contact_name FROM deals d LEFT JOIN contacts c ON d.contact_id = c.id WHERE d.stage_id = ? AND d.is_won = 0 AND d.is_lost = 0 ORDER BY d.created_at DESC LIMIT 10", [$stage->id]); ?>
            <div style="display:inline-block;vertical-align:top;width:230px;flex-shrink:0;">
                <div style="background:var(--pwa-card);border-radius:12px;padding:10px;border-top:3px solid <?php echo $stage->color ?? '#4361ee'; ?>;">
                    <div style="font-weight:700;font-size:12px;margin-bottom:8px;"><?php echo htmlspecialchars($stage->name); ?> <span style="color:var(--pwa-muted);">(<?php echo count($stageDeals); ?>)</span></div>
                    <?php foreach ($stageDeals as $d): ?>
                    <a href="<?php echo $config['url']; ?>/pwa/deals/view/<?php echo $d->id; ?>" style="text-decoration:none;color:var(--pwa-text);">
                    <div style="background:var(--pwa-bg);border-radius:10px;padding:10px;margin-bottom:6px;border:1px solid var(--pwa-border);">
                        <div style="font-size:12px;font-weight:600;margin-bottom:4px;"><?php echo htmlspecialchars(mb_substr($d->title,0,25)); ?></div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <small style="color:var(--pwa-muted);font-size:10px;"><?php echo $d->contact_name ? htmlspecialchars(mb_substr($d->contact_name,0,15)) : ''; ?></small>
                            <span style="font-size:11px;font-weight:700;color:var(--pwa-primary);"><?php echo number_format($d->amount); ?></span>
                        </div>
                    </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php $this->renderBottomNav($config, 'more');
        });
        $pageTitle = htmlspecialchars($pipeline->name);
        include __DIR__ . '/../views/pwa/layout.php';
    }

    // ========== STATIC FILE SERVING ==========

    public function serviceWorker(array $params = []): void
    {
        $swPath = __DIR__ . '/../public/sw.js';
        if (!file_exists($swPath)) { http_response_code(404); exit; }
        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: /');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        readfile($swPath);
        exit;
    }

    public function manifest(array $params = []): void
    {
        $manifestPath = __DIR__ . '/../public/manifest.json';
        if (!file_exists($manifestPath)) { http_response_code(404); exit; }
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: no-cache');
        header('Access-Control-Allow-Origin: *');
        readfile($manifestPath);
        exit;
    }

    public function offline(array $params = []): void
    {
        $offlinePath = __DIR__ . '/../public/offline.html';
        if (!file_exists($offlinePath)) { http_response_code(404); exit; }
        header('Content-Type: text/html; charset=utf-8');
        readfile($offlinePath);
        exit;
    }

    public function icon(array $params = []): void
    {
        $filename = $params['filename'] ?? '';
        $iconPath = __DIR__ . '/../public/assets/icons/' . basename($filename);
        if (!file_exists($iconPath)) { http_response_code(404); exit; }
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeTypes = ['svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','ico'=>'image/x-icon'];
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=604800');
        header('Access-Control-Allow-Origin: *');
        readfile($iconPath);
        exit;
    }

    // ========== PUSH NOTIFICATIONS ==========

    public function subscribe(array $params = []): void
    {
        header('Content-Type: application/json');
        if (!\Core\Auth::check()) { echo json_encode(['success'=>false]); exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['endpoint'])) { echo json_encode(['success'=>false]); exit; }
        $db = \Core\Database::getInstance();
        $userId = \Core\Auth::id();
        try {
            $db->query("CREATE TABLE IF NOT EXISTS push_subscriptions (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, endpoint TEXT NOT NULL, p256dh VARCHAR(255) DEFAULT '', auth VARCHAR(255) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_user_id (user_id), INDEX idx_endpoint (endpoint(255))) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->query("INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE p256dh=VALUES(p256dh), auth=VALUES(auth), updated_at=NOW()", [$userId, $input['endpoint'], $input['keys']['p256dh'] ?? '', $input['keys']['auth'] ?? '']);
        } catch (\Exception $e) {}
        echo json_encode(['success'=>true]);
        exit;
    }

    public function unsubscribe(array $params = []): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['endpoint'])) {
            try { \Core\Database::getInstance()->query("DELETE FROM push_subscriptions WHERE endpoint = ?", [$input['endpoint']]); } catch (\Exception $e) {}
        }
        echo json_encode(['success'=>true]);
        exit;
    }

    // ========== NOTIFICATIONS ==========

    public function notifications(array $params = []): void
    {
        \Core\Auth::requireAuth();
        $config = $GLOBALS['app_config'];
        $db = \Core\Database::getInstance();
        $notifications = $db->fetchAll("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30", [\Core\Auth::id()]);
        $pwaContent = $this->renderSection(function() use ($config, $notifications) { ?>
        <div class="pwa-header"><div class="d-flex align-items-center gap-2"><a href="<?php echo $config['url']; ?>/pwa/more" style="color:var(--pwa-text);text-decoration:none;"><i class="bi bi-arrow-right"></i></a><h5>اعلان‌ها</h5></div></div>
        <div class="pwa-content pwa-fade">
            <?php if (empty($notifications)): ?><div style="text-align:center;padding:40px;color:var(--pwa-muted);"><i class="bi bi-bell" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i><p>اعلان جدیدی ندارید</p></div>
            <?php else: foreach ($notifications as $n): ?>
            <div class="pwa-list-item" style="<?php echo isset($n->is_read) && $n->is_read ? 'opacity:0.5;' : ''; ?>">
                <div class="pwa-list-icon" style="background:rgba(67,97,238,0.1);color:var(--pwa-primary);"><i class="bi bi-bell"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="pwa-list-title"><?php echo htmlspecialchars(mb_substr($n->message ?? $n->title ?? '-', 0, 40)); ?></div>
                    <div class="pwa-list-sub"><?php echo $n->created_at ? \Core\JDate::displayDate($n->created_at) : ''; ?></div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <?php $this->renderBottomNav($config, 'more');
        });
        $pageTitle = 'اعلان‌ها';
        include __DIR__ . '/../views/pwa/layout.php';
    }

    // ========== HELPERS ==========

    private function renderSection(callable $fn): string
    {
        ob_start();
        $fn();
        return ob_get_clean();
    }

    private function renderBottomNav($config, $active): void
    {
        $links = [
            ['url' => '/pwa/app', 'icon' => 'house-door', 'label' => 'خانه', 'key' => 'home'],
            ['url' => '/pwa/deals', 'icon' => 'briefcase', 'label' => 'معاملات', 'key' => 'deals'],
            ['url' => '/pwa/contacts', 'icon' => 'people', 'label' => 'مخاطبان', 'key' => 'contacts'],
            ['url' => '/pwa/activities', 'icon' => 'calendar-check', 'label' => 'فعالیت‌ها', 'key' => 'activities'],
            ['url' => '/pwa/more', 'icon' => 'three-dots', 'label' => 'بیشتر', 'key' => 'more'],
        ];
        echo '<nav class="pwa-bottom-nav">';
        foreach ($links as $link) {
            $isActive = $link['key'] === $active ? ' class="active"' : '';
            echo '<a href="' . $config['url'] . $link['url'] . '"' . $isActive . '><i class="bi bi-' . $link['icon'] . ($link['key'] === $active ? '-fill' : '') . '"></i><span>' . $link['label'] . '</span></a>';
        }
        echo '</nav>';
    }
}
