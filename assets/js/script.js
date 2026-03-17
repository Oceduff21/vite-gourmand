const theme = document.getElementById("filtre-theme");
const regime = document.getElementById("filtre-regime");
const prix = document.getElementById("filtre-prix");

const menus = document.querySelectorAll(".menu-card");

function filtrerMenus(){

menus.forEach(menu => {

const themeMenu = menu.dataset.theme;
const regimeMenu = menu.dataset.regime;
const prixMenu = parseFloat(menu.dataset.prix);

let visible = true;

if(theme.value && theme.value !== themeMenu){
visible = false;
}

if(regime.value && regime.value !== regimeMenu){
visible = false;
}

if(prix.value && prixMenu > prix.value){
visible = false;
}

menu.style.display = visible ? "block" : "none";

});

}

theme.addEventListener("change", filtrerMenus);
regime.addEventListener("change", filtrerMenus);
prix.addEventListener("input", filtrerMenus);