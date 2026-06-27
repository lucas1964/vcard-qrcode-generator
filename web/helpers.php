<?php
declare(strict_types=1);

// ── IP ───────────────────────────────────────────────────────────────────────

function get_real_ip(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP) ?: 'unknown';
    }
    return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?: 'unknown';
}

// ── LOG ──────────────────────────────────────────────────────────────────────

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
    } catch (Throwable) {}
}

// ── EMAIL ────────────────────────────────────────────────────────────────────

function send_mail(string $to, string $subject, string $html, string $plain): bool {
    require_once __DIR__ . '/lib/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/lib/phpmailer/SMTP.php';
    require_once __DIR__ . '/lib/phpmailer/PHPMailerException.php';

    try {
        $mail             = new \PHPMailer\PHPMailer\PHPMailer(true);
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
        $mail->addAddress($to);
        $mail->Subject    = $subject;
        $mail->isHTML(true);
        $mail->Body       = $html;
        $mail->AltBody    = $plain;
        $mail->send();
        return true;
    } catch (Throwable) {
        return false;
    }
}

function email_wrap(string $body, string $footer): string {
    $logoUrl = 'https://qr.ivs.ovh/assets/logo.svg';
    return <<<HTML
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:system-ui,-apple-system,Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px">
<tr><td align="center">
<table cellpadding="0" cellspacing="0" style="max-width:560px;width:100%">
  <tr>
    <td align="center" style="background:#1e293b;border-radius:12px 12px 0 0;padding:36px 40px">
      <img src="{$logoUrl}" alt="IVS" width="72" height="72"
           style="display:block;margin:0 auto 16px;border-radius:16px">
      <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:.5px">QR Code Generator</h1>
      <p style="margin:8px 0 0;font-size:12px;color:#94a3b8;letter-spacing:2px;text-transform:uppercase">by Informatica Valsusa</p>
    </td>
  </tr>
  <tr>
    <td style="background:#ffffff;padding:40px;border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0">
      {$body}
    </td>
  </tr>
  <tr>
    <td align="center" style="background:#f8fafc;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 12px 12px;padding:20px 40px">
      <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6">{$footer}</p>
    </td>
  </tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
}

function send_otp_email(string $toEmail, string $otp): bool {
    $otpMin  = OTP_EXPIRE_MINUTES;
    $siteUrl = 'https://qr.ivs.ovh';

    $body = <<<HTML
<p style="margin:0 0 16px;font-size:15px;color:#334155;line-height:1.7">
  Hai richiesto l'accesso al QR Code Generator.<br>
  Usa il codice qui sotto per entrare:
</p>
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center" style="padding:24px 0">
      <div style="display:inline-block;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:20px 40px">
        <span style="font-size:42px;font-weight:800;letter-spacing:10px;color:#1e293b;font-family:monospace">{$otp}</span>
      </div>
    </td>
  </tr>
</table>
<p style="margin:0 0 28px;font-size:14px;color:#64748b;text-align:center;line-height:1.7">
  Valido per <strong>{$otpMin} minuti</strong>. Non condividerlo con nessuno.
</p>
<hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 24px">
<p style="margin:0;font-size:14px;color:#64748b;line-height:1.7">
  Se non hai richiesto tu questo codice, ignora questa email.<br>
  <strong style="color:#334155">Luigi Cassolini &mdash; Informatica Valsusa</strong>
</p>
HTML;

    $plain = "Il tuo codice di accesso è:\n\n{$otp}\n\nValido per {$otpMin} minuti. Non condividerlo con nessuno.";

    return send_mail($toEmail, 'Il tuo codice di accesso — QR Generator', email_wrap($body, 'Questa email è stata inviata automaticamente. Non rispondere.'), $plain);
}

function send_welcome_email(string $toEmail): bool {
    $siteUrl = 'https://qr.ivs.ovh';
    $otpMin  = OTP_EXPIRE_MINUTES;

    $body = <<<HTML
<p style="margin:0 0 16px;font-size:16px;color:#0f172a">Ciao,</p>
<p style="margin:0 0 20px;font-size:15px;color:#334155;line-height:1.7">
  il tuo account per il generatore di <strong>QR Code vCard</strong> di Informatica Valsusa è pronto.
</p>
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center" style="padding:24px 0">
      <a href="{$siteUrl}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;padding:14px 40px;border-radius:8px">
        Accedi al generatore
      </a>
    </td>
  </tr>
</table>
<p style="margin:0 0 14px;font-size:15px;color:#334155;line-height:1.7">
  Non hai bisogno di una password: inserisci la tua email e riceverai un codice di accesso valido <strong>{$otpMin} minuti</strong>.
</p>
<p style="margin:0 0 28px;font-size:15px;color:#334155;line-height:1.7">
  Una volta dentro puoi compilare il tuo profilo con i dati del biglietto da visita — la prossima volta il form sarà già precompilato.
</p>
<hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 24px">
<p style="margin:0;font-size:14px;color:#64748b;line-height:1.7">
  Per qualsiasi problema rispondi pure a questa email.<br>
  <strong style="color:#334155">Luigi Cassolini &mdash; Informatica Valsusa</strong>
</p>
HTML;

    $plain = "Ciao,\n\nil tuo account per il QR Code Generator di Informatica Valsusa è pronto.\n\nAccedi su: {$siteUrl}\n\nNon hai bisogno di una password: inserisci la tua email e riceverai un codice valido {$otpMin} minuti.\n\nLuigi Cassolini — Informatica Valsusa";

    return send_mail($toEmail, 'Il tuo accesso a QR Generator — Informatica Valsusa', email_wrap($body, 'Hai ricevuto questa email perché il tuo account è stato creato dall\'amministratore. Se non te lo aspettavi, puoi ignorarla.'), $plain);
}

function send_admin_notification(string $userEmail): void {
    $timestamp = date('d/m/Y H:i:s');

    $body = <<<HTML
<p style="margin:0 0 20px;font-size:15px;color:#334155;line-height:1.7">
  L'utente <strong>{$userEmail}</strong> ha effettuato il <strong>primo accesso</strong> al QR Generator.
</p>
<p style="margin:0;font-size:14px;color:#64748b;line-height:1.7">
  Data e ora: <strong style="color:#334155">{$timestamp}</strong>
</p>
HTML;

    $plain = "Primo accesso — QR Generator\n\nUtente: {$userEmail}\nData e ora: {$timestamp}";

    send_mail(ADMIN_EMAIL, "Primo accesso — QR Generator: {$userEmail}", email_wrap($body, 'Notifica automatica — QR Generator'), $plain);
}
