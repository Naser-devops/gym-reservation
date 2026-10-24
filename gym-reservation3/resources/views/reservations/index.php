<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var list<array<string, mixed>> $reservations
 * @var callable(string):string $__icon
 */
$__counts = ['Pending' => 0, 'Confirmed' => 0, 'Rejected' => 0, 'Cancelled' => 0];
$__up = []; $__hist = [];
foreach ($reservations as $__r) {
    if (isset($__counts[$__r['status']])) { $__counts[$__r['status']]++; }
    if (in_array($__r['status'], ['Pending', 'Confirmed'], true) && isFuture($__r['starts_at'])) { $__up[] = $__r; } else { $__hist[] = $__r; }
}
$__card = function (array $r, bool $past) use ($__icon): void {
    $ts = strtotime($r['starts_at']); $te = strtotime($r['ends_at']); ?>
<article class="card res-card<?= $past ? ' is-past' : '' ?>">
    <div class="date-tile"><small><?= e(date('D', $ts)) ?></small><strong><?= e(date('j', $ts)) ?></strong><span><?= e(date('M', $ts)) ?></span></div>
    <div>
        <h3><?= e($r['class_name']) ?></h3>
        <div class="res-card__meta">
            <span><?= e(date('H:i', $ts)) ?> – <?= e(date('H:i', $te)) ?></span>
            <span>Reservation #<?= (int)$r['id'] ?></span>
            <span>Booked <?= e(date('j M Y', strtotime($r['created_at']))) ?></span>
        </div>
<?php if (trim((string)$r['note']) !== ''): ?>
        <p class="res-card__note"><?= e($r['note']) ?></p>
<?php endif; ?>
    </div>
    <div class="res-card__side">
        <span class="badge badge--<?= e(strtolower((string)$r['status'])) ?>"><?= e($r['status']) ?></span>
<?php if (in_array($r['status'], ['Pending', 'Confirmed'], true) && isFuture($r['starts_at'])): ?>
        <form method="post" action="<?= e(linkTo('cancel-reservation')) ?>" data-confirm-title="Cancel this reservation?" data-confirm="Your spot in <?= e($r['class_name']) ?> will be released.">
        <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button type="submit" class="btn btn--danger btn--sm">Cancel</button>
        </form>
<?php endif; ?>
    </div>
</article>
<?php }; ?>
<div class="container page-body">
<div class="page-head"><div><span class="eyebrow">Your bookings</span><h1 style="margin-top:.8rem">My reservations</h1></div>
<a class="btn btn--primary" href="<?= e(linkTo('classes')) ?>">Book another class</a></div>
<?php if (!$reservations): ?>
<div class="empty" data-reveal><?= $__icon('check') ?><h3>No reservations yet</h3><p>You have no reservations. Find a class that fits your week and book your first session.</p><a class="btn btn--primary btn--sm" href="<?= e(linkTo('classes')) ?>">Browse available slots</a></div>
<?php else: ?>
<div class="pills" data-stagger>
<?php foreach ($__counts as $__label => $__n): ?>
    <span class="badge badge--<?= e(strtolower($__label)) ?>"><?= e($__label) ?> · <?= (int)$__n ?></span>
<?php endforeach; ?>
</div>
<?php if ($__up): ?>
<h2 class="group-title">Upcoming</h2>
<div class="res-list" data-stagger>
<?php foreach ($__up as $__r) { $__card($__r, false); } ?>
</div>
<?php endif; ?>
<?php if ($__hist): ?>
<h2 class="group-title">History</h2>
<div class="res-list" data-stagger>
<?php foreach ($__hist as $__r) { $__card($__r, true); } ?>
</div>
<?php endif; ?>
<?php endif; ?>
</div>
