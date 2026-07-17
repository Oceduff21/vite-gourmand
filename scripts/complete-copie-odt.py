#!/usr/bin/env python3
"""Met a jour docs/Copie_a_rendre_TP_Vite_Gourmand.odt (ECF Vite & Gourmand)."""

from __future__ import annotations

import re
import shutil
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
OUTPUT = ROOT / "docs" / "Copie_a_rendre_TP_Vite_Gourmand.odt"
SOURCE_EXTERNAL = Path(
    r"C:\Users\ocean\OneDrive\Documents\Formation WEB DEVELOPPEUR FULL STACK"
    r"\ECF\Nouveau sujet\Copie à rendre_TP – Développeur Web et Web Mobile_remplie.odt"
)
DEPLOY_URL = "https://vitegourmand.infinityfree.io/"
GIT_URL = "https://github.com/Oceduff21/vite-gourmand"
TRELLO_URL = (
    "https://trello.com/invite/b/699bbc30842ff7943e2ca197/"
    "ATTIb8196f37edf8d1cf58dd8c7dada0aad15B0A20DD/tp-developpeur-web-et-web-mobile-vite-gourmand"
)


def paragraph_containing(content: str, needle: str) -> str:
    start = content.find(needle)
    if start == -1:
        raise RuntimeError(f"Needle not found: {needle}")
    p_start = content.rfind("<text:p", 0, start)
    p_end = content.find("</text:p>", start) + len("</text:p>")
    return content[p_start:p_end]


def replace_paragraph(content: str, needle: str, new_inner: str) -> str:
    old = paragraph_containing(content, needle)
    style_match = re.match(r"(<text:p[^>]*>)(.*)(</text:p>)", old, re.DOTALL)
    if not style_match:
        raise RuntimeError(f"Invalid paragraph for needle: {needle}")
    new = f"{style_match.group(1)}{new_inner}{style_match.group(3)}"
    return content.replace(old, new, 1)


def replace_paragraph_optional(content: str, needle: str, new_inner: str) -> str:
    try:
        return replace_paragraph(content, needle, new_inner)
    except RuntimeError:
        print(f"WARN: section ignoree (introuvable) : {needle[:60]}...")
        return content


