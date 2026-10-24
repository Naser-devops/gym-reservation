<?php
/**
 * Non-destructive post-extraction check. Run from this folder's parent:
 * php scripts/check_install.php
 * This script doesn't modify the application or connect to the database.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit('Not found.'); }
$root = dirname(__DIR__);
$required = [
    'index.php', 'router.php', 'config/app.php',
    'app/Core/Database.php', 'app/Core/helpers.php',
    'app/Core/LoginRateLimiter.php',
    'app/Middleware/AuthMiddleware.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/ClassController.php',
    'app/Controllers/ReservationController.php',
    'app/Controllers/AdminController.php',
    'app/Models/User.php', 'app/Models/GymClass.php',
    'app/Models/Reservation.php',
    'resources/views/layouts/header.php',
    'resources/views/layouts/footer.php',
    'resources/views/home/index.php',
    'resources/views/auth/login.php',
    'resources/views/auth/register.php',
    'resources/views/classes/index.php',
    'resources/views/reservations/index.php',
    'resources/views/account.php',
    'resources/views/errors/message.php',
    'resources/views/admin/dashboard.php',
    'resources/views/admin/classes/index.php',
    'resources/views/admin/classes/create.php',
    'resources/views/admin/classes/edit.php',
    'resources/views/admin/reservations/index.php',
    'resources/views/admin/users/index.php',
    'public/assets/css/style.css',
    'public/assets/css/components.css',
    'public/assets/css/layout.css',
    'public/assets/js/main.js',
    'public/assets/js/scene.js',
    'public/assets/js/dashboard.js',
    'public/assets/images/hero.png',
    'public/assets/images/auth.png',
];
$missing = [];
foreach ($required as $file) {
    if (!is_file($root . '/' . $file)) $missing[] = $file;
}
if ($missing) {
    fwrite(STDERR, "ERROR: Files missing after extraction:\n  - " . implode("\n  - ", $missing) . "\n");
    fwrite(STDERR, "Extract the entire ZIP to a NEW empty folder. Do not copy only selected files.\n");
    exit(1);
}
echo "OK: All " . count($required) . " essential files are present.\n";
echo "PHP: " . PHP_VERSION . "\n";
if (PHP_VERSION_ID < 80100) {
    fwrite(STDERR, "ERROR: This project requires PHP 8.1 or newer.\n");
    exit(1);
}
if (!extension_loaded('pdo_mysql')) {
    fwrite(STDERR, "WARNING: pdo_mysql is not active in this PHP environment. XAMPP may use a different PHP executable.\n");
}
$storage = $root . '/storage';
if (is_dir($storage) && !is_writable($storage)) {
    fwrite(STDERR, "WARNING: The storage folder is not writable; login rate-limiting may fall back to the session limit.\n");
}
echo "No database data was modified.\n";
