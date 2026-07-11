<?php
namespace Shared\Repositories;

use Shared\Core\Database;

class HotelRepository extends BaseRepository
{
    protected string $table = 'site_hotel_profiles';

    public function findActiveByCity(int $cityId): array
    {
        return $this->db->fetchAll(
            "SELECT h.*, c.name as city_name, n.name as neighborhood_name
             FROM site_hotel_profiles h
             LEFT JOIN site_cities c ON h.city_id = c.id
             LEFT JOIN site_neighborhoods n ON h.neighborhood_id = n.id
             WHERE h.city_id = :city_id AND h.is_active = 1 AND h.deleted_at IS NULL
             ORDER BY h.sort_order ASC, h.featured DESC, h.id DESC",
            [':city_id' => $cityId]
        );
    }

    public function findFeatured(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT h.*, c.name as city_name
             FROM site_hotel_profiles h
             LEFT JOIN site_cities c ON h.city_id = c.id
             WHERE h.featured = 1 AND h.is_active = 1 AND h.deleted_at IS NULL
             ORDER BY h.sort_order ASC LIMIT {$limit}"
        );
    }

    public function findByNeighborhood(int $neighborhoodId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM site_hotel_profiles
             WHERE neighborhood_id = :nid AND is_active = 1 AND deleted_at IS NULL
             ORDER BY sort_order ASC",
            [':nid' => $neighborhoodId]
        );
    }

    public function findSimilar(int $hotelId, int $limit = 4): array
    {
        $hotel = $this->find($hotelId);
        if (!$hotel) return [];
        return $this->db->fetchAll(
            "SELECT * FROM site_hotel_profiles
             WHERE id != :id AND city_id = :city AND is_active = 1 AND deleted_at IS NULL
             ORDER BY RAND() LIMIT {$limit}",
            [':id' => $hotelId, ':city' => $hotel->city_id]
        );
    }

    public function findBySlugWithJoins(string $slug): ?object
    {
        return $this->db->fetch(
            "SELECT h.*, c.name as city_name, n.name as neighborhood_name,
                    (SELECT AVG(r.rating) FROM site_reviews r WHERE r.crm_hotel_id = h.crm_hotel_id AND r.is_approved = 1) as avg_rating,
                    (SELECT COUNT(*) FROM site_reviews r WHERE r.crm_hotel_id = h.crm_hotel_id AND r.is_approved = 1) as review_count
             FROM site_hotel_profiles h
             LEFT JOIN site_cities c ON h.city_id = c.id
             LEFT JOIN site_neighborhoods n ON h.neighborhood_id = n.id
             WHERE h.slug = :slug AND h.deleted_at IS NULL",
            [':slug' => $slug]
        );
    }

    public function findByCrmId(int $crmHotelId): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM site_hotel_profiles WHERE crm_hotel_id = :cid AND deleted_at IS NULL",
            [':cid' => $crmHotelId]
        );
    }
}