<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var list<array<string, mixed>> $slots
 * @var ?string $day
 * @var callable(string):string $__icon
 */
?>
<div class="container page-body">
<div class="page-head"><div><span class="eyebrow">Book a session</span><h1 style="margin-top:.8rem">Available gym slots</h1><p>Choose a class, check the remaining spots and reserve in one tap.</p></div></div>
<form class="filter-bar" action="<?= e(linkTo('classes')) ?>" method="get">
<input type="hidden" name="page" value="classes">
<label class="field"><span>Filter by date</span><input type="date" name="date" value="<?= e($day) ?>"></label>
<button type="submit" class="btn btn--primary">Filter</button>
<a class="btn btn--ghost" href="<?= e(linkTo('classes')) ?>">Clear</a>
</form>
<?php if (!$slots): ?>
<div class="empty" data-reveal><?= $__icon('calendar') ?><h3>No upcoming slots</h3><p>No upcoming slots match your filter. Try another date or clear the filter to see everything that is open.</p><a class="btn btn--ghost btn--sm" href="<?= e(linkTo('classes')) ?>">Show all slots</a></div>
<?php else: ?>
<div class="class-grid" data-stagger>
<?php foreach ($slots as $slot):
    $remaining = max(0, (int)$slot['capacity'] - (int)$slot['reserved_count']);
    $cap = (int)$slot['capacity'];
    $fill = $cap > 0 ? max(0, min(100, (int)round(((int)$slot['reserved_count'] / $cap) * 100))) : 100;
    $state = $remaining === 0 ? 'full' : (($remaining <= 3 || ($cap > 0 && $remaining / $cap <= 0.25)) ? 'low' : 'open');
    $ts = strtotime($slot['starts_at']); $te = strtotime($slot['ends_at']);
?>
<article class="card card--hover class-card">
    <div class="class-card__top">
        <div class="date-tile"><small><?= e(date('D', $ts)) ?></small><strong><?= e(date('j', $ts)) ?></strong><span><?= e(date('M', $ts)) ?></span></div>
        <div>
            <h3><?= e($slot['class_name']) ?></h3>
            <div class="class-card__time"><?= $__icon('clock') ?><span><time datetime="<?= e(date('c', $ts)) ?>"><?= e(date('H:i', $ts)) ?></time> – <time datetime="<?= e(date('c', $te)) ?>"><?= e(date('H:i', $te)) ?></time><?= date('Y-m-d', $ts) !== date('Y-m-d', $te) ? e(date(' (j M)', $te)) : '' ?></span></div>
        </div>
    </div>
    <p class="class-card__desc"><?= e($slot['description']) ?></p>
    <div class="class-card__cap">
        <div class="class-card__cap-row"><span>Remaining / Total: <strong><?= $remaining ?> / <?= $cap ?></strong></span>
            <span class="badge badge--<?= e($state) ?>"><?= $state === 'full' ? 'Full' : ($state === 'low' ? 'Last spots' : 'Open') ?></span></div>
        <div class="meter<?= $state === 'open' ? '' : ' meter--' . e($state) ?>" role="img" aria-label="<?= $fill ?>% booked"><i style="--fill:<?= $fill ?>%"></i></div>
    </div>
    <div class="class-card__book">
<?php if ($remaining): ?>
        <form action="<?= e(linkTo('book-class')) ?>" method="post">
        <?= csrfField() ?><input type="hidden" name="time_slot_id" value="<?= (int)$slot['id'] ?>">
        <details class="note-toggle"><summary>Add a note (optional)</summary>
        <label class="sr-only" for="note-<?= (int)$slot['id'] ?>">Note (optional)</label>
        <textarea id="note-<?= (int)$slot['id'] ?>" name="note" maxlength="1000" rows="2" placeholder="Optional reservation note"></textarea></details>
        <button type="submit" class="btn btn--primary btn--block">Book (Pending)</button>
        </form>
<?php else: ?>
        <p class="full-note">Full</p>
<?php endif; ?>
    </div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
