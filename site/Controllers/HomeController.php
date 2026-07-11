<?php
namespace Site\Controllers;

use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Core\Logger;
use Shared\Services\SEOService;
use Shared\Services\CacheService;
use Shared\Repositories\HotelRepository;

class HomeController
{
    private Database $db;
    private Config $config;
    private SEOService $seo;
    private CacheService $cache;
    private HotelRepository $hotelRepo;

    public function __construct()
    {
        $container = \Shared\Core\Container::getInstance();
        $this->db = $container->make(Database::class);
        $this->config = $container->make(Config::class);
        $this->seo = $container->make(SEOService::class);
        $this->cache = new CacheService();
        $this->hotelRepo = new HotelRepository($this->db);
    }

    public function index(array $params = []): void
    {
        $featuredHotels = $this->cache->get('home:featured');
        if (!$featuredHotels) {
            $featuredHotels = $this->hotelRepo->findFeatured(8);
            $this->cache->set('home:featured', $featuredHotels, 3600, ['hotels', 'home']);
        }

        $meta = [
            'title' => $this->config->get('site_title', 'رزرو هتل در مشهد'),
            'description' => $this->config->get('site_description', 'رزرو آنلاین هتل با بهترین قیمت'),
            'canonical' => $this->config->url(),
        ];

        $this->render('home/index', [
            'meta' => $meta,
            'featuredHotels' => $featuredHotels,
        ]);
    }

    public function blogIndex(array $params = []): void
    {
        $posts = $this->db->fetchAll(
            "SELECT * FROM site_blog_posts WHERE is_published = 1 AND deleted_at IS NULL ORDER BY published_at DESC LIMIT 20"
        );
        $this->render('blog/index', ['posts' => $posts, 'meta' => ['title' => 'بلاگ', 'description' => 'مقالات و اخبار']]);
    }

    public function blogShow(array $params = []): void
    {
        $slug = $params['slug'] ?? '';
        $post = $this->db->fetch("SELECT * FROM site_blog_posts WHERE slug = :s AND is_published = 1 AND deleted_at IS NULL", [':s' => $slug]);
        if (!$post) { $this->notFound(); return; }
        $this->render('blog/show', ['post' => $post, 'meta' => $this->seo->generateMeta('blog_post', $post->id)]);
    }

    public function page(array $params = []): void
    {
        $path = $_SERVER['REQUEST_URI'];
        $slug = trim(parse_url($path, PHP_URL_PATH), '/');

        // Try database first
        $page = null;
        try {
            $page = $this->db->fetch("SELECT * FROM site_pages WHERE slug = :s AND is_active = 1 AND deleted_at IS NULL", [':s' => $slug]);
        } catch (\Exception $e) {}

        // Fallback for common pages
        if (!$page) {
            $defaults = [
                'contact' => ['title' => 'تماس با ما', 'content' => '<h2>تماس با ما</h2><p>تلفن: ۰۵۱-۱۲۳۴۵۶۷۸</p><p>ایمیل: info@rahyartravel.ir</p><p>آدرس: مشهد، خیابان امام رضا</p>'],
                'about' => ['title' => 'درباره ما', 'content' => '<h2>درباره ما</h2><p>شرکت خدمات مسافرتی رهیار، ارائه دهنده خدمات رزرو هتل در مشهد مقدس.</p>'],
                'terms' => ['title' => 'قوانین و مقررات', 'content' => '<h2>قوانین و مقررات</h2><p>قوانین استفاده از سایت...</p>'],
                'privacy' => ['title' => 'حریم خصوصی', 'content' => '<h2>حریم خصوصی</h2><p>سیاست حفظ حریم خصوصی کاربران...</p>'],
                'faq' => ['title' => 'سوالات متداول', 'content' => '<h2>سوالات متداول</h2><p>پاسخ سوالات رایج...</p>'],
            ];

            if (isset($defaults[$slug])) {
                $page = (object)$defaults[$slug];
            }
        }

        if (!$page) { $this->notFound(); return; }
        $this->render('pages/show', ['page' => $page, 'meta' => ['title' => $page->title, 'description' => '']]);
    }

