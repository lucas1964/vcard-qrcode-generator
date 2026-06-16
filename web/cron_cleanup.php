<?php
declare(strict_types=1);

// Sicurezza: eseguibile solo da CLI, non via browser
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Accesso negato.');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$maxAgeDays = 7; // Cancella file più vecchi di N giorni

// Pulizia file QR
$outDir  = __DIR__ . '/qr_output';
$deleted = 0;
$cutoff  = time() - ($maxAgeDays * 86400);

foreach (glob("{$outDir}/*.{png,svg}", GLOB_BRACE) as $file) {
    if (filemtime($file) < $cutoff) {
        unlink($file);
        $deleted++;
    }
}

// Pulizia OTP scaduti o usati dal DB
$stmt = db()->prepare(
    'DELETE FROM otp_tokens WHERE used = 1 OR expires_at < ?'
);
$stmt->execute([date('Y-m-d H:i:s')]);
$otpDeleted = $stmt->rowCount();

echo date('Y-m-d H:i:s') . " — QR eliminati: {$deleted}, OTP puliti: {$otpDeleted}\n";
