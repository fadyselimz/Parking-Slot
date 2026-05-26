<?php

declare(strict_types=1);


final class ReservationSubject
{
    private static ?self $instance = null;

    
    private array $observers = [];

    private function __construct()
    {
        
        $this->observers[] = new ParkingAvailabilityTracker();
        $this->observers[] = new NotificationService();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function attach(ReservationObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function notifyObservers(ReservationEvent $event): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($event);
        }
    }

    
    public function notify(ReservationEvent $event): void
    {
        $this->notifyObservers($event);
    }
}
