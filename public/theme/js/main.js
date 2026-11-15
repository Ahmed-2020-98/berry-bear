/* =========================================================
   Berry Bear — Sour Gummy | main.js
   ========================================================= */
(function () {
  "use strict";

  /* ---------- Reusable gummy-bear SVG sprite ----------
     Used anywhere via: <svg class="bear-big"><use href="#gummy"></use></svg>
     Body parts use currentColor so the parent's `color` tints the bear. */
  function injectSprite() {
    if (document.getElementById("bb-sprite")) return;
    var svgns = "http://www.w3.org/2000/svg";
    var wrap = document.createElement("div");
    wrap.id = "bb-sprite";
    wrap.style.cssText = "position:absolute;width:0;height:0;overflow:hidden";
    wrap.setAttribute("aria-hidden", "true");
    wrap.innerHTML =
      '<svg xmlns="' + svgns + '"><symbol id="gummy" viewBox="0 0 100 122">' +
        '<g fill="currentColor">' +
          '<ellipse cx="27" cy="17" rx="12" ry="13"/>' +
          '<ellipse cx="73" cy="17" rx="12" ry="13"/>' +
          '<circle cx="50" cy="35" r="27"/>' +
          '<rect x="23" y="54" width="54" height="54" rx="26"/>' +
          '<ellipse cx="18" cy="68" rx="12" ry="16"/>' +
          '<ellipse cx="82" cy="68" rx="12" ry="16"/>' +
          '<ellipse cx="34" cy="108" rx="14" ry="13"/>' +
          '<ellipse cx="66" cy="108" rx="14" ry="13"/>' +
        '</g>' +
        '<ellipse cx="50" cy="78" rx="17" ry="21" fill="#ffffff" opacity="0.22"/>' +
        '<circle cx="50" cy="72" r="6" fill="#ffffff" opacity="0.45"/>' +
        '<circle cx="41" cy="33" r="3.4" fill="#1C1552" opacity="0.85"/>' +
        '<circle cx="59" cy="33" r="3.4" fill="#1C1552" opacity="0.85"/>' +
        '<ellipse cx="50" cy="42" rx="4" ry="3" fill="#1C1552" opacity="0.55"/>' +
        '<ellipse cx="37" cy="24" rx="7" ry="9" fill="#ffffff" opacity="0.4"/>' +
      '</symbol></svg>';
    document.body.insertBefore(wrap, document.body.firstChild);
  }

  /* ---------- Header scroll state ---------- */
  function initHeader() {
    var header = document.querySelector(".site-header");
    if (!header) return;
    var onScroll = function () { header.classList.toggle("scrolled", window.scrollY > 20); };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  /* ---------- Mobile nav + dropdown ---------- */
  function initNav() {
    var toggle = document.querySelector(".nav-toggle");
    var menu = document.querySelector(".nav-menu");
    if (toggle && menu) {
      toggle.addEventListener("click", function () {
        toggle.classList.toggle("active");
        menu.classList.toggle("open");
        document.body.style.overflow = menu.classList.contains("open") ? "hidden" : "";
      });
      menu.querySelectorAll("a").forEach(function (a) {
        a.addEventListener("click", function () {
          if (a.getAttribute("href") && a.getAttribute("href").charAt(0) !== "#") {
            toggle.classList.remove("active");
            menu.classList.remove("open");
            document.body.style.overflow = "";
          }
        });
      });
    }
    // dropdown (mobile tap / accessible toggle)
    document.querySelectorAll(".has-drop > button").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        btn.parentElement.classList.toggle("open");
      });
    });
  }

  /* ---------- Reveal on scroll ---------- */
  function initReveal() {
    var els = document.querySelectorAll(".reveal");
    if (!("IntersectionObserver" in window) || !els.length) {
      els.forEach(function (el) { el.classList.add("in"); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add("in"); io.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -40px 0px" });
    els.forEach(function (el) { io.observe(el); });
  }

  /* ---------- Count-up stats ---------- */
  function initCounters() {
    var nums = document.querySelectorAll("[data-count]");
    if (!nums.length) return;
    var run = function (el) {
      var target = parseFloat(el.getAttribute("data-count"));
      var suffix = el.getAttribute("data-suffix") || "";
      var dur = 1600, start = performance.now();
      var step = function (now) {
        var p = Math.min((now - start) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        var val = target * eased;
        el.textContent = (target % 1 === 0 ? Math.round(val) : val.toFixed(1)) + suffix;
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { run(en.target); io.unobserve(en.target); }
      });
    }, { threshold: 0.5 });
    nums.forEach(function (n) { io.observe(n); });
  }

  /* ---------- FAQ accordion ---------- */
  function initFaq() {
    document.querySelectorAll(".faq-q").forEach(function (q) {
      q.addEventListener("click", function () {
        var item = q.parentElement;
        var ans = item.querySelector(".faq-a");
        var isOpen = item.classList.contains("open");
        // close all
        document.querySelectorAll(".faq-item.open").forEach(function (it) {
          it.classList.remove("open");
          it.querySelector(".faq-a").style.maxHeight = null;
        });
        if (!isOpen) {
          item.classList.add("open");
          ans.style.maxHeight = ans.scrollHeight + "px";
        }
      });
    });
  }

  /* ---------- Scroll to top ---------- */
  function initToTop() {
    var btn = document.querySelector(".to-top");
    if (!btn) return;
    window.addEventListener("scroll", function () {
      btn.classList.toggle("show", window.scrollY > 500);
    }, { passive: true });
    btn.addEventListener("click", function () { window.scrollTo({ top: 0, behavior: "smooth" }); });
  }

  /* ---------- Gallery lightbox ---------- */
  function initLightbox() {
    var boxes = document.querySelectorAll("[data-lightbox]");
    if (!boxes.length) return;
    var lb = document.createElement("div");
    lb.className = "lightbox";
    lb.innerHTML =
      '<div class="lb-inner"><button class="lb-close" aria-label="إغلاق">&times;</button>' +
      '<img class="lb-img" alt="" style="display:none">' +
      '<svg class="bear-big" style="display:none"><use href="#gummy"></use></svg>' +
      '<h3 class="lb-title"></h3><p class="lb-cap"></p></div>';
    document.body.appendChild(lb);
    var img = lb.querySelector(".lb-img");
    var bear = lb.querySelector(".bear-big");
    var title = lb.querySelector(".lb-title");
    var cap = lb.querySelector(".lb-cap");
    var close = function () { lb.classList.remove("show"); document.body.style.overflow = ""; };
    boxes.forEach(function (b) {
      b.addEventListener("click", function () {
        var src = b.getAttribute("data-img");
        if (src) {
          img.src = src; img.style.display = "block"; bear.style.display = "none";
        } else {
          bear.style.color = b.getAttribute("data-color") || "#E51B6E";
          bear.style.display = "block"; img.style.display = "none";
        }
        title.textContent = b.getAttribute("data-title") || "";
        cap.textContent = b.getAttribute("data-cap") || "";
        lb.classList.add("show");
        document.body.style.overflow = "hidden";
      });
    });
    lb.addEventListener("click", function (e) { if (e.target === lb || e.target.classList.contains("lb-close")) close(); });
    document.addEventListener("keydown", function (e) { if (e.key === "Escape") close(); });
  }

  /* ---------- Contact form (demo, no backend) ---------- */
  function initForm() {
    var form = document.getElementById("contact-form");
    if (!form) return;
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var ok = form.querySelector(".form-success");
      if (ok) ok.classList.add("show");
      form.reset();
      setTimeout(function () { if (ok) ok.classList.remove("show"); }, 6000);
    });
  }

  /* ---------- Footer year ---------- */
  function initYear() {
    var y = document.getElementById("year");
    if (y) y.textContent = new Date().getFullYear();
  }

  /* ---------- Boot ---------- */
  document.addEventListener("DOMContentLoaded", function () {
    injectSprite();
    initHeader();
    initNav();
    initReveal();
    initCounters();
    initFaq();
    initToTop();
    initLightbox();
    initForm();
    initYear();
  });

  /* ---------- Preloader ---------- */
  function hidePreloader() {
    var pre = document.getElementById("preloader");
    if (pre) { pre.classList.add("hide"); setTimeout(function () { pre.style.display = "none"; }, 650); }
  }

  function prefersReducedMotion() {
    return !!(window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches);
  }

  /* ---------- Preloader: reduced-motion fallback (today's original behavior, unchanged) ----------
     If the loader is a video, keep the preloader visible for the video's full
     duration (play once, then hide) so the loader length == the video length.
     Otherwise, hide on window load with a safety fallback. */
  function legacyAutoHide() {
    var pre = document.getElementById("preloader");
    if (!pre) return;
    var vid = pre.querySelector("video.pre-media");

    if (vid) {
      vid.loop = false; // play once → loader duration matches the video
      var done = false;
      var finish = function () { if (!done) { done = true; hidePreloader(); } };

      vid.addEventListener("ended", finish);
      vid.addEventListener("error", finish);
      vid.addEventListener("loadedmetadata", function () {
        if (isFinite(vid.duration) && vid.duration > 0) {
          setTimeout(finish, (vid.duration * 1000) + 300);
        }
      });

      var play = vid.play && vid.play();
      if (play && typeof play.catch === "function") { play.catch(function () {}); }

      setTimeout(finish, 60000); // ultimate safety net (video failed to load)
      return;
    }

    window.addEventListener("load", hidePreloader);
    setTimeout(hidePreloader, 2600); // fallback for logo / image / GIF loaders
  }

  /* ---------- Preloader: scroll-driven intro (native scroll — no scroll-jacking) ----------
     The preloader is the page's opening "curtain": it fully covers the viewport,
     and the REAL native page scroll drives it upward. We never call preventDefault
     and never lock overflow, so the exact wheel / trackpad / touch gesture that
     lifts the curtain flows straight on into normal page scrolling — there is no
     lock to "hand back", so the page can never get stuck. A one-viewport spacer at
     the top of <body> supplies the reveal distance; the instant the curtain is
     fully lifted we "commit" (remove the spacer + curtain and re-anchor the scroll)
     so the document becomes exactly as if the preloader never existed, and
     scrolling back up never re-shows it. */
  function initScrollDrivenPreloader() {
    var pre = document.getElementById("preloader");
    if (!pre) { return; }

    // Deep link to an on-page section (e.g. /products/#sour): skip the intro so we
    // don't fight the browser's native anchor jump; just fade out as usual.
    if (location.hash) {
      try {
        if (document.getElementById(decodeURIComponent(location.hash.slice(1)))) { legacyAutoHide(); return; }
      } catch (e) { /* malformed hash — ignore and continue with the intro */ }
    }

    var vid = pre.querySelector("video.pre-media");
    if (vid) {
      vid.loop = true; // loop while the curtain is up
      var playPromise = vid.play && vid.play();
      if (playPromise && typeof playPromise.catch === "function") { playPromise.catch(function () {}); }
    }

    var committed = false;
    var ticking = false;
    var spacer = null;
    var H = 0; // reveal distance / commit threshold (px)

    // Programmatic scroll that ignores the theme's html{scroll-behavior:smooth},
    // so our resets/re-anchor jump instantly instead of gliding.
    function scrollToInstant(x, y) {
      var de = document.documentElement, prev = de.style.scrollBehavior;
      de.style.scrollBehavior = "auto";
      window.scrollTo(x, y);
      de.style.scrollBehavior = prev;
    }

    // Sets H (= one viewport) and returns the spacer height needed so scrollY can
    // always reach H — grown on short pages so the reveal can never dead-lock.
    function computeSizes() {
      var V = window.innerHeight;
      var base = document.documentElement.scrollHeight;
      if (spacer) { base -= spacer.offsetHeight; } // exclude our own spacer when recomputing
      H = V;
      return Math.max(H, (2 * V) - base); // maxScrollY = spacerH + base - V  >=  H
    }

    function paint() {
      ticking = false;
      if (committed) { return; }
      var p = Math.min(1, Math.max(0, window.scrollY / H));
      var pct = p >= 1 ? -101 : -p * 100;
      pre.style.transform = "translate3d(0," + pct + "%,0)";
      if (p >= 1) { commit(); }
    }

    function onScroll() {
      if (!ticking) { ticking = true; requestAnimationFrame(paint); }
    }

    // Only recompute on orientationchange — NOT on plain resize — so mobile
    // address-bar show/hide doesn't jitter the spacer height mid-reveal.
    function onOrient() {
      if (committed || !spacer) { return; }
      spacer.style.height = computeSizes() + "px";
    }

    function onPageShow(e) {
      // bfcache restore can re-apply a large scrollY after init — pin back to top.
      if (e.persisted && !committed) { scrollToInstant(0, 0); paint(); }
    }

    function commit() {
      if (committed) { return; }
      committed = true;
      window.removeEventListener("scroll", onScroll);
      window.removeEventListener("orientationchange", onOrient);
      window.removeEventListener("pageshow", onPageShow);
      pre.style.pointerEvents = "none";
      pre.setAttribute("aria-hidden", "true");
      if (vid) { try { vid.pause(); } catch (e) {} }
      // Atomic, jump-free hand-off: remove the spacer and subtract its height from
      // the scroll position in the SAME frame, so the on-screen position — and the
      // in-flight wheel/touch momentum — is undisturbed and simply continues.
      var y = window.scrollY;
      if (spacer && spacer.parentNode) { spacer.parentNode.removeChild(spacer); }
      scrollToInstant(0, Math.max(0, y - H));
      pre.style.display = "none";
      if ("scrollRestoration" in history) { try { history.scrollRestoration = "auto"; } catch (e) {} }
    }

    // Start at the very top with the curtain covering the viewport.
    if ("scrollRestoration" in history) { try { history.scrollRestoration = "manual"; } catch (e) {} }
    scrollToInstant(0, 0);

    var spacerH = computeSizes();
    spacer = document.createElement("div");
    spacer.id = "pre-spacer";
    spacer.setAttribute("aria-hidden", "true");
    spacer.style.cssText = "width:100%;flex:none;margin:0;padding:0;pointer-events:none;height:" + spacerH + "px;";
    document.body.insertBefore(spacer, document.body.firstChild);

    pre.style.transform = "translate3d(0,0,0)"; // warm the compositor layer at rest

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("orientationchange", onOrient);
    window.addEventListener("pageshow", onPageShow);
  }

  /* ---------- Product flip cards ----------
     Mouse users flip on hover (pure CSS). Touch users tap to flip; tapping
     another card flips the previous one back. Enter/Space flips for keyboard. */
  function initFlipCards() {
    var cards = document.querySelectorAll(".pcard--flip");
    if (!cards.length) return;
    var canHover = window.matchMedia && window.matchMedia("(hover: hover) and (pointer: fine)").matches;

    function toggle(card) {
      var open = !card.classList.contains("is-flipped");
      for (var i = 0; i < cards.length; i++) cards[i].classList.remove("is-flipped");
      if (open) card.classList.add("is-flipped");
    }

    Array.prototype.forEach.call(cards, function (card) {
      card.addEventListener("click", function (e) {
        if (canHover || e.target.closest("a")) return;
        toggle(card);
      });
      card.addEventListener("keydown", function (e) {
        if (e.target !== card || (e.key !== "Enter" && e.key !== " ")) return;
        e.preventDefault();
        toggle(card);
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initFlipCards);
  } else {
    initFlipCards();
  }

  function bootPreloader() {
    if (prefersReducedMotion()) { legacyAutoHide(); return; }
    initScrollDrivenPreloader();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootPreloader);
  } else {
    bootPreloader();
  }
})();
