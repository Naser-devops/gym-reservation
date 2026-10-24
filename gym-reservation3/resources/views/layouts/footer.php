<?php /* Closes whichever shell header.php opened, then loads scripts. Presentation only. */
$__shell = $__shell ?? 'site';
$__scene = $__scene ?? false;
if ($__shell === 'auth'): ?>
</div></main></div>
<?php elseif ($__shell === 'admin'): ?>
</main></div></div>
<?php else: ?>
</main>
<footer class="site-footer"><div class="container site-footer__inner">
    <a class="brand" href="<?= e(linkTo('home')) ?>"><span class="brand__mark"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 3 14h9l-1 8 10-12h-9z"/></svg></span><span>Gym<b>Reserve</b></span></a>
    <small>Gym Reservation Management &middot; Book. Train. Repeat.</small>
</div></footer>
<?php endif; ?>
<script defer src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<?php if ($__scene): ?><script defer src="https://cdn.jsdelivr.net/npm/three@0.149.0/build/three.min.js"></script>
<?php endif; ?>
<script defer src="public/assets/js/main.js"></script>
<?php if ($__scene): ?><script defer src="public/assets/js/scene.js"></script>
<?php endif; ?>
</body></html>
