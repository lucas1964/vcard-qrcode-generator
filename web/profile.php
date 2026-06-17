<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $sanitize = fn(string $v): string => preg_replace('/[\x00-\x1f\x7f]/', '', trim($v));

    $fields = ['first_name','last_name','org','title','tel_work','tel_cell','email','web','street','city','province','zip','country'];
    $data   = [];
    foreach ($fields as $f) {
        $data[$f] = $sanitize($_POST[$f] ?? '');
    }

    $sql = 'UPDATE profiles SET ' . implode(', ', array_map(fn($f) => "{$f} = :{$f}", $fields)) . ' WHERE user_id = :user_id';
    $data['user_id'] = $_SESSION['user_id'];

    db()->prepare($sql)->execute($data);
    write_log('profile_updated', $_SESSION['user_id']);
    $message = 'Profilo salvato.';
}

$p = db()->prepare('SELECT * FROM profiles WHERE user_id = ?');
$p->execute([$_SESSION['user_id']]);
$p = $p->fetch() ?: [];

function val(array $p, string $key): string {
    return htmlspecialchars($p[$key] ?? '');
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo — QR Generator</title>
    <?php include __DIR__ . '/style.php'; ?>
</head>
<body>
<div class="card">
    <nav>
        <strong>Profilo</strong>
        <div>
            <a href="index.php">QR Generator</a> ·
            <a href="logout.php">Esci</a>
        </div>
    </nav>

    <h1>I tuoi dati</h1>
    <p style="color:#52525b;font-size:.88rem;margin-bottom:1.25rem">Salvati qui una volta, il form QR si precompila automaticamente.</p>

    <?php if ($message): ?>
        <div class="message <?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="row">
            <div class="field">
                <label>Nome</label>
                <input type="text" name="first_name" value="<?= val($p, 'first_name') ?>" placeholder="Mario">
            </div>
            <div class="field">
                <label>Cognome</label>
                <input type="text" name="last_name" value="<?= val($p, 'last_name') ?>" placeholder="Rossi">
            </div>
        </div>
        <div class="field">
            <label>Azienda</label>
            <input type="text" name="org" value="<?= val($p, 'org') ?>" placeholder="Acme Srl">
        </div>
        <div class="field">
            <label>Ruolo / Mansione</label>
            <input type="text" name="title" value="<?= val($p, 'title') ?>" placeholder="Finance Sales">
        </div>
        <div class="field">
            <label>Telefono fisso</label>
            <input type="tel" name="tel_work" value="<?= val($p, 'tel_work') ?>" placeholder="+39 011 9367533">
        </div>
        <div class="field">
            <label>Cellulare</label>
            <input type="tel" name="tel_cell" value="<?= val($p, 'tel_cell') ?>" placeholder="+39 333 1234567">
        </div>
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" value="<?= val($p, 'email') ?>" placeholder="mario@example.com">
        </div>
        <div class="field">
            <label>Sito web</label>
            <input type="url" name="web" value="<?= val($p, 'web') ?>" placeholder="https://example.com">
        </div>
        <div class="field">
            <label>Via e numero civico</label>
            <input type="text" name="street" value="<?= val($p, 'street') ?>" placeholder="Via Roma 1">
        </div>
        <div class="row">
            <div class="field">
                <label>Città</label>
                <input type="text" name="city" value="<?= val($p, 'city') ?>" placeholder="Milano">
            </div>
            <div class="field">
                <label>Provincia</label>
                <input type="text" name="province" value="<?= val($p, 'province') ?>" placeholder="MI">
            </div>
        </div>
        <div class="row">
            <div class="field">
                <label>CAP</label>
                <input type="text" name="zip" value="<?= val($p, 'zip') ?>" placeholder="20100">
            </div>
            <div class="field">
                <label>Nazione</label>
                <input type="text" name="country" value="<?= val($p, 'country') ?>" placeholder="Italia">
            </div>
        </div>
        <?= csrf_field() ?>
        <button type="submit">Salva profilo</button>
        <a href="index.php" class="btn btn-secondary">Torna al generatore</a>
    </form>
</div>
</body>
</html>
