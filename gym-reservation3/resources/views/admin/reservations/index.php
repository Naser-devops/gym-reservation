<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var list<array<string, mixed>> $rows
 * @var string $search
 * @var string $status
 * @var callable(string):string $__icon
 */
?>
<div class="admin-title"><div><span class="eyebrow">Bookings</span><h1 style="margin-top:.8rem">Manage reservations</h1></div></div>
<form class="toolbar" method="get" action="index.php">
<input type="hidden" name="page" value="admin-reservations">
<label class="field"><span>Search name / email</span><input name="search" value="<?= e($search) ?>" maxlength="100" placeholder="Name or email"></label>
<label class="field"><span>Status</span><select name="status">
<?php foreach (['','Pending','Confirmed','Rejected','Cancelled'] as $option): ?>
<option value="<?= e($option) ?>" <?= $status===$option?'selected':'' ?>><?= e($option?:'All') ?></option>
<?php endforeach; ?>
</select></label>
<button type="submit" class="btn btn--primary">Search</button>
<a class="btn btn--ghost" href="<?= e(linkTo('admin-reservations')) ?>">Reset</a>
</form>
<?php if (!$rows): ?>
<div class="empty" data-reveal><?= $__icon('check') ?><h3>No matching reservations</h3><p>Nothing matches this search. Adjust the name, email or status filter.</p></div>
<?php else: ?>
<p class="muted" style="margin-bottom:.8rem"><?= count($rows) ?> reservation<?= count($rows) === 1 ? '' : 's' ?></p>
<div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>User</th><th>Email</th><th>Class</th><th>Starts</th><th>Status</th><th>Note</th><th>Actions</th></tr></thead><tbody data-stagger>
<?php foreach ($rows as $r): ?>
<tr><td class="num" data-label="ID"><?= (int)$r['id'] ?></td><td class="cell-title" data-label="User"><?= e($r['full_name']) ?></td><td data-label="Email"><?= e($r['email']) ?></td><td data-label="Class"><?= e($r['class_name']) ?></td><td class="num" data-label="Starts"><?= e($r['starts_at']) ?></td><td data-label="Status"><span class="badge badge--<?= e(strtolower((string)$r['status'])) ?>"><?= e($r['status']) ?></span></td><td data-label="Note"><?= e($r['note']) ?></td>
<td data-label="Actions"><div class="cell-actions"><?php if (isFuture($r['starts_at'])):
    $actions=$r['status']==='Pending' ? ['Confirmed'=>'Confirm','Rejected'=>'Reject','Cancelled'=>'Cancel'] : ($r['status']==='Confirmed' ? ['Cancelled'=>'Cancel'] : []);
    foreach ($actions as $value=>$label): ?>
<form class="inline-form" method="post" action="<?= e(linkTo('admin-reservation-status')) ?>"<?php if ($value !== 'Confirmed'): ?> data-confirm-title="<?= e($label) ?> this reservation?" data-confirm="<?= e($r['full_name']) ?> — <?= e($r['class_name']) ?> will be marked as <?= e($value) ?>."<?php endif; ?>><?= csrfField() ?>
<input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="status" value="<?= e($value) ?>">
<button type="submit" class="btn btn--sm <?= $value === 'Confirmed' ? 'btn--ok' : 'btn--danger' ?>"><?= e($label) ?></button></form>
<?php endforeach; endif; ?></div></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>