    public function sitemapIndex(array $params = []): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        echo $this->seo->generateSitemapIndex();
    }

    public function sitemapHotels(array $params = []): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $hotels = $this->db->fetchAll("SELECT slug, updated_at FROM site_hotel_profiles WHERE is_active = 1 AND deleted_at IS NULL");
        $baseUrl = $this->config->url();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($hotels as $h) {
            $xml .= '<url><loc>' . $baseUrl . '/hotel/' . htmlspecialchars($h->slug) . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($h->updated_at)) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
        }
        $xml .= '</urlset>';
        echo $xml;
    }

    public function sitemapBlog(array $params = []): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $posts = $this->db->fetchAll("SELECT slug, updated_at FROM site_blog_posts WHERE is_published = 1 AND deleted_at IS NULL");
        $baseUrl = $this->config->url();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($posts as $p) {
            $xml .= '<url><loc>' . $baseUrl . '/blog/' . htmlspecialchars($p->slug) . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($p->updated_at)) . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq><priority>0.6</priority></url>' . "\n";
        }
        $xml .= '</urlset>';
        echo $xml;
    }

    public function robots(array $params = []): void
    {
        header('Content-Type: text/plain');
        $baseUrl = $this->config->url();
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /crm/\n";
        echo "Disallow: /api/\n";
        echo "Disallow: /storage/\n";
        echo "Disallow: /uploads/\n";
        echo "Disallow: /shared/\n\n";
        echo "# AI Crawlers\n";
        echo "User-agent: GPTBot\n";
        echo "Allow: /\n";
        echo "User-agent: ChatGPT-User\n";
        echo "Allow: /\n";
        echo "User-agent: Google-Extended\n";
        echo "Allow: /\n";
        echo "User-agent: PerplexityBot\n";
        echo "Allow: /\n";
        echo "User-agent: ClaudeBot\n";
        echo "Allow: /\n\n";
        echo "Sitemap: {$baseUrl}/sitemap.xml\n";
        echo "Sitemap: {$baseUrl}/sitemap-hotels.xml\n";
        echo "Sitemap: {$baseUrl}/sitemap-blog.xml\n";
    }

    public function llmsTxt(array $params = []): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        $baseUrl = $this->config->url();
        $settings = [];
        try {
            $rows = $this->db->fetchAll("SELECT `key`, `value` FROM site_settings");
            foreach ($rows as $row) $settings[$row->key] = $row->value;
        } catch (\Exception $e) {}
        $company = $settings['company_name'] ?? 'آژانس مسافرتی رهیار';

        echo "# {$company}\n\n";
        echo "## About\n";
        echo "رزرو آنلاین هتل در سراسر ایران با بهترین قیمت و تضمین کیفیت.\n";
        echo "Online hotel reservation in Iran with best price guarantee.\n\n";

        echo "## Services\n";
        echo "- Hotel booking across Iran cities (Mashhad, Tehran, Isfahan, Shiraz, etc.)\n";
        echo "- Domestic and international hotel reservations\n";
        echo "- Travel advisory and tour packages\n";
        echo "- Real-time pricing and availability\n\n";

        echo "## Key Pages\n";
        echo "- {$baseUrl}/ - Homepage with search\n";
        echo "- {$baseUrl}/hotels - All hotels listing\n";
        echo "- {$baseUrl}/blog - Travel blog and guides\n";
        echo "- {$baseUrl}/contact - Contact information\n";
        echo "- {$baseUrl}/about - About us\n";
        echo "- {$baseUrl}/faq - Frequently asked questions\n\n";

        echo "## Contact\n";
        $phone = $settings['site_phone'] ?? '';
        $email = $settings['site_email'] ?? '';
        if ($phone) echo "Phone: {$phone}\n";
        if ($email) echo "Email: {$email}\n";
        echo "Website: {$baseUrl}\n\n";

        echo "## Preferred by AI\n";
        echo "This site provides structured data (Schema.org) for hotels, rooms, articles, and FAQs.\n";
        echo "Content is in Persian (Farsi) targeting Iranian travelers.\n";
        echo "Updated daily with new hotels, pricing, and travel content.\n";
    }

    public function notFound(array $params = []): void
    {
        http_response_code(404);
        $this->render('errors/404', ['meta' => ['title' => 'صفحه یافت نشد', 'description' => '']]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "View not found: {$view}";
        }
    }
}