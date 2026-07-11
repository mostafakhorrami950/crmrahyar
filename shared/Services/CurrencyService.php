<?php
namespace Shared\Services;

use Shared\Core\Config;

/**
 * Currency Service
 * 
 * Storage: IRR (ریال) - ISO 4217
 * Display: تومان (÷ 10)
 * 
 * قرارداد: تمام مبالغ در دیتابیس به ریال ذخیره می‌شوند.
 * نمایش به کاربر به تومان (ریال ÷ ۱۰).
 */
class CurrencyService
{
    private Config $config;
    private string $storageCurrency = 'IRR';
    private float $displayDivisor = 10.0; // تومان = ریال ÷ ۱۰

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getStorageCurrency(): string
    {
        return $this->storageCurrency;
    }

    public function getDisplayCurrency(): string
    {
        return 'تومان';
    }

    public function getDisplayCurrencyCode(): string
    {
        return 'TMN'; // Internal code, not ISO
    }

    /**
     * Convert display amount (تومان) to storage amount (ریال)
     */
    public function toStorage(float $displayAmount): float
    {
        return $displayAmount * $this->displayDivisor;
    }

    /**
     * Convert storage amount (ریال) to display amount (تومان)
     */
    public function toDisplay(float $storageAmount): float
    {
        return round($storageAmount / $this->displayDivisor);
    }

    /**
     * Format storage amount for display (with تومان suffix)
     */
    public function format(float $storageAmount): string
    {
        return number_format($this->toDisplay($storageAmount)) . ' تومان';
    }

    /**
     * Format storage amount for display without suffix
     */
    public function formatNumber(float $storageAmount): string
    {
        return number_format($this->toDisplay($storageAmount));
    }

    /**
     * Format for API response
     */
    public function formatApi(float $storageAmount): array
    {
        return [
            'amount' => (int)$storageAmount,
            'currency' => $this->storageCurrency,
            'display_amount' => (int)$this->toDisplay($storageAmount),
            'display_currency' => $this->getDisplayCurrency(),
        ];
    }

    /**
     * Parse user input (تومان) to storage (ریال)
     */
    public function parseInput(string $input): float
    {
        $cleaned = (float)str_replace([',', ' ', 'تومان', 'ت'], '', $input);
        return $this->toStorage($cleaned);
    }

    /**
     * Round storage amount (for IRR, no decimals)
     */
    public function roundStorage(float $amount): float
    {
        return round($amount);
    }
}