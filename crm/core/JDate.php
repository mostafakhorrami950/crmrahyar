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