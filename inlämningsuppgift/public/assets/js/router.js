/**
 * router.js – Layering-router v3 (Säkrad för publika mappar)
 */
const router = (() => {
  const cache = {};
  const layers = {};
  const hist = [];
  let current = null;

  // En hjälpfunktion för att garantera att vi ALLTID anropar vår publika index.php
  function getCleanUrl(url) {
    let pageParam = "home";

    // Ta bort eventuella inledande snedstreck för att läsa strängen rent
    let cleanStr = url.startsWith("/") ? url.substring(1) : url;

    if (cleanStr.includes("page=")) {
      const params = new URLSearchParams(
        cleanStr.substring(cleanStr.indexOf("?")),
      );
      pageParam = params.get("page");
    } else if (cleanStr !== "" && !cleanStr.startsWith("index.php")) {
      // Om man bara skickat "admin_login" istället för "index.php?page=admin_login"
      pageParam = cleanStr;
    }

    // Returnera den absoluta säkra sökvägen till din PHP-router
    return "/ATM/public/index.php?page=" + pageParam;
  }

  // ─── Fetch ───────────────────────────────────────────────────────────────

  async function fetchPage(url) {
    const safeUrl = getCleanUrl(url);

    if (cache[safeUrl]) return cache[safeUrl];

    const res = await fetch(safeUrl);
    const text = await res.text();

    const parser = new DOMParser();
    const doc = parser.parseFromString(text, "text/html");
    const main = doc.querySelector("main");

    cache[safeUrl] = main ? main.innerHTML : doc.body.innerHTML;
    return cache[safeUrl];
  }

  // ─── Lager ───────────────────────────────────────────────────────────────

  function ensureLayer(url) {
    const safeUrl = getCleanUrl(url);
    if (layers[safeUrl]) return layers[safeUrl];

    const div = document.createElement("div");
    div.className = "page-layer";
    div.dataset.url = safeUrl;
    document.getElementById("view-container").appendChild(div);
    layers[safeUrl] = div;
    return div;
  }

  function activate(url) {
    const safeUrl = getCleanUrl(url);

    if (current && layers[current]) {
      layers[current].classList.remove("active");
    }

    if (layers[safeUrl]) {
      layers[safeUrl].classList.add("active");
      current = safeUrl;
      runScripts(layers[safeUrl]);
    }

    const backBtn = document.getElementById("back-btn");
    if (backBtn) backBtn.hidden = hist.length <= 1;
  }

  // ─── Skript ──────────────────────────────────────────────────────────────

  function runScripts(container) {
    container.querySelectorAll("script").forEach((old) => {
      if (old.dataset.ran) return;

      const s = document.createElement("script");
      [...old.attributes].forEach((a) => s.setAttribute(a.name, a.value));
      s.textContent = old.textContent;
      old.replaceWith(s);
      s.dataset.ran = "1";
    });
  }

  // ─── Länkkapning ─────────────────────────────────────────────────────────

  function hijackLinks(container) {
    container.querySelectorAll("a[href]").forEach((a) => {
      const href = a.getAttribute("href");
      if (!href || href.startsWith("http") || href.startsWith("#")) return;

      a.addEventListener("click", (e) => {
        e.preventDefault();

        const hasZoom = !!container.querySelector(".page-overlay");

        if (hasZoom) {
          triggerZoomThenNavigate(container, a, href);
        } else {
          router.navigate(href);
        }
      });
    });
  }

  function triggerZoomThenNavigate(container, anchor, href) {
    window.__routerBlocking = true;
    window.__routerTarget = null;

    anchor.dispatchEvent(
      new MouseEvent("click", {
        bubbles: true,
        cancelable: false,
      }),
    );

    setTimeout(() => {
      window.__routerBlocking = false;
      router.navigate(href);
    }, 1850);
  }

  // ─── Publikt API ─────────────────────────────────────────────────────────

  return {
    async init(startUrl) {
      window.addEventListener("popstate", (e) => {
        if (e.state?.url) this.navigate(e.state.url, false);
      });
      await this.navigate(startUrl, false);
    },

    async navigate(url, pushState = true) {
      const safeUrl = getCleanUrl(url);
      const html = await fetchPage(safeUrl);
      const layer = ensureLayer(safeUrl);

      if (!layer.dataset.loaded) {
        layer.innerHTML = html;
        layer.dataset.loaded = "1";
        hijackLinks(layer);
      }

      if (pushState) {
        // Tvinga webbläsarens adressfält att hålla sig till den publika mappen
        window.history.pushState({ url: safeUrl }, "", safeUrl);
      }
      hist.push(safeUrl);
      activate(safeUrl);
    },

    back() {
      if (hist.length <= 1) return;
      hist.pop();
      const prev = hist[hist.length - 1];
      window.history.back();
      activate(prev);
    },

    goTo(url) {
      this.navigate(url);
    },
  };
})();
