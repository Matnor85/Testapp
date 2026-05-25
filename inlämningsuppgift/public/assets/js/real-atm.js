// ============================================
// REAL-ATM.JS – Logik för bankomatbildsidan
// ============================================

// === TILLSTÅND ===
let kortInsatt = false;
let aktivSkärm = "idle";
let valtBelopp = null;
let inmatat = "";

// === SKÄRMELEMENT ===
const screenLeft = document.getElementById("screen-left");
const screenMid = document.getElementById("screen-mid");
const screenRight = document.getElementById("screen-right");

// === SKÄRMVYER ===
const skärmar = {
  idle: {
    left: ["", "", "", ""],
    mid: '<p class="screen-idle">Välkommen</p><p class="screen-subtitle">Sätt in ditt kort</p>',
    right: ["", "", "", ""],
  },

  språk: {
    left: ["Svenska", "English", "Deutsch", "Français"],
    mid: '<p class="screen-title">Välj språk</p><p class="screen-subtitle">Select language</p>',
    right: ["", "", "", ""],
  },

  meny: {
    left: ["Kontoinformation", "Fler tjänster", "Insättning", "PIN-byte"],
    mid: '<p class="screen-title">Välj tjänst</p>',
    right: ["Saldo", "Uttag", "Snabbuttag 500", "Betalning"],
  },

  avbrutet: {
    left: ["", "", "", ""],
    mid: '<p class="screen-avbrutet">Session avbruten</p><p class="screen-subtitle">Ta ditt kort</p>',
    right: ["", "", "", ""],
  },

  uttag: {
    left: ["100 kr", "200 kr", "500 kr", "Annan summa"],
    mid: '<p class="screen-title">Välj belopp</p>',
    right: ["", "", "", ""],
  },

  bekräftelse: {
    left: ["", "", "", ""],
    mid: "",
    right: ["", "", "", ""],
  },

  utförs: {
    left: ["", "", "", ""],
    mid: '<p class="screen-idle">Hämtar pengar...</p>',
    right: ["", "", "", ""],
  },

  annansumma: {
    left: ["", "", "", ""],
    mid: '<p class="screen-title">Ange belopp</p><p class="screen-subtitle">Rätta = ta bort siffra</p><p class="screen-saldo" id="belopp-display">0 kr</p>',
    right: ["", "", "", ""],
  },

  saldo: {
    left: ["", "", "", ""],
    mid: '<p class="screen-title">Ditt saldo är:</p><p class="screen-saldo">12 450 kr</p>',
    right: ["", "", "", ""],
  },
};

// === VISA SKÄRM ===
function visaSkärm(namn) {
  aktivSkärm = namn;
  const s = skärmar[namn];
  if (!s) return;

  screenLeft.innerHTML = s.left
    .map(
      (text, i) =>
        `<div class="screen-option ${text ? "arrow-left" : ""}" data-idx="${i}">${text}</div>`,
    )
    .join("");

  screenMid.innerHTML = s.mid;

  screenRight.innerHTML = s.right
    .map(
      (text, i) =>
        `<div class="screen-option ${text ? "arrow-right" : ""}" data-idx="${i}">${text}</div>`,
    )
    .join("");
}

// === TA UT KORT ===
function taUtKort() {
  kortInsatt = false;
  valtBelopp = null;
  inmatat = "";
  const card = document.getElementById("card");

  visaSkärm("avbrutet");
  card.classList.remove("insatt");
  card.classList.add("uttag");

  setTimeout(() => {
    card.classList.remove("uttag");
    setTimeout(() => visaSkärm("idle"), 1500);
  }, 1000);
}

// === KORTLÄSARE – sätt in ELLER ryck ut ===
document.getElementById("btn-kort").addEventListener("click", () => {
  if (!kortInsatt) {
    kortInsatt = true;
    document.getElementById("card").classList.add("insatt");
    setTimeout(() => visaSkärm("språk"), 900);
  } else {
    taUtKort();
  }
});

// === SIDOKNAPPAR ===
const sidoMappning = {
  "btn-v1": { side: "v", idx: 0 },
  "btn-v2": { side: "v", idx: 1 },
  "btn-v3": { side: "v", idx: 2 },
  "btn-v4": { side: "v", idx: 3 },
  "btn-h1": { side: "h", idx: 0 },
  "btn-h2": { side: "h", idx: 1 },
  "btn-h3": { side: "h", idx: 2 },
  "btn-h4": { side: "h", idx: 3 },
};

