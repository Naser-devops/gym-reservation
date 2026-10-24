<?php
class User
{
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT id, full_name, email, role, is_active, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function create(string $name, string $email, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, is_active) VALUES (?, ?, ?, \'user\', 1)');
        return $stmt->execute([$name, $email, $hash]);
    }
    public function all(): array
    {
        return $this->pdo->query('SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY created_at DESC, id DESC')->fetchAll();
    }
    public function setActive(int $id, int $active, int $actorId): bool
    {
        // An administrator cannot disable themselves or another administrator here.
        if ($id === $actorId) return false;
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = ? WHERE id = ? AND role = 'user'");
        $stmt->execute([$active, $id]);
        return $stmt->rowCount() === 1;
    }
}
