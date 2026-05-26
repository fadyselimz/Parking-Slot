<?php

declare(strict_types=1);


interface ReservationObserver
{
    public function update(ReservationEvent $event): void;
}
