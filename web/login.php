<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

            $subject = 'Il tuo codice di accesso';
            $body    = "Il tuo codice di accesso è:\n\n{$otp}\n\nValido per " . OTP_EXPIRE_MINUTES . " minuti.";
            $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_USER . ">\r\n"
                     . "Content-Type: text/plain; charset=UTF-8\r\n";

            if (mail($email, $subject, $body, $headers)) {
                $_SESSION['otp_user_id'] = $user['id'];
                header('Location: verify.php');
                exit;
            } else {
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
        <button type="submit">Invia codice</button>
    </form>
</div>
</body>
</html>
