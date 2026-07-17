#!/usr/bin/env python3
"""Complete the ECF copy ODT with deployment URL and corrected technical sections."""

from __future__ import annotations

import re
import shutil
import zipfile
from pathlib import Path

SOURCE = Path(
    r"C:\Users\ocean\OneDrive\Documents\Formation WEB DEVELOPPEUR FULL STACK"
    r"\ECF\Nouveau sujet\Copie à rendre_TP – Développeur Web et Web Mobile_remplie.odt"
)
ROOT = Path(__file__).resolve().parent.parent
OUTPUT = ROOT / "docs" / "Copie_a_rendre_TP_Vite_Gourmand.odt"
DEPLOY_URL = "https://vitegourmand.infinityfree.io/"


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


def replace_between(content: str, start_needle: str, end_needle: str, replacement: str) -> str:
    start = content.find(start_needle)
    end = content.find(end_needle, start)
    if start == -1 or end == -1:
        raise RuntimeError(f"Markers not found: {start_needle!r} / {end_needle!r}")
    return content[:start] + replacement + content[end:]


def patch_content(content: str) -> str:
    # 1. Lien de deploiement
    deploy_block = (
        '<text:p text:style-name="Standard"><text:a xlink:type="simple" '
        f'xlink:href="{DEPLOY_URL}" text:style-name="Internet_20_link" '
        'text:visited-style-name="Visited_20_Internet_20_Link">'
        f"{DEPLOY_URL}</text:a></text:p>"
    )
    content = content.replace(
        '<text:p text:style-name="Standard"><text:span text:style-name="T3"/></text:p>'
        '<text:p text:style-name="P26">Login et mot de passe administrateur',
        deploy_block + '<text:p text:style-name="P26">Login et mot de passe administrateur',
        1,
    )

    # 2. Stack / deploiement
    content = replace_paragraph(
        content,
        "Netlify",
        "Le backend est développé en "
        '<text:span text:style-name="Strong_20_Emphasis">PHP 8.3</text:span> '
        "avec PDO et sessions. La base relationnelle "
        '<text:span text:style-name="Strong_20_Emphasis">MySQL</text:span> '
        "(InfinityFree) stocke le catalogue, les commandes et les utilisateurs. "
        '<text:span text:style-name="Strong_20_Emphasis">MongoDB</text:span> '
        "alimente les statistiques NoSQL en local (XAMPP/Docker) ; le dashboard admin "
        "bascule sur MySQL en production. L'application est déployée sur "
        '<text:span text:style-name="Strong_20_Emphasis">InfinityFree</text:span> '
        f"({DEPLOY_URL}). Le front utilise Bootstrap 5, JavaScript (wizard menu, filtres AJAX) "
        "et HTML sémantique.",
    )

    # 3. Environnement de travail
    content = replace_paragraph(
        content,
        "Live Server",
        "Le développement local repose sur "
        '<text:span text:style-name="Strong_20_Emphasis">XAMPP</text:span> '
        "(Apache, PHP 8.3, MySQL). L'éditeur Visual Studio Code / Cursor est utilisé avec Git. "
        "L'architecture suit une organisation claire : pages PHP (site public et /admin/), "
        "dossier /includes/ (db.php, auth.php, helpers.php), assets CSS/JS et migrations SQL "
        "versionnées dans /database/. Le fichier README.md du dépôt GitHub documente "
        "l'installation et le déploiement.",
    )

    # 4. Securite front-end
    old_sec_fe = paragraph_containing(content, "sont prévus afin de limiter les erreurs de saisie")
    new_sec_fe = (
        '<text:p text:style-name="Text_20_body">Côté front-end, des contrôles de validation HTML5 '
        "et JavaScript sont en place (wizard menu, formulaires inscription/commande). "
        "Les messages d'erreur sont explicites et les champs invalides reçoivent le focus clavier "
        "(accessibilité RGAA). Les données affichées passent par htmlspecialchars() pour limiter les XSS."
        "</text:p>"
    )
    content = content.replace(old_sec_fe, new_sec_fe, 1)

    # 5. Securite back-end
    old_sec_be = paragraph_containing(content, "le projet prévoit une gestion des rôles utilisateurs")
    new_sec_be = (
        '<text:p text:style-name="Text_20_body">Côté back-end, la sécurité est implémentée : '
        "requêtes préparées PDO (anti-injection SQL), tokens CSRF sur inscription, commande et actions admin, "
        "hashage bcrypt (password_hash), gestion des rôles (utilisateur, employé, admin) via auth.php, "
        "sessions séparées site public / admin, validation serveur des délais de livraison et règles métier "
        "(réduction 10 %, prix livraison)."
        "</text:p>"
    )
    content = content.replace(old_sec_be, new_sec_be, 1)

    # 6. Ameliorations futures
    content = replace_paragraph(
        content,
        "système de paiement en ligne afin de finaliser les commandes",
        "Plusieurs améliorations pourraient être envisagées : intégration d'un "
        '<text:span text:style-name="Strong_20_Emphasis">paiement en ligne sécurisé</text:span> '
        "(Stripe/PayPal), développement d'une "
        '<text:span text:style-name="Strong_20_Emphasis">PWA</text:span> ou application mobile native '
        "pour les clients réguliers, et synchronisation calendrier (Google Calendar) pour la disponibilité "
        "du traiteur.",
    )

    content = replace_paragraph(
        content,
        "confirmations par email",
        "D'autres pistes : module de "
        '<text:span text:style-name="Strong_20_Emphasis">facturation B2B</text:span> '
        "automatisée pour les séminaires entreprise, export comptable des commandes, et audit RGAA complet "
        "avec tests utilisateurs en situation réelle.",
    )

    return content


def patch_odt(source: Path, destination: Path) -> None:
    if not source.exists():
        raise FileNotFoundError(f"ODT source introuvable : {source}")

    destination.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(source, destination)

    with zipfile.ZipFile(destination, "r") as zin:
        content = zin.read("content.xml").decode("utf-8")
        content = patch_content(content)
        other_files = {name: zin.read(name) for name in zin.namelist() if name != "content.xml"}

    with zipfile.ZipFile(destination, "w", zipfile.ZIP_DEFLATED) as zout:
        for name, data in other_files.items():
            zout.writestr(name, data)
        zout.writestr("content.xml", content.encode("utf-8"))

    shutil.copy2(destination, source)


def main() -> int:
    patch_odt(SOURCE, OUTPUT)
    print(f"OK ODT complete : {OUTPUT}")
    print(f"OK Copie source mise a jour : {SOURCE}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
