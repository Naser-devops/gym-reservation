<div class="admin-title"><div><span class="eyebrow">Schedule</span><h1 style="margin-top:.8rem">Add a gym time slot</h1></div></div>
<section class="card form-card" data-reveal>
<form method="post" action="<?= e(linkTo('admin-create-class')) ?>">
<?= csrfField() ?>
<div class="form-grid">
<label class="field span-2"><span>Class name</span><input name="class_name" maxlength="100" required placeholder="e.g. Strength Foundations"></label>
<label class="field span-2"><span>Description</span><textarea name="description" maxlength="3000" placeholder="What members can expect"></textarea></label>
<label class="field"><span>Start (Jordan local time)</span><input type="datetime-local" name="starts_at" required></label>
<label class="field"><span>End (Jordan local time)</span><input type="datetime-local" name="ends_at" required></label>
<label class="field"><span>Capacity</span><input type="number" min="1" max="1000" name="capacity" required></label>
</div>
<div class="btn-row"><button type="submit" class="btn btn--primary">Create</button><a class="btn btn--ghost" href="<?= e(linkTo('admin-classes')) ?>">Back to time slots</a></div>
</form></section>
