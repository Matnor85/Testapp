<?php

// =============================================================
//  seed.php
//  Skapar testdata i databasen.
//  Kör: php seed.php
// =============================================================

require_once __DIR__ . '/src/Database.php';

$pdo = Database::connect();

echo "Startar seeding...\n\n";

// =============================================================
//  Rensa befintlig data (i rätt ordning pga foreign keys)
// =============================================================
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE audit_log");
$pdo->exec("TRUNCATE TABLE bills");
$pdo->exec("TRUNCATE TABLE transactions");
$pdo->exec("TRs");
$pdo->exec("SET FOREIGN_KEY_UNCATE TABLE accounts");
$pdo->exec("TRUNCATE TABLE userCHECKS = 1");

echo "Rensade befintlig data.\n\n";

// =============================================================
//  Användare
//  Format: [namn, kortnummer, PIN, roll, färg]
// =============================================================
$users = [
    ['Anna Andersson', '1111111111111111', '1111', 'user',  '🔴 Röd'],
    ['björn Björkman',   '2222222222222222', '2222', 'user',  '🟡 Gul'],
    ['Cecilia Carlsson',  '3333333333333333', '3333', 'user',  '🔵 Blå'],
    ['David Davidsson',  '4444444444444444', '4444', 'user',  '🟠 Orange'],
    ['Eva Eriksson',     '5555555555555555', '5555', 'user',  '🟣 Lila'],
    ['Fredrik Fredriksson','6666666666666666','6666', 'user',  '⚫ Svart'],
    ['Gunnar Gunnarsson','7777777777777777', '7777', 'user',  '🟤 Brun'],
    ['Hanna Hansson',    '8888888888888888', '8888', 'user',  '🩷 Rosa'],
    ['Ivan Ivansson',    '9999999999999999', '9999', 'user',  '🩵 Ljusblå'],
    ['Jenny Johansson',  '0000000000000000', '0000', 'user',  '🟢 Grön'],
    ['Admin Adminsson',  '1234123412341234', '1234', 'admin', '⭐ Admin'],
];

$userIds = [];