def patch_content(content: str) -> str:
    # --- En-tete : deploiement + comptes demo ---
    deploy_block = (
        '<text:p text:style-name="Standard"><text:a xlink:type="simple" '
        f'xlink:href="{DEPLOY_URL}" text:style-name="Internet_20_link" '
        'text:visited-style-name="Visited_20_Internet_20_Link">'
        f"{DEPLOY_URL}</text:a></text:p>"
    )
    accounts_block = (
        '<text:p text:style-name="P26">Comptes de démonstration (production) :</text:p>'
        '<text:p text:style-name="P26">'
        '<text:span text:style-name="Strong_20_Emphasis">Administrateur</text:span>'
        ' — login : '
        '<text:a xlink:type="simple" xlink:href="mailto:jose@vite-gourmand.fr" '
        'text:style-name="Internet_20_link" text:visited-style-name="Visited_20_Internet_20_Link">'
        'jose@vite-gourmand.fr</text:a> / mot de passe : Admin123!'
        '</text:p>'
        '<text:p text:style-name="P26">'
        '<text:span text:style-name="Strong_20_Emphasis">Employée</text:span>'
        ' — login : '
        '<text:a xlink:type="simple" xlink:href="mailto:julie@vite-gourmand.fr" '
        'text:style-name="Internet_20_link" text:visited-style-name="Visited_20_Internet_20_Link">'
        'julie@vite-gourmand.fr</text:a> / mot de passe : Employe123! '
        '(connexion via /admin/login.php)'
        '</text:p>'
        '<text:p text:style-name="P26">'
        '<text:span text:style-name="Strong_20_Emphasis">Client</text:span>'
        ' — login : '
        '<text:a xlink:type="simple" xlink:href="mailto:client@vite-gourmand.fr" '
        'text:style-name="Internet_20_link" text:visited-style-name="Visited_20_Internet_20_Link">'
        'client@vite-gourmand.fr</text:a> / mot de passe : Client123! '
        '(connexion via /login.php)'
        '</text:p>'
        '<text:p text:style-name="P26">Pages clés : accueil, /menus.php (filtres thème/régime/recherche), '
        f'/menu.php, /login.php, /admin/login.php, /accessibilite.php — {DEPLOY_URL}'
        '</text:p>'
    )

    old_admin = paragraph_containing(content, "Login et mot de passe administrateur")
    content = content.replace(
        old_admin,
        accounts_block,
        1,
    )

    # Assurer le lien de deploiement (si deja present, ne pas dupliquer)
    if DEPLOY_URL not in content.split("Comptes de démonstration")[0]:
        content = content.replace(
            '<text:p text:style-name="P26">Login et mot de passe administrateur',
            deploy_block + accounts_block.replace(
                '<text:p text:style-name="P26">Comptes de démonstration',
                '<text:p text:style-name="P26">Comptes de démonstration',
                1,
            ),
            1,
        )

    # --- Partie 1 : filtres menus ---
    content = replace_paragraph_optional(
        content,
        "filtrer les offres selon plusieurs critères",
        "filtrer les offres selon plusieurs critères combinables (recherche textuelle, thème, régime alimentaire, "
        "budget, nombre de personnes) avec une recherche intelligente croisant titre, description et métadonnées",
    )

    # --- Partie 1 : accessibilite ---
    content = replace_paragraph_optional(
        content,
        "Enfin, le projet devra respecter les bonnes pratiques",
        "Enfin, le projet respecte les bonnes pratiques du développement web : accessibilité RGAA "
        "(page dédiée accessibilite.php, labels de formulaires, tables structurées, navigation clavier, "
        "audit WAVE sur les pages clés — score AIM ~8,8/10), sécurité (CSRF, PDO, bcrypt) et conformité RGPD "
        "(politique de confidentialité, cookies, droits utilisateur)",
    )

    # --- Cahier des charges : admin etendu ---
    content = replace_paragraph_optional(
        content,
        "administration (gestion des menus)",
        "Interface d’administration (gestion menus, commandes, avis, utilisateurs ; rôles admin et employé ; "
        "dashboard statistiques MySQL en production, MongoDB en local)",
    )

    # --- Stack technique ---
    content = replace_paragraph_optional(
        content,
        "Le backend est développé en",
        "Le backend est développé en "
        '<text:span text:style-name="Strong_20_Emphasis">PHP 8.3</text:span> '
        "avec PDO et sessions. La base relationnelle "
        '<text:span text:style-name="Strong_20_Emphasis">MySQL</text:span> '
        "(InfinityFree) stocke le catalogue, les commandes et les utilisateurs. "
        '<text:span text:style-name="Strong_20_Emphasis">MongoDB</text:span> '
        "alimente les statistiques NoSQL en local (XAMPP/Docker) ; le dashboard admin "
        "bascule sur MySQL en production. L'application est déployée sur "
        '<text:span text:style-name="Strong_20_Emphasis">InfinityFree</text:span> '
        f"({DEPLOY_URL}). Le front utilise Bootstrap 5.3, Chart.js, JavaScript (wizard menu, filtres AJAX) "
        "et HTML sémantique. Dépôt GitHub : "
        f'<text:a xlink:type="simple" xlink:href="{GIT_URL}" text:style-name="Internet_20_link" '
        f'text:visited-style-name="Visited_20_Internet_20_Link">{GIT_URL}</text:a>.',
    )

    # --- Environnement ---
    content = replace_paragraph_optional(
        content,
        "Le développement local repose sur",
        "Le développement local repose sur "
        '<text:span text:style-name="Strong_20_Emphasis">XAMPP</text:span> '
        "(Apache, PHP 8.3, MySQL). L'éditeur Cursor / Visual Studio Code est utilisé avec Git. "
        "L'architecture suit une organisation claire : pages PHP (site public et /admin/), "
        "dossier /includes/ (db.php, auth.php, helpers.php, a11y-helpers.php), assets CSS/JS et migrations SQL "
        "versionnées dans /database/. Le script scripts/fix-php-encoding.ps1 garantit l'encodage UTF-8 des fichiers PHP "
        "avant déploiement sur InfinityFree. Le README.md du dépôt documente l'installation.",
    )

    # --- Securite front + RGAA ---
    content = replace_paragraph_optional(
        content,
        "Côté front-end, des contrôles de validation HTML5",
        "Côté front-end, des contrôles de validation HTML5 et JavaScript sont en place (wizard menu, "
        "formulaires inscription/commande). Les messages d'erreur sont explicites et les champs invalides "
        "reçoivent le focus clavier. Une page accessibilite.php décrit les mesures RGAA. Les données affichées "
        "passent par htmlspecialchars() pour limiter les XSS. Audit WAVE réalisé (juillet 2026) : 0 erreur "
        "structurelle sur les pages clés ; quelques alertes de contraste en cours de correction.",
    )

    # --- Securite back ---
    content = replace_paragraph_optional(
        content,
        "Côté back-end, la sécurité est implémentée",
        "Côté back-end, la sécurité est implémentée : requêtes préparées PDO (anti-injection SQL), tokens CSRF "
        "sur inscription, commande et actions admin, hashage bcrypt (password_hash), gestion des rôles "
        "(utilisateur, employé, admin) via auth.php, sessions séparées site public / admin, validation serveur "
        "des délais de livraison et règles métier (réduction 10 %, prix livraison, stock menus).",
    )

    # --- Contraintes : RGAA explicite ---
    content = replace_paragraph_optional(
        content,
        "Interface claire et accessible",
        "Interface claire et accessible (RGAA : labels, contrastes, navigation clavier, page accessibilite.php)",
    )

    # --- Livrables PDF ---
    content = replace_paragraph_optional(
        content,
        "Enfin, des ressources en ligne telles que",
        "Les livrables PDF joints au dossier sont : Manuel utilisateur (docs/MANUEL_UTILISATEUR.pdf), "
        "Charte graphique (docs/CHARTE_GRAPHIQUE.pdf) et Documentation technique "
        "(docs/DOCUMENTATION_TECHNIQUE.pdf). Des ressources en ligne telles que MDN Web Docs",
    )

    # --- Difficultes : deploiement ---
    content = replace_paragraph_optional(
        content,
        "La principale difficulté rencontrée lors de ce projet",
        "Les principales difficultés rencontrées ont concerné le déploiement sur hébergement mutualisé "
        "(InfinityFree) : encodage UTF-8 obligatoire des fichiers PHP, limites de taille en upload, "
        "absence de MongoDB en production (fallback MySQL). L'adaptation responsive et l'accessibilité RGAA "
        "ont également demandé plusieurs itérations (audit WAVE, corrections de contrastes et structure HTML).",
    )

    # --- Competences ---
    content = replace_paragraph_optional(
        content,
        "Ce projet m",
        "Ce projet m'a permis de mieux comprendre les enjeux liés à la sécurité, à l'accessibilité numérique "
        "(RGAA/WAVE), au déploiement PHP/MySQL et à la conception d'une application web répondant à des "
        "besoins concrets d'un traiteur événementiel.",
    )

    return content


