<?php
class Database
{
    public function connect(): PDO
    {
        // Local XAMPP defaults are intentionally preserved for development only.
        $production = getenv('APP_ENV') === 'production';
        $host = getenv('DB_HOST');
        $name = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        if ($production && ($host === false || $host === '' || $name === false || $name === '' || $user === false || $user === '' || $pass === false || $pass === '')) {
            throw new RuntimeException('Production database environment variables must be configured.');
        }
        $host = ($host !== false && $host !== '') ? $host : '127.0.0.1';
        $name = ($name !== false && $name !== '') ? $name : 'reservation_db';
        $user = ($user !== false && $user !== '') ? $user : 'root';
        if ($pass === false) $pass = '';

        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        // The app stores local Jordan times in MySQL DATETIME columns.
        $pdo->exec("SET time_zone = '+03:00'");
        return $pdo;
    }
}
