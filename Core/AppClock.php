<?php

declare(strict_types=1);


final class AppClock
{
    public static function timezone(): DateTimeZone
    {
        return new DateTimeZone(defined('APP_TIMEZONE') ? APP_TIMEZONE : 'UTC');
    }

    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', self::timezone());
    }

    
    public static function nowSql(): string
    {
        return self::now()->format('Y-m-d H:i:s');
    }

    public static function timestamp(): int
    {
        return self::now()->getTimestamp();
    }

    
    public static function parseSqlDatetime(string $sql): DateTimeImmutable
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $sql, self::timezone());
        if ($dt instanceof DateTimeImmutable) {
            return $dt;
        }
        
        return new DateTimeImmutable($sql, self::timezone());
    }
}
