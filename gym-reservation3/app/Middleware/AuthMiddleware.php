<?php
require_once __DIR__ . '/../Models/User.php';
class AuthMiddleware
{
    public static function user(PDO $pdo): array
    {
        $id = positiveId($_SESSION['user_id'] ?? null);
        if ($id === null) {
            flash('error', 'Please log in first.');
            redirectTo('login');
        }
        $user = (new User($pdo))->findById($id);
        if (!$user || (int)$user['is_active'] !== 1) {
            unset($_SESSION['user_id']);
            flash('error', 'Your account is unavailable.');
            redirectTo('login');
        }
        return $user;
    }
    public static function admin(PDO $pdo): array
    {
        $user = self::user($pdo);
        if ($user['role'] !== 'admin') abortRequest(403, 'Admin only.');
        return $user;
    }
}
