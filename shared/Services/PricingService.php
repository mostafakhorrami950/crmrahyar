<?php
namespace Shared\Services;

use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Core\Logger;
use Shared\Interfaces\PricingServiceInterface;
use Shared\Repositories\SettingsRepository;
use Shared\Repositories\RoomRepository;

class PricingService implements PricingServiceInterface
{
    private Database $db;
    private Config $config;
    private CurrencyService $currency;
    private SettingsRepository $settings;
    private RoomRepository $roomRepo;

    public function __construct(Database $db, Config $config, CurrencyService $currency, SettingsRepository $settings, RoomRepository $roomRepo)
    {
        $this->db = $db;
        $this->config = $config;
        $this->currency = $currency;
        $this->settings = $settings;
        $this->roomRepo = $roomRepo;
    }

    /**
     * Calculate price for a room for given dates
     * 
     * @param int $roomId site_rooms.id
     * @param string $dateFrom Y-m-d
     * @param string $dateTo Y-m-d
     * @param int $guestsCount
     * @param array $options [agency_id, campaign_code, user_id]
     * @return object PricingResult
     */
    public function calculate(int $roomId, string $dateFrom, string $dateTo, int $guestsCount = 1, array $options = []): object
    {
        $room = $this->roomRepo->find($roomId);
        if (!$room) {
            return $this->emptyResult();
        }

        // Get nights
        $d1 = new \DateTime($dateFrom);
        $d2 = new \DateTime($dateTo);
        $nights = max((int)$d2->diff($d1)->days, 1);

        // Get base price from CRM
        $basePricePerNight = $this->getCrmPrice($room->crm_hotel_id, $room->room_type_key, $dateFrom, $dateTo);

        // Check for daily rate override
        $dailyRates = $this->roomRepo->getDailyRates($roomId, $dateFrom, $dateTo);
        if (!empty($dailyRates)) {
            $totalDaily = 0;
            $daysWithPrice = 0;
            foreach ($dailyRates as $dr) {
                if ($dr->price !== null && $dr->price > 0) {
                    $totalDaily += (float)$dr->price;
                    $daysWithPrice++;
                }
            }
            if ($daysWithPrice > 0) {
                $basePricePerNight = $totalDaily / $daysWithPrice;
            }
        }

        $baseTotal = $basePricePerNight * $nights;

        // Apply markup rules
        $markupResult = $this->applyMarkupRules($room, $basePricePerNight, $nights, $dateFrom);
        $consumerPricePerNight = $markupResult['final_price_per_night'];
        $markupAmount = $markupResult['total_markup'];
        $markupBreakdown = $markupResult['breakdown'];

        $consumerTotal = $consumerPricePerNight * $nights;

        // Agency price (no markup)
        $agencyPricePerNight = $basePricePerNight;
        $agencyTotal = $baseTotal;
        $agencyDiscount = 0;

        if (!empty($options['agency_id'])) {
            $agencyDiscount = $this->getAgencyDiscount($options['agency_id'], $agencyTotal);
            $agencyTotal -= $agencyDiscount;
        }

        // Campaign discount
        $campaignDiscount = 0;
        $campaignData = null;
        if (!empty($options['campaign_code'])) {
            $campaignResult = $this->applyCampaign($options['campaign_code'], $consumerTotal, $room->crm_hotel_id, $roomId);
            if ($campaignResult) {
                $campaignDiscount = $campaignResult['discount'];
                $campaignData = $campaignResult['campaign'];
            }
        }

        $discountPercent = $consumerTotal > 0 ? round(($campaignDiscount / $consumerTotal) * 100, 2) : 0;
        $finalPrice = $consumerTotal - $campaignDiscount;
        $savings = $markupAmount + $campaignDiscount;

        return (object)[
            'base_price' => (float)$basePricePerNight,
            'base_total' => (float)$baseTotal,
            'consumer_price' => (float)$consumerPricePerNight,
            'consumer_total' => (float)$consumerTotal,
            'agency_price' => (float)$agencyPricePerNight,
            'agency_total' => (float)$agencyTotal,
            'agency_discount' => (float)$agencyDiscount,
            'discounted_price' => (float)$finalPrice,
            'original_price' => (float)$consumerTotal,
            'discount_percent' => $discountPercent,
            'savings' => (float)$savings,
            'price_per_night' => (float)$consumerPricePerNight,
            'total_price' => (float)$finalPrice,
            'markup_amount' => (float)$markupAmount,
            'campaign_discount' => (float)$campaignDiscount,
            'campaign_data' => $campaignData,
            'nights' => $nights,
            'currency' => 'IRR',
            'pricing_breakdown' => [
                'crm_price_per_night' => $basePricePerNight,
                'markup_rules_applied' => $markupBreakdown,
                'nights' => $nights,
                'base_total' => $baseTotal,
                'markup_total' => $markupAmount,
                'consumer_total' => $consumerTotal,
                'campaign_discount' => $campaignDiscount,
                'final_total' => $finalPrice,
            ],
        ];
    }

