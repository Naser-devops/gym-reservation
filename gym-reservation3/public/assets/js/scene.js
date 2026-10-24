
/* Gym Reservation — Three.js hero scene (home + auth pages).
   One renderer per page, paused when off-screen or tab hidden, disposed on pagehide.
   If WebGL/Three.js is unavailable the page simply shows the CSS fallback. */
(function () {
  'use strict';
  var host = document.querySelector('[data-scene]');
  if (!host) return;
  var root = document.documentElement;
  function fallback() { root.classList.add('no-webgl'); }
  if (!window.THREE) { fallback(); return; }
  var THREE = window.THREE;

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var small = window.matchMedia && window.matchMedia('(max-width: 820px)').matches;
  var lowPower = small || (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4);

  var renderer;
  try {
    renderer = new THREE.WebGLRenderer({ antialias: !lowPower, alpha: true, powerPreference: 'high-performance' });
  } catch (err) { fallback(); return; }
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, lowPower ? 1.25 : 1.75));
  renderer.setClearColor(0x000000, 0);
  if ('outputColorSpace' in renderer) renderer.outputColorSpace = THREE.SRGBColorSpace; else renderer.outputEncoding = THREE.sRGBEncoding;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.15;
  var canvas = renderer.domElement;
  canvas.setAttribute('aria-hidden', 'true');
  host.appendChild(canvas);

  var scene = new THREE.Scene();
  var camera = new THREE.PerspectiveCamera(42, 1, 0.1, 100);
  camera.position.set(0, 0, 9);

  /* Procedural studio environment: dark box + emissive strip lights -> reflections on metal */
  var pmrem = new THREE.PMREMGenerator(renderer);
  var envScene = new THREE.Scene();
  envScene.add(new THREE.Mesh(new THREE.BoxGeometry(30, 30, 30), new THREE.MeshBasicMaterial({ color: 0x06070a, side: THREE.BackSide })));
  function strip(color, intensity, w, h, x, y, z, ry) {
    var m = new THREE.Mesh(new THREE.PlaneGeometry(w, h), new THREE.MeshBasicMaterial({ color: new THREE.Color(color).multiplyScalar(intensity), side: THREE.DoubleSide }));
    m.position.set(x, y, z); m.rotation.y = ry || 0; envScene.add(m);
  }
  strip(0xffffff, 6, 14, 1.2, 0, 9, -4, 0);
  strip(0xc6ff3d, 5, 1.4, 14, -10, 0, 2, Math.PI / 2);
  strip(0xff6a1a, 5, 1.4, 12, 10, -2, 4, Math.PI / 2);
  strip(0xffffff, 2.5, 8, 1, 0, -8, 3, 0);
  var envTex = pmrem.fromScene(envScene, 0.04).texture;
  scene.environment = envTex;

  var rig = new THREE.Group();
  scene.add(rig);

  var knotGeo = new THREE.TorusKnotGeometry(1.15, 0.34, lowPower ? 140 : 260, lowPower ? 18 : 32, 2, 3);
  var knotMat = new THREE.MeshPhysicalMaterial({ color: 0x20242b, metalness: 1, roughness: 0.2, clearcoat: 1, clearcoatRoughness: 0.12, envMapIntensity: 1.25 });
  var knot = new THREE.Mesh(knotGeo, knotMat);
  rig.add(knot);

  var ringDefs = [
    { r: 2.1, c: 0xc6ff3d, o: 0.9, rx: 1.2, ry: 0.2 },
    { r: 2.6, c: 0xffffff, o: 0.25, rx: 0.4, ry: 0.9 },
    { r: 3.1, c: 0xff6a1a, o: 0.7, rx: 1.9, ry: -0.5 }
  ];
  var rings = ringDefs.map(function (d) {
    var m = new THREE.Mesh(new THREE.TorusGeometry(d.r, 0.011, 8, 180), new THREE.MeshBasicMaterial({ color: d.c, transparent: true, opacity: d.o }));
    m.rotation.set(d.rx, d.ry, 0); rig.add(m); return m;
  });

  var pulse = new THREE.Mesh(new THREE.SphereGeometry(0.07, 16, 16), new THREE.MeshBasicMaterial({ color: 0xc6ff3d }));
  rings[0].add(pulse);

  var count = lowPower ? 240 : 650;
  var pos = new Float32Array(count * 3);
  for (var i = 0; i < count; i++) {
    var rad = 3.6 + Math.random() * 7, th = Math.random() * Math.PI * 2, ph = Math.acos(2 * Math.random() - 1);
    pos[i * 3] = rad * Math.sin(ph) * Math.cos(th); pos[i * 3 + 1] = rad * Math.sin(ph) * Math.sin(th); pos[i * 3 + 2] = rad * Math.cos(ph);
  }
  var pGeo = new THREE.BufferGeometry();
  pGeo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
  var pMat = new THREE.PointsMaterial({ color: 0xc6ff3d, size: lowPower ? 0.05 : 0.036, transparent: true, opacity: 0.65, depthWrite: false, blending: THREE.AdditiveBlending, sizeAttenuation: true });
  var points = new THREE.Points(pGeo, pMat);
  scene.add(points);

  var key = new THREE.DirectionalLight(0xffffff, 1.1); key.position.set(3, 4, 5); scene.add(key);
  var volt = new THREE.PointLight(0xc6ff3d, 2.2, 0); volt.position.set(-4, 2, 3); scene.add(volt);
  var ember = new THREE.PointLight(0xff6a1a, 2, 0); ember.position.set(4, -3, 2); scene.add(ember);

  var mouse = { x: 0, y: 0, tx: 0, ty: 0 };
  var baseScale = 1, running = false, visible = true, raf = 0, t0 = performance.now(), disposed = false;

  function resize() {
    var w = host.clientWidth || 1, h = host.clientHeight || 1;
    renderer.setSize(w, h, false);
    camera.aspect = w / h; camera.updateProjectionMatrix();
    var wide = w / h > 1.15;

    // Keep the Home scene as it is; shift only Login / Register on desktop.
    var isAuth = host.getAttribute('data-scene') === 'auth' && window.innerWidth > 820;
    rig.position.set(
      isAuth ? 0.95 : (wide ? Math.min(2.8, w / h * 1.25) : 0),
      isAuth ? 0.25 : (wide ? 0 : 0.9),
      0
    );
    baseScale = wide ? 0.70 : Math.max(0.48, Math.min(0.64, w / 620));

    if (!running) draw(performance.now());
  }

  function draw(now) {
    var t = (now - t0) / 1000;
    var intro = reduce ? 1 : Math.min(1, t / 1.6), ease = 1 - Math.pow(1 - intro, 3);
    mouse.x += (mouse.tx - mouse.x) * 0.05; mouse.y += (mouse.ty - mouse.y) * 0.05;
    var sp = Math.min(1.5, (window.scrollY || 0) / Math.max(1, window.innerHeight));
    var s = baseScale * (0.6 + 0.4 * ease);
    rig.scale.setScalar(s);
    rig.rotation.y = (reduce ? 0.6 : t * 0.14) + mouse.x * 0.35 + sp * 1.1;
    rig.rotation.x = mouse.y * 0.18 + sp * 0.25;
    knot.rotation.x = reduce ? 0.4 : t * 0.12;
    rings[0].rotation.z = t * 0.25; rings[1].rotation.z = -t * 0.18; rings[2].rotation.z = t * 0.12;
    pulse.position.set(Math.cos(t * 1.1) * ringDefs[0].r, Math.sin(t * 1.1) * ringDefs[0].r, 0);
    points.rotation.y = t * 0.015 + sp * 0.2; points.rotation.x = t * 0.008;
    camera.position.x = mouse.x * 0.35; camera.position.y = -mouse.y * 0.2; camera.position.z = 9 + sp * 1.2;
    camera.lookAt(0, 0, 0);
    renderer.render(scene, camera);
  }

  function loop(now) { if (!running) return; draw(now); raf = requestAnimationFrame(loop); }
  function start() { if (running || reduce || disposed || !visible || document.hidden) return; running = true; raf = requestAnimationFrame(loop); }
  function stop() { running = false; if (raf) cancelAnimationFrame(raf); raf = 0; }

  function onMove(e) { mouse.tx = (e.clientX / window.innerWidth) * 2 - 1; mouse.ty = (e.clientY / window.innerHeight) * 2 - 1; }
  function onVis() { if (document.hidden) stop(); else start(); }
  function onLost(e) { e.preventDefault(); stop(); fallback(); }

  var io = null, ro = null;
  if ('IntersectionObserver' in window) { io = new IntersectionObserver(function (en) { visible = en[0].isIntersecting; if (visible) start(); else stop(); }, { threshold: 0 }); io.observe(host); }
  if ('ResizeObserver' in window) { ro = new ResizeObserver(resize); ro.observe(host); } else window.addEventListener('resize', resize);
  if (!reduce && window.matchMedia && window.matchMedia('(pointer: fine)').matches) window.addEventListener('pointermove', onMove, { passive: true });
  document.addEventListener('visibilitychange', onVis);
  canvas.addEventListener('webglcontextlost', onLost);

  function dispose() {
    if (disposed) return; disposed = true; stop();
    if (io) io.disconnect(); if (ro) ro.disconnect();
    window.removeEventListener('resize', resize); window.removeEventListener('pointermove', onMove);
    document.removeEventListener('visibilitychange', onVis); canvas.removeEventListener('webglcontextlost', onLost);
    scene.traverse(function (o) { if (o.geometry) o.geometry.dispose(); if (o.material) { (Array.isArray(o.material) ? o.material : [o.material]).forEach(function (m) { m.dispose(); }); } });
    envScene.traverse(function (o) { if (o.geometry) o.geometry.dispose(); if (o.material) o.material.dispose(); });
    envTex.dispose(); pmrem.dispose(); renderer.dispose();
    if (renderer.forceContextLoss) renderer.forceContextLoss();
    if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
  }
  function onPageHide(e) { if (e.persisted) stop(); else dispose(); }
  function onPageShow(e) {
    if (!disposed && e.persisted) { resize(); draw(performance.now()); start(); }
  }
  window.addEventListener('pagehide', onPageHide);
  window.addEventListener('pageshow', onPageShow);

  resize();
  draw(performance.now());
  start();
})();
