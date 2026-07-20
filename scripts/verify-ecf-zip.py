#!/usr/bin/env python3
"""Verify ECF jury ZIP contents."""

from __future__ import annotations

import io
import re
import sys
import zipfile
from pathlib import Path

try:
    from pypdf import PdfReader
except ImportError:
    import subprocess

    subprocess.check_call([sys.executable, "-m", "pip", "install", "pypdf", "-q"])
    from pypdf import PdfReader

ROOT = Path(__file__).resolve().parent.parent
ZIP_PATH = ROOT / "ECF_A_RENDRE.zip"

EXPECTED_PDFS = {
    "01_Page_de_garde.pdf",
    "03_Manuel_utilisateur.pdf",
    "04_Charte_graphique.pdf",
    "05_Documentation_technique.pdf",
}


def main() -> int:
    if not ZIP_PATH.exists():
        print(f"ZIP introuvable : {ZIP_PATH}")
        return 1

    print(f"ZIP : {ZIP_PATH}")
    print(f"Taille : {ZIP_PATH.stat().st_size / 1024:.1f} Ko")
    print()

    good: list[str] = []
    issues: list[str] = []

    with zipfile.ZipFile(ZIP_PATH) as z:
        infos = z.infolist()
        print(f"Contenu ({len(infos)} entree(s)) :")
        for info in infos:
            print(f"  {info.file_size // 1024:4d} Ko  {info.filename}")
        print()

        basenames = [Path(i.filename).name for i in infos]
        odt_files = [b for b in basenames if b.lower().endswith(".odt")]

        if any("/" in i.filename for i in infos):
            roots = {i.filename.split("/")[0] for i in infos if "/" in i.filename}
            if roots == {"ECF_A_RENDRE"}:
                good.append("Structure : sous-dossier ECF_A_RENDRE/ (acceptable)")
            else:
                issues.append(f"Structure : sous-dossiers {sorted(roots)}")
        else:
            good.append("Structure : fichiers a la racine du ZIP")

        for unwanted in ("LISEZMOI", ".gitignore", ".~lock"):
            for name in basenames:
                if unwanted.lower() in name.lower():
                    issues.append(f"Fichier indesirable : {name}")

        for pdf in EXPECTED_PDFS:
            if pdf not in basenames:
                issues.append(f"MANQUANT : {pdf}")
            else:
                good.append(f"Present : {pdf}")

        if not odt_files:
            issues.append("MANQUANT : copie a rendre (.odt)")
        elif len(odt_files) > 1:
            issues.append(f"Plusieurs ODT : {odt_files}")
        else:
            good.append(f"Present : {odt_files[0]}")

        extra = set(basenames) - EXPECTED_PDFS - set(odt_files)
        for name in sorted(extra):
            issues.append(f"Fichier en trop : {name}")

        for info in infos:
            name = info.filename
            base = Path(name).name

            if base.lower().endswith(".odt"):
                xml = z.read(name).decode("utf-8", errors="replace")
                text = re.sub(r"<[^>]+>", " ", xml)
                text = re.sub(r"\s+", " ", text)
                for needle in (
                    "OCÉANE VIRGINIE ERICA",
                    "DUFFOUR",
                    "vitegourmand.infinityfree.io",
                    "jose@vite-gourmand.fr",
                    "Admin123!",
                ):
                    ok = needle.lower() in text.lower() or needle in text
                    msg = f"ODT : {needle} -> {'OK' if ok else 'ABSENT'}"
                    (good if ok else issues).append(msg)

            if base in EXPECTED_PDFS:
                data = z.read(name)
                if not data.startswith(b"%PDF"):
                    issues.append(f"{base} : PDF invalide")
                    continue
                reader = PdfReader(io.BytesIO(data))
                pages = len(reader.pages)
                if pages < 1:
                    issues.append(f"{base} : 0 page")
                else:
                    good.append(f"{base} : {pages} page(s), PDF valide")

                if base == "01_Page_de_garde.pdf":
                    text = " ".join((p.extract_text() or "") for p in reader.pages)
                    if "virginie erica duffour" not in text.lower():
                        issues.append("01_Page_de_garde.pdf : nom complet absent")
                    else:
                        good.append("01_Page_de_garde.pdf : nom complet OK")

            if base == "03_Manuel_utilisateur.pdf":
                data = z.read(name)
                reader = PdfReader(io.BytesIO(data))
                text = " ".join((p.extract_text() or "") for p in reader.pages).lower()
                for needle in (
                    "jose@vite-gourmand.fr",
                    "julie@vite-gourmand.fr",
                    "client@vite-gourmand.fr",
                ):
                    if needle not in text:
                        issues.append(f"03_Manuel_utilisateur.pdf : {needle} absent")

    print("=== OK ===")
    for item in good:
        print(f"  + {item}")

    print("\n=== PROBLEMES ===")
    if issues:
        for item in issues:
            print(f"  ! {item}")
        print("\nVERDICT : A CORRIGER")
        return 1

    print("  (aucun)")
    print("\nVERDICT : PRET A ENVOYER")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
