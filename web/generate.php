<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

header('Content-Type: application/json');

$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name']  ?? '');
$org       = trim($_POST['org']        ?? '');
$tel       = trim($_POST['tel']        ?? '');
$email     = trim($_POST['email']      ?? '');
$web       = trim($_POST['web']        ?? '');
$address   = trim($_POST['address']    ?? '');

if ($firstName === '' || $lastName === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Nome e cognome sono obbligatori.']);
    exit;
}

$sanitize = fn(string $v): string => preg_replace('/[\x00-\x1f\x7f]/', '', $v);
$firstName = $sanitize($firstName);
$lastName  = $sanitize($lastName);
$org       = $sanitize($org);
$tel       = $sanitize($tel);
$email     = $sanitize($email);
$web       = $sanitize($web);
$address   = $sanitize($address);

$lines = [
    'BEGIN:VCARD',
    'VERSION:3.0',
    "N:{$lastName};{$firstName};;;",
    "FN:{$firstName} {$lastName}",
];
if ($org     !== '') $lines[] = "ORG:{$org}";
if ($tel     !== '') $lines[] = "TEL;TYPE=CELL:{$tel}";
if ($email   !== '') $lines[] = "EMAIL;TYPE=INTERNET:{$email}";
if ($web     !== '') $lines[] = "URL:{$web}";
if ($address !== '') $lines[] = "ADR;TYPE=WORK:;;{$address};;;;";
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

try {
    $qrCode = new QrCode(
        data: $vcard,
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::Medium,
        size: 600,
        margin: 20,
        roundBlockSizeMode: RoundBlockSizeMode::Margin,
        foregroundColor: new Color(0, 0, 0),
        backgroundColor: new Color(255, 255, 255),
    );

    (new PngWriter())->write($qrCode)->saveToFile($pngPath);
    (new SvgWriter())->write($qrCode)->saveToFile($svgPath);

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
