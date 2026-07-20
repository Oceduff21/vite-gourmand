#!/usr/bin/env python3
"""Extrait le JS wizard de menu.php vers front/js/menu-wizard.js."""

from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
MENU = ROOT / "menu.php"
OUT = ROOT / "front" / "js" / "menu-wizard.js"

MARKER = "let cart = "


def main() -> int:
    text = MENU.read_text(encoding="utf-8")
    start = text.find("<script>")
    end = text.rfind("</script>")
    if start < 0 or end < 0:
        raise SystemExit("Bloc <script> introuvable dans menu.php")

    script_body = text[start + len("<script>") : end].lstrip("\n")
    idx = script_body.find(MARKER)
    if idx < 0:
        raise SystemExit("Marqueur 'let cart =' introuvable")

    config_part = script_body[:idx].rstrip() + "\n"
    logic_part = script_body[idx:].rstrip() + "\n"

    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(
        "/**\n * FRONT — menu-wizard.js\n * Wizard commande (etapes, sliders, validation UI).\n"
        " * Config dynamique injectee par menu.php (constantes PHP).\n */\n"
        + logic_part,
        encoding="utf-8",
    )

    replacement = (
        "<!-- BACK injecte la config ; FRONT = menu-wizard.js -->\n"
        "<script>\n"
        + config_part
        + "</script>\n"
        + '<script src="front/js/menu-wizard.js?v=20260720c"></script>'
    )

    new_text = text[:start] + replacement + text[end + len("</script>") :]
    MENU.write_text(new_text, encoding="utf-8")

    print(f"OK config inline: {len(config_part)} octets")
    print(f"OK {OUT.relative_to(ROOT)}: {len(logic_part)} octets")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
