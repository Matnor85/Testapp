<?php

// =============================================================
//  TransactionRepository.php
//  Alla databasfrågor rörande transaktioner.
//  Affärslogiken (saldokontroll etc.) sköts av index.php.
// =============================================================

class TransactionRepository
{
    public function __construct(private PDO $db) {}

    // ----------------------------------------------------------
    //  Logga en transaktion
    // ----------------------------------------------------------
    public function create(
        string $type,
        float $amount,
        ?int $fromAccountId,
        ?int $toAccountId
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO transactions (type, amount, from_account_id, to_account_id)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$type, $amount, $fromAccountId, $toAccountId]);
        return (int) $this->db->lastInsertId();
    }

    // ----------------------------------------------------------
    //  Hämta transaktioner för ett konto (historik)
    // ----------------------------------------------------------
    public function findByAccountId(int $accountId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, type, amount, from_account_id, to_account_id, created_at
             FROM transactions
             WHERE from_account_id = ? OR to_account_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$accountId, $accountId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    //  Räkna transaktioner för ett konto (för paginering)
    // ----------------------------------------------------------
    public function countByAccountId(int $accountId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM transactions
             WHERE from_account_id = ? OR to_account_id = ?'
        );
        $stmt->execute([$accountId, $accountId]);
        return (int) $stmt->fetchColumn();
    }

    // ----------------------------------------------------------
    //  Hämta alla transaktioner (admin) med filtrering
    // ----------------------------------------------------------
    public function findAll(
        ?string $type = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $limit = 20,
        int $offset = 0
    ): array {
        $where = [];
        $params = [];

        if ($type) {
            $where[] = 'type = ?';
            $params[] = $type;
        }
        if ($dateFrom) {
            $where[] = 'DATE(created_at) >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where[] = 'DATE(created_at) <= ?';
            $params[] = $dateTo;
        }

        $sql = 'SELECT id, type, amount, from_account_id, to_account_id, created_at
                FROM transactions';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    //  Räkna alla transaktioner (admin, för paginering)
    // ----------------------------------------------------------
    public function countAll(
        ?string $type = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        $where = [];
        $params = [];

        if ($type) {
            $where[] = 'type = ?';
            $params[] = $type;
        }
        if ($dateFrom) {
            $where[] = 'DATE(created_at) >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where[] = 'DATE(created_at) <= ?';
            $params[] = $dateTo;
        }

        $sql = 'SELECT COUNT(*) FROM transactions';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