Object.entries(sidoMappning).forEach(([btnId, { side, idx }]) => {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.addEventListener("click", () => {
    if (!kortInsatt) return;

    const container = side === "v" ? screenLeft : screenRight;
    const options = container.querySelectorAll(".screen-option");
    const valt = options[idx];
    const text = valt ? valt.textContent.trim() : "";

    if (!text) return;
    hanteraVal(aktivSkärm, side, idx, text);
  });
});

// === VALHANTERING ===
function hanteraVal(skärm, side, idx, text) {
  switch (skärm) {
    case "språk":
      visaSkärm("meny");
      break;

    case "meny":
      switch (text) {
        case "Uttag":
          visaSkärm("uttag");
          break;
        case "Saldo":
          visaSkärm("saldo");
          break;
        case "Snabbuttag 500":
          visaSkärm("utförs");
          setTimeout(() => visaSkärm("meny"), 2500);
          break;
        case "Betalning":
        case "Kontoinformation":
        case "Fler tjänster":
        case "Insättning":
        case "PIN-byte":
          alert(text + " saknas i denna demo.");
          break;
      }
      break;

    case "uttag":
      if (text === "Annan summa") {
        inmatat = "";
        visaSkärm("annansumma");
      } else {
        visaSkärm("utförs");
        setTimeout(() => visaSkärm("meny"), 2500);
      }
      break;
  }
}

// function hanteraVal(skärm, side, idx, text) {
//   if (skärm === "språk") {
//     visaSkärm("meny");
//   } else if (skärm === "meny") {
//     if (text === "Uttag") visaSkärm("uttag");
//     else if (text === "Saldo") visaSkärm("saldo");
//     else if (text === "Snabbuttag 500") visaBekräftelse("500");
//   } else if (skärm === "uttag") {
//     if (text === "Annan summa") {
//       inmatat = "";
//       visaSkärm("annansumma");
//     } else {
//       const belopp = text.replace(" kr", "");
//       // Direkt till utförs – ingen bekräftelse
//       visaSkärm("utförs");
//       setTimeout(() => visaSkärm("meny"), 2500);
//     }
//   }
// }

// === VISA BEKRÄFTELSE ===
function visaBekräftelse(belopp) {
  valtBelopp = belopp;
  skärmar["bekräftelse"].mid = `<p class="screen-title">Bekräfta uttag</p>
     <p class="screen-saldo">${belopp} kr</p>
     <p class="screen-subtitle">OK = bekräfta</p>
     <p class="screen-subtitle">Avbryt = ångra</p>`;
  visaSkärm("bekräftelse");
}

// === OK-KNAPP ===
document.getElementById("btn-ok").addEventListener("click", () => {
  if (!kortInsatt) return;

  if (aktivSkärm === "bekräftelse") {
    visaSkärm("utförs");
    setTimeout(() => visaSkärm("meny"), 2500);
    valtBelopp = null;
  } else if (aktivSkärm === "saldo") {
    visaSkärm("meny");
  } else if (aktivSkärm === "annansumma") {
    if (inmatat && inmatat !== "0" && parseInt(inmatat) > 0) {
      visaBekräftelse(inmatat);
    }
  }
});

// === AVBRYT-KNAPP ===
document.getElementById("btn-avbryt").addEventListener("click", () => {
  if (!kortInsatt) return;

  if (aktivSkärm === "bekräftelse") {
    valtBelopp = null;
    visaSkärm("uttag");
  } else if (
    aktivSkärm === "uttag" ||
    aktivSkärm === "saldo" ||
    aktivSkärm === "annansumma"
  ) {
    inmatat = "";
    visaSkärm("meny");
  } else {
    taUtKort();
  }
});

// === RÄTTA-KNAPP ===
document.getElementById("btn-ratta").addEventListener("click", () => {
  if (!kortInsatt) return;
  if (aktivSkärm !== "annansumma") return;

  inmatat = inmatat.slice(0, -1);

  const display = document.getElementById("belopp-display");
  if (display) display.textContent = (inmatat || "0") + " kr";
});

// === SIFFERKNAPPAR ===
[
  "n1",
  "n2",
  "n3",
  "n4",
  "n5",
  "n6",
  "n7",
  "n8",
  "n9",
  "n0",
  "n00",
  "nstar",
].forEach((id) => {
  const btn = document.getElementById("btn-" + id);
  if (!btn) return;
  btn.addEventListener("click", () => {
    if (!kortInsatt) return;
    if (aktivSkärm !== "annansumma") return;

    const siffra = btn.title;
    if (siffra === "*") return;

    // Max 6 siffror
    if (inmatat.length >= 6) return;

    inmatat += siffra;

    const display = document.getElementById("belopp-display");
    if (display) display.textContent = inmatat + " kr";
  });
});

// === STARTA ===
visaSkärm("idle");
