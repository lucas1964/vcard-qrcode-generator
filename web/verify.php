<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['otp_user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $otp = preg_replace('/\D/', '', $_POST['otp'] ?? '');

    if (strlen($otp) !== 6) {
        $error = 'Il codice deve essere di 6 cifre.';
    } else {
        $hash = hash('sha256', $otp);
        $now  = date('Y-m-d H:i:s');

        $stmt = db()->prepare(
            'SELECT id FROM otp_tokens
             WHERE user_id = ? AND token_hash = ? AND used = 0 AND expires_at > ?
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$_SESSION['otp_user_id'], $hash, $now]);
        $token = $stmt->fetch();

        if ($token) {
            db()->prepare('UPDATE otp_tokens SET used = 1 WHERE id = ?')
               ->execute([$token['id']]);

            $_SESSION['user_id'] = $_SESSION['otp_user_id'];
            unset($_SESSION['otp_user_id']);

            $u = db()->prepare('SELECT is_admin FROM users WHERE id = ?');
            $u->execute([$_SESSION['user_id']]);
            $_SESSION['is_admin'] = (bool)($u->fetch()['is_admin'] ?? false);

            write_log('login_ok', $_SESSION['user_id']);
            header('Location: index.php');
            exit;
        } else {
            write_log('login_failed', $_SESSION['otp_user_id'] ?? null, ['reason' => 'invalid_otp']);
            $error = 'Codice non valido o scaduto.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica codice — QR Generator</title>
    <?php include __DIR__ . '/style.php'; ?>
</head>
<body>
<div class="auth-wrap">
<div class="auth-card">
    <h1>Inserisci il codice</h1>
    <p class="uk-text-muted uk-text-small uk-margin-small-bottom">Abbiamo inviato un codice a 6 cifre alla tua email. Valido <?= OTP_EXPIRE_MINUTES ?> minuti.</p>

    <?php if ($error): ?>
        <div class="uk-alert-danger uk-margin" uk-alert>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="uk-margin">
            <label class="uk-form-label">Codice OTP</label>
            <div class="uk-form-controls">
                <input class="uk-input uk-text-center" type="text" name="otp"
                       maxlength="6" pattern="\d{6}" inputmode="numeric"
                       required autofocus placeholder="123456"
                       style="font-size:1.5rem;letter-spacing:.4rem">
            </div>
        </div>
        <?= csrf_field() ?>
        <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-top" type="submit">Accedi</button>
    </form>

    <p class="uk-text-center uk-text-small uk-margin-top">
        <a href="login.php" class="uk-link-muted">Richiedi un nuovo codice</a>
    </p>
</div>
</div>
</body>
</html>
