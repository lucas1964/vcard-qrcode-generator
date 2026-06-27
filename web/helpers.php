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

    $siteUrl  = 'https://qr.ivs.ovh';
    $logoUrl  = $siteUrl . '/assets/logo.svg';
    $otpMin   = OTP_EXPIRE_MINUTES;

    $html = <<<HTML
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:system-ui,-apple-system,Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px">
<tr><td align="center">
<table cellpadding="0" cellspacing="0" style="max-width:560px;width:100%">

  <!-- HEADER -->
  <tr>
    <td align="center" style="background:#1e293b;border-radius:12px 12px 0 0;padding:36px 40px">
      <img src="{$logoUrl}" alt="IVS" width="72" height="72"
           style="display:block;margin:0 auto 16px;border-radius:16px">
      <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:.5px">
        QR Code Generator
      </h1>
      <p style="margin:8px 0 0;font-size:12px;color:#94a3b8;letter-spacing:2px;text-transform:uppercase">
        by Informatica Valsusa
      </p>
    </td>
  </tr>

  <!-- BODY -->
  <tr>
    <td style="background:#ffffff;padding:40px;border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0">
      <p style="margin:0 0 16px;font-size:16px;color:#0f172a">Ciao,</p>
      <p style="margin:0 0 20px;font-size:15px;color:#334155;line-height:1.7">
        il tuo account per il generatore di <strong>QR Code vCard</strong>
        di Informatica Valsusa è pronto.
      </p>

      <!-- CTA -->
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:24px 0">
            <a href="{$siteUrl}"
               style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;
                      font-size:15px;font-weight:600;padding:14px 40px;border-radius:8px">
              Accedi al generatore
            </a>
          </td>
        </tr>
      </table>

      <p style="margin:0 0 14px;font-size:15px;color:#334155;line-height:1.7">
        Non hai bisogno di una password: inserisci la tua email e riceverai
        un codice di accesso valido <strong>{$otpMin} minuti</strong>.
      </p>
      <p style="margin:0 0 28px;font-size:15px;color:#334155;line-height:1.7">
        Una volta dentro puoi compilare il tuo profilo con i dati del biglietto
        da visita — la prossima volta il form sarà già precompilato.
      </p>

      <hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 24px">

      <p style="margin:0;font-size:14px;color:#64748b;line-height:1.7">
        Per qualsiasi problema rispondi pure a questa email.<br>
        <strong style="color:#334155">Luigi Cassolini &mdash; Informatica Valsusa</strong>
      </p>
    </td>
  </tr>

  <!-- FOOTER -->
  <tr>
    <td align="center"
        style="background:#f8fafc;border:1px solid #e2e8f0;border-top:none;
               border-radius:0 0 12px 12px;padding:20px 40px">
      <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6">
        Hai ricevuto questa email perché il tuo account è stato creato dall'amministratore.<br>
        Se non te lo aspettavi, puoi ignorarla.
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;

    $plain = "Ciao,\n\n"
           . "il tuo account per il generatore di QR Code vCard di Informatica Valsusa è pronto.\n\n"
           . "Accedi su: {$siteUrl}\n\n"
           . "Non hai bisogno di una password: inserisci la tua email e riceverai\n"
           . "un codice di accesso valido {$otpMin} minuti.\n\n"
           . "Per qualsiasi problema rispondi a questa email.\n\n"
           . "Luigi Cassolini — Informatica Valsusa";

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
        $mail->Subject  = 'Il tuo accesso a QR Generator — Informatica Valsusa';
        $mail->isHTML(true);
        $mail->Body     = $html;
        $mail->AltBody  = $plain;
        $mail->send();
        return true;
    } catch (Throwable) {
        return false;
    }
}
