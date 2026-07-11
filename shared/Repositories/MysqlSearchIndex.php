<?php
namespace Shared\Repositories;

use Shared\Core\Database;
use Shared\Core\Logger;
use Shared\Interfaces\SearchIndexInterface;

class MysqlSearchIndex implements SearchIndexInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function index(string $entityType, int $entityId, array $data): void
    {
        $existing = $this->db->fetch(
            "SELECT id FROM site_search_index WHERE entity_type = :et AND entity_id = :eid",
            [':et' => $entityType, ':eid' => $entityId]
        );

        $searchVector = implode(' ', array_filter([
            $data['title'] ?? '',
            $data['description'] ?? '',
            $data['city'] ?? '',
        ]));

        $indexData = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'city_id' => $data['city_id'] ?? null,
            'neighborhood_id' => $data['neighborhood_id'] ?? null,
            'price_min' => $data['price_min'] ?? null,
            'price_max' => $data['price_max'] ?? null,
            'star_rating' => $data['star_rating'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'tags_json' => isset($data['tags']) ? json_encode($data['tags']) : null,
            'search_vector' => $searchVector,
            'popularity_score' => $data['popularity_score'] ?? 0,
            'rating_score' => $data['rating_score'] ?? 0,
            'conversion_score' => $data['conversion_score'] ?? 0,
        ];

        if ($existing) {
            $this->db->update('site_search_index', $indexData, 'id = :id', [':id' => $existing->id]);
        } else {
            $this->db->insert('site_search_index', $indexData);
        }
    }

    public function remove(string $entityType, int $entityId): void
    {
        $this->db->delete('site_search_index', 'entity_type = :et AND entity_id = :eid', [':et' => $entityType, ':eid' => $entityId]);
    }

    public function search(string $query, array $filters = [], string $sort = 'relevance', int $page = 1, int $perPage = 20): object
    {
        $where = "1=1";
        $params = [];
        $offset = ($page - 1) * $perPage;

        // Fulltext search
        if (!empty($query)) {
            $where .= " AND MATCH(si.title, si.search_vector) AGAINST(:q IN BOOLEAN MODE)";
            $params[':q'] = $query;
        }

        // Filters
        if (!empty($filters['city_id'])) {
            $where .= " AND si.city_id = :city_id";
            $params[':city_id'] = $filters['city_id'];
        }
        if (!empty($filters['star_rating'])) {
            $where .= " AND si.star_rating >= :star";
            $params[':star'] = $filters['star_rating'];
        }
        if (!empty($filters['price_min'])) {
            $where .= " AND si.price_min >= :pmin";
            $params[':pmin'] = $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $where .= " AND si.price_max <= :pmax";
            $params[':pmax'] = $filters['price_max'];
        }

        // Ranking weights from settings (configurable)
        $relevanceWeight = $filters['weight_relevance'] ?? 0.30;
        $popularityWeight = $filters['weight_popularity'] ?? 0.20;
        $ratingWeight = $filters['weight_rating'] ?? 0.15;
        $conversionWeight = $filters['weight_conversion'] ?? 0.10;

        // Sort
        $orderBy = "si.popularity_score DESC, si.title ASC";
        if ($sort === 'relevance' && !empty($query)) {
            $orderBy = "score DESC";
        } elseif ($sort === 'price_asc') {
            $orderBy = "si.price_min ASC";
        } elseif ($sort === 'price_desc') {
            $orderBy = "si.price_min DESC";
        } elseif ($sort === 'rating') {
            $orderBy = "si.rating_score DESC";
        } elseif ($sort === 'popularity') {
            $orderBy = "si.popularity_score DESC";
        }

        $selectScore = !empty($query) ? ", MATCH(si.title, si.search_vector) AGAINST(:q2 IN BOOLEAN MODE) as score" : "";
        $params2 = $params;
        if (!empty($query)) $params2[':q2'] = $query;

        // Count
        $countResult = $this->db->fetch("SELECT COUNT(*) as total FROM site_search_index si WHERE {$where}", $params);
        $total = $countResult ? (int)$countResult->total : 0;

        // Results
        $results = $this->db->fetchAll(
            "SELECT si.* {$selectScore} FROM site_search_index si WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}",
            $params2
        );

        return (object)[
            'results' => $results,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function autocomplete(string $term, int $limit = 8): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT title FROM site_search_index WHERE title LIKE :t ORDER BY popularity_score DESC LIMIT {$limit}",
            [':t' => '%' . $term . '%']
        );
    }

    public function reindex(?string $entityType = null): void
    {
        // Reindex hotels
        if (!$entityType || $entityType === 'hotel') {
            $hotels = $this->db->fetchAll(
                "SELECT h.*, c.name as city_name FROM site_hotel_profiles h LEFT JOIN site_cities c ON h.city_id = c.id WHERE h.is_active = 1 AND h.deleted_at IS NULL"
            );
            foreach ($hotels as $h) {
                $this->index('hotel', $h->id, [
                    'title' => $h->hotel_name ?? $h->slug,
                    'description' => $h->description_short ?? '',
                    'city' => $h->city_name ?? '',
                    'city_id' => $h->city_id,
                    'neighborhood_id' => $h->neighborhood_id,
                    'star_rating' => $h->star_rating,
                    'tags' => array_filter([
                        $h->family_friendly ? 'family' : '',
                        $h->couple_friendly ? 'couple' : '',
                        $h->budget_friendly ? 'budget' : '',
                        $h->luxury ? 'luxury' : '',
                    ]),
                ]);
            }
        }
    }

    public function getStats(): array
    {
        $total = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_search_index");
        $byType = $this->db->fetchAll("SELECT entity_type, COUNT(*) as cnt FROM site_search_index GROUP BY entity_type");
        return ['total' => $total->cnt ?? 0, 'by_type' => $byType];
    }
}