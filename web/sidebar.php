<!-- Mobile topbar -->
<div class="app-topbar">
    <span class="app-topbar-title">QR Generator</span>
    <button class="app-topbar-btn" type="button" uk-toggle="target: #offcanvas-nav">
        <span uk-icon="menu"></span>
    </button>
</div>

<!-- Mobile offcanvas -->
<div id="offcanvas-nav" uk-offcanvas>
    <div class="uk-offcanvas-bar">
        <button class="uk-offcanvas-close" type="button" uk-close></button>
        <div class="sidebar-brand">QR Generator</div>
        <div class="sidebar-nav">
            <?php include __DIR__ . '/_nav_links.php'; ?>
        </div>
    </div>
</div>

<!-- Desktop sidebar -->
<aside class="app-sidebar">
    <div class="sidebar-brand">QR Generator</div>
    <div class="sidebar-nav">
        <?php include __DIR__ . '/_nav_links.php'; ?>
    </div>
    <div class="sidebar-footer">v2.0</div>
</aside>
