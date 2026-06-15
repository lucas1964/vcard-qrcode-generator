<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';
require_once __DIR__ . '/PHPMailerException.php';

use PHPMailer\PHPMailer\PHPMailer;

// Destinatario del test — cambia con la tua email
$testTo = 'tua@email.com';

echo "<pre>\n";
echo "=== Test SMTP ===\n\n";
echo "Host:       " . SMTP_HOST . "\n";
echo "Porta:      " . SMTP_PORT . "\n";
echo "Encryption: " . SMTP_ENCRYPTION . "\n";
echo "Utente:     " . SMTP_USER . "\n";
echo "Mittente:   " . SMTP_FROM_NAME . "\n\n";

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug  = 2; // Mostra tutta la comunicazione SMTP
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
    $mail->Subject = 'Test invio email — QR Generator';
    $mail->Body    = "Se ricevi questa email, l'SMTP funziona correttamente.\n\nData/ora: " . date('d/m/Y H:i:s');

    $mail->send();
    echo "\n✅ EMAIL INVIATA con successo a {$testTo}\n";

} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $mail->ErrorInfo . "\n";
}

echo "</pre>\n";
