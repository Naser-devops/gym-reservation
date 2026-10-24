<?php
/**
 * View variables injected by renderView() and the shared layout.
 * Editor-only annotations; no changes to runtime behavior.
 * @var list<array<string, mixed>> $users
 * @var callable(string):string $__icon
 */
?>
<div class="admin-title"><div><span class="eyebrow">Members</span><h1 style="margin-top:.8rem">Manage users</h1></div></div>
<div class="callout"><?= $__icon('shield') ?><p>Administrators cannot deactivate themselves or other administrators from this page.</p></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Created</th><th>Action</th></tr></thead><tbody data-stagger>
<?php foreach ($users as $u): ?>
<tr><td class="num" data-label="ID"><?= (int)$u['id'] ?></td><td class="cell-title" data-label="Name"><?= e($u['full_name']) ?></td><td data-label="Email"><?= e($u['email']) ?></td>
<td data-label="Role"><span class="badge <?= $u['role']==='admin' ? 'badge--admin' : 'badge--plain' ?>"><?= e($u['role']) ?></span></td>
<td data-label="Active"><span class="badge <?= (int)$u['is_active'] ? 'badge--confirmed' : 'badge--cancelled' ?>"><?= (int)$u['is_active'] ? 'Yes':'No' ?></span></td>
<td class="num" data-label="Created"><?= e($u['created_at']) ?></td>
<td data-label="Action"><?php if ($u['role']==='user'): ?>
<form class="inline-form" method="post" action="<?= e(linkTo('admin-user-status')) ?>"><?= csrfField() ?>
<input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><input type="hidden" name="is_active" value="<?= (int)$u['is_active'] ? 0:1 ?>">
<button type="submit" class="btn btn--sm <?= (int)$u['is_active'] ? 'btn--danger' : 'btn--ok' ?>"><?= (int)$u['is_active'] ? 'Deactivate':'Activate' ?></button></form>
<?php else: ?>—<?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table></div>
