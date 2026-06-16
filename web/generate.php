<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
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

// DEBUG TEMPORANEO — rimuovere dopo il test
echo json_encode([
    'debug' => true,
    'session_id'    => session_id(),
    'user_id'       => $_SESSION['user_id'] ?? null,
    'session_token' => substr($session_token, 0, 8) . '...',
    'post_token'    => substr($token, 0, 8) . '...',
    'match'         => hash_equals($session_token ?: '', $token),
    'post_keys'     => array_keys($_POST),
]);
exit;
// FINE DEBUG

$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name']  ?? '');
$org       = trim($_POST['org']        ?? '');
$title    = trim($_POST['title']    ?? '');
$telWork  = trim($_POST['tel_work'] ?? '');
$telCell  = trim($_POST['tel_cell'] ?? '');
$email     = trim($_POST['email']      ?? '');
$web       = trim($_POST['web']        ?? '');
$street    = trim($_POST['street']     ?? '');
$city      = trim($_POST['city']       ?? '');
$province  = trim($_POST['province']   ?? '');
$zip       = trim($_POST['zip']        ?? '');
$country   = trim($_POST['country']    ?? '');

if ($firstName === '' || $lastName === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Nome e cognome sono obbligatori.']);
    exit;
}

$sanitize = fn(string $v): string => preg_replace('/[\x00-\x1f\x7f]/', '', $v);
$firstName = $sanitize($firstName);
$lastName  = $sanitize($lastName);
$org       = $sanitize($org);
$title   = $sanitize($title);
$telWork = $sanitize($telWork);
$telCell = $sanitize($telCell);
$email     = $sanitize($email);
$web       = $sanitize($web);
$street    = $sanitize($street);
$city      = $sanitize($city);
$province  = $sanitize($province);
$zip       = $sanitize($zip);
$country   = $sanitize($country);

$lines = [
    'BEGIN:VCARD',
    'VERSION:3.0',
    "N:{$lastName};{$firstName};;;",
    "FN:{$firstName} {$lastName}",
];
if ($org     !== '') $lines[] = "ORG:{$org}";
if ($title   !== '') $lines[] = "TITLE:{$title}";
if ($telWork !== '') $lines[] = "TEL;TYPE=WORK,voice:{$telWork}";
if ($telCell !== '') $lines[] = "TEL;TYPE=cell:{$telCell}";
if ($email   !== '') $lines[] = "EMAIL;type=INTERNET;type=WORK;type=pref:{$email}";
if ($web     !== '') $lines[] = "URL:{$web}";
$hasAddress = $street || $city || $province || $zip || $country;
if ($hasAddress) $lines[] = "ADR;TYPE=WORK:;;{$street};{$city};{$province};{$zip};{$country}";
$lines[] = 'END:VCARD';

$vcard = implode("\n", $lines);

$outDir  = __DIR__ . '/qr_output';
$urlBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/qr_output';

if (!is_dir($outDir) && !mkdir($outDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['error' => 'Impossibile creare la directory di output.']);
    exit;
}

$baseName = strtolower("{$firstName}_{$lastName}");
$baseName = preg_replace('/[^a-z0-9_\-]/', '_', $baseName);
$pngPath  = "{$outDir}/{$baseName}.png";
$svgPath  = "{$outDir}/{$baseName}.svg";

// QR_ECLEVEL_M = error correction 15%, size 10 (~330px), margin 4
try {
    QRcode::png($vcard, $pngPath, QR_ECLEVEL_H, 10, 4);
    QRcode::svg($vcard, $svgPath, QR_ECLEVEL_H, 10, 4);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore nella generazione del QR: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'png'      => "{$urlBase}/{$baseName}.png",
    'svg'      => "{$urlBase}/{$baseName}.svg",
    'filename' => $baseName,
]);
