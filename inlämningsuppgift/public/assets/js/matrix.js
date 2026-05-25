const canvas = document.getElementById("matrix");
const ctx = canvas.getContext("2d");

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

const characters =
  "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$+-*/=%\"'#&_(),.;:?!\\|{}<>[]";
const fontSize = 16;
const columns = canvas.width / fontSize;
const drops = Array(Math.floor(columns)).fill(1);

function draw() {
  // Skapar en svag toning för att få "trail"-effekten
  ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = "#0F0"; // Matrix-grön
  ctx.font = fontSize + "px monospace";

  for (let i = 0; i < drops.length; i++) {
    const text = characters.charAt(
      Math.floor(Math.random() * characters.length),
    );
    ctx.fillText(text, i * fontSize, drops[i] * fontSize);

    if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
      drops[i] = 0;
    }
    drops[i]++;
  }
}

setInterval(draw, 63); // Ca 30 frames per sekund hade 33 från star.

// --------------------------------------------------
// 2. GENERERA BLINKANDE SERVER-LAMPOR
// --------------------------------------------------

const serverOverlay = document.getElementById("server-overlay");
const colors = ["green", "yellow", "red"];

// Antal lampor vi vill skapa över det avlägsna racket "60"
const totalLights = 100;

for (let i = 0; i < totalLights; i++) {
  // Skapa en LED-div
  const led = document.createElement("div");

  // Välj en slumpmässig färg (men övervägande grön/gul)
  const colorRand = Math.random();
  let colorClass;
  if (colorRand > 0.9)
    colorClass = "red"; // 10% röd
  else if (colorRand > 0.5)
    colorClass = "yellow"; // 40% gul
  else colorClass = "green"; // 50% grön

  led.className = `led ${colorClass}`;

  // --- Beräkna position ---
  // Placera lamporna i horisontella rader som passar server-enheterna.
  // Vertikal position (mestadels jämnt fördelad men med lite slump)
  const topPercent = Math.floor(i / 3) * 6 + Math.random() * 2 + 2;
  led.style.top = `${topPercent}%`;

  // Horisontell position (sprid ut inom racket) 80 + 10
  const leftPercent = Math.random() * 80 + 10;
  led.style.left = `${leftPercent}%`;

  // --- Beräkna animation ---
  // Slumpmässig tid för blinket (0.2s - 0.8s) varr * 0.6 + 0.2 + "s";
  const duration = Math.random() * 0.6 + 0.2 + "s";

  // Slumpmässig fördröjning innan det startar (0.0s - 3.0s)
  const delay = Math.random() * 1.0 + "s";

  // Applicera animationen
  led.style.animation = `blink-led ${duration} infinite ${delay}`;

  // Lägg till lampan i racket
  serverOverlay.appendChild(led);
}
