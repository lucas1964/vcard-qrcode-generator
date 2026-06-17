<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

session_start();

if (isset($_SESSION['user_id'])) {
    write_log('logout', $_SESSION['user_id']);
}

session_destroy();
header('Location: login.php');
exit;
