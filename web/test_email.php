<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';
require_once __DIR__ . '/PHPMailerException.php';

use PHPMailer\PHPMailer\PHPMailer;

// Cambia con la tua email per ricevere il test
$testTo = 'luigi.cassolini@gmail.com';

header('Content-Type: text/plain; charset=utf-8');
echo "=== Test SMTP OVH interno ===\n\n";
echo "Host:       " . SMTP_HOST . "\n";
echo "Porta:      " . SMTP_PORT . "\n";
echo "Encryption: " . SMTP_ENCRYPTION . "\n";
echo "User:       " . SMTP_USER . "\n\n";

// Test connessione TCP prima
$errno = $errstr = '';
$sock = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 5);
if ($sock) {
    echo "✅ Porta " . SMTP_PORT . " raggiungibile\n\n";
    fclose($sock);
} else {
    echo "❌ Porta " . SMTP_PORT . " NON raggiungibile: [{$errno}] {$errstr}\n\n";
}

// Test PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug  = 2;
    $mail->Debugoutput = function(string $str, int $level) {
        echo htmlspecialchars($str) . "\n";
    };
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_ENCRYPTION;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
    $mail->addAddress($testTo);
    $mail->Subject = 'Test SMTP OVH interno';
    $mail->Body    = 'Test riuscito — ' . date('d/m/Y H:i:s');
    $mail->send();
    echo "\n✅ EMAIL INVIATA a {$testTo}\n";
} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $mail->ErrorInfo . "\n";
}
