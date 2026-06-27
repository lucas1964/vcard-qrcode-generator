<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <title>404 - Pagina non trovata</title>
    <link rel="stylesheet" href="/assets/uikit/css/uikit.min.css">
    <script src="/assets/uikit/js/uikit.min.js"></script>
    <script src="/assets/uikit/js/uikit-icons.min.js"></script>
    <style>
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
            text-align: center;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: #e2e8f0;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }
        .error-message {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 1.75rem;
        }
        .btn-home {
            display: inline-block;
            background: #6366f1;
            color: #fff;
            padding: 0.6rem 1.5rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-home:hover { background: #4f46e5; color: #fff; text-decoration: none; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="error-code">404</div>
        <div class="error-title">Pagina non trovata</div>
        <p class="error-message">La pagina che cerchi non esiste.</p>
        <a href="/index.php" class="btn-home">
            <span uk-icon="icon: home; ratio: 0.85"></span>
            Torna alla home
        </a>
    </div>
</div>
</body>
</html>
