<?php
namespace Shared\Services;

class IranPayamakSMS
{
    private string $apiKey;
    private string $patternCode;
    private string $lineNumber;
    private string $apiUrl = 'https://api.iranpayamak.com/ws/v1/sms/pattern';

    public function __construct()
    {
        $config = $GLOBALS['app_config'];
        $this->apiKey = getenv('IRANPAYAMAK_API_KEY') ?: '2SuZcGcUimnmhYEhodA6cbOikVPJQPsF3PnVprb2TGccapVWEk';
        $this->patternCode = getenv('IRANPAYAMAK_PATTERN_CODE') ?: 'cxUY2HyHMf';
        $this->lineNumber = getenv('IRANPAYAMAK_LINE_NUMBER') ?: '90008361';
    }

    /**
     * Send OTP/pattern SMS
     */
    public function sendPattern(string $recipient, array $attributes = []): array
    {
        $payload = [
            'code' => $this->patternCode,
            'attributes' => $attributes,
            'recipient' => $this->normalizePhone($recipient),
            'line_number' => $this->lineNumber,
            'number_format' => 'english',
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Api-Key: ' . $this->apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'خطای اتصال: ' . $error];
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $data];
        }

        return ['success' => false, 'message' => $data['message'] ?? 'خطا در ارسال پیامک', 'data' => $data];
    }

    /**
     * Send verification code
     */
    public function sendVerification(string $phone, string $code): array
    {
        return $this->sendPattern($phone, ['code' => $code]);
    }

    /**
     * Generate random code
     */
    public static function generateCode(int $length = 5): string
    {
        return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Normalize phone number
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10 && $phone[0] === '9') {
            $phone = '0' . $phone;
        }
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '98') {
            $phone = '0' . substr($phone, 2);
        }
        return $phone;
    }
}