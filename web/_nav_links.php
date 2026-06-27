<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<ul class="uk-nav uk-nav-default">
    <li class="<?= $cur === 'index.php' ? 'uk-active' : '' ?>">
        <a href="index.php">
            <span uk-icon="icon: thumbnails; ratio: .85"></span>Genera QR
        </a>
    </li>
    <li class="<?= $cur === 'profile.php' ? 'uk-active' : '' ?>">
        <a href="profile.php">
            <span uk-icon="icon: user; ratio: .85"></span>Profilo
        </a>
    </li>
    <li class="<?= $cur === 'history.php' ? 'uk-active' : '' ?>">
        <a href="history.php">
            <span uk-icon="icon: history; ratio: .85"></span>Storico
        </a>
    </li>
    <?php if (!empty($_SESSION['is_admin'])): ?>
    <li class="<?= $cur === 'admin.php' ? 'uk-active' : '' ?>">
        <a href="admin.php">
            <span uk-icon="icon: settings; ratio: .85"></span>Admin
        </a>
    </li>
    <?php endif; ?>
    <li class="uk-nav-divider"></li>
    <li>
        <a href="logout.php">
            <span uk-icon="icon: sign-out; ratio: .85"></span>Esci
        </a>
    </li>
</ul>
