<?php
namespace Shared\Services;

use Shared\Core\Config;
use Shared\Core\Database;
use Shared\Repositories\SettingsRepository;

class SEOService
{
    private Config $config;
    private Database $db;
    private SettingsRepository $settings;

    public function __construct(Config $config, Database $db, SettingsRepository $settings)
    {
        $this->config = $config;
        $this->db = $db;
        $this->settings = $settings;
    }

    public function generateMeta(string $entityType, int $entityId): array
    {
        $entity = $this->getEntity($entityType, $entityId);
        if (!$entity) return $this->defaultMeta();

        $title = $entity->meta_title ?: ($entity->title ?? $entity->hotel_name ?? '');
        $desc = $entity->meta_description ?: mb_substr(strip_tags($entity->description_long ?? $entity->description ?? ''), 0, 160);
        $canonical = $this->config->url() . '/' . $this->getEntityUrl($entityType, $entity);
        $robots = $entity->robots_meta ?? 'index, follow';

        return [
            'title' => $title,
            'description' => $desc,
            'canonical' => $canonical,
            'robots' => $robots,
            'og_title' => $title,
            'og_description' => $desc,
            'og_image' => $entity->og_image_id ? $this->getMediaUrl($entity->og_image_id) : '',
            'og_type' => $entityType === 'blog_post' ? 'article' : 'website',
        ];
    }

    public function generateSchema(string $entityType, int $entityId): array
    {
        $entity = $this->getEntity($entityType, $entityId);
        if (!$entity) return [];

        switch ($entityType) {
            case 'hotel':
                return $this->hotelSchema($entity);
            case 'room':
                return $this->roomSchema($entity);
            case 'blog_post':
                return $this->articleSchema($entity);
            default:
                return [];
        }
    }

    public function generateBreadcrumb(array $items): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];
        foreach ($items as $i => $item) {
            $schema['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? '',
            ];
        }
        return $schema;
    }

    public function generateCanonical(string $url): string
    {
        return rtrim($this->config->url(), '/') . '/' . ltrim($url, '/');
    }

    public function generateSitemapIndex(): string
    {
        $baseUrl = $this->config->url();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= '<sitemap><loc>' . $baseUrl . '/sitemap-hotels.xml</loc></sitemap>' . "\n";
        $xml .= '<sitemap><loc>' . $baseUrl . '/sitemap-rooms.xml</loc></sitemap>' . "\n";
        $xml .= '<sitemap><loc>' . $baseUrl . '/sitemap-blog.xml</loc></sitemap>' . "\n";
        $xml .= '<sitemap><loc>' . $baseUrl . '/sitemap-pages.xml</loc></sitemap>' . "\n";
        $xml .= '</sitemapindex>';
        return $xml;
    }

    public function getRobotsMeta(string $entityType, int $entityId): string
    {
        $entity = $this->getEntity($entityType, $entityId);
        return $entity->robots_meta ?? 'index, follow';
    }

    public function autoGenerateMeta(string $title, string $content): array
    {
        $desc = mb_substr(strip_tags($content), 0, 160);
        return ['title' => $title, 'description' => $desc];
    }

    public function checkRedirect(string $url): ?array
    {
        try {
            $redirect = $this->db->fetch(
                "SELECT * FROM site_seo_redirects WHERE from_url = :url AND is_active = 1",
                [':url' => $url]
            );
            if ($redirect) {
                return ['to_url' => $redirect->to_url, 'type' => $redirect->redirect_type];
            }
        } catch (\Exception $e) {}
        return null;
    }

    private function hotelSchema(object $hotel): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Hotel',
            'name' => $hotel->hotel_name ?? '',
            'description' => $hotel->description_short ?? '',
            'url' => $this->config->url() . '/hotel/' . $hotel->slug,
        ];
        if ($hotel->star_rating) $schema['starRating'] = str_repeat('★', $hotel->star_rating);
        if ($hotel->address) $schema['address'] = ['@type' => 'PostalAddress', 'addressLocality' => $hotel->city ?? '', 'streetAddress' => $hotel->address];
        if ($hotel->latitude) $schema['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float)$hotel->latitude, 'longitude' => (float)$hotel->longitude];
        return $schema;
    }

    private function roomSchema(object $room): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'HotelRoom',
            'name' => $room->room_type_key ?? '',
            'description' => $room->description ?? '',
            'occupancy' => ['@type' => 'QuantitativeValue', 'maxValue' => $room->capacity_adults ?? 2],
        ];
    }

    private function articleSchema(object $post): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title ?? '',
            'description' => $post->excerpt ?? '',
            'datePublished' => $post->published_at ?? '',
            'url' => $this->config->url() . '/blog/' . $post->slug,
        ];
    }

    private function getEntity(string $type, int $id): ?object
    {
        $tableMap = ['hotel' => 'site_hotel_profiles', 'room' => 'site_rooms', 'blog_post' => 'site_blog_posts', 'page' => 'site_pages'];
        $table = $tableMap[$type] ?? null;
        if (!$table) return null;
        return $this->db->fetch("SELECT * FROM {$table} WHERE id = :id", [':id' => $id]);
    }

    private function getEntityUrl(string $type, object $entity): string
    {
        switch ($type) {
            case 'hotel': return 'hotel/' . ($entity->slug ?? '');
            case 'room': return 'room/' . ($entity->slug ?? '');
            case 'blog_post': return 'blog/' . ($entity->slug ?? '');
            case 'page': return ($entity->slug ?? '');
            default: return '';
        }
    }

    private function getMediaUrl(int $mediaId): string
    {
        $media = $this->db->fetch("SELECT path_original FROM site_media WHERE id = :id", [':id' => $mediaId]);
        return $media ? $this->config->url() . '/' . $media->path_original : '';
    }

    private function defaultMeta(): array
    {
        return [
            'title' => $this->settings->get('site_title', 'رزرو هتل'),
            'description' => $this->settings->get('site_description', ''),
            'canonical' => $this->config->url(),
            'robots' => 'index, follow',
            'og_title' => '', 'og_description' => '', 'og_image' => '', 'og_type' => 'website',
        ];
    }
}