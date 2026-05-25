<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?></title>
    <link rel="stylesheet" href="/ATM/public/assets/css/admin.css">
</head>
<body>

<nav>
    <div class="nav-dot"></div>
    <a href="index.php?page=admin_users" class="nav-brand">ATM_ADMIN</a>

    <div class="nav-links">
        <a href="index.php?page=admin_users"
           <?= str_starts_with(($page ?? ''), 'admin_users') ? 'class="active"' : '' ?>>Users</a>
        <a href="index.php?page=admin_accounts"
           <?= (($page ?? '') === 'admin_accounts') ? 'class="active"' : '' ?>>Accounts</a>
        <a href="index.php?page=admin_transactions"
           <?= (($page ?? '') === 'admin_transactions') ? 'class="active"' : '' ?>>Transactions</a>
        <a href="index.php?page=admin_audit_log"
           <?= (($page ?? '') === 'admin_audit_log') ? 'class="active"' : '' ?>>Audit Log</a>
        <a href="index.php?page=admin_settings"
           <?= (($page ?? '') === 'admin_settings') ? 'class="active"' : '' ?>>Settings</a>
    </div>

    <div class="nav-user">
        <strong><?= e($_SESSION['user_name'] ?? '') ?></strong>
        <a href="index.php?page=admin_logout" class="btn-logout">[ Sign Out ]</a>
    </div>
</nav>

<main>
