// ============================================
// ATM.JS – Bankomatlogik
// ============================================

// === ATM Animation ===
const overlay = document.getElementById("overlay");
const bgImg = document.getElementById("atm-bg");

document.querySelectorAll(".atm-hotspot").forEach((link) => {
  link.addEventListener("click", function (e) {
    e.preventDefault();
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

    // Fada ut till svart efter 400ms
    setTimeout(() => overlay.classList.add("fade-out"), 1200);

    // Navigera när allt är klart
    setTimeout(() => (window.location.href = href), 1800);
  });
});
