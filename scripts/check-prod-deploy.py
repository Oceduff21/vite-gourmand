import re
import urllib.request

BASE = "https://vitegourmand.infinityfree.io"


def fetch(path: str) -> tuple[int, str]:
    req = urllib.request.Request(BASE + path, headers={"User-Agent": "deploy-check/1.0"})
    with urllib.request.urlopen(req, timeout=30) as resp:
        return resp.status, resp.read().decode("utf-8", "replace")


def head(path: str) -> int:
    req = urllib.request.Request(BASE + path, method="HEAD", headers={"User-Agent": "deploy-check/1.0"})
    with urllib.request.urlopen(req, timeout=20) as resp:
        return resp.status


print("=== PAGES ===")
for path in ["/", "/menu.php?id=14", "/menus.php", "/a-propos.php", "/faq.php", "/admin/login.php", "/cgv.php"]:
    status, html = fetch(path)
    print(f"{status} {path} ({len(html)} bytes)")

print("\n=== MENU ENTREPRISE ===")
_, html = fetch("/menu.php?id=14")
entrees = ["Brochettes poulet halal", "Salade quinoa", "Veloute de saison"]
for e in entrees:
    print(("OK" if e in html else "MANQUANT"), e)
print("plat-select-card:", html.count("plat-select-card"))
print("wizard:", "wizard-step-btn" in html or "menu-wizard" in html)
print("cache ?v=:", "?v=" in html)
print("skip link:", "aller au contenu" in html.lower() or "skip-link" in html.lower())

imgs = sorted(set(re.findall(r"assets/images/[^\"'?]+", html)))
print("images on menu page:", len(imgs))
for img in imgs[:12]:
    try:
        print(" ", head("/" + img), img)
    except Exception as exc:
        print("  ERR", img, exc)

print("\n=== ASSETS SPOT CHECK ===")
assets = [
    "assets/css/style.css",
    "assets/images/favicon.svg",
    "assets/images/brochettes-poulet.jpg",
    "assets/images/carpaccio-boeuf.jpg",
    "assets/images/curry.jpg",
    "assets/images/menu-entreprise.jpg",
]
for asset in assets:
    try:
        req = urllib.request.Request(BASE + "/" + asset, method="HEAD", headers={"User-Agent": "deploy-check/1.0"})
        with urllib.request.urlopen(req, timeout=20) as resp:
            size = resp.headers.get("Content-Length", "?")
            print(resp.status, size, asset)
    except Exception as exc:
        print("ERR", asset, exc)

print("\n=== LOCAL CSS SIZE MATCH ===")
from pathlib import Path

local_css = Path(__file__).resolve().parent.parent / "assets" / "css" / "style.css"
print("local style.css bytes:", local_css.stat().st_size)
