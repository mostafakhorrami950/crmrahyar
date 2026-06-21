<?php
namespace Core;

/**
 * Jalali (Shamsi) Date Helper
 * Converts Gregorian dates to Persian dates
 */
class JDate
{
    /**
     * Convert Gregorian timestamp to Jalali date string
     */
    public static function date(string $format, ?string $timestamp = null): string
    {
        if ($timestamp === null || empty($timestamp)) {
            $timestamp = date('Y-m-d H:i:s');
        }
        
        $g = explode(' ', $timestamp);
        $gDate = explode('-', $g[0]);
        $gTime = isset($g[1]) ? explode(':', $g[1]) : [0, 0, 0];
        
        $gy = (int)($gDate[0] ?? 0);
        $gm = (int)($gDate[1] ?? 1);
        $gd = (int)($gDate[2] ?? 1);
        
        // Validate date parts
        if ($gy < 1900 || $gy > 2100 || $gm < 1 || $gm > 12 || $gd < 1 || $gd > 31) {
            return $timestamp;
        }
        $gh = isset($gTime[0]) ? (int)$gTime[0] : 0;
        $gi = isset($gTime[1]) ? (int)$gTime[1] : 0;
        $gs = isset($gTime[2]) ? (int)$gTime[2] : 0;
        
        list($jy, $jm, $jd) = self::gregorianToJalali($gy, $gm, $gd);
        
        $formatMap = [
            'Y' => $jy,
            'y' => substr($jy, 2),
            'm' => str_pad($jm, 2, '0', STR_PAD_LEFT),
            'n' => $jm,
            'd' => str_pad($jd, 2, '0', STR_PAD_LEFT),
            'j' => $jd,
            'H' => str_pad($gh, 2, '0', STR_PAD_LEFT),
            'G' => $gh,
            'i' => str_pad($gi, 2, '0', STR_PAD_LEFT),
            's' => str_pad($gs, 2, '0', STR_PAD_LEFT),
        ];
        
        $dayNames = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];
        $monthNames = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
        
        $dayOfWeek = self::dayOfWeek($gy, $gm, $gd);
        
        $result = '';
        for ($i = 0; $i < strlen($format); $i++) {
            $char = $format[$i];
            if ($char === 'l') {
                $result .= $dayNames[$dayOfWeek] ?? '';
            } elseif ($char === 'F') {
                $result .= $monthNames[$jm - 1] ?? '';
            } elseif (isset($formatMap[$char])) {
                $result .= $formatMap[$char];
            } else {
                $result .= $char;
            }
        }
        