def patch_odt(source: Path, destination: Path) -> None:
    if not source.exists():
        raise FileNotFoundError(f"ODT source introuvable : {source}")

    destination.parent.mkdir(parents=True, exist_ok=True)
    temp_out = destination.with_suffix(".tmp.odt")

    with zipfile.ZipFile(source, "r") as zin:
        content = zin.read("content.xml").decode("utf-8")
        content = patch_content(content)
        other_files = {name: zin.read(name) for name in zin.namelist() if name != "content.xml"}

    with zipfile.ZipFile(temp_out, "w", zipfile.ZIP_DEFLATED) as zout:
        for name, data in other_files.items():
            zout.writestr(name, data)
        zout.writestr("content.xml", content.encode("utf-8"))

    temp_out.replace(destination)


def main() -> int:
    source = OUTPUT if OUTPUT.exists() else SOURCE_EXTERNAL
    if not source.exists():
        raise FileNotFoundError("Aucun fichier ODT source trouve.")

    patch_odt(source, OUTPUT)
    print(f"OK ODT mis a jour : {OUTPUT}")

    if SOURCE_EXTERNAL.exists() and SOURCE_EXTERNAL != OUTPUT:
        shutil.copy2(OUTPUT, SOURCE_EXTERNAL)
        print(f"OK Copie OneDrive mise a jour : {SOURCE_EXTERNAL}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
