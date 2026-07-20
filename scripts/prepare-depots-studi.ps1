# Prepare deux ZIP separes pour Studi (Dossier Projet + Dossier Pro)
$ErrorActionPreference = "Stop"
$Root = "C:\xampp\htdocs\vite-gourmand"
if (-not (Test-Path $Root)) {
    $Root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
}
if (-not (Test-Path $Root)) {
    $Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
}

# Annexes PDF partagees
$AnnexesSrc = Join-Path $Root "docs"
$AnnexesDstProjet = Join-Path $Root "dossier-projet\annexes"
New-Item -ItemType Directory -Force -Path $AnnexesDstProjet | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $AnnexesDstProjet "captures") | Out-Null

foreach ($pdf in @("CHARTE_GRAPHIQUE.pdf", "MANUEL_UTILISATEUR.pdf", "DOCUMENTATION_TECHNIQUE.pdf")) {
    $src = Join-Path $AnnexesSrc $pdf
    if (Test-Path $src) {
        Copy-Item $src (Join-Path $AnnexesDstProjet $pdf) -Force
    }
}

# ZIP Dossier Projet
$zipFileProjet = Join-Path $Root "DEPOT_DOSSIER_PROJET.zip"
$pdfProjet = Join-Path $Root "dossier-projet\DOSSIER_PROJET.pdf"
if (-not (Test-Path $pdfProjet)) {
    Write-Host "MANQUANT: $pdfProjet — lancez: python scripts/generate-pdfs.py" -ForegroundColor Yellow
} else {
    if (Test-Path $zipFileProjet) { Remove-Item $zipFileProjet -Force }
    Compress-Archive -Path $pdfProjet -DestinationPath $zipFileProjet -Force
    Write-Host "OK DEPOT_DOSSIER_PROJET.zip"
}

# Ajouter annexes au zip projet si presentes
$captures = Get-ChildItem (Join-Path $AnnexesDstProjet "captures") -File -ErrorAction SilentlyContinue
if ($captures) {
    Write-Host "INFO: $($captures.Count) capture(s) dans dossier-projet/annexes/captures/"
}

# ZIP Dossier Professionnel
$zipFileDp = Join-Path $Root "DEPOT_DOSSIER_PROFESSIONNEL.zip"
$pdfFileDp = Join-Path $Root "dossier-professionnel\DOSSIER_PROFESSIONNEL.pdf"
if (-not (Test-Path $pdfFileDp)) {
    Write-Host "MANQUANT: $pdfFileDp — lancez: python scripts/generate-pdfs.py" -ForegroundColor Yellow
} else {
    if (Test-Path $zipFileDp) { Remove-Item $zipFileDp -Force }
    Compress-Archive -Path $pdfFileDp -DestinationPath $zipFileDp -Force
    Write-Host "OK DEPOT_DOSSIER_PROFESSIONNEL.zip"
}

Write-Host ""
Write-Host "Depot separe sur Studi:"
Write-Host "  1. DEPOT_DOSSIER_PROJET.zip       -> espace 1er depot dossier projet"
Write-Host "  2. DEPOT_DOSSIER_PROFESSIONNEL.zip -> espace Dossier Professionnel"
Write-Host ""
Write-Host "Voir docs/depot/ELEMENTS_A_FOURNIR.md pour ce qu'il reste a completer."
