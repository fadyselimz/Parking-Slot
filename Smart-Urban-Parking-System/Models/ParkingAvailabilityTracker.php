<?php

declare(strict_types=1);

require_once __DIR__ . '/WaitlistModel.php';


final class ParkingAvailabilityTracker implements ReservationObserver
{
    public function update(ReservationEvent $event): void
    {
        $pdo = $event->pdo;
        switch ($event->type) {
            case ReservationEvent::TYPE_CANCELLED:
                
                $r = $event->payload['reservation_row'] ?? [];
                $spotId = (int)($r['spot_id'] ?? 0);
                $resId = (int)($r['reservation_id'] ?? 0);
                if ($spotId > 0 && $resId > 0) {
                    self::syncSpotStatus($pdo, $spotId, $resId);
                }
                break;

            case ReservationEvent::TYPE_COMPLETED:
                
                $r = $event->payload['reservation_row'] ?? [];
                $spotId = (int)($r['spot_id'] ?? 0);
                $resId = (int)($r['reservation_id'] ?? 0);
                if ($spotId > 0 && $resId > 0) {
                    self::syncSpotStatus($pdo, $spotId, $resId);
                    (new WaitlistModel($pdo))->notifySpotAvailable($spotId);
                }
                break;

            default:
                break;
        }
    }

    
    public static function syncSpotStatus(PDO $pdo, int $spotId, int $excludeReservationId): void
    {
        $otherActive = $pdo->prepare(
            'SELECT COUNT(*) FROM reservations WHERE spot_id=? AND reservation_id<>? AND status IN ("confirmed","active")'
        );
        $otherActive->execute([$spotId, $excludeReservationId]);
        if ((int)$otherActive->fetchColumn() === 0) {
            $pdo->prepare("UPDATE parking_spots SET status='available' WHERE spot_id=?")->execute([$spotId]);
        } else {
            $pdo->prepare("UPDATE parking_spots SET status='reserved' WHERE spot_id=?")->execute([$spotId]);
        }
    }
}
