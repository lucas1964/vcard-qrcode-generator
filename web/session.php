<?php
declare(strict_types=1);

// Cookie sicuri: HttpOnly, Secure, SameSite=Strict
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();

// Timeout sessione: 2 ore di inattività
$timeout = 7200;
if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header('Location: /login.php');
    exit;
}
$_SESSION['_last_activity'] = time();
