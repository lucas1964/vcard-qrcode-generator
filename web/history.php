<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';

require_once __DIR__ . '/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = db()->prepare(
    "SELECT created_at, detail FROM logs WHERE user_id = ? AND event = 'qr_generated' ORDER BY created_at DESC LIMIT 50"
);
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();

$entries = [];
foreach ($rows as $row) {
    $detail = json_decode($row['detail'] ?? '{}', true) ?: [];
    $entries[] = [
        'created_at' => $row['created_at'],
        'name'       => $detail['name']     ?? '',
        'org'        => $detail['org']      ?? '',
        'filename'   => $detail['filename'] ?? '',
        'vcard'      => $detail['vcard']    ?? '',
    ];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storico QR — QR Generator</title>
    <?php include __DIR__ . '/style.php'; ?>
    <style>
        .history-table th { font-size: .8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
        .history-table td { vertical-align: middle; font-size: .9rem; }
        .history-table tr:hover td { background: #f8fafc; }
        .empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
        .empty-state span[uk-icon] { color: #cbd5e1; margin-bottom: 1rem; display: block; }

        /* QR result overlay */
        #regen-result { display: none; max-width: 520px; margin-top: 2rem; text-align: center; }
        #regen-result h2 { font-size: 1rem; font-weight: 600; color: #64748b; margin-bottom: 1rem; }
        #regen-qr-img { width: 220px; height: 220px; border: 1px solid #e2e8f0; border-radius: 8px; display: block; margin: 0 auto 1.25rem; }
        .downloads { display: flex; gap: .75rem; justify-content: center; }
        .btn-dl { display: inline-block; flex: 1; max-width: 160px; padding: .5rem; border-radius: 6px; font-size: .875rem; font-weight: 600; text-decoration: none !important; text-align: center; transition: background .15s, color .15s; line-height: 1.8; }
        .btn-png { background: #f1f5f9 !important; color: #1e293b !important; border: 1px solid #e2e8f0; }
        .btn-png:hover { background: #e2e8f0 !important; color: #1e293b !important; }
        .btn-svg { background: #6366f1 !important; color: #fff !important; border: 1px solid #6366f1; }
        .btn-svg:hover { background: #4f46e5 !important; color: #fff !important; }
    </style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="app-main">
    <h1 class="page-title">Storico QR</h1>
    <p class="page-subtitle">Gli ultimi 50 QR code generati dal tuo account.</p>

    <div id="regen-alert" style="display:none" class="uk-margin"></div>

    <div id="regen-result" class="uk-card uk-card-default uk-card-body uk-border-rounded">
        <h2>QR rigenerato</h2>
        <img id="regen-qr-img" src="" alt="QR Code">
        <div class="downloads">
            <a id="regen-dl-png" href="#" class="btn-dl btn-png">
                <span uk-icon="icon: image; ratio:.85"></span> Scarica PNG
            </a>
            <a id="regen-dl-svg" href="#" class="btn-dl btn-svg">
                <span uk-icon="icon: code; ratio:.85"></span> Scarica SVG
            </a>
        </div>
    </div>

    <?php if (empty($entries)): ?>
        <div class="uk-card uk-card-default uk-card-body uk-border-rounded empty-state">
            <span uk-icon="icon: history; ratio: 2.5"></span>
            <p>Nessun QR generato ancora.<br>
               <a href="index.php" class="uk-link-text" style="color:#6366f1">Genera il tuo primo QR</a></p>
        </div>
    <?php else: ?>
        <div class="uk-card uk-card-default uk-border-rounded" style="overflow:hidden">
            <div class="uk-overflow-auto">
                <table class="uk-table uk-table-divider uk-table-hover history-table uk-margin-remove">
                    <thead>
                        <tr>
                            <th>Data/ora</th>
                            <th>Nome</th>
                            <th>Azienda</th>
                            <th style="width:110px">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($entries as $e): ?>
                        <tr>
                            <td style="white-space:nowrap;color:#64748b;font-size:.82rem">
                                <?= htmlspecialchars(date('d/m/Y H:i', strtotime($e['created_at']))) ?>
                            </td>
                            <td><?= htmlspecialchars($e['name']) ?></td>
                            <td><?= htmlspecialchars($e['org']) ?></td>
                            <td>
                                <button
                                    class="uk-button uk-button-primary uk-button-small btn-rigenera"
                                    data-vcard="<?= htmlspecialchars($e['vcard'], ENT_QUOTES) ?>"
                                    data-filename="<?= htmlspecialchars($e['filename'], ENT_QUOTES) ?>">
                                    Rigenera
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    var csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? csrf_token()) ?>;

    document.querySelectorAll('.btn-rigenera').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var vcard    = this.dataset.vcard;
            var filename = this.dataset.filename;
            var alertEl  = document.getElementById('regen-alert');
            var resultEl = document.getElementById('regen-result');

            alertEl.style.display  = 'none';
            resultEl.style.display = 'none';

            var originalText = this.textContent;
            this.disabled    = true;
            this.textContent = 'Rigenero…';
            var self = this;

            var fd = new FormData();
            fd.append('csrf_token', csrfToken);
            fd.append('vcard',      vcard);
            fd.append('filename',   filename);

            fetch('regenerate.php', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) {
                        alertEl.innerHTML = '<div class="uk-alert-danger uk-alert"><p>' + data.error + '</p></div>';
                        alertEl.style.display = 'block';
                    } else {
                        var qrImg  = document.getElementById('regen-qr-img');
                        var dlPng  = document.getElementById('regen-dl-png');
                        var dlSvg  = document.getElementById('regen-dl-svg');

                        qrImg.src        = data.png_inline;
                        dlPng.href       = data.png_inline;
                        dlPng.download   = data.filename + '.png';
                        dlSvg.href       = 'download.php?file=' + encodeURIComponent(data.filename) + '&type=svg';
                        dlSvg.download   = data.filename + '.svg';

                        resultEl.style.display = 'block';
                        resultEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                        alertEl.innerHTML = '<div class="uk-alert-success uk-alert"><p>QR rigenerato con successo.</p></div>';
                        alertEl.style.display = 'block';
                    }
                })
                .catch(function () {
                    alertEl.innerHTML = '<div class="uk-alert-danger uk-alert"><p>Errore di rete. Riprova.</p></div>';
                    alertEl.style.display = 'block';
                })
                .finally(function () {
                    self.disabled    = false;
                    self.textContent = originalText;
                });
        });
    });
})();
</script>
</body>
</html>
