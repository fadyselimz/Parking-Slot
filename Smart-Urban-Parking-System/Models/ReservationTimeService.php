<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/AppClock.php';


final class ReservationTimeService
{
    
    private const CHECK_IN_EARLY_MINS = 15;

    public static function verifyQrToken(?string $posted, string $stored): bool
    {
        $posted = $posted !== null ? trim($posted) : '';
        if ($posted === '' || $stored === '') {
            return false;
        }

        return hash_equals($stored, $posted);
    }

    
    public static function syncNoShowStatuses(PDO $pdo): int
    {
        $stmt = $pdo->exec(
            'UPDATE reservations
             SET status = "no_show"
             WHERE status = "confirmed"
               AND check_in_time IS NULL
               AND DATE_ADD(start_time, INTERVAL grace_period_mins MINUTE) < NOW()'
        );

        return max(0, (int)$stmt);
    }

    
    public static function checkInAllowed(DateTimeImmutable $now, array $r): ?string
    {
        if (($r['status'] ?? '') !== 'confirmed') {
            return 'Check-in is only available for confirmed bookings.';
        }

        $start = AppClock::parseSqlDatetime((string)$r['start_time']);
        $end = AppClock::parseSqlDatetime((string)$r['end_time']);
        $early = $start->modify('-' . self::CHECK_IN_EARLY_MINS . ' minutes');
        $graceEnd = $start->modify('+' . max(0, (int)($r['grace_period_mins'] ?? 5)) . ' minutes');

        if ($now < $early) {
            return 'Check-in opens ' . self::CHECK_IN_EARLY_MINS . ' minutes before your reservation start time.';
        }
        if ($now > $end) {
            return 'This reservation window has ended; you cannot check in.';
        }
        if ($now > $graceEnd) {
            return 'The grace period for check-in has expired. If you believe this is wrong, contact support or file a dispute.';
        }

        return null;
    }

    
    public static function checkOutAllowed(DateTimeImmutable $now, array $r): ?string
    {
        if (($r['status'] ?? '') !== 'active') {
            return 'Check-out is only available after check-in.';
        }

        return null;
    }

    
    public static function cancelRefundPercent(DateTimeImmutable $now, string $startTimeSql): array
    {
        $start = AppClock::parseSqlDatetime($startTimeSql);
        $secondsUntilStart = $start->getTimestamp() - $now->getTimestamp();
        $refund = 0;
        if ($secondsUntilStart > 7200) {
            $refund = 100;
        } elseif ($secondsUntilStart > 3600) {
            $refund = 50;
        }

        return ['refund' => $refund, 'seconds_until_start' => $secondsUntilStart];
    }
}
