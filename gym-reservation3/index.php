<?php
// All browser requests enter from here. Do not browse view files directly.
$config = require __DIR__ . '/config/app.php';
date_default_timezone_set($config['timezone']);
ini_set('session.use_strict_mode','1');
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_samesite','Lax');
// On HTTPS deployments set APP_ENV=production (including reverse-proxy TLS setups).
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || getenv('APP_ENV') === 'production') ini_set('session.cookie_secure','1');
session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');

require_once __DIR__ . '/app/Core/helpers.php';
// Public landing does not need a database; keep it available during DB outages.
$page = $_GET['page'] ?? 'home';
if (!is_string($page)) abortRequest(400, 'Invalid page.');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($page === 'home') {
    getOnly();
    renderView('home/index');
    exit;
}
// Unknown URLs should return 404 even when MySQL is offline.
$knownPages = [
    'register', 'login', 'logout', 'account', 'classes', 'book-class',
    'my-reservations', 'cancel-reservation', 'admin', 'admin-classes',
    'admin-create-class', 'admin-edit-class', 'admin-update-class',
    'admin-toggle-class', 'admin-delete-class', 'admin-users',
    'admin-user-status', 'admin-reservations', 'admin-reservation-status'
];
if (!in_array($page, $knownPages, true)) abortRequest(404, 'Page not found.');

require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/ClassController.php';
require_once __DIR__ . '/app/Controllers/ReservationController.php';
require_once __DIR__ . '/app/Controllers/AdminController.php';

try { $pdo=(new Database())->connect(); }
catch (Throwable $e) {
    error_log('DB connection failure: '.get_class($e).' '.$e->getCode());
    abortRequest(503, 'Database temporarily unavailable. Please try again later.');
}
try {
    $auth=new AuthController($pdo);
    if ($page==='register') {
        if ($method==='POST') $auth->register();
        getOnly(); $auth->showRegister();
    } elseif ($page==='login') {
        if ($method==='POST') $auth->login();
        getOnly(); $auth->showLogin();
    } elseif ($page==='logout') {
        postOnly(); $auth->logout();
    } elseif ($page==='account') {
        getOnly(); $user=AuthMiddleware::user($pdo);
        renderView('account',['user'=>$user]);
    } elseif ($page==='classes') {
        getOnly(); AuthMiddleware::user($pdo);
        (new ClassController($pdo))->index();
    } elseif ($page==='book-class') {
        postOnly();$user=AuthMiddleware::user($pdo);
        (new ReservationController($pdo))->store($user);
    } elseif ($page==='my-reservations') {
        getOnly();$user=AuthMiddleware::user($pdo);
        (new ReservationController($pdo))->index($user);
    } elseif ($page==='cancel-reservation') {
        postOnly();$user=AuthMiddleware::user($pdo);
        (new ReservationController($pdo))->cancel($user);
    } elseif (str_starts_with($page,'admin')) {
        $admin=AuthMiddleware::admin($pdo);
        $classes=new ClassController($pdo);
        $dashboard=new AdminController($pdo);
        if ($page==='admin') {getOnly();$dashboard->dashboard();}
        elseif ($page==='admin-classes') {getOnly();$classes->adminIndex();}
        elseif ($page==='admin-create-class') {
            if ($method==='POST') $classes->store();
            else {getOnly();$classes->createForm();}
        }
        elseif ($page==='admin-edit-class') {getOnly();$classes->editForm();}
        elseif ($page==='admin-update-class') {postOnly();$classes->update();}
        elseif ($page==='admin-toggle-class') {postOnly();$classes->toggle();}
        elseif ($page==='admin-delete-class') {postOnly();$classes->delete();}
        elseif ($page==='admin-users') {getOnly();$dashboard->users();}
        elseif ($page==='admin-user-status') {postOnly();$dashboard->changeUserStatus($admin);}
        elseif ($page==='admin-reservations') {getOnly();$dashboard->reservations();}
        elseif ($page==='admin-reservation-status') {postOnly();$dashboard->changeReservationStatus();}
        else abortRequest(404,'Page not found.');
    } else abortRequest(404,'Page not found.');
} catch (PDOException $e) {
    error_log('Database SQLSTATE: '.$e->getCode());
    abortRequest(503,'Database operation failed. Please try again later.');
} catch (Throwable $e) {
    error_log('Application error: '.get_class($e).' '.$e->getMessage());
    abortRequest(500,'Unexpected application error.');
}
