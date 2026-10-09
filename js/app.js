/* Opunga Cyber and Electronics - shared behaviour */
(function () {
    "use strict";

    var STORAGE_KEY = "opunga-theme";

    function currentFile() {
        var parts = window.location.pathname.split("/");
        return parts[parts.length - 1] || "index.php";
    }

    function readTheme() {
        try {
            return window.localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function saveTheme(value) {
        try {
            window.localStorage.setItem(STORAGE_KEY, value);
        } catch (e) {
            /* private mode - theme just won't persist */
        }
    }

    // Applied as early as possible so there is no light-mode flash.
    if (readTheme() === "dark") {
        document.documentElement.classList.add("dark");
    }

    window.toggleDarkMode = function () {
        var root = document.documentElement;
        var dark = root.classList.toggle("dark");
        saveTheme(dark ? "dark" : "light");
        syncToggleLabels(dark);
    };

    function syncToggleLabels(dark) {
        var buttons = document.querySelectorAll("[data-theme-toggle]");
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].setAttribute("aria-pressed", dark ? "true" : "false");
            var label = buttons[i].getAttribute("data-label-light");
            if (label) {
                buttons[i].innerHTML = dark ? label : buttons[i].getAttribute("data-label-dark");
            }
        }
    }

    function markActiveLinks() {
        var here = currentFile();
        var links = document.querySelectorAll(".nav-list a, .sidebar a");

        for (var i = 0; i < links.length; i++) {
            var href = links[i].getAttribute("href");
            if (!href) continue;

            var target = href.split("?")[0].split("#")[0];
            if (target === here) {
                links[i].classList.add("active");
                links[i].setAttribute("aria-current", "page");
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        var buttons = document.querySelectorAll("[data-theme-toggle]");

        for (var i = 0; i < buttons.length; i++) {
            buttons[i].addEventListener("click", function () {
                window.toggleDarkMode();
            });
        }

        syncToggleLabels(document.documentElement.classList.contains("dark"));
        markActiveLinks();
    });
})();
