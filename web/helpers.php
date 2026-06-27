<?php
declare(strict_types=1);

function get_real_ip(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP) ?: 'unknown';
    }
    return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?: 'unknown';
}

function write_log(string $event, ?int $userId = null, array $detail = []): void {
    try {
        db()->prepare(
            'INSERT INTO logs (user_id, event, detail, ip) VALUES (?, ?, ?, ?)'
        )->execute([
            $userId,
            $event,
            $detail ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null,
            get_real_ip(),
        ]);
    } catch (Throwable) {
        // Il log non deve mai bloccare l'applicazione
    }
}

function send_welcome_email(string $toEmail): bool {
    require_once __DIR__ . '/lib/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/lib/phpmailer/SMTP.php';
    require_once __DIR__ . '/lib/phpmailer/PHPMailerException.php';

    $siteUrl = 'https://qr.ivs.ovh';

    $body = "Ciao,\n\n"
          . "il tuo account per il generatore di QR Code vCard di Informatica Valsusa è pronto.\n\n"
          . "Per accedere vai su:\n"
          . "{$siteUrl}\n\n"
          . "Non hai bisogno di una password: inserisci la tua email e riceverai\n"
          . "un codice di accesso valido " . OTP_EXPIRE_MINUTES . " minuti.\n\n"
          . "Una volta dentro puoi compilare il tuo profilo con i dati del biglietto\n"
          . "da visita — la prossima volta il form sarà già precompilato.\n\n"
          . "Per qualsiasi problema rispondi pure a questa email.\n\n"
          . "Buon lavoro,\n"
          . "Luigi Cassolini\n"
          . "Informatica Valsusa";

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
        $mail->Subject = 'Il tuo accesso a QR Generator — Informatica Valsusa';
        $mail->Body    = $body;
        $mail->send();
        return true;
    } catch (Throwable) {
        return false;
    }
}
