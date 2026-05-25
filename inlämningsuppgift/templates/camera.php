<?php
/**
 * camera.php
 * Konvex säkerhetsspegel med kameravy.
 * Inkludera med: <?php include 'camera.php'; ?>
 *
 * Admin kan justera fisheye-styrkan via:
 *   window.ConvexMirror.setStrength(0.8);
 */

// Standardvärde – kan överskridas från PHP om du vill
$fisheye_strength = 0.95;
?>

<div class="atm-mirror" id="atmMirror">
  <div class="atm-mirror__idle" id="mirrorIdle"></div>

  <div class="atm-mirror__clip">
    <canvas class="atm-mirror__canvas" id="mirrorCanvas" width="320" height="320"></canvas>
  </div>

  <svg class="atm-mirror__overlay" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <radialGradient id="mg-sheen" cx="33%" cy="28%" r="60%">
        <stop offset="0%"   stop-color="white" stop-opacity="0.28"/>
        <stop offset="60%"  stop-color="white" stop-opacity="0.03"/>
        <stop offset="100%" stop-color="white" stop-opacity="0"/>
      </radialGradient>
      <radialGradient id="mg-edge" cx="50%" cy="50%" r="50%">
        <stop offset="75%"  stop-color="transparent"/>
        <stop offset="100%" stop-color="black" stop-opacity="0.5"/>
      </radialGradient>
    </defs>
    <circle cx="50" cy="50" r="49" fill="none" stroke="#b0c8d0" stroke-width="3"/>
    <circle cx="50" cy="50" r="46" fill="none" stroke="#6a8a94" stroke-width="1"/>
    <circle cx="50" cy="50" r="49" fill="url(#mg-sheen)"/>
    <circle cx="50" cy="50" r="49" fill="url(#mg-edge)"/>
  </svg>
</div>

<link rel="stylesheet" href="../public/assets/css/camera.css">
<script>
  window.MIRROR_CONFIG = { strength: <?= $fisheye_strength ?> };
</script>
<script src="../public/assets/js/camera.js" defer></script>