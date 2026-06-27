<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

require_once __DIR__ . '/session.php';

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

    <ul class="uk-accordion uk-margin-top" style="max-width:560px" uk-accordion>
        <li>
            <a class="uk-accordion-title" href="#">Anteprima vCard</a>
            <div class="uk-accordion-content">
                <pre id="vcard-preview" style="background:#f8f8f8;padding:12px;border:1px solid #e0e0e0;border-radius:4px;font-size:0.85em;white-space:pre-wrap;word-break:break-all;"></pre>
            </div>
        </li>
    </ul>

    <div class="uk-alert-danger uk-margin-top" id="error" style="display:none" uk-alert></div>

    <div id="result">
        <h2>Il tuo QR Code</h2>
        <img id="qr-img" src="" alt="QR Code">
        <div class="downloads">
            <a class="btn-dl btn-png" id="dl-png" href="#" download>Scarica PNG</a>
            <a class="btn-dl btn-svg" id="dl-svg" href="#" download>Scarica SVG</a>
            <button class="btn-dl btn-copy" id="btn-copy" type="button" style="display:none">Copia immagine</button>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[type="tel"]').forEach(function(el) {
    el.addEventListener('blur', function() {
        var v = this.value.trim();
        if (v && !v.startsWith('+')) this.value = '+39 ' + v;
    });
});

function buildVCard() {
    var form = document.getElementById('form');
    var fd = new FormData(form);
    var first = (fd.get('first_name') || '').trim();
    var last  = (fd.get('last_name')  || '').trim();
    var org   = (fd.get('org')        || '').trim();
    var title = (fd.get('title')      || '').trim();
    var tel_work = (fd.get('tel_work') || '').trim();
    var tel_ext  = (fd.get('tel_ext')  || '').trim();
    var tel_cell = (fd.get('tel_cell') || '').trim();
    var email  = (fd.get('email') || '').trim();
    var web    = (fd.get('web')   || '').trim();
    var street   = (fd.get('street')   || '').trim();
    var city     = (fd.get('city')     || '').trim();
    var province = (fd.get('province') || '').trim();
    var zip      = (fd.get('zip')      || '').trim();
    var country  = (fd.get('country')  || '').trim();

    var lines = [];
    lines.push('BEGIN:VCARD');
    lines.push('VERSION:3.0');
    lines.push('N:' + last + ';' + first + ';;;');
    lines.push('FN:' + (first + ' ' + last).trim());
    if (org)   lines.push('ORG:' + org);
    if (title) lines.push('TITLE:' + title);
    if (tel_work) {
        var tel_line = 'TEL;TYPE=WORK,voice:' + tel_work;
        if (tel_ext) tel_line += ',,' + tel_ext;
        lines.push(tel_line);
    }
    if (tel_cell) lines.push('TEL;TYPE=cell:' + tel_cell);
    if (email) lines.push('EMAIL:' + email);
    if (web)   lines.push('URL:' + web);
    if (street || city || province || zip || country) {
        lines.push('ADR;TYPE=WORK:;;' + street + ';' + city + ';' + province + ';' + zip + ';' + country);
    }
    lines.push('END:VCARD');
    return lines.join('\r\n');
}

function updateVCardPreview() {
    document.getElementById('vcard-preview').textContent = buildVCard();
}

document.getElementById('form').querySelectorAll('input').forEach(function(el) {
    el.addEventListener('input', updateVCardPreview);
});

updateVCardPreview();

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
        const pngInline = json.png_inline;
        document.getElementById('qr-img').src = pngInline;
        document.getElementById('dl-png').href = 'download.php?f=' + json.filename + '.png';
        document.getElementById('dl-png').download = json.filename + '.png';
        document.getElementById('dl-svg').href = 'download.php?f=' + json.filename + '.svg';
        document.getElementById('dl-svg').download = json.filename + '.svg';

        const copyBtn = document.getElementById('btn-copy');
        copyBtn.style.display = '';
        copyBtn.onclick = async function() {
            try {
                const res2 = await fetch(pngInline);
                const blob = await res2.blob();
                await navigator.clipboard.write([
                    new ClipboardItem({ 'image/png': blob })
                ]);
                copyBtn.textContent = '✓ Copiato!';
                setTimeout(function() { copyBtn.textContent = 'Copia immagine'; }, 2000);
            } catch (err) {
                copyBtn.textContent = 'Errore copia';
                setTimeout(function() { copyBtn.textContent = 'Copia immagine'; }, 2000);
            }
        };

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
