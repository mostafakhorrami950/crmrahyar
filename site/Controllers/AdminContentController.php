<?php
namespace Site\Controllers;

use Shared\Core\Database;
use Shared\Core\Config;

class AdminContentController extends AdminController
{
    private Database $db;
    private Config $config;

    public function __construct()
    {
        parent::__construct();
        $c = \Shared\Core\Container::getInstance();
        $this->db = $c->make(Database::class);
        $this->config = $c->make(Config::class);
    }

    // ==================== BLOG ====================
    public function blogIndex(array $params = []): void
    {
        $this->requireAdmin();
        $posts = $this->db->fetchAll("SELECT * FROM site_blog_posts WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 100");
        $this->renderAdmin('admin/blog/index', ['posts' => $posts, 'meta' => ['title' => 'مدیریت بلاگ']]);
    }

    public function blogCreate(array $params = []): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'slug' => !empty(trim($_POST['slug'] ?? '')) ? trim($_POST['slug']) : $this->slugify($_POST['title'] ?? ''),
                'excerpt' => trim($_POST['excerpt'] ?? ''),
                'content' => $_POST['content'] ?? '',
                'meta_title' => trim($_POST['meta_title'] ?? ''),
                'meta_description' => trim($_POST['meta_description'] ?? ''),
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
                'published_at' => isset($_POST['is_published']) ? date('Y-m-d H:i:s') : null,
                'author_id' => $_SESSION['user_id'] ?? 1,
            ];
            // Optional SEO columns (may not exist yet)
            foreach (['focus_keyword', 'featured_image', 'image_alt'] as $col) {
                if (isset($_POST[$col])) $data[$col] = trim($_POST[$col]);
            }
            try {
                $this->db->insert('site_blog_posts', $data);
            } catch (\Exception $e) {
                // Remove optional columns and retry
                unset($data['focus_keyword'], $data['featured_image'], $data['image_alt']);
                $this->db->insert('site_blog_posts', $data);
            }
            header('Location: /admin/blog?updated=1');
            exit;
        }
        $this->renderAdmin('admin/blog/form', ['post' => null, 'meta' => ['title' => 'مقاله جدید']]);
    }

    public function blogEdit(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        $post = $this->db->fetch("SELECT * FROM site_blog_posts WHERE id = :id AND deleted_at IS NULL", [':id' => $id]);
        if (!$post) { header('Location: /admin/blog'); exit; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'excerpt' => trim($_POST['excerpt'] ?? ''),
                'content' => $_POST['content'] ?? '',
                'meta_title' => trim($_POST['meta_title'] ?? ''),
                'meta_description' => trim($_POST['meta_description'] ?? ''),
                'focus_keyword' => trim($_POST['focus_keyword'] ?? ''),
                'featured_image' => trim($_POST['featured_image'] ?? ''),
                'image_alt' => trim($_POST['image_alt'] ?? ''),
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
            ];
            if (!empty($_POST['slug'])) $data['slug'] = trim($_POST['slug']);
            if (isset($_POST['is_published']) && !$post->published_at) $data['published_at'] = date('Y-m-d H:i:s');
            try {
                $this->db->update('site_blog_posts', $data, 'id = :id', [':id' => $id]);
            } catch (\Exception $e) {
                // Remove optional columns if they don't exist and retry
                unset($data['focus_keyword'], $data['featured_image'], $data['image_alt']);
                $this->db->update('site_blog_posts', $data, 'id = :id', [':id' => $id]);
            }
            header('Location: /admin/blog?updated=1');
            exit;
        }
        $this->renderAdmin('admin/blog/form', ['post' => $post, 'meta' => ['title' => 'ویرایش مقاله']]);
    }

    public function blogImageUpload(array $params = []): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
            echo json_encode(['success' => false, 'message' => 'فایل ارسال نشد']);
            exit;
        }
        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'فرمت مجاز نیست']);
            exit;
        }
        $dir = __DIR__ . '/../../uploads/blog/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $filename = 'blog_' . time() . '_' . rand(100, 999) . '.' . $ext;
        $path = 'uploads/blog/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            echo json_encode(['success' => true, 'path' => '/' . $path]);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطا در آپلود']);
        }
        exit;
    }

    public function blogDelete(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        $this->db->update('site_blog_posts', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $id]);
        header('Location: /admin/blog?updated=1');
        exit;
    }

    // ==================== PAGES ====================
    public function pagesIndex(array $params = []): void
    {
        $this->requireAdmin();
        $pages = [];
        try { $pages = $this->db->fetchAll("SELECT * FROM site_pages WHERE deleted_at IS NULL ORDER BY id DESC"); } catch (\Exception $e) {}
        $defaults = ['contact' => 'تماس با ما', 'about' => 'درباره ما', 'terms' => 'قوانین', 'privacy' => 'حریم خصوصی', 'faq' => 'سوالات متداول'];
        $existingSlugs = array_column($pages, 'slug');
        foreach ($defaults as $slug => $title) {
            if (!in_array($slug, $existingSlugs)) {
                $pages[] = (object)['id' => 0, 'slug' => $slug, 'title' => $title, 'is_active' => 1, 'is_default' => true];
            }
        }
        $this->renderAdmin('admin/pages/index', ['pages' => $pages, 'meta' => ['title' => 'مدیریت صفحات']]);
    }

    public function pagesEdit(array $params = []): void
    {
        $this->requireAdmin();
        $slug = $params['slug'] ?? '';
        $page = null;
        try { $page = $this->db->fetch("SELECT * FROM site_pages WHERE slug = :s", [':s' => $slug]); } catch (\Exception $e) {}
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'slug' => $slug,
                'content' => $_POST['content'] ?? '',
                'meta_title' => trim($_POST['meta_title'] ?? ''),
                'meta_description' => trim($_POST['meta_description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];
            if ($page) {
                $this->db->update('site_pages', $data, 'id = :id', [':id' => $page->id]);
            } else {
                $this->db->insert('site_pages', $data);
            }
            header('Location: /admin/pages?updated=1');
            exit;
        }
        $this->renderAdmin('admin/pages/form', ['page' => $page, 'slug' => $slug, 'meta' => ['title' => 'ویرایش صفحه']]);
    }

    // ==================== SETTINGS ====================
    public function settingsIndex(array $params = []): void
    {
        $this->requireAdmin();
        $settings = [];
        try {
            $rows = $this->db->fetchAll("SELECT * FROM site_settings ORDER BY id");
            foreach ($rows as $row) {
                $k = $row->setting_key ?? $row->key ?? '';
                $settings[$k] = $row->value ?? $row->setting_value ?? '';
            }
        } catch (\Exception $e) {}
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fields = ['site_title', 'site_description', 'site_phone', 'site_email', 'site_address', 'site_logo', 'company_name', 'footer_text', 'openrouter_api_key', 'openrouter_model'];
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $val = trim($_POST[$field]);
                    $exists = $this->db->fetch("SELECT id FROM site_settings WHERE `setting_key` = :k", [':k' => $field]);
                    if ($exists) {
                        $this->db->update('site_settings', ['setting_value' => $val], '`setting_key` = :k', [':k' => $field]);
                    } else {
                        $this->db->insert('site_settings', ['setting_key' => $field, 'setting_value' => $val]);
                    }
                }
            }
            header('Location: /admin/settings?updated=1');
            exit;
        }
        $this->renderAdmin('admin/settings/index', ['settings' => $settings, 'meta' => ['title' => 'تنظیمات سایت']]);
    }

    // ==================== SEO ====================
    public function seoIndex(array $params = []): void
    {
        $this->requireAdmin();
        $redirects = [];
        try { $redirects = $this->db->fetchAll("SELECT * FROM site_seo_redirects ORDER BY id DESC LIMIT 50"); } catch (\Exception $e) {}
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_redirect'])) {
            $this->db->insert('site_seo_redirects', [
                'from_url' => trim($_POST['from_url'] ?? ''),
                'to_url' => trim($_POST['to_url'] ?? ''),
                'redirect_type' => (int)($_POST['redirect_type'] ?? 301),
                'is_active' => 1,
            ]);
            header('Location: /admin/seo?updated=1');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_keyword'])) {
            try {
                $keyword = trim($_POST['keyword'] ?? '');
                $kwSlug = trim($_POST['keyword_slug'] ?? '');
                if (empty($kwSlug) && !empty($keyword)) {
                    $kwSlug = $this->slugify($keyword);
                }
                $this->db->insert('site_seo_keywords', [
                    'keyword' => $keyword,
                    'keyword_slug' => $kwSlug,
                    'target_url' => trim($_POST['target_url'] ?? ''),
                    'description' => trim($_POST['keyword_description'] ?? ''),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {
                // Table may not exist yet
            }
            header('Location: /admin/seo?updated=1');
            exit;
        }
        $keywords = [];
        try { $keywords = $this->db->fetchAll("SELECT * FROM site_seo_keywords ORDER BY id DESC LIMIT 50"); } catch (\Exception $e) {}
        $this->renderAdmin('admin/seo/index', ['redirects' => $redirects, 'keywords' => $keywords, 'meta' => ['title' => 'سئو']]);
    }

    public function seoDeleteRedirect(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        try { $this->db->delete('site_seo_redirects', 'id = :id', [':id' => $id]); } catch (\Exception $e) {}
        header('Location: /admin/seo?updated=1');
        exit;
    }

    public function seoGenerate(array $params = []): void
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $keyword = trim($input['keyword'] ?? '');
        $type = trim($input['type'] ?? 'meta');
        if (empty($keyword)) {
            echo json_encode(['success' => false, 'message' => 'کلمه کلیدی الزامی است']);
            exit;
        }
        $c = \Shared\Core\Container::getInstance();
        $ai = new \Shared\Services\OpenRouterService($this->db);
        $result = $ai->generateSEOContent($keyword, $type);
        echo json_encode($result);
        exit;
    }

    public function seoDeleteKeyword(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        try { $this->db->delete('site_seo_keywords', 'id = :id', [':id' => $id]); } catch (\Exception $e) {}
        header('Location: /admin/seo?updated=1');
        exit;
    }

    // ==================== CITIES ====================
    public function citiesIndex(array $params = []): void
    {
        $this->requireAdmin();
        $cities = $this->db->fetchAll("SELECT * FROM site_cities ORDER BY name");
        $this->renderAdmin('admin/cities/index', ['cities' => $cities, 'meta' => ['title' => 'مدیریت شهرها']]);
    }

    public function citiesEdit(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        $city = $id ? $this->db->fetch("SELECT * FROM site_cities WHERE id = :id", [':id' => $id]) : null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'slug' => trim($_POST['slug'] ?? '') ?: $this->slugify($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];
            if ($city) {
                $this->db->update('site_cities', $data, 'id = :id', [':id' => $id]);
            } else {
                $this->db->insert('site_cities', $data);
            }
            header('Location: /admin/cities?updated=1');
            exit;
        }
        $this->renderAdmin('admin/cities/form', ['city' => $city, 'meta' => ['title' => $city ? 'ویرایش شهر' : 'شهر جدید']]);
    }

    // ==================== FAQs ====================
    public function faqsIndex(array $params = []): void
    {
        $this->requireAdmin();
        $faqs = [];
        try { $faqs = $this->db->fetchAll("SELECT * FROM site_faqs ORDER BY sort_order, id DESC LIMIT 100"); } catch (\Exception $e) {}
        $this->renderAdmin('admin/faqs/index', ['faqs' => $faqs, 'meta' => ['title' => 'سوالات متداول']]);
    }

    public function faqsEdit(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        $faq = $id ? $this->db->fetch("SELECT * FROM site_faqs WHERE id = :id", [':id' => $id]) : null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'question' => trim($_POST['question'] ?? ''),
                'answer' => $_POST['answer'] ?? '',
                'entity_type' => trim($_POST['entity_type'] ?? 'global'),
                'entity_id' => (int)($_POST['entity_id'] ?? 0),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];
            if ($faq) {
                $this->db->update('site_faqs', $data, 'id = :id', [':id' => $id]);
            } else {
                $this->db->insert('site_faqs', $data);
            }
            header('Location: /admin/faqs?updated=1');
            exit;
        }
        $this->renderAdmin('admin/faqs/form', ['faq' => $faq, 'meta' => ['title' => $faq ? 'ویرایش سوال' : 'سوال جدید']]);
    }

    public function faqsDelete(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        try { $this->db->delete('site_faqs', 'id = :id', [':id' => $id]); } catch (\Exception $e) {}
        header('Location: /admin/faqs?updated=1');
        exit;
    }

    // ==================== BOOKINGS ====================
    public function bookingsIndex(array $params = []): void
    {
        $this->requireAdmin();
        $bookings = [];
        try {
            $bookings = $this->db->fetchAll("SELECT b.*, g.full_name as guest_name, g.phone as guest_phone FROM site_bookings b LEFT JOIN site_booking_guests g ON b.id = g.booking_id WHERE b.deleted_at IS NULL ORDER BY b.id DESC LIMIT 100");
        } catch (\Exception $e) {}
        $this->renderAdmin('admin/bookings/index', ['bookings' => $bookings, 'meta' => ['title' => 'مدیریت رزروها']]);
    }

    // ==================== MEDIA ====================
    public function mediaUpload(array $params = []): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
            echo json_encode(['success' => false, 'message' => 'فایل ارسال نشد']);
            exit;
        }

        $file = $_FILES['file'];
        $entityType = $_POST['entity_type'] ?? 'hotel';
        $entityId = (int)($_POST['entity_id'] ?? 0);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'فرمت فایل مجاز نیست']);
            exit;
        }

        $dir = __DIR__ . '/../../uploads/' . $entityType . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = $entityType . '_' . $entityId . '_' . time() . '.' . $ext;
        $path = 'uploads/' . $entityType . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            $mediaId = $this->db->insert('site_media', [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'path_original' => $path,
                'alt_text' => trim($_POST['alt_text'] ?? ''),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'uploaded_by' => $_SESSION['user_id'] ?? 1,
            ]);
            echo json_encode(['success' => true, 'media_id' => $mediaId, 'path' => '/' . $path]);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطا در آپلود']);
        }
        exit;
    }

    public function mediaDelete(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        $media = $this->db->fetch("SELECT * FROM site_media WHERE id = :id", [':id' => $id]);
        if ($media) {
            $fullPath = __DIR__ . '/../../' . $media->path_original;
            if (file_exists($fullPath)) unlink($fullPath);
            $this->db->delete('site_media', 'id = :id', [':id' => $id]);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    private function slugify(string $text): string
    {
        $text = trim($text);
        // Persian to English transliteration map
        $persianMap = [
            'آ' => 'a', 'ا' => 'a', 'ب' => 'b', 'پ' => 'p', 'ت' => 't', 'ث' => 's',
            'ج' => 'j', 'چ' => 'ch', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'z',
            'ر' => 'r', 'ز' => 'z', 'ژ' => 'zh', 'س' => 's', 'ش' => 'sh', 'ص' => 's',
            'ض' => 'z', 'ط' => 't', 'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f',
            'ق' => 'gh', 'ک' => 'k', 'گ' => 'g', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'و' => 'v', 'ه' => 'h', 'ی' => 'y', 'ة' => 'h', 'ئ' => 'y', 'ؤ' => 'v',
            '0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4',
            '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9',
        ];
        $result = '';
        $len = mb_strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1);
            if (isset($persianMap[$char])) {
                $result .= $persianMap[$char];
            } elseif (preg_match('/[a-zA-Z0-9]/', $char)) {
                $result .= $char;
            } elseif (in_array($char, [' ', '-', '_', '‌'])) {
                $result .= '-';
            }
        }
        $result = preg_replace('/\-+/', '-', $result);
        $result = trim($result, '-');
        return $result ?: 'post-' . time();
    }
}