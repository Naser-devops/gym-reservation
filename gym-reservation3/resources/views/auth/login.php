<span class="eyebrow">Member access</span>
<h1>Welcome back</h1>
<p>Log in to book classes and manage your reservations.</p>
<form action="<?= e(linkTo('login')) ?>" method="post">
<?= csrfField() ?>
<label class="field"><span>Email</span><input name="email" type="email" maxlength="255" required autocomplete="email" placeholder="you@example.com"></label>
<label class="field"><span>Password</span><input name="password" type="password" required autocomplete="current-password" placeholder="Your password" data-toggle></label>
<button type="submit" class="btn btn--primary btn--block">Login</button>
</form><p class="alt">New here? <a href="<?= e(linkTo('register')) ?>">Register</a></p>
