<?php

declare(strict_types=1);


final class SubscriptionPeriodService
{
    
    public static function calendarDayDelta(DateTimeImmutable $startDate, DateTimeImmutable $endDate): int
    {
        return (int) floor(($endDate->getTimestamp() - $startDate->getTimestamp()) / 86400);
    }

    
    public static function durationWeeksFromCalendarDayDelta(int $dayDelta): int
    {
        return (int) ceil($dayDelta / 7);
    }
}
