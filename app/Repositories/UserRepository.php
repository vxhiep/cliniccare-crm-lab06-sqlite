<?php
class UserRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findActiveByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, password_hash, role
             FROM users
             WHERE email = :email AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}
