<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
<link rel="stylesheet" href="/assets/uikit/css/uikit.min.css">
<script src="/assets/uikit/js/uikit.min.js"></script>
<script src="/assets/uikit/js/uikit-icons.min.js"></script>
<style>
/* ── Sidebar (fixed, desktop) ── */
.app-sidebar {
    position: fixed;
    left: 0; top: 0;
    width: 240px;
    height: 100vh;
    background: #1e293b;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    z-index: 200;
}
.sidebar-brand {
    padding: 1.5rem 1.25rem 1.25rem;
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: .03em;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.sidebar-nav {
    flex: 1;
    padding: .75rem .75rem 0;
}
.sidebar-nav .uk-nav-default > li > a {
    color: #94a3b8;
    padding: .55rem .75rem;
    border-radius: 6px;
    font-size: .9rem;
    display: flex;
    align-items: center;
    gap: .55rem;
    transition: background .15s, color .15s;
    text-decoration: none;
}
.sidebar-nav .uk-nav-default > li > a:hover,
.sidebar-nav .uk-nav-default > li.uk-active > a {
    background: rgba(255,255,255,.09);
    color: #fff;
}
.sidebar-nav .uk-nav-default .uk-nav-divider {
    border-top-color: rgba(255,255,255,.08);
    margin: .5rem 0;
}
.sidebar-footer {
    padding: 1rem 1.25rem;
    font-size: .75rem;
    color: #475569;
    border-top: 1px solid rgba(255,255,255,.08);
}

/* ── Mobile topbar ── */
.app-topbar {
    display: none;
    position: sticky;
    top: 0;
    z-index: 150;
    background: #1e293b;
    padding: .75rem 1rem;
    align-items: center;
    justify-content: space-between;
    color: #fff;
}
.app-topbar-title { font-weight: 700; font-size: .95rem; }
.app-topbar-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #cbd5e1;
    padding: .25rem;
    line-height: 1;
}
.app-topbar-btn:hover { color: #fff; }

/* ── Offcanvas sidebar (mobile) ── */
#offcanvas-nav .uk-offcanvas-bar {
    background: #1e293b;
    padding: 0;
}
#offcanvas-nav .uk-offcanvas-close {
    color: #94a3b8;
    top: 1.1rem;
    right: 1rem;
}
#offcanvas-nav .sidebar-brand {
    padding-top: 1.25rem;
    padding-right: 3rem;
}
#offcanvas-nav .sidebar-nav { padding: .75rem .75rem 0; }

/* ── Main content area ── */
.app-main {
    margin-left: 240px;
    min-height: 100vh;
    background: #f1f5f9;
    padding: 2rem 2.5rem;
}
.page-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}
.page-subtitle {
    font-size: .875rem;
    color: #64748b;
    margin-top: -.75rem;
    margin-bottom: 1.25rem;
}

/* ── Forms ── */
.uk-form-label {
    font-size: .85rem;
    font-weight: 600;
    color: #475569;
}
.req { color: #ef4444; margin-left: 2px; }
.uk-input:focus, .uk-select:focus, .uk-textarea:focus {
    border-color: #6366f1;
    outline: none;
    box-shadow: 0 0 0 3px rgba(99,102,241,.15);
}

/* ── Buttons ── */
.uk-button-primary {
    background: #6366f1;
    border-color: #6366f1;
    color: #fff;
}
.uk-button-primary:hover, .uk-button-primary:focus {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #fff;
}
.uk-button-danger {
    background: #ef4444;
    border-color: #ef4444;
    color: #fff;
}
.uk-button-danger:hover {
    background: #dc2626;
    border-color: #dc2626;
}

/* ── QR result ── */
#result { display: none; margin-top: 2rem; text-align: center; }
#result h2 { font-size: 1rem; font-weight: 600; color: #64748b; margin-bottom: 1rem; }
#qr-img { width: 220px; height: 220px; border: 1px solid #e2e8f0; border-radius: 8px; display: block; margin: 0 auto 1.25rem; }
.downloads { display: flex; gap: .75rem; justify-content: center; }
.btn-dl {
    flex: 1; max-width: 160px; padding: .5rem;
    border-radius: 6px; font-size: .875rem; font-weight: 600;
    text-decoration: none; text-align: center; transition: background .15s;
}
.btn-png { background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0; }
.btn-png:hover { background: #e2e8f0; }
.btn-svg { background: #6366f1; color: #fff; border: 1px solid #6366f1; }
.btn-svg:hover { background: #4f46e5; color: #fff; }

/* ── Alerts ── */
.uk-alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; border-radius: 6px; }
.uk-alert-danger  { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; border-radius: 6px; }
.uk-alert-primary { background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; border-radius: 6px; }

/* ── Login / Verify (no sidebar) ── */
.auth-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    padding: 2rem 1rem;
}
.auth-card {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,.08);
    padding: 2.25rem 2rem;
}
.auth-card h1 {
    font-size: 1.4rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 1.5rem;
}

/* ── Responsive ── */
@media (max-width: 959px) {
    .app-sidebar  { display: none; }
    .app-topbar   { display: flex; }
    .app-main     { margin-left: 0; padding: 1.25rem 1rem; }
}
</style>
