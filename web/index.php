<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vCard QR Code Generator</title>
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
            max-width: 480px;
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
        input[type="url"] {
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

        button {
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
            transition: background .15s;
        }

        button:hover { background: #4f46e5; }

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

        .downloads {
            display: flex;
            gap: .75rem;
            justify-content: center;
        }

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
        }

        .btn-png { background: #f4f4f5; color: #18181b; border: 1px solid #d4d4d8; }
        .btn-png:hover { background: #e4e4e7; }
        .btn-svg { background: #6366f1; color: #fff; }
        .btn-svg:hover { background: #4f46e5; }

        .error {
            margin-top: 1rem;
            padding: .75rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #dc2626;
            font-size: .9rem;
            display: none;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>vCard QR Code Generator</h1>

    <form id="form">
        <div class="row">
            <div class="field">
                <label>Nome <span class="req">*</span></label>
                <input type="text" name="first_name" placeholder="Mario" required>
            </div>
            <div class="field">
                <label>Cognome <span class="req">*</span></label>
                <input type="text" name="last_name" placeholder="Rossi" required>
            </div>
        </div>

        <div class="field">
            <label>Azienda</label>
            <input type="text" name="org" placeholder="Acme Srl">
        </div>

        <div class="field">
            <label>Ruolo / Mansione</label>
            <input type="text" name="title" placeholder="Finance Sales">
        </div>

        <div class="field">
            <label>Telefono fisso</label>
            <input type="tel" name="tel_work" placeholder="+39 011 9367533">
        </div>

        <div class="field">
            <label>Cellulare</label>
            <input type="tel" name="tel_cell" placeholder="+39 333 1234567">
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email" placeholder="mario@example.com">
        </div>

        <div class="field">
            <label>Sito web</label>
            <input type="url" name="web" placeholder="https://example.com">
        </div>

        <div class="field">
            <label>Via e numero civico</label>
            <input type="text" name="street" placeholder="Via Roma 1">
        </div>

        <div class="row">
            <div class="field">
                <label>Città</label>
                <input type="text" name="city" placeholder="Milano">
            </div>
            <div class="field">
                <label>Provincia</label>
                <input type="text" name="province" placeholder="MI">
            </div>
        </div>

        <div class="row">
            <div class="field">
                <label>CAP</label>
                <input type="text" name="zip" placeholder="20100">
            </div>
            <div class="field">
                <label>Nazione</label>
                <input type="text" name="country" placeholder="Italia">
            </div>
        </div>

        <button type="submit">Genera QR Code</button>
    </form>

    <div class="error" id="error"></div>

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
        document.getElementById('qr-img').src = json.png + '?t=' + Date.now();
        document.getElementById('dl-png').href = json.png;
        document.getElementById('dl-png').download = json.filename + '.png';
        document.getElementById('dl-svg').href = json.svg;
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
