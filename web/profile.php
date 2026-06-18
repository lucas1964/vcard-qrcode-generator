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

    $fields = ['first_name','last_name','org','title','tel_work','tel_ext','tel_cell','email','web','street','city','province','zip','country'];
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

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="app-main">
    <h1 class="page-title">I tuoi dati</h1>
    <p class="page-subtitle">Salvati qui una volta, il form QR si precompila automaticamente.</p>

    <?php if ($message): ?>
        <div class="uk-alert-<?= $msgType === 'error' ? 'danger' : 'success' ?> uk-margin" uk-alert>
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>

    <form method="post" style="max-width:560px">

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Nome</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="first_name" value="<?= val($p, 'first_name') ?>" placeholder="Nome">
                    </div>
                </div>
            </div>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Cognome</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="last_name" value="<?= val($p, 'last_name') ?>" placeholder="Cognome">
                    </div>
                </div>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Azienda</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="text" name="org" value="<?= val($p, 'org') ?>" placeholder="Nome azienda">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Ruolo / Mansione</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="text" name="title" value="<?= val($p, 'title') ?>" placeholder="Es. Responsabile commerciale">
            </div>
        </div>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-2-3@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Telefono fisso</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="tel" name="tel_work" value="<?= val($p, 'tel_work') ?>" placeholder="+39 011 0000000">
                    </div>
                </div>
            </div>
            <div class="uk-width-1-3@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Interno</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="tel_ext" value="<?= val($p, 'tel_ext') ?>" placeholder="Es. 123">
                    </div>
                </div>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Cellulare</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="tel" name="tel_cell" value="<?= val($p, 'tel_cell') ?>" placeholder="+39 333 0000000">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Email</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="email" name="email" value="<?= val($p, 'email') ?>" placeholder="nome@azienda.it">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Sito web</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="url" name="web" value="<?= val($p, 'web') ?>" placeholder="https://www.azienda.it">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Via e numero civico</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="text" name="street" value="<?= val($p, 'street') ?>" placeholder="Via Esempio 1">
            </div>
        </div>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Città</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="city" value="<?= val($p, 'city') ?>" placeholder="Città">
                    </div>
                </div>
            </div>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Provincia</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="province" value="<?= val($p, 'province') ?>" placeholder="TO">
                    </div>
                </div>
            </div>
        </div>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">CAP</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="zip" value="<?= val($p, 'zip') ?>" placeholder="00000">
                    </div>
                </div>
            </div>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Nazione</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="country" value="<?= val($p, 'country') ?>" placeholder="Italia">
                    </div>
                </div>
            </div>
        </div>

        <?= csrf_field() ?>
        <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-top" type="submit">Salva profilo</button>
        <a href="index.php" class="uk-button uk-button-default uk-width-1-1 uk-margin-small-top">Torna al generatore</a>
    </form>
</div>
</body>
</html>