$stmtUser = $pdo->prepare("
    INSERT INTO users (name, card_number, pin_hash, role)
    VALUES (:name, :card_number, :pin_hash, :role)
");

foreach ($users as [$name, $card, $pin, $role, $color]) {
    $stmtUser->execute([
        ':name'        => $name,
        ':card_number' => $card,
        ':pin_hash'    => password_hash($pin, PASSWORD_BCRYPT),
        ':role'        => $role,
    ]);
    $userIds[] = $pdo->lastInsertId();
    echo "Skapade användare: {$color} — {$name} (kort: {$card}, PIN: {$pin})\n";
}

echo "\n";

// =============================================================
//  Konton per användare
//  Varje användare får ett lönekonto + 0-4 sparkonton/kreditkonton
// =============================================================
$stmtAccount = $pdo->prepare("
    INSERT INTO accounts (user_id, account_type, balance, interest_rate, credit_limit, locked_until)
    VALUES (:user_id, :account_type, :balance, :interest_rate, :credit_limit, :locked_until)
");

// [user_index, typ, saldo, ränta, kreditgräns, låst till]
$accounts = [
    // Anna — lönekonto + 2 sparkonton
    [0, 'checking', 15000.00, 0.00,  null, null],
    [0, 'savings',   8000.00, 1.50,  null, null],
    [0, 'savings',   3000.00, 1.50,  null, null],

    // Björn — lönekonto + fasträntekonto
    [1, 'checking', 22000.00, 0.00,  null, null],
    [1, 'fixed',    50000.00, 3.50,  null, '2026-12-31'],

    // Cecilia — lönekonto + sparkonto + kreditkonto
    [2, 'checking', 9500.00,  0.00,  null, null],
    [2, 'savings',  12000.00, 1.50,  null, null],
    [2, 'credit',   1500.00,  19.99, 10000.00, null],

    // David — lönekonto + 3 sparkonton + fasträntekonto
    [3, 'checking', 31000.00, 0.00,  null, null],
    [3, 'savings',   5000.00, 1.50,  null, null],
    [3, 'savings',   7500.00, 1.50,  null, null],
    [3, 'savings',   2000.00, 1.50,  null, null],
    [3, 'fixed',    25000.00, 3.50,  null, '2027-06-30'],

    // Eva — bara lönekonto
    [4, 'checking',  4200.00, 0.00,  null, null],

    // Fredrik — lönekonto + kreditkonto
    [5, 'checking', 18000.00, 0.00,  null, null],
    [5, 'credit',    3200.00, 19.99, 15000.00, null],

    // Gunnar — lönekonto + sparkonto + fasträntekonto
    [6, 'checking', 11000.00, 0.00,  null, null],
    [6, 'savings',  20000.00, 1.50,  null, null],
    [6, 'fixed',    75000.00, 3.50,  null, '2026-09-30'],

    // Hanna — lönekonto + 2 sparkonton
    [7, 'checking',  6800.00, 0.00,  null, null],
    [7, 'savings',   4500.00, 1.50,  null, null],
    [7, 'savings',   9000.00, 1.50,  null, null],

    // Ivan — lönekonto + kreditkonto + fasträntekonto
    [8, 'checking', 27000.00, 0.00,  null, null],
    [8, 'credit',    5000.00, 19.99, 20000.00, null],
    [8, 'fixed',    40000.00, 3.50,  null, '2027-01-31'],

    // Jenny — lönekonto + sparkonto
    [9, 'checking',  3100.00, 0.00,  null, null],
    [9, 'savings',   1500.00, 1.50,  null, null],

    // Admin — lönekonto (för att kunna testa)
    [10, 'checking', 99999.00, 0.00, null, null],
];

$accountIds = []; // [user_index => [account_id, ...]]

$typeLabels = [
    'checking' => 'Lönekonto',
    'savings'  => 'Sparkonto',
    'fixed'    => 'Fasträntekonto',
    'credit'   => 'Kreditkonto',
];

foreach ($accounts as [$userIndex, $type, $balance, $rate, $limit, $locked]) {
    $stmtAccount->execute([
        ':user_id'      => $userIds[$userIndex],
        ':account_type' => $type,
        ':balance'      => $balance,
        ':interest_rate'=> $rate,
        ':credit_limit' => $limit,
        ':locked_until' => $locked,
    ]);
    $accountId = $pdo->lastInsertId();
    $accountIds[$userIndex][] = $accountId;

    $label = $typeLabels[$type];
    echo "{$users[$userIndex][0]}: {$label} — {$balance} kr\n";
}

echo "\n";

// =============================================================
//  Transaktioner — lite historik per användare
// =============================================================
$stmtTx = $pdo->prepare("
    INSERT INTO transactions (type, amount, from_account_id, to_account_id)
    VALUES (:type, :amount, :from_id, :to_id)
");

// Hjälpfunktion för att hämta första kontot (lönekontot) per användare
$checking = fn(int $i) => $accountIds[$i][0];

$transactions = [
    ['deposit',      5000.00, null,           $checking(0)],
    ['withdrawal',    500.00, $checking(0),   null],
    ['transfer',     2000.00, $checking(0),   $accountIds[0][1]],
    ['deposit',      8000.00, null,           $checking(1)],
    ['withdrawal',   1000.00, $checking(1),   null],
    ['bill_payment',  299.00, $checking(2),   null],
    ['deposit',      3000.00, null,           $checking(3)],
    ['transfer',     1000.00, $checking(3),   $accountIds[3][1]],
    ['withdrawal',    200.00, $checking(4),   null],
    ['deposit',     10000.00, null,           $checking(5)],
    ['withdrawal',   2500.00, $checking(6),   null],
    ['transfer',     5000.00, $checking(6),   $accountIds[6][1]],
    ['deposit',      1500.00, null,           $checking(7)],
    ['bill_payment',  850.00, $checking(8),   null],
    ['withdrawal',    100.00, $checking(9),   null],
];

foreach ($transactions as [$type, $amount, $fromId, $toId]) {
    $stmtTx->execute([
        ':type'    => $type,
        ':amount'  => $amount,
        ':from_id' => $fromId,
        ':to_id'   => $toId,
    ]);
}

echo "Skapade " . count($transactions) . " transaktioner.\n\n";

// =============================================================
//  Fakturor (bills)
// =============================================================
$stmtBill = $pdo->prepare("
    INSERT INTO bills (account_id, description, amount, due_date, paid, paid_at)
    VALUES (:account_id, :description, :amount, :due_date, :paid, :paid_at)
");

$bills = [
    [$checking(0), 'Elräkning april',        850.00, '2026-04-30', 0, null],
    [$checking(0), 'Internetabonnemang',      299.00, '2026-05-01', 0, null],
    [$checking(1), 'Hyra maj',              5500.00, '2026-05-01', 0, null],
    [$checking(2), 'Strömräkning mars',       420.00, '2026-03-31', 1, '2026-03-28 10:22:00'],
    [$checking(3), 'Försäkring',             650.00, '2026-05-15', 0, null],
    [$checking(4), 'Telefonräkning',         199.00, '2026-04-28', 0, null],
    [$checking(5), 'Kreditfaktura april',   3200.00, '2026-04-30', 0, null],
    [$checking(6), 'Hyra maj',              6200.00, '2026-05-01', 0, null],
    [$checking(7), 'Elräkning april',        510.00, '2026-04-30', 0, null],
    [$checking(8), 'Kreditfaktura april',   5000.00, '2026-04-30', 0, null],
    [$checking(9), 'Internetabonnemang',     149.00, '2026-05-01', 0, null],
];

foreach ($bills as [$accountId, $desc, $amount, $due, $paid, $paidAt]) {
    $stmtBill->execute([
        ':account_id'  => $accountId,
        ':description' => $desc,
        ':amount'      => $amount,
        ':due_date'    => $due,
        ':paid'        => $paid,
        ':paid_at'     => $paidAt,
    ]);
}

echo "Skapade " . count($bills) . " fakturor.\n\n";

// =============================================================
//  Sammanfattning för README
// =============================================================
echo "Seeding klar!\n\n";
echo "=============================================================\n";
echo "  TESTINLOGGNINGAR\n";
echo "=============================================================\n";
foreach ($users as [$name, $card, $pin, $role, $color]) {
    $roleLabel = $role === 'admin' ? ' (ADMIN)' : '';
    echo "  {$color}{$roleLabel}\n";
    echo "  Namn:      {$name}\n";
    echo "  Kort:      {$card}\n";
    echo "  PIN:       {$pin}\n";
    echo "\n";
}
echo "=============================================================\n";