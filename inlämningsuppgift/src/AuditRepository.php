<?php

class AuditRepository
{
    public function __construct(private PDO $db) {}

    //  Logga en händelse

    public function log(
        ?int $userId,
        string $action,
        ?string $description = null
    ): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $stmt = $this->db->prepare(
            'INSERT INTO audit_log (user_id, action, description, ip_address)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $action, $description, $ip]);
    }

    //  Hämta alla loggposter (admin) med paginering

    public function findAll(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            'SELECT al.id, al.action, al.description, al.ip_address,
                    al.created_at, u.name AS user_name
             FROM audit_log al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    //  Räkna alla loggposter (för paginering)

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM audit_log')->fetchColumn();
    }

    //  Hämta filtrerade loggposter med MySQL-bindningar

    public function findFiltered(string $type, string $dateFrom, string $dateTo, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT al.id, al.action, al.description, al.ip_address, al.created_at, u.name AS user_name
                FROM audit_log al
                LEFT JOIN users u ON u.id = al.user_id
                WHERE 1=1";
        
        $params = [];

        if (!empty($type)) {
            $sql .= " AND al.action = ?";
            $params[] = $type;
        }

        if (!empty($dateFrom)) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }

        if (!empty($dateTo)) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        
        $paramIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //  Räkna filtrerade loggposter (för korrekt paginering)
    
    public function countFiltered(string $type, string $dateFrom, string $dateTo): int
    {
        $sql = "SELECT COUNT(*) FROM audit_log WHERE 1=1";
        $params = [];

        if (!empty($type)) {
            $sql .= " AND action = ?";
            $params[] = $type;
        }

        if (!empty($dateFrom)) {
            $sql .= " AND created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }

        if (!empty($dateTo)) {
            $sql .= " AND created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}