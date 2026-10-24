<?php
/* Presentation-only layout. Picks one of three shells (site / auth / admin) from the current ?page=
   value. No application logic lives here; flash handling is unchanged. */
$__page   = (isset($_GET['page']) && is_string($_GET['page'])) ? $_GET['page'] : 'home';
$__ok     = ((http_response_code() ?: 200) === 200);
$__logged = !empty($_SESSION['user_id']);
$__shell  = 'site';
if ($__ok && in_array($__page, ['register', 'login'], true)) { $__shell = 'auth'; }
elseif ($__ok && $__logged && str_starts_with($__page, 'admin')) { $__shell = 'admin'; }
$__scene  = ($__shell === 'auth') || ($__ok && $__page === 'home');
$__titles = [
    'home' => 'Train beyond limits', 'register' => 'Create account', 'login' => 'Log in',
    'account' => 'My account', 'classes' => 'Classes', 'my-reservations' => 'My reservations',
    'admin' => 'Dashboard', 'admin-classes' => 'Time slots', 'admin-create-class' => 'New time slot',
    'admin-edit-class' => 'Edit time slot', 'admin-users' => 'Users', 'admin-reservations' => 'Reservations',
];
$__title = $__ok ? ($__titles[$__page] ?? 'Gym Reservation') : 'Request could not be completed';
$__cur = fn(array $pages): string => in_array($__page, $pages, true) ? ' aria-current="page"' : '';
$__paths = [
    'bolt' => '<path d="M13 2 3 14h9l-1 8 10-12h-9z"/>',
    'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
    'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    'check' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
    'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
    'plus' => '<path d="M12 5v14M5 12h14"/>',
    'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
    'alert' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
    'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    'tag' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><path d="M7 7h.01"/>',
];
$__icon = fn(string $n): string => '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . ($__paths[$n] ?? '') . '</svg>';
$__brand = '<a class="brand" href="' . e(linkTo('home')) . '"><span class="brand__mark">' . $__icon('bolt') . '</span><span>Gym<b>Reserve</b></span></a>';
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#07080a">
<title><?= e($__title) ?> — Gym Reservation</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="public/assets/css/style.css">
<link rel="stylesheet" href="public/assets/css/components.css">
<link rel="stylesheet" href="public/assets/css/layout.css">
</head><body class="shell-<?= e($__shell) ?>" data-page="<?= e($__page) ?>">
<a class="skip-link" href="#main">Skip to content</a>
<?php if ($__shell === 'auth'): ?>
<div class="auth-shell">
<aside class="auth-visual">
    <div class="scene" data-scene="auth"><div class="scene__fallback"></div></div>
    <?= $__brand ?>
    <div class="auth-visual__copy">
        <h2>Your next<br>session <span class="lime">starts here.</span></h2>
        <p>Pick a class, secure your spot, and keep every booking in one place.</p>
    </div>
</aside>
<main id="main" class="auth-main"><div class="auth-card">
<?php if (!empty($_SESSION['flash'])):
    $flashMessage=$_SESSION['flash'];unset($_SESSION['flash']); ?>
