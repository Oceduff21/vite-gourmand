#!/usr/bin/env python3
"""Assemble les deux dossiers Studi prets a envoyer."""

from __future__ import annotations

import shutil
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
PRET = ROOT / "PRET_A_ENVOYER"
PROJET = PRET / "DOSSIER_PROJET"
PRO = PRET / "DOSSIER_PROFESSIONNEL"

PROJET_FILES = [
    ROOT / "dossier-projet" / "DOSSIER_PROJET.pdf",
    ROOT / "docs" / "CHARTE_GRAPHIQUE.pdf",
    ROOT / "docs" / "MANUEL_UTILISATEUR.pdf",
    ROOT / "docs" / "DOCUMENTATION_TECHNIQUE.pdf",
    ROOT / "database" / "schema.sql",
]

PRO_FILES = [
    ROOT / "dossier-professionnel" / "DP_Duffour_Oceane.docx",
    ROOT / "dossier-professionnel" / "DOSSIER_PROFESSIONNEL.pdf",
]

CAPTURES_SRC = ROOT / "dossier-projet" / "annexes" / "captures"


def reset_dir(path: Path) -> None:
    if path.exists():
        shutil.rmtree(path)
    path.mkdir(parents=True, exist_ok=True)


def zip_dir(source_files: list[Path], zip_path: Path, arc_prefix: str = "") -> None:
    if zip_path.exists():
        zip_path.unlink()
    with zipfile.ZipFile(zip_path, "w", zipfile.ZIP_DEFLATED) as zf:
        for f in source_files:
            if not f.exists():
                continue
            arc = f"{arc_prefix}{f.name}" if arc_prefix else f.name
            zf.write(f, arc)


def main() -> int:
    reset_dir(PROJET)
    reset_dir(PRO)

    # Dossier Projet
    copied_projet = []
    for src in PROJET_FILES:
        if src.exists():
            dst = PROJET / src.name
            shutil.copy2(src, dst)
            copied_projet.append(dst)
            print(f"OK projet: {src.name}")

    ann_projet = PROJET / "annexes"
    ann_projet.mkdir(exist_ok=True)
    if CAPTURES_SRC.exists():
        for img in CAPTURES_SRC.glob("*.*"):
            shutil.copy2(img, ann_projet / img.name)
            copied_projet.append(ann_projet / img.name)
            print(f"OK capture: {img.name}")

    shutil.copy2(ROOT / "docs/depot/PRET_A_ENVOYER_LISEZMOI.md", PRET / "LISEZMOI.md") if (ROOT / "docs/depot/PRET_A_ENVOYER_LISEZMOI.md").exists() else None

    # Dossier Professionnel
    copied_pro = []
    for src in PRO_FILES:
        if src.exists():
            dst = PRO / src.name
            shutil.copy2(src, dst)
            copied_pro.append(dst)
            print(f"OK pro: {src.name}")

    # Captures pour illustration DP
    if CAPTURES_SRC.exists():
        ann_pro = PRO / "annexes_captures"
        ann_pro.mkdir(exist_ok=True)
        for name in ["capture-03-wizard.png", "capture-05-admin.png", "capture-01-accueil.png"]:
            src = CAPTURES_SRC / name
            if src.exists():
                shutil.copy2(src, ann_pro / name)
                copied_pro.append(ann_pro / name)

    # ZIPs
    zip_projet = ROOT / "DEPOT_DOSSIER_PROJET_Duffour.zip"
    zip_pro = ROOT / "DEPOT_DOSSIER_PROFESSIONNEL_Duffour.zip"

    all_projet = list(PROJET.rglob("*"))
    all_projet = [f for f in all_projet if f.is_file()]
    zip_dir(all_projet, zip_projet)

    all_pro = list(PRO.rglob("*"))
    all_pro = [f for f in all_pro if f.is_file()]
    zip_dir(all_pro, zip_pro)

    print("")
    print(f"PRET_A_ENVOYER/DOSSIER_PROJET/          ({len(all_projet)} fichiers)")
    print(f"PRET_A_ENVOYER/DOSSIER_PROFESSIONNEL/  ({len(all_pro)} fichiers)")
    print(f"{zip_projet.name}")
    print(f"{zip_pro.name}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
