<?php
if (!defined('APP_REMEMBER_ME_COOKIE')) {
    define('APP_REMEMBER_ME_COOKIE', 'remember_me');
}

if (!defined('APP_REMEMBER_ME_DAYS')) {
    define('APP_REMEMBER_ME_DAYS', 7);
}

if (!function_exists('appIsHttps')) {
    function appIsHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        return isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443';
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => appIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
?>
