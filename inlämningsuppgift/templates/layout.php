<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Bankomat') ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0d1117;
            --surface:   #161b22;
            --border:    #30363d;
            --accent:    #00b8d4;
            --accent2:   #00d4ff;
            --text:      #e6edf3;
            --muted:     #8b949e;
            --danger:    #f85149;
            --success:   #3fb950;
            --warning:   #d29922;
            --radius:    8px;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── NAV ── */
        nav {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: 0.05em;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 0.25rem;
            flex: 1;
        }

        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            padding: 6px 14px;
            border-radius: var(--radius);
            font-size: 0.9rem;
            transition: color 0.15s, background 0.15s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--text);
            background: rgba(255,255,255,0.07);
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.85rem;
            color: var(--muted);
        }

        .nav-user strong { color: var(--text); }

        .btn-logout {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
            padding: 5px 12px;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            transition: border-color 0.15s, color 0.15s;
        }
        .btn-logout:hover { border-color: var(--danger); color: var(--danger); }

        /* ── MAIN ── */
        main {
            flex: 1;
            padding: 2.5rem 2rem;
            max-width: 960px;
            width: 100%;
            margin: 0 auto;
        }

        h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text);
        }

        h2 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--muted);
        }

        /* ── CARDS ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* ── FORMULÄR ── */
        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="number"],
        input[type="password"],
        select {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            padding: 10px 14px;
            font-size: 1rem;
            transition: border-color 0.15s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--accent);
        }

        /* ── KNAPPAR ── */
        .btn {
            display: inline-block;
            padding: 10px 22px;
            border-radius: var(--radius);
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: opacity 0.15s, transform 0.1s;
        }

        .btn:active { transform: translateY(1px); }

        .btn-primary {
            background: var(--accent);
            color: #000;
        }

        .btn-primary:hover { opacity: 0.88; }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-danger:hover { opacity: 0.88; }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .btn-secondary:hover { border-color: var(--accent); color: var(--text); }

        /* ── ALERTS ── */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 1.2rem;
            font-size: 0.9rem;
        }

        .alert-error   { background: rgba(248,81,73,0.12); border: 1px solid var(--danger); color: var(--danger); }
        .alert-success { background: rgba(63,185,80,0.12); border: 1px solid var(--success); color: var(--success); }

        /* ── TABELLER ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th {
            text-align: left;
            padding: 10px 14px;
            color: var(--muted);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(48,54,61,0.5);
            color: var(--text);
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-admin   { background: rgba(0,184,212,0.15); color: var(--accent); }
        .badge-user    { background: rgba(139,148,158,0.15); color: var(--muted); }
        .badge-deposit    { background: rgba(63,185,80,0.15); color: var(--success); }
        .badge-withdrawal { background: rgba(248,81,73,0.15); color: var(--danger); }
        .badge-transfer   { background: rgba(210,153,34,0.15); color: var(--warning); }
        .badge-bill_payment { background: rgba(0,184,212,0.15); color: var(--accent); }

        /* ── PAGINERING ── */
        .pagination {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
            align-items: center;
        }

        .pagination a, .pagination span {
            padding: 6px 12px;
            border-radius: var(--radius);
            font-size: 0.85rem;
            text-decoration: none;
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .pagination a:hover { border-color: var(--accent); color: var(--text); }
        .pagination .current { background: var(--accent); color: #000; border-color: var(--accent); }
    </style>
</head>
<body>

<nav>
    <a href="/index.php?page=dashboard" class="nav-brand">⬡ Bankomat</a>

    <div class="nav-links">
        <a href="/index.php?page=dashboard"
           <?= (($page ?? '') === 'dashboard') ? 'class="active"' : '' ?>>Dashboard</a>
        <a href="/index.php?page=withdraw"
           <?= (($page ?? '') === 'withdraw') ? 'class="active"' : '' ?>>Uttag</a>
        <a href="/index.php?page=deposit"
           <?= (($page ?? '') === 'deposit') ? 'class="active"' : '' ?>>Insättning</a>
        <a href="/index.php?page=transfer"
           <?= (($page ?? '') === 'transfer') ? 'class="active"' : '' ?>>Överföring</a>
        <a href="/index.php?page=history"
           <?= (($page ?? '') === 'history') ? 'class="active"' : '' ?>>Historik</a>
        <a href="/index.php?page=change_pin"
           <?= (($page ?? '') === 'change_pin') ? 'class="active"' : '' ?>>Byt PIN</a>
        <?php if (has_role('admin')): ?>
        <a href="/index.php?page=admin_users"
           <?= str_starts_with(($page ?? ''), 'admin') ? 'class="active"' : '' ?>>⚙ Admin</a>
        <?php endif; ?>
    </div>

    <div class="nav-user">
        <strong><?= e($_SESSION['user_name'] ?? '') ?></strong>
        <a href="/index.php?page=logout" class="btn-logout">Logga ut</a>
    </div>
</nav>

<main>
