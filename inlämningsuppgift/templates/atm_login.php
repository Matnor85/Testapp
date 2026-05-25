<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bankomat – Logga in</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #0d1117;
            --surface: #161b22;
            --border:  #30363d;
            --accent:  #00b8d4;
            --text:    #e6edf3;
            --muted:   #8b949e;
            --danger:  #f85149;
            --radius:  8px;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrap {
            width: 100%;
            max-width: 400px;
            padding: 1rem;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo span {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent);
            letter-spacing: 0.1em;
        }

        .login-logo p {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
        }

        h1 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text);
        }

        .form-group { margin-bottom: 1.2rem; }

        label {
            display: block;
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        input {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            padding: 11px 14px;
            font-size: 1rem;
            transition: border-color 0.15s;
            letter-spacing: 0.05em;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: opacity 0.15s;
            letter-spacing: 0.05em;
        }

        .btn-login:hover { opacity: 0.88; }

        .alert-error {
            background: rgba(248,81,73,0.12);
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 10px 14px;
            border-radius: var(--radius);
            font-size: 0.9rem;
            margin-bottom: 1.2rem;
        }

        .hint {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            font-size: 0.8rem;
            color: var(--muted);
        }

        .hint p { margin-bottom: 4px; }

        .timeout-msg {
            background: rgba(210,153,34,0.12);
            border: 1px solid #d29922;
            color: #d29922;
            padding: 10px 14px;
            border-radius: var(--radius);
            font-size: 0.9rem;
            margin-bottom: 1.2rem;
        }
    </style>
</head>
<body>

<div class="login-wrap">
    <div class="login-logo">
        <span>⬡ BANKOMAT</span>
        <p>Sätt in ditt kort och logga in</p>
    </div>

    <div class="login-card">
        <h1>Logga in</h1>

        <?php if (($_GET['reason'] ?? '') === 'timeout'): ?>
            <div class="timeout-msg">Du loggades ut automatiskt efter inaktivitet.</div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/index.php?page=atm_login">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="card_number">Kortnummer</label>
                <input
                    type="text"
                    id="card_number"
                    name="card_number"
                    maxlength="16"
                    autocomplete="off"
                    required
                    placeholder="16 siffror"
                    value="<?= e($_POST['card_number'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="pin">PIN-kod</label>
                <input
                    type="password"
                    id="pin"
                    name="pin"
                    maxlength="10"
                    autocomplete="off"
                    required
                    placeholder="••••"
                >
            </div>

            <button type="submit" class="btn-login">Logga in</button>
        </form>

        <div class="hint">
            <p><strong>Testinloggningar:</strong></p>
            <p>Användare: kort 1111111111111111, PIN 1111</p>
            <p>Admin: kort 1234123412341234, PIN 1234</p>
        </div>
    </div>
</div>

</body>
</html>
