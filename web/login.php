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

session_start();

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
                    $mail->Subject = 'Il tuo codice di accesso';
                    $mail->Body    = "Il tuo codice di accesso è:\n\n{$otp}\n\nValido per " . OTP_EXPIRE_MINUTES . " minuti.";
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
