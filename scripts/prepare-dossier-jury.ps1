# Copie les livrables ECF dans ECF_A_RENDRE/ (dossier jury uniquement)
$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$Docs = Join-Path $Root "docs"
$Out  = Join-Path $Root "ECF_A_RENDRE"

$Files = @(
    @{ Src = "PAGE_GARDE_ECF.pdf";              Dst = "01_Page_de_garde.pdf" },
    @{ Src = "Copie_a_rendre_TP_Vite_Gourmand.odt"; Dst = "02_Copie_a_rendre.odt" },
    @{ Src = "MANUEL_UTILISATEUR.pdf";          Dst = "03_Manuel_utilisateur.pdf" },
    @{ Src = "CHARTE_GRAPHIQUE.pdf";            Dst = "04_Charte_graphique.pdf" },
    @{ Src = "DOCUMENTATION_TECHNIQUE.pdf";     Dst = "05_Documentation_technique.pdf" }
)

if (-not (Test-Path $Out)) {
    New-Item -ItemType Directory -Path $Out | Out-Null
}

# Nettoyer les anciennes copies (garder LISEZMOI.md et .gitignore)
Get-ChildItem -Path $Out -File | Where-Object {
    $_.Name -notin @("LISEZMOI.md", ".gitignore")
} | Remove-Item -Force

$missing = @()
foreach ($f in $Files) {
    $srcPath = Join-Path $Docs $f.Src
    $dstPath = Join-Path $Out $f.Dst
    if (-not (Test-Path $srcPath)) {
        $missing += $f.Src
        continue
    }
    Copy-Item -Path $srcPath -Destination $dstPath -Force
    $kb = [math]::Round((Get-Item $dstPath).Length / 1KB, 0)
    Write-Host "OK  $($f.Dst)  ($kb Ko)"
}

Write-Host ""
if ($missing.Count -gt 0) {
    Write-Host "MANQUANT dans docs/ :" -ForegroundColor Yellow
    $missing | ForEach-Object { Write-Host "  - $_" }
    Write-Host ""
    Write-Host "Regenerez les PDF : python scripts\generate-pdfs.py"
    exit 1
}

Write-Host "Dossier jury pret : $Out"
Write-Host "Prochaine etape : verifier 02_Copie_a_rendre.odt puis zipper ou imprimer."
