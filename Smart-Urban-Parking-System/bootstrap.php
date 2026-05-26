<?php

declare(strict_types=1);

// ─── Load environment configuration ──────────────────────────────────────────
// If a .env file exists next to this file, parse it into defined constants.
// This keeps secrets out of source control while remaining framework-free.
(static function (): void {
    $envFile = __DIR__ . '/.env';
    if (!is_file($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and blank lines
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Strip surrounding quotes if present
        if (
            strlen($value) >= 2
            && (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            )
        ) {
            $value = substr($value, 1, -1);
        }
        if ($key !== '' && !defined($key)) {
            define($key, $value);
        }
    }
})();

// ─── Application timezone ─────────────────────────────────────────────────────
if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', 'Africa/Cairo');
}
date_default_timezone_set(APP_TIMEZONE);

// ─── Database credentials ─────────────────────────────────────────────────────
if (!defined('DB_HOST')) { define('DB_HOST', 'localhost'); }
if (!defined('DB_PORT')) { define('DB_PORT', '3306'); }
if (!defined('DB_NAME')) { define('DB_NAME', 'parking_db'); }
if (!defined('DB_USER')) { define('DB_USER', 'root'); }
if (!defined('DB_PASS')) { define('DB_PASS', ''); }

// ─── Core bootstrap ───────────────────────────────────────────────────────────
require_once __DIR__ . '/Core/Session.php';
Session::start();

require_once __DIR__ . '/Core/View.php';
require_once __DIR__ . '/Core/Auth.php';
require_once __DIR__ . '/Core/Router.php';

// ─── URL helpers ──────────────────────────────────────────────────────────────
function base_url(string $path = ''): string
{
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($base === '/' || $base === '.') {
        $base = '';
    }
    $path = '/' . ltrim($path, '/');
    return $base . ($path === '/' ? '/' : $path);
}

function route_url(string $path = ''): string
{
    $path  = '/' . ltrim($path, '/');
    $front = base_url('/index.php');
    return $front . ($path === '/' ? '' : $path);
}

function asset_url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return base_url('/assets' . $path);
}

// ─── Auto-loader ──────────────────────────────────────────────────────────────
spl_autoload_register(static function (string $class): void {
    $candidates = [
        __DIR__ . '/Controllers/' . $class . '.php',
        __DIR__ . '/Models/'      . $class . '.php',
        __DIR__ . '/Core/'        . $class . '.php',
    ];
    foreach ($candidates as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

// ─── Business-rule constants ──────────────────────────────────────────────────
if (!defined('PEAK_MULTIPLIER')) {
    define('PEAK_MULTIPLIER', (float)(defined('_ENV_PEAK_MULTIPLIER') ? _ENV_PEAK_MULTIPLIER : 1.25));
}
if (!defined('PEAK_HOURS')) {
    define('PEAK_HOURS', [
        ['start' => '08:00', 'end' => '10:00'],
        ['start' => '17:00', 'end' => '20:00'],
    ]);
}
if (!defined('SPECIAL_EVENT_WINDOWS')) {
    define('SPECIAL_EVENT_WINDOWS', []);
}
if (!defined('PENALTY_RATE_PER_MINUTE')) {
    define('PENALTY_RATE_PER_MINUTE', 0.5);
}
if (!defined('DEFAULT_PROMO_VALIDITY_MONTHS')) {
    define('DEFAULT_PROMO_VALIDITY_MONTHS', 6);
}

// ─── Static report PDF path ───────────────────────────────────────────────────
// Falls back to the bundled report inside the project directory so the system
// works out of the box without any manual configuration.
if (!defined('OWNER_REPORT_STATIC_PDF')) {
    $envPdf = defined('OWNER_REPORT_STATIC_PDF_ENV') ? constant('OWNER_REPORT_STATIC_PDF_ENV') : '';
    define(
        'OWNER_REPORT_STATIC_PDF',
        ($envPdf !== '' && is_file($envPdf))
            ? $envPdf
            : __DIR__ . '/parking_system_report.pdf'
    );
}
