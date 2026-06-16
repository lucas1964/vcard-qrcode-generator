<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Accesso negato.');
}

$file = basename($_GET['f'] ?? '');
$path = __DIR__ . '/qr_output/' . $file;

if (!$file || !file_exists($path) || !preg_match('/\.(png|svg)$/', $file)) {
    http_response_code(404);
    exit('File non trovato.');
}

$mime = str_ends_with($file, '.svg') ? 'image/svg+xml' : 'image/png';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store');

readfile($path);
