<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: system-ui, -apple-system, sans-serif;
        background: #f4f4f5;
        color: #18181b;
        min-height: 100vh;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px rgba(0,0,0,.08);
        padding: 2rem;
        width: 100%;
        max-width: 520px;
    }

    h1 {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: #18181b;
    }

    .field { margin-bottom: 1rem; }

    label {
        display: block;
        font-size: .85rem;
        font-weight: 600;
        margin-bottom: .3rem;
        color: #52525b;
    }

    label .req { color: #ef4444; margin-left: 2px; }

    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="url"],
    input[type="password"] {
        width: 100%;
        padding: .55rem .75rem;
        border: 1px solid #d4d4d8;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color .15s;
        outline: none;
    }

    input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }

    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    button, .btn {
        display: inline-block;
        width: 100%;
        margin-top: 1.25rem;
        padding: .7rem;
        background: #6366f1;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        transition: background .15s;
    }

    button:hover, .btn:hover { background: #4f46e5; }

    .btn-secondary {
        background: #f4f4f5;
        color: #18181b;
        border: 1px solid #d4d4d8;
        margin-top: .5rem;
    }

    .btn-secondary:hover { background: #e4e4e7; }

    .btn-danger {
        background: #ef4444;
        margin-top: .5rem;
    }

    .btn-danger:hover { background: #dc2626; }

    #result {
        display: none;
        margin-top: 2rem;
        text-align: center;
    }

    #result h2 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #52525b;
    }

    #qr-img {
        width: 220px;
        height: 220px;
        border: 1px solid #e4e4e7;
        border-radius: 8px;
        display: block;
        margin: 0 auto 1.25rem;
    }

    .downloads { display: flex; gap: .75rem; justify-content: center; }

    .btn-dl {
        flex: 1;
        max-width: 160px;
        padding: .55rem;
        border-radius: 8px;
        font-size: .9rem;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        transition: background .15s;
        margin-top: 0;
        width: auto;
    }

    .btn-png { background: #f4f4f5; color: #18181b; border: 1px solid #d4d4d8; }
    .btn-png:hover { background: #e4e4e7; }
    .btn-svg { background: #6366f1; color: #fff; }
    .btn-svg:hover { background: #4f46e5; }

    .message {
        margin-bottom: 1rem;
        padding: .75rem;
        border-radius: 8px;
        font-size: .9rem;
    }

    .message.error   { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
    .message.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
    .message.info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; }

    nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e4e4e7;
        font-size: .85rem;
    }

    nav a { color: #6366f1; text-decoration: none; }
    nav a:hover { text-decoration: underline; }

    table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: .9rem; }
    th, td { padding: .5rem .75rem; text-align: left; border-bottom: 1px solid #e4e4e7; }
    th { font-weight: 600; color: #52525b; }
</style>
