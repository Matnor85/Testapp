<?php

class UserRepository
{
    public function __construct(private PDO $db) {}

    // Hämtar en specifik användare baserat på ID
    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, name, card_number, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    //  Hämta användare på kortnummer (för inloggning)
    public function findByCardNumber(string $cardNumber): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, card_number, pin_hash, role
             FROM users
             WHERE card_number = ?
             LIMIT 1'
        );
        $stmt->execute([$cardNumber]);
        return $stmt->fetch();
    }

    //  Hämta användare på id
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, card_number, role, created_at
             FROM users
             WHERE id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    //  Hämta alla användare (admin)
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, name, card_number, role, created_at
             FROM users
             ORDER BY id ASC'
        );
        return $stmt->fetchAll();
    }

    //  Skapa ny användare (admin CRUD)
    public function create(string $name, string $cardNumber, string $pinHash, string $role = 'user'): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, card_number, pin_hash, role)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $cardNumber, $pinHash, $role]);
        return (int) $this->db->lastInsertId();
    }

    //  Uppdatera användare (admin CRUD)
    public function update(int $id, string $name, string $cardNumber, string $role): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET name = ?, card_number = ?, role = ?
             WHERE id = ?'
        );
        return $stmt->execute([$name, $cardNumber, $role, $id]);
    }

    //  Uppdatera PIN-hash
    public function updatePin(int $id, string $pinHash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET pin_hash = ? WHERE id = ?'
        );
        return $stmt->execute([$pinHash, $id]);
    }

    //  Ta bort användare (admin CRUD)
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    //  Kontrollera om kortnummer redan finns (för validering)
    public function cardNumberExists(string $cardNumber, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                'SELECT 1 FROM users WHERE card_number = ? AND id != ? LIMIT 1'
            );
            $stmt->execute([$cardNumber, $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT 1 FROM users WHERE card_number = ? LIMIT 1'
            );
            $stmt->execute([$cardNumber]);
        }
        return (bool) $stmt->fetch();
    }
}
