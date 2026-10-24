<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var callable(string):string $__icon
 */
 /* Public landing page (new, additive). Uses only session presence to pick call-to-action buttons. */
$__in = !empty($_SESSION['user_id']); ?>
<section class="hero">
    <div class="hero__grid" aria-hidden="true"></div>
    <div class="scene" data-scene="hero" aria-hidden="true"><div class="scene__fallback"></div></div>
    <div class="container"><div class="hero__copy">
        <span class="eyebrow" data-hero>Gym reservation platform</span>
        <h1 data-hero>Train<br><span class="stroke">beyond</span> <span class="lime">limits.</span></h1>
        <p class="hero__lead" data-hero>Book your sessions. Build your momentum. Own your progress.</p>
        <div class="btn-row hero__cta" data-hero>
<?php if ($__in): ?>
            <a class="btn btn--primary" href="<?= e(linkTo('classes')) ?>">Browse available slots</a>
            <a class="btn btn--ghost" href="<?= e(linkTo('my-reservations')) ?>">My reservations</a>
<?php else: ?>
            <a class="btn btn--primary" href="<?= e(linkTo('register')) ?>">Create your account</a>
            <a class="btn btn--ghost" href="<?= e(linkTo('login')) ?>">Log in</a>
<?php endif; ?>
        </div>
    </div></div>
    <a class="hero__scroll" href="#how"><span>How it works</span><i></i></a>
</section>

<section class="section" id="how"><div class="container">
    <div class="section__head" data-reveal>
        <span class="eyebrow">The process</span>
        <h2>Three steps from couch to session.</h2>
        <p>No phone calls, no queues. Reserve a spot in a class and follow it from request to confirmation.</p>
    </div>
    <div class="steps" data-stagger>
        <article class="card card--glow step"><h3>Pick your slot</h3><p>Browse upcoming gym sessions, filter by date and see exactly how many spots are left.</p></article>
        <article class="card card--glow step"><h3>Request a spot</h3><p>Reserve in one tap and add an optional note. Every booking starts as Pending.</p></article>
        <article class="card card--glow step"><h3>Get confirmed</h3><p>An administrator confirms or declines your request. Track the status in My Reservations.</p></article>
    </div>
</div></section>

<section class="section" style="padding-top:0"><div class="container">
    <div class="section__head" data-reveal>
        <span class="eyebrow">Built for members</span>
        <h2>Everything you need. Nothing you don’t.</h2>
    </div>
    <div class="features" data-stagger>
        <article class="card feature"><?= $__icon('clock') ?><h3>Live availability</h3><p>Remaining capacity is shown on every class, so you always know if a spot is open.</p></article>
        <article class="card feature"><?= $__icon('calendar') ?><h3>Date filtering</h3><p>Jump straight to the day you want to train and see only the sessions that match.</p></article>
        <article class="card feature"><?= $__icon('check') ?><h3>Clear statuses</h3><p>Pending, Confirmed, Rejected or Cancelled — your reservation status is always visible.</p></article>
        <article class="card feature"><?= $__icon('shield') ?><h3>Cancel before start</h3><p>Plans change. Cancel a Pending or Confirmed booking any time before the class begins.</p></article>
    </div>
</div></section>

<section class="band"><div class="container band__inner">
    <h2 data-reveal>Your next session is waiting.</h2>
    <div class="btn-row" data-reveal>
<?php if ($__in): ?>
        <a class="btn btn--primary" href="<?= e(linkTo('classes')) ?>">Book a class</a>
<?php else: ?>
        <a class="btn btn--primary" href="<?= e(linkTo('register')) ?>">Get started</a>
        <a class="btn btn--ghost" href="<?= e(linkTo('login')) ?>">I already have an account</a>
<?php endif; ?>
    </div>
</div></section>
