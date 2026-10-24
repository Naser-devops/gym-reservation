<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var array<string, mixed> $user
 * @var callable(string):string $__icon
 */
 $__ini = preg_match('/^./us', (string)$user['full_name'], $__m) ? $__m[0] : '?'; ?>
<div class="container page-body">
<div class="page-head"><div><span class="eyebrow">Member area</span><h1 style="margin-top:.8rem">My account</h1></div></div>
<section class="card profile" data-reveal>
    <div class="profile__who">
        <div class="avatar" aria-hidden="true"><?= e($__ini) ?></div>
        <div>
            <p style="margin:0">Welcome,</p>
            <h2 style="font-size:clamp(1.8rem,4vw,2.6rem)"><strong><?= e($user['full_name']) ?></strong></h2>
            <p>(<?= e($user['email']) ?>) &nbsp;<span class="badge <?= $user['role']==='admin' ? 'badge--admin' : 'badge--open' ?>">Your role: <?= e($user['role']) ?></span></p>
        </div>
    </div>
    <form action="<?= e(linkTo('logout')) ?>" method="post"><?= csrfField() ?><button type="submit" class="btn btn--ghost"><?= $__icon('logout') ?>Logout</button></form>
</section>
<div class="tiles" data-stagger>
    <a class="card card--hover tile" href="<?= e(linkTo('classes')) ?>"><?= $__icon('calendar') ?><h3>View available gym slots</h3><p>Browse upcoming classes and book your next session.</p></a>
    <a class="card card--hover tile" href="<?= e(linkTo('my-reservations')) ?>"><?= $__icon('check') ?><h3>My reservations</h3><p>Check statuses and cancel bookings before they start.</p></a>
</div>
<?php if ($user['role']==='admin'): ?>
<p class="section-label">Administration</p>
<div class="tiles" data-stagger>
    <a class="card card--hover tile tile--admin" href="<?= e(linkTo('admin')) ?>"><?= $__icon('grid') ?><h3>Dashboard</h3><p>Reservation statistics and status overview.</p></a>
    <a class="card card--hover tile tile--admin" href="<?= e(linkTo('admin-classes')) ?>"><?= $__icon('clock') ?><h3>Manage time slots</h3><p>Create, edit, activate or remove classes.</p></a>
    <a class="card card--hover tile tile--admin" href="<?= e(linkTo('admin-reservations')) ?>"><?= $__icon('check') ?><h3>Manage reservations</h3><p>Search, confirm, reject or cancel bookings.</p></a>
    <a class="card card--hover tile tile--admin" href="<?= e(linkTo('admin-users')) ?>"><?= $__icon('users') ?><h3>Manage users</h3><p>Review accounts and control access.</p></a>
</div>
<?php endif; ?>
</div>