        return $result;
    }
    
    /**
     * Format a datetime for display (YYYY/MM/DD format)
     */
    public static function displayDate(?string $datetime, string $separator = '/'): string
    {
        if (empty($datetime)) return '-';
        return self::date('Y' . $separator . 'm' . $separator . 'd', $datetime);
    }
    
    /**
     * Format a datetime with time
     */
    public static function displayDateTime(?string $datetime): string
    {
        if (empty($datetime)) return '-';
        return self::date('Y/m/d - H:i', $datetime);
    }
    
    /**
     * Format time only
     */
    public static function displayTime(?string $datetime): string
    {
        if (empty($datetime)) return '-';
        return self::date('H:i', $datetime);
    }
    
    /**
     * Public wrapper: Convert Gregorian Y/M/D to Jalali array [jy, jm, jd]
     */
    public static function toJalali(int $gy, int $gm, int $gd): array
    {
        return self::gregorianToJalali($gy, $gm, $gd);
    }
    
    /**
     * Get Jalali year, month, day from Gregorian
     */
    private static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * ((int)($days / 12053)));
        $days %= 12053;
        $jy += 4 * ((int)($days / 1461));
        $days %= 1461;
        
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        
        if ($days < 186) {
            $jm = 1 + (int)($days / 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + (int)(($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }
        
        return [$jy, $jm, $jd];
    }
    
    /**
     * Public wrapper: Convert Jalali Y/M/D to Gregorian array [gy, gm, gd]
     */
    public static function toGregorian(int $jy, int $jm, int $jd): array
    {
        return self::jalaliToGregorian($jy, $jm, $jd);
    }
    
    /**
     * Get current Jalali date as array [year, month, day]
     */
    public static function now(): array
    {
        return self::gregorianToJalali((int)date('Y'), (int)date('m'), (int)date('d'));
    }
    
    /**
     * Get Jalali month name
     */
    public static function monthName(int $month): string
    {
        $months = ['', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
        return $months[$month] ?? '';
    }
    
    /**
     * Get number of days in a Jalali month
     */
    public static function daysInMonth(int $jy, int $jm): int
    {
        if ($jm <= 6) return 31;
        if ($jm <= 11) return 30;
        // Esfand - check leap year
        return self::isLeapYear($jy) ? 30 : 29;
    }
    
    /**
     * Check if Jalali year is leap
     */
    public static function isLeapYear(int $jy): bool
    {
        $breaks = [-1468, -1063, -657, -252, 158, 563, 968, 1373, 1778, 2183, 2588, 2993, 3398, 3803, 4208, 4613, 5018, 5423, 5828, 6233, 6638, 7043, 7448, 7853, 8258, 8663, 9068, 9473, 9878, 10283, 10688, 11093, 11498, 11903, 12308, 12713, 13118, 13523, 13928, 14333, 14738, 15143, 15548, 15953, 16358, 16763, 17168, 17573, 17978, 18383, 18788, 19193, 19598, 20003, 20408, 20813, 21218, 21623, 22028, 22433, 22838, 23243, 23648, 24053, 24458, 24863, 25268, 25673, 26078, 26483, 26888, 27293, 27698, 28103, 28508, 28913, 29318, 29723, 30128, 30533, 30938, 31343, 31748, 32153, 32558, 32963, 33368, 33773, 34178];
        $jump = 0;
        for ($i = 0; $i < count($breaks); $i++) {
            if ($jy < $breaks[$i]) { $jump = $i; break; }
        }
        $breaks_mod = [];
        foreach ($breaks as $b) { $breaks_mod[] = $b % 2820; }
        return ($jy % 4 === 0 && !in_array($jy % 2820, [0, 5, 9, 13, 17, 21, 25, 29, 33, 37, 41, 45, 49, 53, 57, 61, 65, 69, 73, 77, 81, 85, 89, 93, 97]));
    }
    
    /**
     * Convert Jalali Y/M/D to Gregorian array [gy, gm, gd]
     */
    private static function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;
        $days = -355668 + (365 * $jy) + ((int)($jy / 33)) * 8 + (int)((($jy % 33) + 3) / 4) + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30 + 186));
        $gy = 400 * (int)($days / 146097);
        $days %= 146097;
        $leap = true;
        if ($days >= 36525) {
            $days--;
            $gy += 100 * (int)($days / 36524);
            $days %= 36524;
            if ($days >= 365) $days++;
            else $leap = false;
        }
        $gy += 4 * (int)($days / 1461);
        $days %= 1461;
        if ($days >= 366) {
            $leap = false;
            $days--;
            $gy += (int)($days / 365);
            $days %= 365;
        }
        $gd = $days + 1;
        $g_d_m = [0, 31, ($leap ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        for ($gm = 1; $gm <= 12; $gm++) {
            if ($gd <= $g_d_m[$gm]) break;
            $gd -= $g_d_m[$gm];
        }
        return [$gy, $gm, $gd];
    }
    
    /**
     * Get day of week (0=Sunday, 6=Saturday)
     */
    private static function dayOfWeek(int $y, int $m, int $d): int
    {
        if ($m < 3) {
            $m += 12;
            $y--;
        }
        $c = (int)($y / 100);
        $y = $y % 100;
        $w = ($d + (int)((13 * ($m + 1)) / 5) + $y + (int)($y / 4) + (int)($c / 4) - (2 * $c)) % 7;
        return ($w + 6) % 7;
    }
}