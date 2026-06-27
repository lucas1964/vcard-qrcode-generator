<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';

require_once __DIR__ . '/session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autenticato.']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!$_SESSION['csrf_token'] || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Richiesta non valida.']);
    exit;
}

$filename = preg_replace('/[^a-z0-9_\-]/', '', $_POST['filename'] ?? '');
if ($filename === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Filename mancante.']);
    exit;
}

$outDir  = __DIR__ . '/qr_output';
$pngPath = "{$outDir}/{$filename}.png";
$svgPath = "{$outDir}/{$filename}.svg";

if (!file_exists($pngPath) || !file_exists($svgPath)) {
    http_response_code(404);
    echo json_encode(['error' => 'File QR non trovato. Rigenera il QR prima di inviarlo.']);
    exit;
}

// Get user email
$row = db()->prepare('SELECT email FROM users WHERE id = ?');
$row->execute([$_SESSION['user_id']]);
$toEmail = $row->fetchColumn();

if (!$toEmail) {
    http_response_code(500);
    echo json_encode(['error' => 'Email utente non trovata.']);
    exit;
}

// Build zip in a temp file
$tmpZip = tempnam(sys_get_temp_dir(), 'qr_') . '.zip';
$zip = new ZipArchive();
if ($zip->open($tmpZip, ZipArchive::CREATE) !== true) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore creazione archivio.']);
    exit;
}
$zip->addFile($pngPath, "{$filename}.png");
$zip->addFile($svgPath, "{$filename}.svg");
$zip->close();

// Send email with attachment
require_once __DIR__ . '/lib/phpmailer/PHPMailer.php';
require_once __DIR__ . '/lib/phpmailer/SMTP.php';
require_once __DIR__ . '/lib/phpmailer/PHPMailerException.php';

$displayName = str_replace('_', ' ', $filename);

$body = <<<HTML
<p style="margin:0 0 16px;font-size:15px;color:#334155;line-height:1.7">
  In allegato trovi il QR Code vCard per <strong>{$displayName}</strong>.
</p>
<p style="margin:0 0 20px;font-size:15px;color:#334155;line-height:1.7">
  L'archivio ZIP contiene:
</p>
<ul style="margin:0 0 24px;padding-left:20px;font-size:14px;color:#475569;line-height:1.9">
  <li><strong>{$filename}.png</strong> — immagine raster, ideale per stampa e digitale</li>
  <li><strong>{$filename}.svg</strong> — vettoriale, scalabile a qualsiasi dimensione senza perdita di qualità</li>
</ul>
<hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 24px">
<p style="margin:0;font-size:14px;color:#64748b;line-height:1.7">
  <strong style="color:#334155">Luigi Cassolini &mdash; Informatica Valsusa</strong>
</p>
HTML;

$plain = "Il tuo QR Code vCard per {$displayName} è in allegato.\n\nL'archivio ZIP contiene:\n- {$filename}.png (raster)\n- {$filename}.svg (vettoriale)\n\nLuigi Cassolini — Informatica Valsusa";

try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_ENCRYPTION;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
    $mail->addReplyTo(SMTP_REPLY_TO);
    $mail->addAddress($toEmail);
    $mail->Subject  = "Il tuo QR Code — {$displayName}";
    $mail->isHTML(true);
    $mail->Body     = email_wrap($body, 'Questa email è stata inviata automaticamente dal QR Generator di Informatica Valsusa.');
    $mail->AltBody  = $plain;
    $mail->addAttachment($tmpZip, "{$filename}.zip");
    $mail->send();
    $sent = true;
} catch (Throwable) {
    $sent = false;
}

@unlink($tmpZip);

if ($sent) {
    write_log('qr_sent', $_SESSION['user_id'], ['filename' => $filename, 'to' => $toEmail]);
    echo json_encode(['ok' => true, 'to' => $toEmail]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Errore invio email. Contatta l\'amministratore.']);
}
