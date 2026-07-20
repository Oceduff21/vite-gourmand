# Build production deploy ZIP for InfinityFree / FileZilla
$ErrorActionPreference = "Stop"

$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$outDir = Join-Path $root "deploy"
$zipPath = Join-Path $outDir "vite-gourmand-prod.zip"

New-Item -ItemType Directory -Force -Path $outDir | Out-Null

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

$excludeDirs = @(
    ".git",
    "deploy",
    "node_modules",
    "vendor",
    "docs",
    "dossier-projet",
    "dossier-professionnel",
    "PRET_A_ENVOYER",
    "ECF_A_RENDRE",
    "agent-transcripts"
)

$excludeFiles = @(
    "includes\config.local.php",
    ".env",
    ".gitignore"
)

$excludePatterns = @(
    "*.pdf",
    "*.odt",
    "*.md",
    "DEPOT_*.zip",
    "ECF_A_RENDRE.zip",
    "PRET_A_ENVOYER*"
)

$temp = Join-Path $env:TEMP ("vite-gourmand-deploy-" + [guid]::NewGuid().Guid)
New-Item -ItemType Directory -Force -Path $temp | Out-Null

function ShouldExclude($relativePath) {
    $normalized = $relativePath -replace "/", "\"
    foreach ($dir in $excludeDirs) {
        if ($normalized -eq $dir -or $normalized.StartsWith("$dir\")) { return $true }
    }
    foreach ($file in $excludeFiles) {
        if ($normalized -ieq $file) { return $true }
    }
    foreach ($pattern in $excludePatterns) {
        if ($normalized -like $pattern) { return $true }
    }
    return $false
}

Get-ChildItem -Path $root -Recurse -File | ForEach-Object {
    $rel = $_.FullName.Substring($root.Length + 1)
    if (-not (ShouldExclude $rel)) {
        $dest = Join-Path $temp $rel
        $destDir = Split-Path $dest -Parent
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Force -Path $destDir | Out-Null
        }
        Copy-Item $_.FullName $dest -Force
    }
}

# PHP must be UTF-8 (not UTF-16) or InfinityFree serves source code instead of executing
Get-ChildItem -Path $temp -Recurse -Filter "*.php" -File | ForEach-Object {
    $bytes = [System.IO.File]::ReadAllBytes($_.FullName)
    if ($bytes.Length -ge 2 -and $bytes[1] -eq 0 -and $bytes[0] -eq 0x3C) {
        $text = [System.Text.Encoding]::Unicode.GetString($bytes)
        $utf8 = New-Object System.Text.UTF8Encoding $false
        [System.IO.File]::WriteAllText($_.FullName, $text, $utf8)
        Write-Host "UTF-8 fix: $($_.Name)"
    } elseif ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        [System.IO.File]::WriteAllBytes($_.FullName, $bytes[3..($bytes.Length - 1)])
        Write-Host "BOM removed: $($_.Name)"
    }
}

Compress-Archive -Path (Join-Path $temp "*") -DestinationPath $zipPath -Force
Remove-Item $temp -Recurse -Force

$sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
$fileCount = (Get-ChildItem -Path $root -Recurse -File | Where-Object {
    $rel = $_.FullName.Substring($root.Length + 1)
    -not (ShouldExclude $rel)
}).Count

Write-Host "OK ZIP cree : $zipPath"
Write-Host "   Taille : ${sizeMb} Mo"
Write-Host "   Fichiers : $fileCount"
Write-Host ""
Write-Host "FileZilla : uploadez le contenu de deploy/ vers htdocs/ sur InfinityFree"
