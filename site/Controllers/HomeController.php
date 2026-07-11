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
        $page = $this->db->fetch("SELECT * FROM site_pages WHERE slug = :s AND is_active = 1 AND deleted_at IS NULL", [':s' => $slug]);
        if (!$page) { $this->notFound(); return; }
        $this->render('pages/show', ['page' => $page, 'meta' => ['title' => $page->title, 'description' => $page->meta_description ?? '']]);
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
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /crm/\n";
        echo "Disallow: /api/\n";
        echo "Disallow: /storage/\n";
        echo "Disallow: /uploads/\n";
        echo "Disallow: /shared/\n";
        echo "Sitemap: " . $this->config->url() . "/sitemap.xml\n";
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