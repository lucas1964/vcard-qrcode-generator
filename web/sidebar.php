<!-- Mobile topbar -->
<div class="app-topbar">
    <span class="app-topbar-title" style="display:flex;align-items:center;gap:.5rem">
        <img src="/assets/logo.svg" alt="IVS" style="width:28px;height:28px;border-radius:6px">
        QR Generator
    </span>
    <button class="app-topbar-btn" type="button" uk-toggle="target: #offcanvas-nav">
        <span uk-icon="menu"></span>
    </button>
</div>

<!-- Mobile offcanvas -->
<div id="offcanvas-nav" uk-offcanvas>
    <div class="uk-offcanvas-bar">
        <button class="uk-offcanvas-close" type="button" uk-close></button>
        <div class="sidebar-brand" style="display:flex;align-items:center;gap:.65rem">
            <img src="/assets/logo.svg" alt="IVS" style="width:36px;height:36px;border-radius:8px">
            QR Generator
        </div>
        <div class="sidebar-nav">
            <?php include __DIR__ . '/_nav_links.php'; ?>
        </div>
    </div>
</div>

<!-- Desktop sidebar -->
<aside class="app-sidebar">
    <div class="sidebar-brand" style="display:flex;align-items:center;gap:.65rem">
        <img src="/assets/logo.svg" alt="IVS" style="width:36px;height:36px;border-radius:8px">
        QR Generator
    </div>
    <div class="sidebar-nav">
        <?php include __DIR__ . '/_nav_links.php'; ?>
    </div>
    <div class="sidebar-footer">v2.0</div>
</aside>
