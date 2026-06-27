<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/lib/phpmailer/PHPMailer.php';
require_once __DIR__ . '/lib/phpmailer/SMTP.php';
require_once __DIR__ . '/lib/phpmailer/PHPMailerException.php';

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/session.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Inserisci un indirizzo email valido.';
    } else {
        $user = db()->prepare('SELECT id FROM users WHERE email = ?');
        $user->execute([$email]);
        $user = $user->fetch();

        if ($user) {
            $rateCheck = db()->prepare(
                'SELECT COUNT(*) FROM otp_tokens WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)'
            );
            $rateCheck->execute([$user['id']]);
            $recentCount = (int)$rateCheck->fetchColumn();

            if ($recentCount >= 3) {
                $error = 'Troppi tentativi. Attendi qualche minuto prima di richiedere un nuovo codice.';
            } else {
                $otp  = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $hash = hash('sha256', $otp);
                $exp  = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRE_MINUTES . ' minutes'));

                db()->prepare('UPDATE otp_tokens SET used = 1 WHERE user_id = ?')
                   ->execute([$user['id']]);

                db()->prepare('INSERT INTO otp_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)')
                   ->execute([$user['id'], $hash, $exp]);

                $siteUrl = 'https://qr.ivs.ovh';
                $logoUrl = $siteUrl . '/assets/logo.svg';
                $otpMin  = OTP_EXPIRE_MINUTES;

                $otpHtml = <<<HTML
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
      <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:.5px">
        QR Code Generator
      </h1>
      <p style="margin:8px 0 0;font-size:12px;color:#94a3b8;letter-spacing:2px;text-transform:uppercase">
        by Informatica Valsusa
      </p>
    </td>
  </tr>

  <tr>
    <td style="background:#ffffff;padding:40px;border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0">
      <p style="margin:0 0 16px;font-size:15px;color:#334155;line-height:1.7">
        Hai richiesto l'accesso al QR Code Generator.<br>
        Usa il codice qui sotto per entrare:
      </p>

      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:24px 0">
            <div style="display:inline-block;background:#f8fafc;border:2px solid #e2e8f0;
                        border-radius:12px;padding:20px 40px">
              <span style="font-size:42px;font-weight:800;letter-spacing:10px;
                           color:#1e293b;font-family:monospace">{$otp}</span>
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
    </td>
  </tr>

  <tr>
    <td align="center"
        style="background:#f8fafc;border:1px solid #e2e8f0;border-top:none;
               border-radius:0 0 12px 12px;padding:20px 40px">
      <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6">
        Questa email è stata inviata automaticamente. Non rispondere.
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;

                $otpPlain = "Il tuo codice di accesso è:\n\n{$otp}\n\nValido per {$otpMin} minuti. Non condividerlo con nessuno.";

                $mail = new PHPMailer(true);
                try {
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
                    $mail->addAddress($email);
                    $mail->Subject  = 'Il tuo codice di accesso — QR Generator';
                    $mail->isHTML(true);
                    $mail->Body     = $otpHtml;
                    $mail->AltBody  = $otpPlain;
                    $mail->send();

                    $_SESSION['otp_user_id'] = $user['id'];
                    write_log('otp_requested', $user['id'], ['email' => $email]);
                    header('Location: verify.php');
                    exit;
                } catch (Exception $e) {
                    write_log('otp_send_failed', $user['id'], ['email' => $email, 'error' => $e->getMessage()]);
                    $error = 'Errore invio email. Contatta l\'amministratore.';
                }
            }
        } else {
            $error = 'Se l\'email è registrata riceverai il codice a breve.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso — QR Generator</title>
    <?php include __DIR__ . '/style.php'; ?>
</head>
<body>
<div class="auth-wrap">
<div class="auth-card">
    <div style="text-align:center;margin-bottom:1.5rem">
        <img src="/assets/logo.svg" alt="IVS" style="width:72px;height:72px;border-radius:16px">
    </div>
    <h1 style="text-align:center">Accesso</h1>

    <?php if ($error): ?>
        <div class="uk-alert-<?= str_contains($error, 'riceverai') ? 'primary' : 'danger' ?> uk-margin" uk-alert>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="uk-margin">
            <label class="uk-form-label" style="display:block;text-align:center">Email</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="email" name="email" required autofocus placeholder="la-tua@email.com">
            </div>
        </div>
        <?= csrf_field() ?>
        <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-top" type="submit">Invia codice</button>
    </form>
</div>
</div>
</body>
</html>
