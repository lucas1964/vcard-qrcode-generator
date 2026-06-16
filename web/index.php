<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Carica profilo utente per precompilare il form
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
<div class="card">
    <nav>
        <strong>QR Generator</strong>
        <div>
            <?php if (!empty($_SESSION['is_admin'])): ?>
                <a href="admin.php">Admin</a> ·
            <?php endif; ?>
            <a href="profile.php">Profilo</a> ·
            <a href="logout.php">Esci</a>
        </div>
    </nav>

    <h1>Genera QR Code vCard</h1>

    <form id="form">
        <div class="row">
            <div class="field">
                <label>Nome <span class="req">*</span></label>
                <input type="text" name="first_name" placeholder="Mario" required value="<?= val($p, 'first_name') ?>">
            </div>
            <div class="field">
                <label>Cognome <span class="req">*</span></label>
                <input type="text" name="last_name" placeholder="Rossi" required value="<?= val($p, 'last_name') ?>">
            </div>
        </div>

        <div class="field">
            <label>Azienda</label>
            <input type="text" name="org" placeholder="Acme Srl" value="<?= val($p, 'org') ?>">
        </div>

        <div class="field">
            <label>Ruolo / Mansione</label>
            <input type="text" name="title" placeholder="Finance Sales" value="<?= val($p, 'title') ?>">
        </div>

        <div class="field">
            <label>Telefono fisso</label>
            <input type="tel" name="tel_work" placeholder="+39 011 9367533" value="<?= val($p, 'tel_work') ?>">
        </div>

        <div class="field">
            <label>Cellulare</label>
            <input type="tel" name="tel_cell" placeholder="+39 333 1234567" value="<?= val($p, 'tel_cell') ?>">
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email" placeholder="mario@example.com" value="<?= val($p, 'email') ?>">
        </div>

        <div class="field">
            <label>Sito web</label>
            <input type="url" name="web" placeholder="https://example.com" value="<?= val($p, 'web') ?>">
        </div>

        <div class="field">
            <label>Via e numero civico</label>
            <input type="text" name="street" placeholder="Via Roma 1" value="<?= val($p, 'street') ?>">
        </div>

        <div class="row">
            <div class="field">
                <label>Città</label>
                <input type="text" name="city" placeholder="Milano" value="<?= val($p, 'city') ?>">
            </div>
            <div class="field">
                <label>Provincia</label>
                <input type="text" name="province" placeholder="MI" value="<?= val($p, 'province') ?>">
            </div>
        </div>

        <div class="row">
            <div class="field">
                <label>CAP</label>
                <input type="text" name="zip" placeholder="20100" value="<?= val($p, 'zip') ?>">
            </div>
            <div class="field">
                <label>Nazione</label>
                <input type="text" name="country" placeholder="Italia" value="<?= val($p, 'country') ?>">
            </div>
        </div>

        <button type="submit">Genera QR Code</button>
    </form>

    <div class="message error" id="error" style="display:none;margin-top:1rem"></div>

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
            errorEl.textContent = json.error || 'Errore nella generazione.';
            errorEl.style.display = 'block';
            return;
        }
        document.getElementById('qr-img').src = 'download.php?f=' + json.filename + '.png&t=' + Date.now();
        document.getElementById('dl-png').href = 'download.php?f=' + json.filename + '.png';
        document.getElementById('dl-png').download = json.filename + '.png';
        document.getElementById('dl-svg').href = 'download.php?f=' + json.filename + '.svg';
        document.getElementById('dl-svg').download = json.filename + '.svg';
        resultEl.style.display = 'block';
        resultEl.scrollIntoView({ behavior: 'smooth' });
    } catch (err) {
        errorEl.textContent = 'Errore di rete. Riprova.';
        errorEl.style.display = 'block';
    }
});
</script>
</body>
</html>
