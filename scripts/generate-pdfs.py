#!/usr/bin/env python3
"""Generate ECF PDF livrables from docs/*.md."""

from __future__ import annotations

import re
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

SOURCES = [
    ("MANUEL_UTILISATEUR.md", "MANUEL_UTILISATEUR.pdf"),
    ("CHARTE_GRAPHIQUE.md", "CHARTE_GRAPHIQUE.pdf"),
    ("DOCUMENTATION_TECHNIQUE.md", "DOCUMENTATION_TECHNIQUE.pdf"),
]

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


def md_to_html(md_path: Path) -> str:
    text = md_path.read_text(encoding="utf-8")
    body = markdown.markdown(
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


def main() -> int:
    generated = []
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

    print(f"\n{len(generated)} PDF(s) in {DOCS}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
