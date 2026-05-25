<?php

// Sidor som INTE kräver databasuppkoppling
$openPages = ['home', 'around-the-corner-closed-door', 'atm_login', 'admin_login', 'atm_logout', 'admin_logout'];

// ÄNDRING: Sätt startskärmen till 'home' istället för 'atm_login' om ingen sida är vald
$page = $_GET['page'] ?? 'home';

// Sortering och ordning för Saldo-kolumnen i admin_accounts
$sort = $_GET['sort'] ?? 'balance';
$order = $_GET['order'] ?? 'DESC';
$nextOrder = ($order === 'DESC') ? 'ASC' : 'DESC';

// Ladda alltid helpers (hanterar session)
require_once __DIR__ . '/../src/helpers.php';

// Koppla till databasen endast om sidan kräver det
$dbPages = [
    'home', 'around-the-corner-open-door', 'real-atm-nr1', // Tillagda för visuella flödet
    'atm_login', 'admin_login', 'dashboard', 'withdraw', 'deposit',
    'transfer', 'history', 'change_pin',
    'admin_users', 'admin_accounts', 'admin_transactions', 'admin_audit_log',
    'admin_customer_details', 'admin_toggle_account', 'admin_settings',
    'atm_logout', 'admin_logout'
];

if (in_array($page, $dbPages)) {
    require_once __DIR__ . '/../src/Database.php';
    require_once __DIR__ . '/../src/UserRepository.php';
    require_once __DIR__ . '/../src/AccountRepository.php';
    require_once __DIR__ . '/../src/TransactionRepository.php';
    require_once __DIR__ . '/../src/AuditRepository.php';

    $pdo       = Database::connect();
    $userRepo  = new UserRepository($pdo);
    $accRepo   = new AccountRepository($pdo);
    $txRepo    = new TransactionRepository($pdo);
    $auditRepo = new AuditRepository($pdo);
}

//  ROUTING

