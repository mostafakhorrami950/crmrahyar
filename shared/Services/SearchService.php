<?php
namespace Shared\Services;

use Shared\Core\Database;
use Shared\Core\Logger;
use Shared\Interfaces\SearchIndexInterface;
use Shared\Repositories\SettingsRepository;

class SearchService
{
    private Database $db;
    private SearchIndexInterface $index;
    private SettingsRepository $settings;
    private CacheService $cache;

    public function __construct(Database $db, SearchIndexInterface $index, SettingsRepository $settings, CacheService $cache)
    {
        $this->db = $db;
        $this->index = $index;
        $this->settings = $settings;
        $this->cache = $cache;
    }

    public function search(string $query, array $filters = [], string $sort = 'relevance', int $page = 1, int $perPage = 20): object
    {
        $cacheKey = 'search:' . md5($query . serialize($filters) . $sort . $page);
        $cached = $this->cache->get($cacheKey);
        if ($cached) return (object)$cached;

        $result = $this->index->search($query, $filters, $sort, $page, $perPage);

        // Track search term
        $this->trackSearchTerm($query, $result->total ?? 0);

        $this->cache->set($cacheKey, (array)$result, 900, ['search']); // 15 min
        return $result;
    }

    public function autocomplete(string $term, int $limit = 8): array
    {
        if (strlen($term) < 2) return [];
        return $this->index->autocomplete($term, $limit);
    }

    public function suggest(string $query): array
    {
        $cacheKey = 'suggest:' . md5($query);
        $cached = $this->cache->get($cacheKey);
        if ($cached) return $cached;

        $suggestions = $this->db->fetchAll(
            "SELECT term, search_count FROM site_search_terms WHERE term LIKE :q ORDER BY search_count DESC LIMIT 5",
            [':q' => '%' . $query . '%']
        );

        $result = array_column($suggestions, 'term');
        $this->cache->set($cacheKey, $result, 3600);
        return $result;
    }

    public function getRelated(int $hotelId, int $limit = 4): array
    {
        $cacheKey = 'related:hotel:' . $hotelId;
        $cached = $this->cache->get($cacheKey);
        if ($cached) return $cached;

        $hotel = $this->db->fetch("SELECT city_id, star_rating FROM site_hotel_profiles WHERE crm_hotel_id = :id", [':id' => $hotelId]);
        if (!$hotel) return [];

        $related = $this->db->fetchAll(
            "SELECT h.*, c.name as city_name FROM site_hotel_profiles h
             LEFT JOIN site_cities c ON h.city_id = c.id
             WHERE h.crm_hotel_id != :hid AND h.city_id = :city AND h.is_active = 1 AND h.deleted_at IS NULL
             ORDER BY ABS(h.star_rating - :star) ASC, h.featured DESC LIMIT {$limit}",
            [':hid' => $hotelId, ':city' => $hotel->city_id, ':star' => $hotel->star_rating]
        );

        $this->cache->set($cacheKey, $related, 3600, ['hotel:' . $hotelId]);
        return $related;
    }

    public function getPopular(?int $cityId = null, int $limit = 10): array
    {
        $cacheKey = 'popular:' . ($cityId ?: 'all') . ':' . $limit;
        $cached = $this->cache->get($cacheKey);
        if ($cached) return $cached;

        $where = "h.is_active = 1 AND h.deleted_at IS NULL";
        $params = [];
        if ($cityId) { $where .= " AND h.city_id = :city"; $params[':city'] = $cityId; }

        $hotels = $this->db->fetchAll(
            "SELECT h.*, c.name as city_name,
                (SELECT COUNT(*) FROM site_bookings b WHERE b.crm_hotel_id = h.crm_hotel_id AND b.booking_status = 'completed') as booking_count
             FROM site_hotel_profiles h
             LEFT JOIN site_cities c ON h.city_id = c.id
             WHERE {$where}
             ORDER BY booking_count DESC, h.featured DESC LIMIT {$limit}",
            $params
        );

        $this->cache->set($cacheKey, $hotels, 1800, ['popular']);
        return $hotels;
    }

    public function reindex(?string $entityType = null): void
    {
        $this->index->reindex($entityType);
    }

    private function trackSearchTerm(string $term, int $resultCount): void
    {
        try {
            $term = mb_substr(trim($term), 0, 200);
            if (empty($term)) return;
            $existing = $this->db->fetch("SELECT id, search_count FROM site_search_terms WHERE term = :t", [':t' => $term]);
            if ($existing) {
                $this->db->query("UPDATE site_search_terms SET search_count = search_count + 1, last_searched = NOW() WHERE id = :id", [':id' => $existing->id]);
            } else {
                $this->db->insert('site_search_terms', ['term' => $term, 'search_count' => 1, 'result_count' => $resultCount]);
            }
        } catch (\Exception $e) {}
    }
}