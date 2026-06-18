<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';

session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $email   = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
    $isAdmin = isset($_POST['is_admin']) ? 1 : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email non valida.';
        $msgType = 'error';
    } else {
        try {
            db()->prepare('INSERT INTO users (email, is_admin) VALUES (?, ?)')
               ->execute([$email, $isAdmin]);
            $uid = db()->lastInsertId();
            db()->prepare('INSERT INTO profiles (user_id) VALUES (?)')->execute([$uid]);

            write_log('user_created', $_SESSION['user_id'], ['new_email' => $email, 'is_admin' => $isAdmin]);
            $message = "Utente {$email} creato.";
        } catch (PDOException $e) {
            $message = 'Email già registrata.';
            $msgType = 'error';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($uid && $uid !== (int)$_SESSION['user_id']) {
        $row = db()->prepare('SELECT email FROM users WHERE id = ?');
        $row->execute([$uid]);
        $deleted_email = $row->fetchColumn();

        db()->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
        write_log('user_deleted', $_SESSION['user_id'], ['deleted_email' => $deleted_email]);
        $message = 'Utente eliminato.';
    } else {
        $message = 'Non puoi eliminare te stesso.';
        $msgType = 'error';
    }
}

$users = db()->query(
    'SELECT u.id, u.email, u.is_admin, u.created_at,
            CONCAT(p.first_name, " ", p.last_name) AS display_name
     FROM users u LEFT JOIN profiles p ON p.user_id = u.id
     ORDER BY u.created_at DESC'
)->fetchAll();

$logs = db()->query(
    'SELECT l.created_at, l.event, l.detail, l.ip, u.email
     FROM logs l LEFT JOIN users u ON u.id = l.user_id
     ORDER BY l.created_at DESC LIMIT 100'
)->fetchAll();

$eventLabels = [
    'otp_requested'  => '📧 OTP richiesto',
    'otp_send_failed'=> '❌ OTP fallito',
    'login_ok'       => '✅ Login',
    'login_failed'   => '⚠️ Login fallito',
    'logout'         => '🚪 Logout',
    'qr_generated'   => '🔲 QR generato',
    'download'       => '⬇️ Download',
    'csrf_failed'    => '🚨 CSRF fallito',
    'user_created'   => '👤 Utente creato',
    'user_deleted'   => '🗑️ Utente eliminato',
    'profile_updated'=> '✏️ Profilo aggiornato',
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — QR Generator</title>
    <?php include __DIR__ . '/style.php'; ?>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="app-main">
    <h1 class="page-title">Gestione utenti</h1>

    <?php if ($message): ?>
        <div class="uk-alert-<?= $msgType === 'error' ? 'danger' : 'success' ?> uk-margin" uk-alert>
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>

    <form method="post" class="uk-margin-medium-bottom">
        <input type="hidden" name="action" value="create">
        <div class="uk-grid-small uk-flex-middle" uk-grid>
            <div class="uk-width-expand">
                <label class="uk-form-label">Email nuovo utente <span class="req">*</span></label>
                <div class="uk-form-controls">
                    <input class="uk-input" type="email" name="email" required placeholder="utente@example.com">
                </div>
            </div>
            <div class="uk-width-auto uk-margin-top">
                <label class="uk-flex uk-flex-middle" style="gap:.5rem;cursor:pointer;font-size:.875rem;color:#475569">
                    <input type="checkbox" name="is_admin" class="uk-checkbox">
                    Admin
                </label>
            </div>
        </div>
        <?= csrf_field() ?>
        <button class="uk-button uk-button-primary uk-margin-small-top" type="submit">Crea utente</button>
    </form>

    <table class="uk-table uk-table-divider uk-table-hover uk-table-small">
        <thead>
            <tr>
                <th>Email</th>
                <th>Nome</th>
                <th>Ruolo</th>
                <th>Creato</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars(trim($u['display_name'])) ?></td>
                <td>
                    <?php if ($u['is_admin']): ?>
                        <span class="uk-badge" style="background:#6366f1">Admin</span>
                    <?php else: ?>
                        <span class="uk-text-muted uk-text-small">Utente</span>
                    <?php endif; ?>
                </td>
                <td class="uk-text-small"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <form method="post" onsubmit="return confirm('Eliminare questo utente?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="uk-button uk-button-danger" style="padding:.25rem .6rem;font-size:.8rem;height:auto;line-height:1.5">Elimina</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h1 class="page-title uk-margin-large-top">Log attività</h1>

    <div style="overflow-x:auto">
    <table class="uk-table uk-table-divider uk-table-small" style="font-size:.8rem">
        <thead>
            <tr>
                <th>Data/ora</th>
                <th>Evento</th>
                <th>Utente</th>
                <th>Dettaglio</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td style="white-space:nowrap"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                <td style="white-space:nowrap"><?= $eventLabels[$log['event']] ?? htmlspecialchars($log['event']) ?></td>
                <td><?= htmlspecialchars($log['email'] ?? '—') ?></td>
                <td class="uk-text-muted">
                    <?php
                    if ($log['detail']) {
                        $d = json_decode($log['detail'], true);
                        echo htmlspecialchars(implode(' | ', array_map(
                            fn($k, $v) => "{$k}: {$v}",
                            array_keys($d), $d
                        )));
                    }
                    ?>
                </td>
                <td style="font-family:monospace"><?= htmlspecialchars($log['ip']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>
