<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $port = defined('DB_PORT') ? DB_PORT : '3306';
            $db   = defined('DB_NAME') ? DB_NAME : 'parking_db';
            $user = defined('DB_USER') ? DB_USER : 'root';
            $pass = defined('DB_PASS') ? DB_PASS : '';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $host, $port, $db
            );

            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            self::syncMysqlSessionTimeZone(self::$connection);
        }

        return self::$connection;
    }

    /**
     * Keep MySQL session timezone in sync with the PHP application timezone
     * so that NOW() / CURRENT_TIMESTAMP comparisons behave correctly.
     */
    private static function syncMysqlSessionTimeZone(PDO $pdo): void
    {
        if (!defined('APP_TIMEZONE')) {
            return;
        }
        try {
            $tz            = new DateTimeZone(APP_TIMEZONE);
            $now           = new DateTimeImmutable('now', $tz);
            $offsetSeconds = $tz->getOffset($now);
            $sign          = $offsetSeconds >= 0 ? '+' : '-';
            $abs           = abs($offsetSeconds);
            $h             = intdiv($abs, 3600);
            $m             = intdiv($abs % 3600, 60);
            $pdo->exec(sprintf("SET time_zone = '%s%02d:%02d'", $sign, $h, $m));
        } catch (Throwable) {
            // Non-fatal: fall back to server default timezone.
        }
    }
}
