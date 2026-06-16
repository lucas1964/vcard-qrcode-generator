<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

session_start();

// --- Autenticazione --------------------------------------------------------
// Invariato rispetto al tuo: blocca chi non ha sessione valida.
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Accesso negato.');
}

// --- Validazione del nome file ---------------------------------------------
// basename() resta (uccide il path traversal: ../../ viene appiattito).
$file = basename($_GET['f'] ?? '');

// FIX PRINCIPALE: regex ancorata ^...$ con whitelist di caratteri.
// Prima: '/\.(png|svg)$/'  -> controllava solo la FINE, accettava
//        nomi tipo "qualsiasi cosa strana.png".
// Ora:   '/^[A-Za-z0-9_-]+\.(png|svg)$/'  -> il nome DEVE essere
//        interamente [lettere/numeri/_/-] + .png oppure .svg, niente altro.
if (!preg_match('/^[A-Za-z0-9_-]+\.(png|svg)$/', $file)) {
    http_response_code(404);
    exit('File non trovato.');
}

$path = __DIR__ . '/qr_output/' . $file;

// Controllo esistenza dopo la validazione del formato (ordine corretto:
// prima validi la forma, poi tocchi il filesystem).
if (!is_file($path)) {
    http_response_code(404);
    exit('File non trovato.');
}

// --- Header di risposta -----------------------------------------------------
// str_ends_with su un nome gia' validato: a questo punto e' sicuro.
$mime = str_ends_with($file, '.svg') ? 'image/svg+xml' : 'image/png';

header('Content-Type: ' . $mime);
// attachment = il browser SCARICA, non renderizza (chiude la XSS-SVG inline).
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store');
// Difesa in profondita': vieta a un eventuale render inline di eseguire script.
header("Content-Security-Policy: default-src 'none'");
// Evita sniffing del MIME da parte del browser.
header('X-Content-Type-Options: nosniff');

readfile($path);
exit; // chiusura netta: nessun byte accodato dopo il file.
