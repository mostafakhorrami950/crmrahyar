<?php
namespace Shared\Services;

use Shared\Core\Database;

class OpenRouterService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct(Database $db)
    {
        $this->apiKey = '';
        $this->model = 'deepseek/deepseek-v4-pro';
        try {
            $key = $db->fetch("SELECT `value` FROM site_settings WHERE `key` = 'openrouter_api_key'");
            $model = $db->fetch("SELECT `value` FROM site_settings WHERE `key` = 'openrouter_model'");
            if ($key) $this->apiKey = $key->value;
            if ($model && !empty($model->value)) $this->model = $model->value;
        } catch (\Exception $e) {}
    }

    public function generateSEOContent(string $keyword, string $type = 'blog'): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'API key تنظیم نشده'];
        }

        $prompts = [
            'blog' => "یک مقاله SEO بهینه شده برای کلمه کلیدی «{$keyword}» بنویس. شامل: عنوان جذاب (حداکثر 60 کاراکتر)، متا توضیحات (حداکثر 160 کاراکتر)، خلاصه، و محتوای کامل با H2 و H3. محتوا باید به فارسی و مرتبط با رزرو هتل مشهد باشد.",
            'meta' => "برای کلمه کلیدی «{$keyword}» یک عنوان SEO (حداکثر 60 کاراکتر) و متا توضیحات (حداکثر 160 کاراکتر) به فارسی بنویس.",
            'faq' => "برای کلمه کلیدی «{$keyword}» پنج سوال متداول و پاسخ آنها را به فارسی بنویس. مرتبط با رزرو هتل مشهد باشد.",
            'hotel_description' => "توضیحات کامل SEO برای هتل «{$keyword}» بنویس. شامل امکانات، موقعیت مکانی، و مزایا. به فارسی و حداقل 300 کلمه.",
        ];

        $prompt = $prompts[$type] ?? $prompts['blog'];

        $ch = curl_init($this->baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'HTTP-Referer: https://crm.mobixai.ir',
                'X-OpenRouter-Title: Rahyar Travel SEO',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'شما یک متخصص SEO هستید. تمام پاسخ‌ها باید به فارسی و بهینه برای موتورهای جستجو باشند. از JSON format استفاده کنید.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]),
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'message' => "خطای API: HTTP {$httpCode}", 'response' => $response];
        }

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? '';

        return ['success' => true, 'content' => $content, 'model' => $this->model];
    }

    public function generateSlug(string $text): string
    {
        // Transliterate Persian to English
        $map = [
            'آ' => 'a', 'ا' => 'a', 'ب' => 'b', 'پ' => 'p', 'ت' => 't', 'ث' => 's',
            'ج' => 'j', 'چ' => 'ch', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'z',
            'ر' => 'r', 'ز' => 'z', 'ژ' => 'zh', 'س' => 's', 'ش' => 'sh', 'ص' => 's',
            'ض' => 'z', 'ط' => 't', 'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f',
            'ق' => 'gh', 'ک' => 'k', 'گ' => 'g', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'و' => 'v', 'ه' => 'h', 'ی' => 'y',
        ];
        $result = '';
        $len = mb_strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1);
            if (isset($map[$char])) $result .= $map[$char];
            elseif (preg_match('/[a-zA-Z0-9]/', $char)) $result .= $char;
            elseif (in_array($char, [' ', '-', '_', '‌'])) $result .= '-';
        }
        $result = preg_replace('/\-+/', '-', $result);
        return trim($result, '-') ?: 'page-' . time();
    }
}