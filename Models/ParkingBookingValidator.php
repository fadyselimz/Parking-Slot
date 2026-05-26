<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/AppClock.php';
require_once __DIR__ . '/SubscriptionPeriodService.php';


class ParkingBookingValidator
{
    
    public static function validateClientDateTimePair(?string $startRaw, ?string $endRaw): array
    {
        $startRaw = $startRaw !== null ? trim(str_replace('T', ' ', $startRaw)) : '';
        $endRaw = $endRaw !== null ? trim(str_replace('T', ' ', $endRaw)) : '';
        if ($startRaw === '' || $endRaw === '') {
            return ['error' => 'Please provide both start and end times.'];
        }
        $startTs = strtotime($startRaw);
        $endTs = strtotime($endRaw);
        if ($startTs === false || $endTs === false) {
            return ['error' => 'Start or end time is not a valid date/time.'];
        }
        if ($endTs <= $startTs) {
            return ['error' => 'End time must be after start time.'];
        }
        if ($startTs < AppClock::timestamp()) {
            return ['error' => 'Start time cannot be in the past.'];
        }
        return [
            'start' => date('Y-m-d H:i:s', $startTs),
            'end' => date('Y-m-d H:i:s', $endTs),
        ];
    }

    
    public static function validateOwnerDailyWindow(?string $availStartRaw, ?string $availEndRaw): ?string
    {
        $as = self::normalizeTimeString($availStartRaw ?? '');
        $ae = self::normalizeTimeString($availEndRaw ?? '');
        if ($as === null || $ae === null) {
            return 'Availability times must be valid (HH:MM).';
        }
        $t1 = strtotime('1970-01-01 ' . $as);
        $t2 = strtotime('1970-01-01 ' . $ae);
        if ($t2 <= $t1) {
            return 'Available-until must be after available-from.';
        }
        return null;
    }

    
    private static function normalizeTimeString(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $input, $m)) {
            return sprintf('%02d:%02d:%02d', (int)$m[1], (int)$m[2], isset($m[3]) ? (int)$m[3] : 0);
        }
        return null;
    }

    
    public static function reservationFitsOwnerAvailability(
        string $startSql,
        string $endSql,
        mixed $availStartDb,
        mixed $availEndDb
    ): ?string {
        $tz = new DateTimeZone(date_default_timezone_get());

        try {
            $start = new DateTimeImmutable($startSql, $tz);
            $end = new DateTimeImmutable($endSql, $tz);
        } catch (Exception) {
            return 'Reservation timestamps are invalid.';
        }

        if ($start->format('Y-m-d') !== $end->format('Y-m-d')) {
            return 'This spot only accepts bookings that start and end on the same calendar day.';
        }

        $day = $start->format('Y-m-d');

        $as = self::normalizeTimeString(is_string($availStartDb) ? $availStartDb : (string)$availStartDb);
        $ae = self::normalizeTimeString(is_string($availEndDb) ? $availEndDb : (string)$availEndDb);
        if ($as === null) {
            $as = '00:00:00';
        }
        if ($ae === null) {
            $ae = '23:59:59';
        }

        try {
            $winLo = new DateTimeImmutable($day . ' ' . $as, $tz);
            $winHi = new DateTimeImmutable($day . ' ' . $ae, $tz);
        } catch (Exception) {
            return 'Owner availability configuration is invalid.';
        }

        
        if ($start < $winLo || $end > $winHi) {
            return 'Booking time falls outside the owner’s allowed availability window for this spot.';
        }

        return null;
    }

    
    public static function validateSubscriptionTimeOfDayAndWindow(
        string $startTime,
        string $endTime,
        mixed $availStartDb,
        mixed $availEndDb
    ): array {
        $startNorm = self::normalizeTimeString($startTime);
        $endNorm = self::normalizeTimeString($endTime);
        if ($startNorm === null || $endNorm === null) {
            return ['error' => 'Subscription times must be valid (HH:MM).'];
        }
        $day = date('Y-m-d');
        $tz = new DateTimeZone(date_default_timezone_get());
        try {
            $s = new DateTimeImmutable($day . ' ' . $startNorm, $tz);
            $e = new DateTimeImmutable($day . ' ' . $endNorm, $tz);
        } catch (Exception) {
            return ['error' => 'Subscription times are invalid.'];
        }
        if ($e <= $s) {
            return ['error' => 'Recurring start time must be before recurring end time.'];
        }

        $err = self::reservationFitsOwnerAvailability(
            $s->format('Y-m-d H:i:s'),
            $e->format('Y-m-d H:i:s'),
            $availStartDb,
            $availEndDb
        );
        if ($err !== null) {
            return ['error' => $err];
        }
        return ['start' => $startNorm, 'end' => $endNorm];
    }

    
    public static function subscriptionRejectOneTimeFieldsPresent(?string $startRaw, ?string $endRaw): ?string
    {
        $s = self::normalizedNonEmptyDatetimeLocal($startRaw);
        $e = self::normalizedNonEmptyDatetimeLocal($endRaw);
        if ($s !== '' || $e !== '') {
            return 'Subscription bookings must not include one-time start or end times.';
        }
        return null;
    }

    private static function normalizedNonEmptyDatetimeLocal(?string $raw): string
    {
        $raw = $raw !== null ? trim(str_replace('T', ' ', $raw)) : '';
        return $raw === '' ? '' : $raw;
    }

    
    public static function validateSubscriptionDateRange(?string $startDateRaw, ?string $endDateRaw): array
    {
        $tz = new DateTimeZone(date_default_timezone_get());

        $startDateRaw = $startDateRaw !== null ? trim($startDateRaw) : '';
        $endDateRaw = $endDateRaw !== null ? trim($endDateRaw) : '';
        if ($startDateRaw === '' || $endDateRaw === '') {
            return ['error' => 'Please provide subscription start date and end date.'];
        }
        $startDt = DateTimeImmutable::createFromFormat('!Y-m-d', $startDateRaw, $tz);
        $endDt = DateTimeImmutable::createFromFormat('!Y-m-d', $endDateRaw, $tz);
        if (!$startDt || $startDt->format('Y-m-d') !== $startDateRaw) {
            return ['error' => 'Subscription start date must use a valid calendar date (YYYY-MM-DD).'];
        }
        if (!$endDt || $endDt->format('Y-m-d') !== $endDateRaw) {
            return ['error' => 'Subscription end date must use a valid calendar date (YYYY-MM-DD).'];
        }
        if ($endDt <= $startDt) {
            return ['error' => 'Subscription end date must be after start date.'];
        }
        $today = new DateTimeImmutable('today', $tz);
        if ($startDt < $today) {
            return ['error' => 'Subscription start date cannot be in the past.'];
        }

        $dayDelta = SubscriptionPeriodService::calendarDayDelta($startDt, $endDt);
        if ($dayDelta < 7) {
            return ['error' => 'Subscription must be at least 7 days long'];
        }

        $durationWeeks = SubscriptionPeriodService::durationWeeksFromCalendarDayDelta($dayDelta);
        if ($durationWeeks < 1) {
            return ['error' => 'Calculated subscription duration is invalid. Please choose a longer date range.'];
        }

        return [
            'start_date' => $startDt->format('Y-m-d'),
            'end_date' => $endDt->format('Y-m-d'),
            'day_delta' => $dayDelta,
            'duration_weeks' => $durationWeeks,
        ];
    }

    
    public static function validateSubscriptionDaysSelected(?array $days): ?string
    {
        $selected = false;
        foreach ($days ?? [] as $d) {
            if ($d === '' || $d === null) {
                continue;
            }
            $selected = true;
            break;
        }
        return $selected ? null : 'Please select at least one day of the week';
    }
}
