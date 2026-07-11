<?php
namespace Shared\Migrations;

use Shared\Core\Database;

class Migration008
{
    public static function up(Database $db): void
    {
        // Seed cities
        $existing = $db->fetch("SELECT COUNT(*) as cnt FROM site_cities");
        if (!$existing || $existing->cnt == 0) {
            $db->insert('site_cities', [
                'name' => 'مشهد', 'slug' => 'mashhad', 'province' => 'خراسان رضوی',
                'latitude' => 36.2605, 'longitude' => 59.6168,
                'description' => 'مشهد مقدس، شهر امام رضا (ع)، مهم‌ترین مقصد زیارتی و گردشگری ایران',
                'is_active' => 1, 'sort_order' => 1,
            ]);
        }

        // Seed neighborhoods
        $existing = $db->fetch("SELECT COUNT(*) as cnt FROM site_neighborhoods");
        if (!$existing || $existing->cnt == 0) {
            $city = $db->fetch("SELECT id FROM site_cities WHERE slug = 'mashhad'");
            if ($city) {
                $neighborhoods = [
                    ['name' => 'حرم مطهر', 'slug' => 'haram', 'distance_to_haram_km' => 0, 'sort_order' => 1],
                    ['name' => 'خیابان امام رضا', 'slug' => 'emam-reza', 'distance_to_haram_km' => 0.5, 'sort_order' => 2],
                    ['name' => 'خیابان نواب صفوی', 'slug' => 'navab', 'distance_to_haram_km' => 1, 'sort_order' => 3],
                    ['name' => 'طوس', 'slug' => 'toos', 'distance_to_haram_km' => 3, 'sort_order' => 4],
                    ['name' => 'احمدآباد', 'slug' => 'ahmadabad', 'distance_to_haram_km' => 4, 'sort_order' => 5],
                ];
                foreach ($neighborhoods as $n) {
                    $db->insert('site_neighborhoods', array_merge($n, ['city_id' => $city->id, 'is_active' => 1]));
                }
            }
        }

        // Seed settings
        $existing = $db->fetch("SELECT COUNT(*) as cnt FROM site_settings");
        if (!$existing || $existing->cnt == 0) {
            $settings = [
                ['setting_key' => 'site_title', 'setting_value' => 'رزرو هتل مشهد', 'setting_type' => 'string', 'setting_group' => 'general'],
                ['setting_key' => 'site_description', 'setting_value' => 'رزرو آنلاین هتل در مشهد با بهترین قیمت', 'setting_type' => 'string', 'setting_group' => 'general'],
                ['setting_key' => 'reservation_hold_minutes', 'setting_value' => '10', 'setting_type' => 'number', 'setting_group' => 'booking'],
                ['setting_key' => 'pricing_engine_version', 'setting_value' => '1.0.0', 'setting_type' => 'string', 'setting_group' => 'system'],
                ['setting_key' => 'workflow_version', 'setting_value' => '1.0.0', 'setting_type' => 'string', 'setting_group' => 'system'],
                ['setting_key' => 'campaign_engine_version', 'setting_value' => '1.0.0', 'setting_type' => 'string', 'setting_group' => 'system'],
                // Search weights
                ['setting_key' => 'search_weight_relevance', 'setting_value' => '0.30', 'setting_type' => 'number', 'setting_group' => 'search'],
                ['setting_key' => 'search_weight_popularity', 'setting_value' => '0.20', 'setting_type' => 'number', 'setting_group' => 'search'],
                ['setting_key' => 'search_weight_rating', 'setting_value' => '0.15', 'setting_type' => 'number', 'setting_group' => 'search'],
                ['setting_key' => 'search_weight_conversion', 'setting_value' => '0.10', 'setting_type' => 'number', 'setting_group' => 'search'],
                ['setting_key' => 'search_weight_availability', 'setting_value' => '0.10', 'setting_type' => 'number', 'setting_group' => 'search'],
                ['setting_key' => 'search_weight_price', 'setting_value' => '0.10', 'setting_type' => 'number', 'setting_group' => 'search'],
                ['setting_key' => 'search_weight_distance', 'setting_value' => '0.05', 'setting_type' => 'number', 'setting_group' => 'search'],
                // Feature flags
                ['setting_key' => 'feature_blog', 'setting_value' => '1', 'setting_type' => 'boolean', 'setting_group' => 'features'],
                ['setting_key' => 'feature_agency_panel', 'setting_value' => '1', 'setting_type' => 'boolean', 'setting_group' => 'features'],
                ['setting_key' => 'feature_campaigns', 'setting_value' => '1', 'setting_type' => 'boolean', 'setting_group' => 'features'],
                ['setting_key' => 'feature_ai_content', 'setting_value' => '0', 'setting_type' => 'boolean', 'setting_group' => 'features'],
                ['setting_key' => 'feature_multi_lang', 'setting_value' => '0', 'setting_type' => 'boolean', 'setting_group' => 'features'],
            ];
            foreach ($settings as $s) {
                $db->insert('site_settings', $s);
            }
        }

        // Seed default workflow
        $existing = $db->fetch("SELECT COUNT(*) as cnt FROM site_workflows");
        if (!$existing || $existing->cnt == 0) {
            $wid = $db->insert('site_workflows', [
                'name' => 'رزرو هتل',
                'entity_type' => 'booking',
                'is_default' => 1,
                'steps_json' => json_encode(['draft','reserved','waiting_payment','payment_processing','paid','confirmed','checked_in','checked_out','completed']),
            ]);

            $transitions = [
                ['from_status' => 'draft', 'to_status' => 'reserved', 'sort_order' => 1],
                ['from_status' => 'reserved', 'to_status' => 'waiting_payment', 'sort_order' => 1],
                ['from_status' => 'reserved', 'to_status' => 'expired', 'sort_order' => 2],
                ['from_status' => 'reserved', 'to_status' => 'cancelled', 'sort_order' => 3],
                ['from_status' => 'waiting_payment', 'to_status' => 'payment_processing', 'sort_order' => 1],
                ['from_status' => 'waiting_payment', 'to_status' => 'cancelled', 'sort_order' => 2],
                ['from_status' => 'payment_processing', 'to_status' => 'paid', 'sort_order' => 1],
                ['from_status' => 'payment_processing', 'to_status' => 'payment_failed', 'sort_order' => 2],
                ['from_status' => 'payment_failed', 'to_status' => 'waiting_payment', 'sort_order' => 1],
                ['from_status' => 'paid', 'to_status' => 'confirmed', 'sort_order' => 1],
                ['from_status' => 'confirmed', 'to_status' => 'checked_in', 'sort_order' => 1],
                ['from_status' => 'confirmed', 'to_status' => 'cancelled', 'sort_order' => 2, 'allowed_role' => 'super_admin'],
                ['from_status' => 'checked_in', 'to_status' => 'checked_out', 'sort_order' => 1],
                ['from_status' => 'checked_out', 'to_status' => 'completed', 'sort_order' => 1],
                ['from_status' => 'cancelled', 'to_status' => 'refunded', 'sort_order' => 1, 'allowed_role' => 'super_admin'],
            ];

            foreach ($transitions as $t) {
                $db->insert('site_workflow_transitions', array_merge($t, ['workflow_id' => $wid, 'is_active' => 1]));
            }
        }
    }

    public static function down(Database $db): void
    {
        $db->query("DELETE FROM site_workflow_transitions");
        $db->query("DELETE FROM site_workflows");
        $db->query("DELETE FROM site_settings");
        $db->query("DELETE FROM site_neighborhoods");
        $db->query("DELETE FROM site_cities");
    }
}