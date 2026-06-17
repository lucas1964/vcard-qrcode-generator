<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';
require_once __DIR__ . '/PHPMailerException.php';

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
<div class="card">
    <h1>Accesso</h1>
    <?php if ($error): ?>
        <div class="message <?= str_contains($error, 'riceverai') ? 'info' : 'error' ?>"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" required autofocus placeholder="la-tua@email.com">
        </div>
        <?= csrf_field() ?>
        <button type="submit">Invia codice</button>
    </form>
</div>
</body>
</html>
