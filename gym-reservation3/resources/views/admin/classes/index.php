<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var list<array<string, mixed>> $slots
 * @var callable(string):string $__icon
 */
?>
<div class="admin-title"><div><span class="eyebrow">Schedule</span><h1 style="margin-top:.8rem">Manage gym time slots</h1></div>
<div class="btn-row"><a class="btn btn--primary btn--sm" href="<?= e(linkTo('admin-create-class')) ?>"><?= $__icon('plus') ?>Add new slot</a><a class="btn btn--ghost btn--sm" href="<?= e(linkTo('admin')) ?>">Dashboard</a></div></div>
<?php if (!$slots): ?><div class="empty" data-reveal><?= $__icon('calendar') ?><h3>No time slots yet</h3><p>No time slots created yet. Add your first class to open bookings.</p><a class="btn btn--primary btn--sm" href="<?= e(linkTo('admin-create-class')) ?>">Add new slot</a></div><?php else: ?>
<div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Class</th><th>Start</th><th>End</th><th>Capacity</th><th>Booked</th><th>Active?</th><th>Actions</th></tr></thead><tbody data-stagger>
<?php foreach ($slots as $s):
    $cap = (int)$s['capacity']; $bk = (int)$s['reserved_count'];
    $fill = $cap > 0 ? max(0, min(100, (int)round($bk / $cap * 100))) : 100; ?>
<tr><td class="num" data-label="ID"><?= (int)$s['id'] ?></td><td class="cell-title" data-label="Class"><?= e($s['class_name']) ?></td><td class="num" data-label="Start"><?= e($s['starts_at']) ?></td><td class="num" data-label="End"><?= e($s['ends_at']) ?></td>
<td class="num" data-label="Capacity"><?= $cap ?></td>
<td class="slot-cap" data-label="Booked"><?= $bk ?> / <?= $cap ?><div class="meter<?= $fill >= 100 ? ' meter--full' : '' ?>"><i style="--fill:<?= $fill ?>%"></i></div></td>
<td data-label="Active?"><span class="badge <?= (int)$s['is_active'] ? 'badge--confirmed' : 'badge--cancelled' ?>"><?= (int)$s['is_active'] ? 'Yes':'No' ?></span></td>
<td data-label="Actions"><div class="cell-actions"><a class="btn btn--ghost btn--sm" href="<?= e(linkTo('admin-edit-class',['id'=>(int)$s['id']])) ?>">Edit</a>
<form class="inline-form" method="post" action="<?= e(linkTo('admin-toggle-class')) ?>"><?= csrfField() ?>
<input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><input type="hidden" name="is_active" value="<?= (int)$s['is_active'] ? 0 : 1 ?>">
<button type="submit" class="btn btn--ghost btn--sm"><?= (int)$s['is_active'] ? 'Deactivate':'Activate' ?></button></form>
<form class="inline-form" method="post" action="<?= e(linkTo('admin-delete-class')) ?>" onsubmit="return confirm('Remove this slot from the catalog?');"><?= csrfField() ?>
<input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button type="submit" class="btn btn--danger btn--sm">Delete eligible slot</button></form></div>
</td></tr>
<?php endforeach; ?></tbody></table></div><?php endif; ?>
<p class="hint" style="margin-top:1.2rem">Deletion is blocked when Pending or Confirmed bookings exist. Eligible slots are hidden, while old history is preserved.</p>
