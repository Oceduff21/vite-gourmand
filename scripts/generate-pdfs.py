#!/usr/bin/env python3
"""Generate ECF PDF livrables from docs/*.md and cover page."""

from __future__ import annotations

import json
import sys
from pathlib import Path

try:
    import markdown
    from xhtml2pdf import pisa
except ImportError:
    print("Installing markdown and xhtml2pdf...")
    import subprocess

    subprocess.check_call(
        [sys.executable, "-m", "pip", "install", "markdown", "xhtml2pdf", "-q"]
    )
    import markdown
    from xhtml2pdf import pisa

ROOT = Path(__file__).resolve().parent.parent
DOCS = ROOT / "docs"
CSS = DOCS / "pdf-style.css"
META = DOCS / "ecf-meta.json"

SOURCES = [
    ("MANUEL_UTILISATEUR.md", "MANUEL_UTILISATEUR.pdf"),
    ("CHARTE_GRAPHIQUE.md", "CHARTE_GRAPHIQUE.pdf"),
    ("DOCUMENTATION_TECHNIQUE.md", "DOCUMENTATION_TECHNIQUE.pdf"),
]

DOSSIER_PROJET = (
    ROOT / "dossier-projet" / "DOSSIER_PROJET.md",
    ROOT / "dossier-projet" / "DOSSIER_PROJET.pdf",
)

DOSSIER_PROFESSIONNEL = (
    ROOT / "dossier-professionnel" / "DOSSIER_PROFESSIONNEL.md",
    ROOT / "dossier-professionnel" / "DOSSIER_PROFESSIONNEL.pdf",
)

HTML_TEMPLATE = """<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <title>{title}</title>
  <style>
{css}
  </style>
</head>
<body>
{body}
</body>
</html>
"""


def load_meta() -> dict:
    defaults = {
        "student_name": "Prénom NOM",
        "formation": "Développeur Web et Web Mobile",
        "project_title": "Vite & Gourmand — Traiteur en ligne",
        "submission_date": "2026",
        "deploy_url": "https://vitegourmand.infinityfree.io/",
        "github_url": "https://github.com/Oceduff21/vite-gourmand",
    }
    if META.exists():
        data = json.loads(META.read_text(encoding="utf-8"))
        defaults.update(data)
    return defaults


def cover_html(meta: dict) -> str:
    return f"""
<section class="cover-page">
  <p class="label">Dossier ECF — TP Développeur Web et Web Mobile</p>
  <h1>{meta["project_title"]}</h1>
  <p class="subtitle">{meta["formation"]}</p>
  <div class="meta">
    <p><strong>Candidat :</strong> {meta["student_name"]}</p>
    <p><strong>Date de remise :</strong> {meta["submission_date"]}</p>
    <p><strong>Site de production :</strong><br/>
      <a href="{meta["deploy_url"]}">{meta["deploy_url"]}</a></p>
    <p><strong>GitHub :</strong><br/>
      <a href="{meta["github_url"]}">{meta["github_url"]}</a></p>
  </div>
  <p class="footer-note">
    Livrables joints : Copie à rendre (ODT), Manuel utilisateur, Charte graphique,
    Documentation technique
  </p>
</section>
"""


def md_to_html(md_path: Path, body_prefix: str = "") -> str:
    text = md_path.read_text(encoding="utf-8")
    body = body_prefix + markdown.markdown(
        text,
        extensions=["tables", "fenced_code", "sane_lists", "nl2br"],
    )
    title = md_path.stem.replace("_", " ")
    css = CSS.read_text(encoding="utf-8") if CSS.exists() else ""
    return HTML_TEMPLATE.format(title=title, css=css, body=body)


def html_to_pdf(html: str, pdf_path: Path) -> None:
    with pdf_path.open("wb") as out:
        status = pisa.CreatePDF(html, dest=out, encoding="utf-8")
    if status.err:
        raise RuntimeError(f"PDF generation failed for {pdf_path.name}")


def generate_cover_and_sommaire() -> Path | None:
    sommaire_md = DOCS / "PAGE_GARDE_ECF.md"
    pdf_path = DOCS / "PAGE_GARDE_ECF.pdf"
    if not sommaire_md.exists():
        print("SKIP missing: PAGE_GARDE_ECF.md")
        return None

    meta = load_meta()
    html = md_to_html(sommaire_md, body_prefix=cover_html(meta))
    html_to_pdf(html, pdf_path)
    return pdf_path


def main() -> int:
    generated = []

    cover_pdf = generate_cover_and_sommaire()
    if cover_pdf:
        generated.append(cover_pdf)
        print(f"OK {cover_pdf.name} ({cover_pdf.stat().st_size // 1024} KB)")

    for src_name, pdf_name in SOURCES:
        md_path = DOCS / src_name
        pdf_path = DOCS / pdf_name
        if not md_path.exists():
            print(f"SKIP missing: {md_path}")
            continue
        html = md_to_html(md_path)
        html_to_pdf(html, pdf_path)
        generated.append(pdf_path)
        print(f"OK {pdf_path.name} ({pdf_path.stat().st_size // 1024} KB)")

    md_path, pdf_path = DOSSIER_PROJET
    if md_path.exists():
        html = md_to_html(md_path)
        html_to_pdf(html, pdf_path)
        generated.append(pdf_path)
        print(f"OK {pdf_path.name} ({pdf_path.stat().st_size // 1024} KB)")
    else:
        print(f"SKIP missing: {md_path}")

    md_path, pdf_path = DOSSIER_PROFESSIONNEL
    if md_path.exists():
        html = md_to_html(md_path)
        html_to_pdf(html, pdf_path)
        generated.append(pdf_path)
        print(f"OK {pdf_path.name} ({pdf_path.stat().st_size // 1024} KB)")
    else:
        print(f"SKIP missing: {md_path}")

    print(f"\n{len(generated)} PDF(s)")
    if load_meta()["student_name"] == "Prénom NOM":
        print("NOTE: modifiez docs/ecf-meta.json (student_name) puis relancez ce script.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
