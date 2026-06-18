<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$profile = db()->prepare('SELECT * FROM profiles WHERE user_id = ?');
$profile->execute([$_SESSION['user_id']]);
$p = $profile->fetch() ?: [];

function val(array $p, string $key): string {
    return htmlspecialchars($p[$key] ?? '');
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vCard QR Code Generator</title>
    <?php include __DIR__ . '/style.php'; ?>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="app-main">
    <h1 class="page-title">Genera QR Code vCard</h1>

    <form id="form" style="max-width:560px">

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Nome <span class="req">*</span></label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="first_name" placeholder="Nome" required value="<?= val($p, 'first_name') ?>">
                    </div>
                </div>
            </div>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Cognome <span class="req">*</span></label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="last_name" placeholder="Cognome" required value="<?= val($p, 'last_name') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Azienda</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="text" name="org" placeholder="Nome azienda" value="<?= val($p, 'org') ?>">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Ruolo / Mansione</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="text" name="title" placeholder="Es. Responsabile commerciale" value="<?= val($p, 'title') ?>">
            </div>
        </div>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-2-3@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Telefono fisso</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="tel" name="tel_work" placeholder="+39 011 0000000" value="<?= val($p, 'tel_work') ?>">
                    </div>
                </div>
            </div>
            <div class="uk-width-1-3@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Interno</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="tel_ext" placeholder="Es. 123" value="<?= val($p, 'tel_ext') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Cellulare</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="tel" name="tel_cell" placeholder="+39 333 0000000" value="<?= val($p, 'tel_cell') ?>">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Email</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="email" name="email" placeholder="nome@azienda.it" value="<?= val($p, 'email') ?>">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Sito web</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="url" name="web" placeholder="https://www.azienda.it" value="<?= val($p, 'web') ?>">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Via e numero civico</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="text" name="street" placeholder="Via Esempio 1" value="<?= val($p, 'street') ?>">
            </div>
        </div>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Città</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="city" placeholder="Città" value="<?= val($p, 'city') ?>">
                    </div>
                </div>
            </div>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Provincia</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="province" placeholder="TO" value="<?= val($p, 'province') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">CAP</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="zip" placeholder="00000" value="<?= val($p, 'zip') ?>">
                    </div>
                </div>
            </div>
            <div class="uk-width-1-2@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Nazione</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="text" name="country" placeholder="Italia" value="<?= val($p, 'country') ?>">
                    </div>
                </div>
            </div>
        </div>

        <?= csrf_field() ?>
        <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-top" type="submit">Genera QR Code</button>
    </form>

    <div class="uk-alert-danger uk-margin-top" id="error" style="display:none" uk-alert></div>

    <div id="result">
        <h2>Il tuo QR Code</h2>
        <img id="qr-img" src="" alt="QR Code">
        <div class="downloads">
            <a class="btn-dl btn-png" id="dl-png" href="#" download>Scarica PNG</a>
            <a class="btn-dl btn-svg" id="dl-svg" href="#" download>Scarica SVG</a>
        </div>
    </div>
</div>

<script>
document.getElementById('form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errorEl = document.getElementById('error');
    const resultEl = document.getElementById('result');
    errorEl.style.display = 'none';
    resultEl.style.display = 'none';
    const data = new FormData(this);
    try {
        const res = await fetch('generate.php', { method: 'POST', body: data });
        const json = await res.json();
        if (!res.ok || json.error) {
            errorEl.innerHTML = '<p>' + (json.error || 'Errore nella generazione.') + '</p>';
            errorEl.style.display = 'block';
            return;
        }
        document.getElementById('qr-img').src = json.png_inline;
        document.getElementById('dl-png').href = 'download.php?f=' + json.filename + '.png';
        document.getElementById('dl-png').download = json.filename + '.png';
        document.getElementById('dl-svg').href = 'download.php?f=' + json.filename + '.svg';
        document.getElementById('dl-svg').download = json.filename + '.svg';
        resultEl.style.display = 'block';
        resultEl.scrollIntoView({ behavior: 'smooth' });
    } catch (err) {
        errorEl.innerHTML = '<p>Errore di rete. Riprova.</p>';
        errorEl.style.display = 'block';
    }
});
</script>
</body>
</html>
