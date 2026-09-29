(function () {
  var root = document.documentElement, KEY = "portfolio-theme";

  // 1. Theme preference saved in localStorage
  var saved = null;
  try { saved = localStorage.getItem(KEY); } catch (e) {}
  var theme = saved || (window.matchMedia && matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
  root.setAttribute("data-theme", theme);

  document.addEventListener("DOMContentLoaded", function () {
    // 2. Dark / light mode toggle
    var tbtn = document.getElementById("themeToggle");
    function paint() {
      var dark = root.getAttribute("data-theme") === "dark";
      tbtn.textContent = dark ? "☀️" : "🌙";
      tbtn.setAttribute("aria-label", "Switch to " + (dark ? "light" : "dark") + " mode");
    }
    paint();
    tbtn.addEventListener("click", function () {
      var t = root.getAttribute("data-theme") === "dark" ? "light" : "dark";
      root.setAttribute("data-theme", t);
      try { localStorage.setItem(KEY, t); } catch (e) {}
      paint();
    });

    // 3. Mobile navigation
    var menu = document.getElementById("menuBtn"), links = document.getElementById("navLinks");
    menu.addEventListener("click", function () {
      var open = links.classList.toggle("open");
      menu.setAttribute("aria-expanded", open);
    });
    links.addEventListener("click", function (e) {
      if (e.target.tagName === "A") { links.classList.remove("open"); menu.setAttribute("aria-expanded", "false"); }
    });

    // 4. Active navigation link
    var page = location.pathname.split("/").pop() || "index.html";
    links.querySelectorAll("a").forEach(function (a) {
      if (a.getAttribute("href") === page) { a.classList.add("active"); a.setAttribute("aria-current", "page"); }
    });

    // 5. Scroll-to-top button
    var topBtn = document.getElementById("scrollTop");
    window.addEventListener("scroll", function () { topBtn.classList.toggle("show", window.scrollY > 300); });
    topBtn.addEventListener("click", function () { window.scrollTo({ top: 0, behavior: "smooth" }); });

    // 6. Project filtering
    var fbs = document.querySelectorAll(".filter-btn");
    fbs.forEach(function (b) {
      b.addEventListener("click", function () {
        fbs.forEach(function (x) { x.classList.remove("active"); x.setAttribute("aria-pressed", "false"); });
        b.classList.add("active"); b.setAttribute("aria-pressed", "true");
        var f = b.dataset.filter;
        document.querySelectorAll(".project").forEach(function (p) {
          p.hidden = !(f === "all" || p.dataset.category === f);
        });
      });
    });

    // 7. Contact form validation
    var form = document.getElementById("contactForm");
    if (form) {
      var rules = {
        name: function (v) { return v.trim().length >= 2 || "Enter your name (at least 2 characters)."; },
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) || "Enter a valid email address, like name@example.com."; },
        message: function (v) { return v.trim().length >= 10 || "Write a message of at least 10 characters."; }
      };
      var check = function (el) {
        var r = rules[el.name](el.value), ok = r === true;
        el.classList.toggle("invalid", !ok);
        el.setAttribute("aria-invalid", !ok);
        document.getElementById(el.name + "Error").textContent = ok ? "" : r;
        return ok;
      };
      var fields = form.querySelectorAll("input,textarea");
      fields.forEach(function (el) {
        el.addEventListener("blur", function () { check(el); });
        el.addEventListener("input", function () { if (el.classList.contains("invalid")) check(el); });
      });
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        var all = true;
        fields.forEach(function (el) { if (!check(el)) all = false; });
        var s = document.getElementById("formSuccess");
        if (all) { form.reset(); s.classList.add("show"); } else { s.classList.remove("show"); }
      });
    }
  });
})();
