<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var array<string, mixed> $slot
 * @var callable(string):string $__icon
 */
?>
<div class="admin-title"><div><span class="eyebrow">Schedule</span><h1 style="margin-top:.8rem">Edit time slot #<?= (int)$slot['id'] ?></h1></div></div>
<div class="callout"><?= $__icon('alert') ?><p>Changing start/end time is blocked if this slot has Pending or Confirmed bookings.</p></div>
<section class="card form-card" data-reveal>
<form method="post" action="<?= e(linkTo('admin-update-class')) ?>">
<?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$slot['id'] ?>">
<div class="form-grid">
<label class="field span-2"><span>Class name</span><input name="class_name" maxlength="100" value="<?= e($slot['class_name']) ?>" required></label>
<label class="field span-2"><span>Description</span><textarea name="description" maxlength="3000"><?= e($slot['description']) ?></textarea></label>
<label class="field"><span>Start</span><input type="datetime-local" name="starts_at" value="<?= e(str_replace(' ','T',substr($slot['starts_at'],0,16))) ?>" required></label>
<label class="field"><span>End</span><input type="datetime-local" name="ends_at" value="<?= e(str_replace(' ','T',substr($slot['ends_at'],0,16))) ?>" required></label>
<label class="field"><span>Capacity</span><input type="number" min="1" max="1000" name="capacity" value="<?= (int)$slot['capacity'] ?>" required></label>
</div>
<div class="btn-row"><button type="submit" class="btn btn--primary">Save changes</button><a class="btn btn--ghost" href="<?= e(linkTo('admin-classes')) ?>">Back</a></div>
</form></section>
