<?php

class AccountRepository
{
    public function __construct(private PDO $db) {}

    //  Hämta alla konton för en användare

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_id, account_type, balance, credit_limit,
                    interest_rate, locked_until, active, created_at
             FROM accounts
             WHERE user_id = ?
             ORDER BY id ASC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    //  Hämta ett specifikt konto på id

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_id, account_type, balance, credit_limit,
                    interest_rate, locked_until, active, created_at
             FROM accounts
             WHERE id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    //  Hämta alla konton (admin)
    
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT a.id, u.name AS owner_name, a.account_type,
                    a.balance, a.credit_limit, a.active, a.created_at
             FROM accounts a
             JOIN users u ON u.id = a.user_id
             ORDER BY a.id ASC'
        );
        return $stmt->fetchAll();
    }

    //  Sätt in pengar — ökar saldo

    public function deposit(int $accountId, float $amount): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE accounts
             SET balance = balance + ?
             WHERE id = ?'
        );
        return $stmt->execute([$amount, $accountId]);
    }

    //  Ta ut pengar — minskar saldo (kontrollera täckning före!)

    public function withdraw(int $accountId, float $amount): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE accounts
             SET balance = balance - ?
             WHERE id = ? AND balance >= ?'
        );
        $stmt->execute([$amount, $accountId, $amount]);
        return $stmt->rowCount() === 1;
    }

    //  Kontrollera att kontot tillhör rätt användare
    //  (säkerhetskontroll innan varje transaktion)

    public function belongsToUser(int $accountId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM accounts
             WHERE id = ? AND user_id = ?
             LIMIT 1'
        );
        $stmt->execute([$accountId, $userId]);
        return (bool) $stmt->fetch();
    }
public function getCustomersOverview(string $sort = 'balance', string $order = 'DESC'): array
    {
        // Vitlista indata för att säkra mot SQL-injections via URL-strängen
        $allowedSort = ['owner_name', 'account_count', 'balance'];
        $sort = in_array($sort, $allowedSort) ? $sort : 'balance';
        
        // Översätt interna alias till faktiska MySQL-kolumner/aggregeringar
        if ($sort === 'owner_name') $sort = 'users.name';
        if ($sort === 'account_count') $sort = 'account_count';
        if ($sort === 'balance') $sort = 'total_balance';

        $order = ($order === 'ASC') ? 'ASC' : 'DESC';

        $sql = "SELECT 
                    users.id AS user_id,
                    users.name AS owner_name,
                    COUNT(accounts.id) AS account_count,
                    SUM(accounts.balance) AS total_balance
                FROM users
                LEFT JOIN accounts ON users.id = accounts.user_id
                GROUP BY users.id, users.name
                ORDER BY {$sort} {$order}";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Förberedelse för nästa steg: Hämta alla unika konton för en specifik användare
     */
    public function getAccountsByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, account_type, balance, interest_rate, active, created_at 
             FROM accounts 
             WHERE user_id = ?
             ORDER BY id ASC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
     // Ändrar status på ett konto (1 = Aktiv, 0 = Låst)
   
    public function updateAccountStatus(int $accountId, int $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE accounts 
             SET active = ? 
             WHERE id = ?"
        );
        return $stmt->execute([$status, $accountId]);
    }

    // Skapa ett helt nytt konto för en befintlig kund

    public function create(int $userId, string $accountType, float $initialBalance = 0.00): int|false
    {
        // Sätt räntesatser automatiskt baserat på kontotypen
        $interestRate = 0.00;
        if ($accountType === 'savings') {
            $interestRate = 1.50;
        } elseif ($accountType === 'fixed') {
            $interestRate = 3.50;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO accounts (user_id, account_type, balance, interest_rate, active)
             VALUES (?, ?, ?, ?, 1)'
        );
        
        $success = $stmt->execute([$userId, $accountType, $initialBalance, $interestRate]);
        
        // Returnera det nya kontots unika ID om det lyckades, annars false
        return $success ? (int)$this->db->lastInsertId() : false;
    }
}
