/**
 * camera.js – Konvex säkerhetsspegel
 *
 * Läser konfiguration från window.MIRROR_CONFIG (satt av camera.php).
 * Admin-API: window.ConvexMirror.setStrength(0.0 – 1.0)
 */

const ConvexMirror = (function () {
  const W = 320;
  const H = 320;

  const canvas = document.getElementById("mirrorCanvas");
  const ctx = canvas.getContext("2d");
  const idleEl = document.getElementById("mirrorIdle");

  // Hämta startvärde från PHP eller använd default
  let strength = window.MIRROR_CONFIG?.strength ?? 0.65;

  let lutX, lutY;

  // ── Bygg lookup-tabell för fisheye-distorsion ──
  function buildLUT() {
    lutX = new Float32Array(W * H);
    lutY = new Float32Array(W * H);

    const cx = W / 2;
    const cy = H / 2;
    const maxR = cx;
    const k = strength;

    for (let y = 0; y < H; y++) {
      for (let x = 0; x < W; x++) {
        const dx = (x - cx) / maxR;
        const dy = (y - cy) / maxR;
        const r = Math.sqrt(dx * dx + dy * dy);

        let sx, sy;

        if (r >= 1) {
          sx = x;
          sy = y;
        } else {
          const rf =
            r === 0
              ? 0
              : Math.atan((r * k * Math.PI) / 2) / ((k * Math.PI) / 2);
          const scale = rf / (r + 1e-9);
          sx = cx + dx * scale * maxR;
          sy = cy + dy * scale * maxR;
        }

        const i = y * W + x;
        lutX[i] = sx;
        lutY[i] = sy;
      }
    }
  }

  // ── Applicera fisheye via LUT + bilinär interpolering ──
  function applyFisheye(srcData) {
    const sd = srcData.data;
    const dst = ctx.createImageData(W, H);
    const dd = dst.data;

    for (let y = 0; y < H; y++) {
      for (let x = 0; x < W; x++) {
        const i = y * W + x;
        const sx = lutX[i];
        const sy = lutY[i];
        const x0 = sx | 0;
        const y0 = sy | 0;
        const fx = sx - x0;
        const fy = sy - y0;

        function sample(px, py, c) {
          if (px < 0 || py < 0 || px >= W || py >= H) return 0;
          return sd[(py * W + px) * 4 + c];
        }

        const p = i * 4;
        for (let c = 0; c < 3; c++) {
          dd[p + c] =
            sample(x0, y0, c) * (1 - fx) * (1 - fy) +
            sample(x0 + 1, y0, c) * fx * (1 - fy) +
            sample(x0, y0 + 1, c) * (1 - fx) * fy +
            sample(x0 + 1, y0 + 1, c) * fx * fy;
        }
        dd[p + 3] = 255;
      }
    }

    ctx.putImageData(dst, 0, 0);
  }

  // ── Renderingsloop ──
  const tmp = document.createElement("canvas");
  tmp.width = W;
  tmp.height = H;
  const tmpCtx = tmp.getContext("2d");
  let video;

  function drawLoop() {
    tmpCtx.drawImage(video, 0, 0, W, H);
    applyFisheye(tmpCtx.getImageData(0, 0, W, H));
    requestAnimationFrame(drawLoop);
  }

  // ── Starta kamera ──
  function init() {
    buildLUT();

    video = document.createElement("video");
    video.autoplay = true;
    video.playsInline = true;
    video.muted = true;

    // Försök bakre kamera (mer verklighetstrogen för ATM), annars valfri
    navigator.mediaDevices
      .getUserMedia({
        video: { facingMode: "environment", width: W, height: H },
      })
      .catch(() => navigator.mediaDevices.getUserMedia({ video: true }))
      .then((stream) => {
        video.srcObject = stream;
        video.onloadedmetadata = () => {
          video.play();
          idleEl.classList.add("hidden");
          drawLoop();
        };
      })
      .catch((err) => {
        console.warn("Kamera ej tillgänglig:", err.message);
        // idle-bakgrunden stannar kvar som fallback
      });
  }

  // ── Publikt API (för admin-sida) ──
  return {
    init,

    /**
     * Justera fisheye-styrkan live.
     * Anropas från admin: window.ConvexMirror.setStrength(0.8)
     * @param {number} val – värde mellan 0.0 och 1.0
     */
    setStrength(val) {
      strength = Math.max(0, Math.min(1, val));
      buildLUT();
    },

    getStrength() {
      return strength;
    },
  };
})();

