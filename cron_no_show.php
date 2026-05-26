<?php


declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Models/ReservationTimeService.php';

$n = ReservationTimeService::syncNoShowStatuses(Database::getConnection());
echo "no_show updates: {$n}\n";
