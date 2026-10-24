<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var string $message
 * @var callable(string):string $__icon
 */
?>
<div class="container"><div class="error-block">
    <?= $__icon('alert') ?>
    <h1>Request could not be completed</h1>
    <p><?= e($message) ?></p>
    <a class="btn btn--primary" href="<?= e(linkTo('account')) ?>">Go back</a>
</div></div>
