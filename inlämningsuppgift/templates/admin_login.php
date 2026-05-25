<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serverrum – Admin</title>
    <link rel="stylesheet" href="/ATM/public/assets/css/admin.css">
    <style>
        body {
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="login-wrap">
    <div class="login-logo">
        <span class="icon">🖥</span>
        <div class="title">Serverrum — Admin</div>
        <div class="subtitle">Behörig personal only</div>
    </div>

    <div class="login-card">
        <div class="terminal-header">
            <div class="terminal-dot"></div>
            <span class="terminal-title">ATM_ADMIN_TERMINAL v2.4.1</span>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">⚠ <?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=admin_login">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="card_number">Admin-kort ID</label>
                <input
                    type="text"
                    id="card_number"
                    name="card_number"
                    maxlength="16"
                    autocomplete="off"
                    required
                    placeholder="________________"
                    value="<?= e($_POST['card_number'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="pin">Autentiseringskod (PIN)</label>
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

            <button type="submit" class="btn-login">[ Logga in ]</button>
        </form>
    </div>

    <a href="index.php?page=around-the-corner-open-door" class="back-link">← Logga ut och gå tillbaka</a>
</div>

</body>
</html>
