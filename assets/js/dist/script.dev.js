"use strict";

var theme = document.getElementById("filtre-theme");
var regime = document.getElementById("filtre-regime");
var prix = document.getElementById("filtre-prix");
var menus = document.querySelectorAll(".menu-card");

function filtrerMenus() {
  menus.forEach(function (menu) {
    var themeMenu = menu.dataset.theme;
    var regimeMenu = menu.dataset.regime;
    var prixMenu = parseFloat(menu.dataset.prix);
    var visible = true;

    if (theme.value && theme.value !== themeMenu) {
      visible = false;
    }

    if (regime.value && regime.value !== regimeMenu) {
      visible = false;
    }

    if (prix.value && prixMenu > prix.value) {
      visible = false;
    }

    menu.style.display = visible ? "block" : "none";
  });
}

theme.addEventListener("change", filtrerMenus);
regime.addEventListener("change", filtrerMenus);
prix.addEventListener("input", filtrerMenus);
//# sourceMappingURL=script.dev.js.map