    private function getCrmPrice(int $crmHotelId, string $roomType, string $dateFrom, string $dateTo): float
    {
        try {
            $rate = $this->db->fetch(
                "SELECT price_sobhaneh FROM hotel_rate_list 
                 WHERE hotel_id = :hid AND room_type = :rt AND is_active = 1
                 AND date_from <= :df AND date_to >= :dt
                 ORDER BY date_from DESC LIMIT 1",
                [':hid' => $crmHotelId, ':rt' => $roomType, ':df' => $dateTo, ':dt' => $dateFrom]
            );
            if ($rate && $rate->price_sobhaneh > 0) {
                return (float)$rate->price_sobhaneh;
            }
            // Fallback to first available price column
            $rate = $this->db->fetch(
                "SELECT price_ekht, price_sobhaneh, price_nahar, price_entekhabifulboard, price_fulboard_boufeh
                 FROM hotel_rate_list 
                 WHERE hotel_id = :hid AND room_type = :rt AND is_active = 1
                 AND date_from <= :df AND date_to >= :dt
                 ORDER BY date_from DESC LIMIT 1",
                [':hid' => $crmHotelId, ':rt' => $roomType, ':df' => $dateTo, ':dt' => $dateFrom]
            );
            if ($rate) {
                foreach (['price_sobhaneh', 'price_ekht', 'price_nahar', 'price_entekhabifulboard', 'price_fulboard_boufeh'] as $col) {
                    if ($rate->$col > 0) return (float)$rate->$col;
                }
            }
        } catch (\Exception $e) {
            Logger::error('getCrmPrice failed', ['error' => $e->getMessage()]);
        }
        return 0;
    }

    private function applyMarkupRules(object $room, float $basePrice, int $nights, string $dateFrom): array
    {
        $rules = $this->db->fetchAll(
            "SELECT * FROM site_pricing_rules 
             WHERE is_active = 1 AND deleted_at IS NULL
             AND (valid_from IS NULL OR valid_from <= :df)
             AND (valid_to IS NULL OR valid_to >= :df)
             ORDER BY priority ASC, rule_level ASC",
            [':df' => $dateFrom]
        );

        $totalMarkup = 0;
        $breakdown = [];
        $currentPrice = $basePrice;

        foreach ($rules as $rule) {
            $applies = false;
            switch ($rule->rule_level) {
                case 'global': $applies = true; break;
                case 'hotel': $applies = ((int)$rule->target_id === (int)$room->crm_hotel_id); break;
                case 'room_type': $applies = ((int)$rule->target_id === (int)$room->id); break;
                default: $applies = true; break;
            }
            if (!$applies) continue;

            $markup = 0;
            if ($rule->markup_type === 'percent') {
                $markup = $currentPrice * ($rule->markup_value / 100);
            } else {
                $markup = (float)$rule->markup_value;
            }

            $currentPrice += $markup;
            $totalMarkup += $markup * $nights;
            $breakdown[] = [
                'rule_name' => $rule->name,
                'level' => $rule->rule_level,
                'type' => $rule->markup_type,
                'value' => (float)$rule->markup_value,
                'markup_per_night' => $markup,
            ];
        }

        return [
            'final_price_per_night' => $currentPrice,
            'total_markup' => $totalMarkup,
            'breakdown' => $breakdown,
        ];
    }

    private function getAgencyDiscount(int $agencyId, float $amount): float
    {
        try {
            $agency = $this->db->fetch("SELECT discount_percent FROM site_agencies WHERE id = :id AND is_active = 1", [':id' => $agencyId]);
            if ($agency && $agency->discount_percent > 0) {
                return $amount * ($agency->discount_percent / 100);
            }
        } catch (\Exception $e) {}
        return 0;
    }

    private function applyCampaign(string $code, float $amount, int $crmHotelId, int $roomId): ?array
    {
        try {
            $campaign = $this->db->fetch(
                "SELECT * FROM site_campaigns WHERE code = :code AND is_active = 1 AND deleted_at IS NULL",
                [':code' => $code]
            );
            if (!$campaign) return null;

            // Check validity
            $now = date('Y-m-d H:i:s');
            if ($campaign->valid_from && $campaign->valid_from > $now) return null;
            if ($campaign->valid_to && $campaign->valid_to < $now) return null;
            if ($campaign->usage_limit && $campaign->used_count >= $campaign->usage_limit) return null;
            if ($campaign->min_amount && $amount < $campaign->min_amount) return null;

            // Check target hotels/rooms
            if ($campaign->target_hotels_json) {
                $hotelIds = json_decode($campaign->target_hotels_json, true);
                if (is_array($hotelIds) && !empty($hotelIds) && !in_array($crmHotelId, $hotelIds)) return null;
            }

            // Calculate discount
            $discount = 0;
            if ($campaign->discount_type === 'percent') {
                $discount = $amount * ($campaign->discount_value / 100);
            } else {
                $discount = (float)$campaign->discount_value;
            }

            if ($campaign->max_discount && $discount > $campaign->max_discount) {
                $discount = (float)$campaign->max_discount;
            }

            return [
                'discount' => $discount,
                'campaign' => [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'type' => $campaign->campaign_type,
                    'code' => $campaign->code,
                ],
            ];
        } catch (\Exception $e) {
            Logger::error('applyCampaign failed', ['error' => $e->getMessage()]);
        }
        return null;
    }

    private function emptyResult(): object
    {
        return (object)[
            'base_price' => 0, 'base_total' => 0,
            'consumer_price' => 0, 'consumer_total' => 0,
            'agency_price' => 0, 'agency_total' => 0, 'agency_discount' => 0,
            'discounted_price' => 0, 'original_price' => 0,
            'discount_percent' => 0, 'savings' => 0,
            'price_per_night' => 0, 'total_price' => 0,
            'markup_amount' => 0, 'campaign_discount' => 0,
            'campaign_data' => null, 'nights' => 0, 'currency' => 'IRR',
            'pricing_breakdown' => [],
        ];
    }
}