switch ($page) {

    //  GEMENSAMMA SIDOR / VISUELLA VYER (HÄMTAS FRÅN TEMPLATES)

    case 'home':
        require __DIR__ . '/../templates/home.php';
        break;

    case 'real-atm-nr1':
        require __DIR__ . '/../templates/real-atm-nr1.php';
        break;

    case 'around-the-corner-closed-door':
        require __DIR__ . '/../templates/around-the-corner-closed-door.php';
        break;

    case 'around-the-corner-open-door':
        // TILLFÄLLIGT: Ingen säkerhetskontroll alls för att testa vyn snabbt
        require __DIR__ . '/../templates/around-the-corner-open-door.php';
        break;

    //  ATM-FLÖDE

    //  ATM LOGIN — kortnummer + PIN → endast användare

    case 'atm_login':
        // Redan inloggad som användare → dashboard
        if (!empty($_SESSION['user_id']) && ($_SESSION['context'] ?? '') === 'atm') {
            header('Location: index.php?page=dashboard');
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $cardNumber = trim($_POST['card_number'] ?? '');
            $pin        = trim($_POST['pin'] ?? '');
            $user       = $userRepo->findByCardNumber($cardNumber);

            if (!$user || !password_verify($pin, $user['pin_hash'])) {
                $error = 'Felaktigt kortnummer eller PIN-kod.';
                $auditRepo->log(null, 'atm_login_failed', "Kort: {$cardNumber}");
            } elseif ($user['role'] !== 'user') {
                $error = 'Administratörer använder serverrummet för inloggning.';
                $auditRepo->log($user['id'], 'atm_login_denied', 'Admin versucht logga in i ATM');
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['role']       = $user['role'];
                $_SESSION['context']    = 'atm';
                $_SESSION['last_active'] = time();
                $_SESSION['login_time']  = time();

                $auditRepo->log($user['id'], 'atm_login_success');
                header('Location: index.php?page=dashboard');
                exit;
            }
        }

        require __DIR__ . '/../templates/atm_login.php';
        break;

    //  ATM LOGOUT

    case 'atm_logout':
        if (!empty($_SESSION['user_id'])) {
            $auditRepo->log($_SESSION['user_id'], 'atm_logout');
        }
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: index.php?page=atm_login');
        exit;

    //  DASHBOARD — saldo + snabblänkar

    case 'dashboard':
        require_atm_login();

        $accounts           = $accRepo->findByUserId($_SESSION['user_id']);
        $recentTransactions = [];

        if (!empty($accounts)) {
            $recentTransactions = $txRepo->findByAccountId($accounts[0]['id'], 5);
        }

        require __DIR__ . '/../templates/dashboard.php';
        break;

    //  UTTAG

    case 'withdraw':
        require_atm_login();

        $accounts = $accRepo->findByUserId($_SESSION['user_id']);
        $error    = '';
        $success  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $accountId = (int) ($_POST['account_id'] ?? 0);
            $amount    = (float) ($_POST['amount'] ?? 0);

            if ($amount <= 0) {
                $error = 'Beloppet måste vara större än 0 kr.';
            } elseif (!$accRepo->belongsToUser($accountId, $_SESSION['user_id'])) {
                $error = 'Ogiltigt konto.';
            } else {
                $account = $accRepo->findById($accountId);

                if (!$account) {
                    $error = 'Kontot hittades inte.';
                } elseif ($account['balance'] < $amount) {
                    $error = 'Otillräckligt saldo. Tillgängligt: ' . format_money((float)$account['balance']);
                } else {
                    $accRepo->withdraw($accountId, $amount);
                    $txRepo->create('withdrawal', $amount, $accountId, null);
                    $auditRepo->log($_SESSION['user_id'], 'withdrawal', "Konto #{$accountId}, {$amount} kr");

                    $success  = 'Uttag på ' . format_money($amount) . ' genomfört.';
                    $accounts = $accRepo->findByUserId($_SESSION['user_id']);
                }
            }
        }

        require __DIR__ . '/../templates/withdraw.php';
        break;

    //  INSÄTTNING

    case 'deposit':
        require_atm_login();

        $accounts = $accRepo->findByUserId($_SESSION['user_id']);
        $error    = '';
        $success  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $accountId = (int) ($_POST['account_id'] ?? 0);
            $amount    = (float) ($_POST['amount'] ?? 0);

            if ($amount <= 0) {
                $error = 'Beloppet måste vara större än 0 kr.';
            } elseif (!$accRepo->belongsToUser($accountId, $_SESSION['user_id'])) {
                $error = 'Ogiltigt konto.';
            } else {
                $accRepo->deposit($accountId, $amount);
                $txRepo->create('deposit', $amount, null, $accountId);
                $auditRepo->log($_SESSION['user_id'], 'deposit', "Konto #{$accountId}, {$amount} kr");

                $success  = 'Insättning på ' . format_money($amount) . ' genomförd.';
                $accounts = $accRepo->findByUserId($_SESSION['user_id']);
            }
        }

        require __DIR__ . '/../templates/deposit.php';
        break;

    //  ÖVERFÖRING

    case 'transfer':
        require_atm_login();

        $accounts = $accRepo->findByUserId($_SESSION['user_id']);
        $error    = '';
        $success  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $fromId = (int) ($_POST['from_account_id'] ?? 0);
            $toId   = (int) ($_POST['to_account_id']   ?? 0);
            $amount = (float) ($_POST['amount'] ?? 0);

            if ($fromId === $toId) {
                $error = 'Från- och till-konto kan inte vara samma.';
            } elseif ($amount <= 0) {
                $error = 'Beloppet måste vara större än 0 kr.';
            } elseif (!$accRepo->belongsToUser($fromId, $_SESSION['user_id'])) {
                $error = 'Ogiltigt från-konto.';
            } elseif (!$accRepo->belongsToUser($toId, $_SESSION['user_id'])) {
                $error = 'Ogiltigt till-konto.';
            } else {
                $fromAccount = $accRepo->findById($fromId);

                if ($fromAccount['balance'] < $amount) {
                    $error = 'Otillräckligt saldo. Tillgängligt: ' . format_money((float)$fromAccount['balance']);
                } else {
                    $pdo->beginTransaction();
                    try {
                        $accRepo->withdraw($fromId, $amount);
                        $accRepo->deposit($toId, $amount);
                        $txRepo->create('transfer', $amount, $fromId, $toId);
                        $pdo->commit();

                        $auditRepo->log($_SESSION['user_id'], 'transfer', "Från #{$fromId} till #{$toId}, {$amount} kr");
                        $success  = 'Överföring på ' . format_money($amount) . ' genomförd.';
                        $accounts = $accRepo->findByUserId($_SESSION['user_id']);
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = 'Något gick fel. Försök igen.';
                    }
                }
            }
        }

        require __DIR__ . '/../templates/transfer.php';
        break;

    //  HISTORIK

    case 'history':
        require_atm_login();

        $accounts          = $accRepo->findByUserId($_SESSION['user_id']);
        $selectedAccountId = (int) ($_GET['account_id'] ?? ($accounts[0]['id'] ?? 0));
        $perPage           = 20;
        $currentPage       = max(1, (int) ($_GET['p'] ?? 1));
        $offset            = ($currentPage - 1) * $perPage;

        if (!$accRepo->belongsToUser($selectedAccountId, $_SESSION['user_id'])) {
            $selectedAccountId = $accounts[0]['id'] ?? 0;
        }

        $totalCount   = $txRepo->countByAccountId($selectedAccountId);
        $totalPages   = (int) ceil($totalCount / $perPage);
        $transactions = $txRepo->findByAccountId($selectedAccountId, $perPage, $offset);

        require __DIR__ . '/../templates/history.php';
        break;

    //  BYT PIN

    case 'change_pin':
        require_atm_login();

        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $currentPin = $_POST['current_pin'] ?? '';
            $newPin     = $_POST['new_pin']     ?? '';
            $confirmPin = $_POST['confirm_pin'] ?? '';
            $user       = $userRepo->findById($_SESSION['user_id']);

            if (!password_verify($currentPin, $user['pin_hash'])) {
                $error = 'Nuvarande PIN-kod är felaktig.';
            } elseif (strlen($newPin) < 4) {
                $error = 'Ny PIN-kod måste vara minst 4 tecken.';
            } elseif ($newPin !== $confirmPin) {
                $error = 'PIN-koderna matchar inte.';
            } else {
                $userRepo->updatePin($_SESSION['user_id'], password_hash($newPin, PASSWORD_BCRYPT));
                $auditRepo->log($_SESSION['user_id'], 'change_pin');
                $success = 'PIN-koden har bytts.';
            }
        }

        require __DIR__ . '/../templates/change_pin.php';
        break;

    //  ADMIN-FLÖDE

    //  ADMIN LOGIN

    case 'admin_login':
        // FIX: Tog bort inledande "/" så att du stannar kvar i din ATM-projektmapp
        if (!empty($_SESSION['user_id']) && ($_SESSION['context'] ?? '') === 'admin') {
            header('Location: index.php?page=admin_users');
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $cardNumber = trim($_POST['card_number'] ?? '');
            $pin        = trim($_POST['pin'] ?? '');
            $user       = $userRepo->findByCardNumber($cardNumber);

            if (!$user || !password_verify($pin, $user['pin_hash'])) {
                $error = 'Felaktigt kortnummer eller PIN-kod.';
                $auditRepo->log(null, 'admin_login_failed', "Kort: {$cardNumber}");
            } elseif ($user['role'] !== 'admin') {
                $error = 'Du har inte behörighet till adminpanelen.';
                $auditRepo->log($user['id'], 'admin_login_denied', 'Användare försökte nå admin');
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id']     = $user['id'];
                $_SESSION['user_name']   = $user['name'];
                $_SESSION['role']        = $user['role'];
                $_SESSION['context']     = 'admin';
                $_SESSION['last_active'] = time();
                $_SESSION['login_time']  = time();

                $auditRepo->log($user['id'], 'admin_login_success');
                
                // FIX: Skicka administratören direkt till adminverktygen istället för startskärmen
                header('Location: index.php?page=admin_users'); 
                exit;
            }
        }

        require __DIR__ . '/../templates/admin_login.php';
        break;

    //  ADMIN LOGOUT

    case 'admin_logout':
        if (!empty($_SESSION['user_id'])) {
            $auditRepo->log($_SESSION['user_id'], 'admin_logout');
        }
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: index.php?page=admin_login');
        exit;

    //  ADMIN – ANVÄNDARE (CRUD)

    case 'admin_users':
        require_admin_login();

        $users      = $userRepo->findAll();
        $action     = $_GET['action'] ?? '';
        $editUser   = null;
        $error      = '';
        $successMsg = '';

        if (in_array($action, ['edit', 'delete']) && isset($_GET['user_id'])) {
            $editUser = $userRepo->findById((int) $_GET['user_id']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $postAction = $_POST['action'] ?? '';

            if ($postAction === 'create') {
                $name        = trim($_POST['name'] ?? '');
                $pin         = $_POST['pin'] ?? '';
                $role        = in_array($_POST['role'] ?? '', ['user', 'admin']) ? $_POST['role'] : 'user';
                $accountType = trim($_POST['account_type'] ?? 'Lönekonto'); // Hämta vår nya kontotyp

                if (empty($name)) {
                    $error = 'Namn måste fyllas i.';
                } elseif (strlen($pin) < 4) {
                    $error = 'PIN-koden måste vara minst 4 tecken.';
                } else {
                    // 1. GENERERA ETT UNIKT 16-SIFFRIGT KORTNUMMER AUTOMATISKT
                    // Loopar 16 gånger och slumpar fram siffror mellan 0 och 9
                    $cardNumber = '';
                    for ($i = 0; $i < 16; $i++) {
                        $cardNumber .= random_int(0, 9);
                    }

                    // Säkerställ att det slumpade numret inte råkar krocka i databasen
                    if ($userRepo->cardNumberExists($cardNumber)) {
                        // En extremt osannolik krock skedde – vi slumpar en gång till för säkerhets skull
                        $cardNumber = '';
                        for ($i = 0; $i < 16; $i++) {
                            $cardNumber .= random_int(0, 9);
                        }
                    }

                    // 2. SKAPA ANVÄNDAREN I DATABASEN
                    // Vi skickar in det nyss slumpade $cardNumber här istället för $_POST
                    // OBS: Om ditt system kräver att du även anropar $accRepo->create() för kontotypen separat, 
                    // kan du lägga till den raden här under, t.ex: $accRepo->create($userId, $cardNumber, $accountType);
                    $userRepo->create($name, $cardNumber, password_hash($pin, PASSWORD_BCRYPT), $role);
                    
                    // 3. LOGGA TILL AUDIT-LOGGEN (Med det nyskapade kontonumret och typen i detaljerna)
                    $adminId = $_SESSION['user_id'] ?? null;
                    $auditRepo->log($adminId, 'admin_create_user', "Skapade {$name} med ett {$accountType} (Kort: {$cardNumber})");
                    
                    $successMsg = "Användaren {$name} har skapats med kortnummer: {$cardNumber}";
                    $users  = $userRepo->findAll();
                    $action = '';
                }

            } elseif ($postAction === 'edit') {
                $userId     = (int) ($_POST['user_id'] ?? 0);
                $name       = trim($_POST['name'] ?? '');
                $cardNumber = trim($_POST['card_number'] ?? '');
                $role       = in_array($_POST['role'] ?? '', ['user', 'admin']) ? $_POST['role'] : 'user';

                if (strlen($cardNumber) !== 16 || !ctype_digit($cardNumber)) {
                    $error    = 'Kortnumret måste vara exakt 16 siffror.';
                    $editUser = $userRepo->findById($userId);
                    $action   = 'edit';
                } elseif ($userRepo->cardNumberExists($cardNumber, $userId)) {
                    $error    = 'Kortnumret används redan av en annan användare.';
                    $editUser = $userRepo->findById($userId);
                    $action   = 'edit';
                } else {
                    $userRepo->update($userId, $name, $cardNumber, $role);
                    $auditRepo->log($_SESSION['user_id'], 'admin_edit_user', "Redigerade user #{$userId}");
                    $successMsg = 'Användaren har uppdaterats.';
                    $users  = $userRepo->findAll();
                    $action = '';
                }

            } elseif ($postAction === 'delete_confirm') {
                $userId = (int) ($_POST['user_id'] ?? 0);

                if ($userId === (int) $_SESSION['user_id']) {
                    $error = 'Du kan inte ta bort dig själv.';
                } else {
                    $userRepo->delete($userId);
                    $auditRepo->log($_SESSION['user_id'], 'admin_delete_user', "Raderade user #{$userId}");
                    $successMsg = 'Användaren har tagits bort.';
                    $users = $userRepo->findAll();
                }
            }
        }

        require __DIR__ . '/../templates/admin/users.php';
        break;

    //  ADMIN – KONTON

    case 'admin_accounts':
        require_admin_login();
        
        $customers = $accRepo->getCustomersOverview($sort, $order);
        require __DIR__ . '/../templates/admin/accounts.php';
        break;

case 'admin_customer_details':
        require_admin_login();

        // 1. DEFINIERA ID OCH KUND DIREKT HÖGST UPP (Så att alla POST-block kan använda dem!)
        $customerId = (int)($_GET['id'] ?? 0);
        $customer = $userRepo->getUserById($customerId); 
        
        // HÄR FÅNGAR VI UPP OM FORMULÄRET SKICKATS (ÖPPNA KONTO)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['admin_action'] ?? '') === 'open_account') {
            csrf_verify();

            $userId      = (int)($_POST['user_id'] ?? 0);
            $accountType = trim($_POST['account_type'] ?? 'checking');
            $insertCash  = trim($_POST['insertCash'] ?? '');
            $initialBalance = ($insertCash !== '') ? (float)$insertCash : 0.00;

            if ($userId > 0) {
                $newAccountId = $accRepo->create($userId, $accountType, $initialBalance);

                if ($newAccountId) {
                    if ($initialBalance > 0) {
                        $txRepo->create('deposit', $initialBalance, null, $newAccountId);
                    }

                    $typeLabels = ['checking' => 'Lönekonto', 'savings' => 'Sparkonto', 'fixed' => 'Fasträntekonto', 'credit' => 'Kreditkonto'];
                    $labelText = $typeLabels[$accountType] ?? $accountType;

                    $adminId = $_SESSION['user_id'] ?? null;
                    $auditRepo->log($adminId, 'admin_create_account', "Öppnade ett nytt {$labelText} (Konto ID: #{$newAccountId}) för användare ID #{$userId} med startbalans " . number_format($initialBalance, 2, ',', ' ') . " kr.");

                    header("Location: index.php?page=admin_customer_details&id=" . $userId . "&success=" . urlencode("Kontot har öppnats!"));
                    exit;
                }
            }
        }
        
        // HÄR FÅNGAR VI UPP ÖVERFÖRINGEN
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['admin_action'] ?? '') === 'admin_transfer') {
            csrf_verify();

            $fromAccountId = (int)($_POST['from_account_id'] ?? 0);
            $internalTo    = (int)($_POST['to_account_id_internal'] ?? 0);
            $externalTo    = (int)($_POST['to_account_id_external'] ?? 0);
            $amount        = (float)($_POST['amount'] ?? 0);

            // Bestäm vilket mottagarkonto som ska användas
            $toAccountId = ($externalTo > 0) ? $externalTo : $internalTo;

            // Validering
            if ($fromAccountId === 0 || $toAccountId === 0 || $amount <= 0) {
                header("Location: index.php?page=admin_customer_details&id={$customerId}&error=" . urlencode("Ogiltiga konton eller belopp."));
                exit;
            }

            if ($fromAccountId === $toAccountId) {
                header("Location: index.php?page=admin_customer_details&id={$customerId}&error=" . urlencode("Du kan inte överföra till och från samma konto."));
                exit;
            }

            // Kontrollera att mottagarkontot existerar
            $toAccount = $accRepo->findById($toAccountId);
            if (!$toAccount) {
                header("Location: index.php?page=admin_customer_details&id={$customerId}&error=" . urlencode("Mottagarkontot (ID #{$toAccountId}) existerar inte."));
                exit;
            }

            // Utför transaktionen
            $withdrawalSuccessful = $accRepo->withdraw($fromAccountId, $amount);

            if ($withdrawalSuccessful) {
                $accRepo->deposit($toAccountId, $amount);

                // Registrera i transaktionshistoriken
                $txRepo->create('transfer', $amount, $fromAccountId, $toAccountId);

                // Skriv till Audit-loggen
                $adminId = $_SESSION['user_id'] ?? null;
                $auditRepo->log($adminId, 'admin_transfer', "Administratör överförde " . number_format($amount, 2, ',', ' ') . " kr från Konto #{$fromAccountId} till Konto #{$toAccountId}.");

                header("Location: index.php?page=admin_customer_details&id={$customerId}&success=" . urlencode("Överföringen genomfördes utan problem!"));
                exit;
            } else {
                header("Location: index.php?page=admin_customer_details&id={$customerId}&error=" . urlencode("Överföringen misslyckades. Kontrollera att det finns tillräckligt saldo på källkontot."));
                exit;
            }
        }
        
        // Hämta konton till vyn (görs bara om ingen POST har gjort en redirect)
        $accounts = $accRepo->getAccountsByUserId($customerId);
        
        require __DIR__ . '/../templates/admin/customer_details.php';
        break;

    case 'admin_toggle_account':
        require_admin_login(); // Säkerhetskoll så bara inloggade admins kan göra detta

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accountId     = (int)($_POST['account_id'] ?? 0);
            $userId        = (int)($_POST['user_id'] ?? 0);
            $currentStatus = (int)($_POST['current_status'] ?? 0);

            // Om det var 1 (aktivt), ska det bli 0 (låst) och vice versa
            
            // ÄNDRING: Använd engelska statusnycklar för att undvika Å/Ä/Ö i databassökningen
            $newStatus = ($currentStatus === 1) ? 0 : 1;
            $statusText = ($newStatus === 0) ? 'LOCKED' : 'UNLOCKED'; 
            $statusLogText = ($newStatus === 0) ? 'LÅST' : 'UPPLÅST';

            // 1. Kör uppdateringen i MySQL för själva kontot
            $accRepo->updateAccountStatus($accountId, $newStatus);
            
            // 2. Hämta den inloggade adminens ID från sessionen
            $adminId = $_SESSION['admin_user_id'] ?? $_SESSION['user_id'] ?? null;

            // 3. SKRIV TILL AUDIT-LOGGEN
            $action = "ACCOUNT_" . $statusText; // Blir nu 'ACCOUNT_LOCKED' eller 'ACCOUNT_UNLOCKED'
            $description = "Ändrade status på konto #{$accountId} till {$statusLogText} för användare ID #{$userId}.";
            
            $auditRepo->log($adminId, $action, $description);
            // Skicka tillbaka admin till kundens detaljsida direkt
            header("Location: index.php?page=admin_customer_details&id=" . $userId);
            exit;
        }
        break;

    //  ADMIN – TRANSAKTIONER

    case 'admin_transactions':
        require_admin_login();

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $filterType     = $_GET['type']      ?? null;
            $filterDateFrom = $_GET['date_from'] ?? null;
            $filterDateTo   = $_GET['date_to']   ?? null;

            $all = $txRepo->findAll($filterType ?: null, $filterDateFrom ?: null, $filterDateTo ?: null, 10000, 0);

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="transaktioner.csv"');
            echo "\xEF\xBB\xBF";

            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Typ', 'Belopp', 'Från konto', 'Till konto', 'Datum'], ';');
            foreach ($all as $tx) {
                fputcsv($out, [
                    $tx['id'],
                    tx_type_label($tx['type']),
                    number_format($tx['amount'], 2, ',', ' '),
                    $tx['from_account_id'] ?? '',
                    $tx['to_account_id']   ?? '',
                    $tx['created_at'],
                ], ';');
            }
            fclose($out);
            exit;
        }

        $filterType     = $_GET['type']      ?? null;
        $filterDateFrom = $_GET['date_from'] ?? null;
        $filterDateTo   = $_GET['date_to']   ?? null;
        $perPage        = 20;
        $currentPage    = max(1, (int) ($_GET['p'] ?? 1));
        $offset         = ($currentPage - 1) * $perPage;

        $totalCount   = $txRepo->countAll($filterType ?: null, $filterDateFrom ?: null, $filterDateTo ?: null);
        $totalPages   = (int) ceil($totalCount / $perPage);
        $transactions = $txRepo->findAll($filterType ?: null, $filterDateFrom ?: null, $filterDateTo ?: null, $perPage, $offset);

        require __DIR__ . '/../templates/admin/transactions.php';
        break;

    //  ADMIN – AUDIT-LOGG

    case 'admin_audit_log':
        require_admin_login();

        // 1. Fånga upp filter-parametrar från GET
        $filterType     = $_GET['type'] ?? '';
        $filterDateFrom = $_GET['date_from'] ?? '';
        $filterDateTo   = $_GET['date_to'] ?? '';

        // 2. Hantera paginering
        $currentPage = (int)($_GET['p'] ?? 1);
        if ($currentPage < 1) $currentPage = 1;
        $limit = 20;
        $offset = ($currentPage - 1) * $limit;

        // 3. Hämta filtrerad data och räkna totalen utifrån filtret
        $logs = $auditRepo->findFiltered($filterType, $filterDateFrom, $filterDateTo, $limit, $offset);
        $totalLogs = $auditRepo->countFiltered($filterType, $filterDateFrom, $filterDateTo);
        
        $totalPages = ceil($totalLogs / $limit);

        require __DIR__ . '/../templates/admin/audit_log.php';
        break;

    case 'admin_settings':
        require_admin_login();
        require __DIR__ . '/../templates/admin/settings.php';
        break;

    //  404

    default:
        http_response_code(404);
        echo '<h1 style="font-family:sans-serif;padding:2rem;">404 – Sidan hittades inte</h1>';
        break;
}