<div class="flash-wrap"><p role="status" class="flash flash--<?= e($flashMessage['kind']) ?> message <?= e($flashMessage['kind']) ?>"><span class="flash__text"><?= e($flashMessage['text']) ?></span></p></div>
<?php endif; ?>
<div class="auth-mobile-brand"><?= $__brand ?></div>
<?php elseif ($__shell === 'admin'): ?>
<div class="admin-shell" data-open="false">
<aside class="admin-side" id="admin-side" aria-label="Admin navigation">
    <?= $__brand ?>
    <ul class="admin-nav">
        <li class="admin-nav__label">Overview</li>
        <li><a href="<?= e(linkTo('admin')) ?>"<?= $__cur(['admin']) ?>><?= $__icon('grid') ?>Dashboard</a></li>
        <li class="admin-nav__label">Manage</li>
        <li><a href="<?= e(linkTo('admin-reservations')) ?>"<?= $__cur(['admin-reservations']) ?>><?= $__icon('check') ?>Reservations</a></li>
        <li><a href="<?= e(linkTo('admin-classes')) ?>"<?= $__cur(['admin-classes', 'admin-create-class', 'admin-edit-class']) ?>><?= $__icon('calendar') ?>Time slots</a></li>
        <li><a href="<?= e(linkTo('admin-users')) ?>"<?= $__cur(['admin-users']) ?>><?= $__icon('users') ?>Users</a></li>
        <li class="admin-nav__label">Member area</li>
        <li><a href="<?= e(linkTo('classes')) ?>"><?= $__icon('clock') ?>Available slots</a></li>
        <li><a href="<?= e(linkTo('account')) ?>"><?= $__icon('user') ?>My account</a></li>
    </ul>
    <div class="admin-side__foot">
        <form action="<?= e(linkTo('logout')) ?>" method="post"><?= csrfField() ?><button type="submit" class="btn btn--ghost btn--sm btn--block"><?= $__icon('logout') ?>Logout</button></form>
    </div>
</aside>
<div class="admin-scrim"></div>
<div class="admin-body">
<header class="admin-top">
    <div class="crumbs"><button type="button" class="nav-toggle" data-admin-toggle aria-controls="admin-side" aria-expanded="false" aria-label="Toggle navigation"><?= $__icon('menu') ?></button><span>Admin</span><span>/</span><b><?= e($__title) ?></b></div>
    <span class="badge badge--admin">Administrator</span>
</header>
<main id="main" class="admin-main">
<?php if (!empty($_SESSION['flash'])):
    $flashMessage=$_SESSION['flash'];unset($_SESSION['flash']); ?>
<div class="flash-wrap"><p role="status" class="flash flash--<?= e($flashMessage['kind']) ?> message <?= e($flashMessage['kind']) ?>"><span class="flash__text"><?= e($flashMessage['text']) ?></span></p></div>
<?php endif; ?>
<?php else: ?>
<header class="site-nav" data-nav data-open="false"><div class="site-nav__inner">
    <?= $__brand ?>
    <button type="button" class="nav-toggle" data-nav-toggle aria-controls="site-links" aria-expanded="false" aria-label="Toggle navigation"><?= $__icon('menu') ?></button>
    <nav aria-label="Main"><ul class="nav-links" id="site-links">
        <li><a href="<?= e(linkTo('home')) ?>"<?= $__cur(['home']) ?>>Home</a></li>
<?php if ($__logged): ?>
        <li><a href="<?= e(linkTo('classes')) ?>"<?= $__cur(['classes']) ?>>Available Slots</a></li>
        <li><a href="<?= e(linkTo('my-reservations')) ?>"<?= $__cur(['my-reservations']) ?>>My Reservations</a></li>
        <li><a href="<?= e(linkTo('account')) ?>"<?= $__cur(['account']) ?>>My Account</a></li>
        <li><form class="inline-form" action="<?= e(linkTo('logout')) ?>" method="post"><?= csrfField() ?><button type="submit" class="nav-link">Logout</button></form></li>
<?php else: ?>
        <li><a href="<?= e(linkTo('login')) ?>"<?= $__cur(['login']) ?>>Login</a></li>
        <li><a class="btn btn--primary btn--sm" href="<?= e(linkTo('register')) ?>">Register</a></li>
<?php endif; ?>
    </ul></nav>
</div></header>
<main id="main" class="page">
<?php if (!empty($_SESSION['flash'])):
    $flashMessage=$_SESSION['flash'];unset($_SESSION['flash']); ?>
<div class="flash-wrap"><p role="status" class="flash flash--<?= e($flashMessage['kind']) ?> message <?= e($flashMessage['kind']) ?>"><span class="flash__text"><?= e($flashMessage['text']) ?></span></p></div>
<?php endif; ?>
<?php endif; ?>
