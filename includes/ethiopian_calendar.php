<?php
/**
 * Ethiopian Calendar Conversion and Time Calculation
 * Faithful port of the system's GMT+3 East Africa Time algorithm
 */

class EthiopianCalendar {
    const MONTHS = [
        1 => ['am' => 'መስከረም', 'en' => 'Meskerem'],
        2 => ['am' => 'ጥቅምት', 'en' => 'Tikimt'],
        3 => ['am' => 'ኅዳር', 'en' => 'Hidar'],
        4 => ['am' => 'ታኅሣሥ', 'en' => 'Tahsas'],
        5 => ['am' => 'ጥር', 'en' => 'Tir'],
        6 => ['am' => 'የካቲት', 'en' => 'Yekatit'],
        7 => ['am' => 'መጋቢት', 'en' => 'Megabit'],
        8 => ['am' => 'ሚያዝያ', 'en' => 'Miyazya'],
        9 => ['am' => 'ግንቦት', 'en' => 'Ginbot'],
        10 => ['am' => 'ሰኔ', 'en' => 'Sene'],
        11 => ['am' => 'ሐምሌ', 'en' => 'Hamle'],
        12 => ['am' => 'ነሐሴ', 'en' => 'Nehase'],
        13 => ['am' => 'ጳጉሜ', 'en' => 'Pagume']
    ];

    const WEEKDAYS = [
        0 => ['am' => 'እሑድ', 'en' => 'Sunday'],
        1 => ['am' => 'ሰኞ', 'en' => 'Monday'],
        2 => ['am' => 'ማክሰኞ', 'en' => 'Tuesday'],
        3 => ['am' => 'ረቡዕ', 'en' => 'Wednesday'],
        4 => ['am' => 'ሐሙስ', 'en' => 'Thursday'],
        5 => ['am' => 'ዓርብ', 'en' => 'Friday'],
        6 => ['am' => 'ቅዳሜ', 'en' => 'Saturday']
    ];

    /**
     * Convert Gregorian date/timestamp to Ethiopian Date representation
     */
    public static function toEthiopianDate($dateInput = null): array {
        // Shift timestamp to East Africa Time (UTC+3)
        $dt = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
        if ($dateInput !== null && $dateInput !== '') {
            try {
                if (is_numeric($dateInput)) {
                    $dt->setTimestamp((int)$dateInput);
                } else {
                    $parsed = new DateTime((string)$dateInput);
                    $parsed->setTimezone(new DateTimeZone('Africa/Addis_Ababa'));
                    $dt = $parsed;
                }
            } catch (Exception $e) {
                // fallback to current
            }
        }

        $gYear = (int)$dt->format('Y');
        $gMonth = (int)$dt->format('n');
        $gDay = (int)$dt->format('j');
        $dayOfWeek = (int)$dt->format('w');
        $hours = (int)$dt->format('G');
        $minutes = (int)$dt->format('i');
        $seconds = (int)$dt->format('s');

        // JDN (Julian Day Number)
        $a = (int)floor((14 - $gMonth) / 12);
        $y = $gYear + 4800 - $a;
        $m = $gMonth + 12 * $a - 3;
        $jdn = $gDay + (int)floor((153 * $m + 2) / 5) + 365 * $y + (int)floor($y / 4) - (int)floor($y / 100) + (int)floor($y / 400) - 32045;

        // JDN to Ethiopian
        $ethJdnOffset = 1723856;
        $daysSinceEpoch = $jdn - $ethJdnOffset;
        $ethEra = (int)floor($daysSinceEpoch / 1461);
        $remDaysInEra = $daysSinceEpoch % 1461;
        $ethYearInEra = min((int)floor($remDaysInEra / 365), 3);
        $dayOfYear = $remDaysInEra - $ethYearInEra * 365;

        $ethYear = $ethEra * 4 + $ethYearInEra;
        $ethMonth = (int)floor($dayOfYear / 30) + 1;
        $ethDay = ($dayOfYear % 30) + 1;

        $monthObj = self::MONTHS[$ethMonth] ?? self::MONTHS[1];
        $weekdayObj = self::WEEKDAYS[$dayOfWeek] ?? self::WEEKDAYS[0];

        $displayHours = $hours % 12 ?: 12;
        $pad = fn($n) => sprintf('%02d', $n);
        $ampm = $hours >= 12 ? 'PM' : 'AM';

        $timeEn = "{$pad($displayHours)}:{$pad($minutes)}:{$pad($seconds)} {$ampm} (GMT+3)";
        $timeAm = "{$pad($displayHours)}:{$pad($minutes)}:{$pad($seconds)} " . ($ampm === 'AM' ? 'ጠዋት' : 'ከሰዓት') . ' (GMT+3)';

        // Traditional Ethiopian 12-hour Clock System (shifted by 6 hours)
        $ethHour = ($hours + 6) % 12 ?: 12;
        if ($hours >= 6 && $hours < 12) {
            $ethPeriodAm = 'ጠዋት';
            $ethPeriodEn = 'Morning';
        } elseif ($hours >= 12 && $hours < 18) {
            $ethPeriodAm = 'ቀን';
            $ethPeriodEn = 'Afternoon';
        } elseif ($hours >= 18 && $hours < 24) {
            $ethPeriodAm = 'ምሽት';
            $ethPeriodEn = 'Evening';
        } else {
            $ethPeriodAm = 'ሌሊት';
            $ethPeriodEn = 'Night';
        }

        $traditionalTimeAm = "{$ethHour}:{$pad($minutes)} {$ethPeriodAm} (GMT+3)";
        $traditionalTimeEn = "{$ethHour}:{$pad($minutes)} {$ethPeriodEn} (Eth Time GMT+3)";

        return [
            'year' => $ethYear,
            'month' => $ethMonth,
            'day' => $ethDay,
            'monthNameAm' => $monthObj['am'],
            'monthNameEn' => $monthObj['en'],
            'weekdayAm' => $weekdayObj['am'],
            'weekdayEn' => $weekdayObj['en'],
            'formattedAm' => "{$monthObj['am']} {$ethDay}, {$ethYear} ዓ.ም",
            'formattedEn' => "{$monthObj['en']} {$ethDay}, {$ethYear} EC",
            'timeAm' => $timeAm,
            'timeEn' => $timeEn,
            'traditionalTimeAm' => $traditionalTimeAm,
            'traditionalTimeEn' => $traditionalTimeEn,
            'isPagume' => $ethMonth === 13
        ];
    }

    public static function formatDate($dateInput = null, string $lang = 'am'): string {
        if (!$dateInput) return '—';
        $res = self::toEthiopianDate($dateInput);
        return $lang === 'am' ? $res['formattedAm'] : $res['formattedEn'];
    }

    public static function formatDateTime($dateInput = null, string $lang = 'am'): string {
        if (!$dateInput) return '—';
        $res = self::toEthiopianDate($dateInput);
        return $lang === 'am' ? "{$res['formattedAm']} ({$res['traditionalTimeAm']})" : "{$res['formattedEn']} ({$res['traditionalTimeEn']})";
    }

    public static function formatHeaderDate(string $lang = 'am'): string {
        $res = self::toEthiopianDate();
        return $lang === 'am' ? "{$res['weekdayAm']}፣ {$res['monthNameAm']} {$res['day']}, {$res['year']} ዓ.ም" : "{$res['weekdayEn']}, {$res['monthNameEn']} {$res['day']}, {$res['year']} EC";
    }
}
