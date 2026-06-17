<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

session_start();

// --- Autenticazione --------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Accesso negato.');
}

// --- Validazione del nome file ---------------------------------------------
// basename() uccide il path traversal: ../../ viene appiattito.
// Regex ancorata ^...$ con whitelist: il nome DEVE essere
// interamente [lettere/numeri/_/-] + .png oppure .svg, niente altro.
$file = basename($_GET['f'] ?? '');

if (!preg_match('/^[A-Za-z0-9_-]+\.(png|svg)$/', $file)) {
    http_response_code(404);
    exit('File non trovato.');
}

$path = __DIR__ . '/qr_output/' . $file;

// Controllo esistenza dopo la validazione del formato (ordine corretto).
if (!is_file($path)) {
    http_response_code(404);
    exit('File non trovato.');
}

// --- Header di risposta -----------------------------------------------------
$mime = str_ends_with($file, '.svg') ? 'image/svg+xml' : 'image/png';

header('Content-Type: ' . $mime);
// attachment = il browser SCARICA, non renderizza (chiude la XSS-SVG inline).
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store');
// Difesa in profondità: vieta a un eventuale render inline di eseguire script.
header("Content-Security-Policy: default-src 'none'");
// Evita sniffing del MIME da parte del browser.
header('X-Content-Type-Options: nosniff');

write_log('download', $_SESSION['user_id'], ['file' => $file]);

readfile($path);
exit;