// Starta spegeln när DOM:en är klar
document.addEventListener("DOMContentLoaded", () => ConvexMirror.init());

// const canvas = document.getElementById("mirrorCanvas");
// const ctx = canvas.getContext("2d");
// const video = document.getElementById("srcVideo");
// const W = 400,
//   H = 400;

// let fisheyeStrength = 0.6;
// let lutX, lutY;

// // Bygg lookup-tabell för fisheye-distorsion
// function buildLUT() {
//   lutX = new Float32Array(W * H);
//   lutY = new Float32Array(W * H);
//   const cx = W / 2,
//     cy = H / 2;
//   const maxR = Math.min(cx, cy);
//   const k = fisheyeStrength;

//   for (let y = 0; y < H; y++) {
//     for (let x = 0; x < W; x++) {
//       const dx = (x - cx) / maxR;
//       const dy = (y - cy) / maxR;
//       const r = Math.sqrt(dx * dx + dy * dy);
//       let srcX, srcY;

//       if (r >= 1.0) {
//         srcX = x;
//         srcY = y;
//       } else {
//         const rf =
//           r === 0 ? 0 : Math.atan((r * k * Math.PI) / 2) / ((k * Math.PI) / 2);
//         const scale = rf / (r + 1e-9);
//         srcX = cx + dx * scale * maxR;
//         srcY = cy + dy * scale * maxR;
//       }

//       const idx = y * W + x;
//       lutX[idx] = srcX;
//       lutY[idx] = srcY;
//     }
//   }
// }

// buildLUT();

// // Applicera fisheye via LUT + bilinear interpolering
// function applyFisheye(srcImageData) {
//   const src = srcImageData.data;
//   const dst = ctx.createImageData(W, H);
//   const d = dst.data;

//   for (let y = 0; y < H; y++) {
//     for (let x = 0; x < W; x++) {
//       const idx = y * W + x;
//       const sx = lutX[idx];
//       const sy = lutY[idx];
//       const sx0 = Math.floor(sx),
//         sy0 = Math.floor(sy);
//       const sx1 = sx0 + 1,
//         sy1 = sy0 + 1;
//       const fx = sx - sx0,
//         fy = sy - sy0;

//       function getSafe(px, py, c) {
//         if (px < 0 || py < 0 || px >= W || py >= H) return 0;
//         return src[(py * W + px) * 4 + c];
//       }

//       const i = idx * 4;
//       for (let c = 0; c < 3; c++) {
//         d[i + c] = Math.round(
//           getSafe(sx0, sy0, c) * (1 - fx) * (1 - fy) +
//             getSafe(sx1, sy0, c) * fx * (1 - fy) +
//             getSafe(sx0, sy1, c) * (1 - fx) * fy +
//             getSafe(sx1, sy1, c) * fx * fy,
//         );
//       }
//       d[i + 3] = 255;
//     }
//   }
//   ctx.putImageData(dst, 0, 0);
// }

// // Rita en frame
// const tmpCanvas = document.createElement("canvas");
// tmpCanvas.width = W;
// tmpCanvas.height = H;
// const tmpCtx = tmpCanvas.getContext("2d");

// function drawFrame() {
//   tmpCtx.drawImage(video, 0, 0, W, H);
//   applyFisheye(tmpCtx.getImageData(0, 0, W, H));
//   requestAnimationFrame(drawFrame);
// }

// // Starta kamera
// document.getElementById("startBtn").addEventListener("click", async () => {
//   const statusEl = document.getElementById("statusMsg");
//   const btn = document.getElementById("startBtn");
//   statusEl.textContent = "Begär kameraåtkomst...";
//   btn.disabled = true;

//   try {
//     const stream = await navigator.mediaDevices.getUserMedia({
//       video: {
//         facingMode: "user",
//         width: { ideal: 400 },
//         height: { ideal: 400 },
//       },
//     });
//     video.srcObject = stream;
//     video.onloadedmetadata = () => {
//       video.play();
//       statusEl.textContent = "Kamera aktiv";
//       btn.style.display = "none";
//       document.getElementById("controls").style.display = "flex";
//       drawFrame();
//     };
//   } catch (e) {
//     statusEl.textContent = "Fel: " + e.message;
//     btn.disabled = false;
//   }
// });

// // Reglage för fisheye-styrka
// document.getElementById("fisheyeSlider").addEventListener("input", function () {
//   fisheyeStrength = parseFloat(this.value);
//   document.getElementById("fisheyeVal").textContent =
//     fisheyeStrength.toFixed(2);
//   buildLUT();
// });
