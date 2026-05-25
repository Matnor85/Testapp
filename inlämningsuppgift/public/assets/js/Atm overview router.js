// ============================================
// ATM-OVERVIEW-ROUTER.JS
// Router-medveten ersättning för atm-overview.js
//
// Skillnad mot originalet:
//   - Använder router.goTo(href) istället för window.location.href
//   - Animationen (zoom + fade) körs exakt som förut
//   - Ingen helsidesomladdning sker
// ============================================

const overlay = document.getElementById("overlay");
const bgImg = document.getElementById("atm-bg");

document.querySelectorAll(".atm-hotspot").forEach((link) => {
  link.addEventListener("click", function (e) {
    // STOPPA HUVUDROUTERN FRÅN ATT KÖRA SITT KLICK SAMTIDIGT!
    e.preventDefault();
    e.stopImmediatePropagation();

    const href = this.getAttribute("href");

    // Räkna ut mitten av den klickade bankomaten
    const rect = this.getBoundingClientRect();
    const container = document
      .querySelector(".image-container")
      .getBoundingClientRect();
    const cx =
      ((rect.left + rect.width / 2 - container.left) / container.width) * 100;
    const cy =
      ((rect.top + rect.height / 2 - container.top) / container.height) * 100;

    // Zooma bilden mot just den bankomaten
    bgImg.style.transformOrigin = `${cx}% ${cy}%`;
    bgImg.classList.add("zoom-to-atm");

    // Fada ut till svart efter 1200ms
    setTimeout(() => overlay.classList.add("fade-out"), 1200);

    // Navigera via routern (ingen sidladdning) när animationen är klar
    setTimeout(() => {
      // Återställ animationsstate i detta lager inför nästa besök
      bgImg.classList.remove("zoom-to-atm");
      overlay.classList.remove("fade-out");
      bgImg.style.transformOrigin = "center center";

      // Säkerställ att vi skickar med hela den rena parametern
      let targetHref = href;
      if (!targetHref.startsWith("index.php")) {
        targetHref = "index.php?page=" + targetHref;
      }

      router.goTo(targetHref);
    }, 1800);
  });
});
