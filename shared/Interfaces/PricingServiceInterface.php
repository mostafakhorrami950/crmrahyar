<?php
namespace Shared\Interfaces;

interface PricingServiceInterface
{
    public function calculate(int $roomId, string $dateFrom, string $dateTo, int $guestsCount, array $options = []): object;
    // Returns: basePrice, consumerPrice, agencyPrice, discountedPrice, originalPrice,
    // discountPercent, savings, pricePerNight, totalPrice, markupAmount, campaignDiscount, pricingBreakdown
}