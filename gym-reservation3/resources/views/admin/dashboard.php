<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var array<string, int> $stats
 * @var callable(string):string $__icon
 */
?>
<div class="admin-title"><div><span class="eyebrow">Overview</span><h1 style="margin-top:.8rem">Admin dashboard</h1><p>Live reservation and member statistics.</p></div>
<div class="btn-row"><a class="btn btn--ghost btn--sm" href="<?= e(linkTo('admin-classes')) ?>">Manage slots</a><a class="btn btn--ghost btn--sm" href="<?= e(linkTo('admin-reservations')) ?>">Manage reservations</a><a class="btn btn--ghost btn--sm" href="<?= e(linkTo('admin-users')) ?>">Manage users</a></div></div>
<div class="stats" data-stagger>
    <div class="card stat"><div class="stat__label"><span>All reservations</span><?= $__icon('check') ?></div><span class="stat__value" data-count="<?= (int)$stats['total'] ?>"><?= (int)$stats['total'] ?></span></div>
    <div class="card stat stat--warn"><div class="stat__label"><span>Pending</span><?= $__icon('clock') ?></div><span class="stat__value" data-count="<?= (int)$stats['pending'] ?>"><?= (int)$stats['pending'] ?></span></div>
    <div class="card stat stat--ok"><div class="stat__label"><span>Confirmed</span><?= $__icon('shield') ?></div><span class="stat__value" data-count="<?= (int)$stats['confirmed'] ?>"><?= (int)$stats['confirmed'] ?></span></div>
    <div class="card stat"><div class="stat__label"><span>Registered users</span><?= $__icon('users') ?></div><span class="stat__value" data-count="<?= (int)$stats['users'] ?>"><?= (int)$stats['users'] ?></span></div>
</div>
<div class="dash-grid">
    <section class="card" data-reveal>
        <h2 style="font-size:1.6rem;margin-bottom:1rem">Reservations by status</h2>
        <div class="chart-box"><canvas id="reservationChart" role="img" aria-label="Reservations by status" data-values="<?= e(json_encode([(int)$stats['pending'], (int)$stats['confirmed'], (int)$stats['rejected'], (int)$stats['cancelled']])) ?>"></canvas></div>
        <p class="hint" style="margin:1rem 0 0">The numbers on the right stay available if the Chart.js CDN is offline.</p>
    </section>
    <section class="card" data-reveal>
        <h2 style="font-size:1.6rem;margin-bottom:1rem">Status breakdown</h2>
        <ul class="legend">
            <li><span><i style="--c:#ffb020"></i>Pending</span><strong><?= (int)$stats['pending'] ?></strong></li>
            <li><span><i style="--c:#3ddc84"></i>Confirmed</span><strong><?= (int)$stats['confirmed'] ?></strong></li>
            <li><span><i style="--c:#ff4d5e"></i>Rejected</span><strong><?= (int)$stats['rejected'] ?></strong></li>
            <li><span><i style="--c:#8b94a1"></i>Cancelled</span><strong><?= (int)$stats['cancelled'] ?></strong></li>
        </ul>
    </section>
</div>
<div class="quick" data-stagger>
    <a class="card card--hover tile" href="<?= e(linkTo('admin-reservations')) ?>"><?= $__icon('check') ?><h3>Reservations</h3><p>Review, confirm or reject bookings.</p></a>
    <a class="card card--hover tile" href="<?= e(linkTo('admin-classes')) ?>"><?= $__icon('calendar') ?><h3>Time slots</h3><p>Create and maintain the class schedule.</p></a>
    <a class="card card--hover tile" href="<?= e(linkTo('admin-users')) ?>"><?= $__icon('users') ?><h3>Users</h3><p>Control who can access the platform.</p></a>
</div>
<script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script defer src="public/assets/js/dashboard.js"></script>
