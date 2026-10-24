<span class="eyebrow">Join the club</span>
<h1>Create account</h1>
<p>One account for every class, booking and confirmation.</p>
<form action="<?= e(linkTo('register')) ?>" method="post">
<?= csrfField() ?>
<label class="field"><span>Full name</span><input name="full_name" maxlength="100" required autocomplete="name" placeholder="Your full name"></label>
<label class="field"><span>Email</span><input name="email" type="email" maxlength="255" required autocomplete="email" placeholder="you@example.com"></label>
<label class="field"><span>Password (8–72 bytes)</span><input name="password" type="password" minlength="8" maxlength="72" required autocomplete="new-password" placeholder="At least 8 characters" data-toggle></label>
<button type="submit" class="btn btn--primary btn--block">Register</button>
</form><p class="alt">Already registered? <a href="<?= e(linkTo('login')) ?>">Login</a></p>
