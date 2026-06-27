<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/phpqrcode.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autenticato.']);
    exit;
}

$token         = $_POST['csrf_token'] ?? '';
$session_token = $_SESSION['csrf_token'] ?? '';
if (!$session_token || !hash_equals($session_token, $token)) {
    write_log('csrf_failed', $_SESSION['user_id'] ?? null, ['file' => 'regenerate.php']);
    http_response_code(403);
    echo json_encode(['error' => 'Richiesta non valida.']);
    exit;
}

$vcard    = $_POST['vcard']    ?? '';
$filename = $_POST['filename'] ?? '';

if ($vcard === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Dati vCard mancanti.']);
    exit;
}

// Sanitise filename: allow only safe characters
$baseName = preg_replace('/[^a-z0-9_\-]/i', '_', basename($filename));
if ($baseName === '' || $baseName === '_') {
    http_response_code(400);
    echo json_encode(['error' => 'Nome file non valido.']);
    exit;
}

$outDir = __DIR__ . '/qr_output';
if (!is_dir($outDir) && !mkdir($outDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['error' => 'Impossibile creare la directory di output.']);
    exit;
}

$pngPath = "{$outDir}/{$baseName}.png";
$svgPath = "{$outDir}/{$baseName}.svg";

try {
    QRcode::png($vcard, $pngPath, QR_ECLEVEL_H, 10, 4);
    QRcode::svg($vcard, $svgPath, QR_ECLEVEL_H, 10, 4);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore nella generazione del QR: ' . $e->getMessage()]);
    exit;
}

write_log('qr_regenerated', $_SESSION['user_id'], [
    'filename' => $baseName,
]);

$pngBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($pngPath));

echo json_encode([
    'png_inline' => $pngBase64,
    'filename'   => $baseName,
]);
