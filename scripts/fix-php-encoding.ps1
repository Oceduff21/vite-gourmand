# Force tous les .php en UTF-8 sans BOM (requis InfinityFree)
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$utf8 = New-Object System.Text.UTF8Encoding $false
$fixed = 0

Get-ChildItem -Path $root -Recurse -Filter "*.php" -File | ForEach-Object {
    $rel = $_.FullName.Substring($root.Length + 1)
    if ($rel -match '^deploy\\' -or $rel -match '^\.git\\') { return }

    $bytes = [System.IO.File]::ReadAllBytes($_.FullName)
    $changed = $false
    $text = $null

    if ($bytes.Length -ge 2 -and $bytes[1] -eq 0 -and $bytes[0] -eq 0x3C) {
        $text = [System.Text.Encoding]::Unicode.GetString($bytes)
        $changed = $true
        Write-Host "UTF-16 -> UTF-8: $rel"
    } elseif ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        $text = [System.Text.Encoding]::UTF8.GetString($bytes, 3, $bytes.Length - 3)
        $changed = $true
        Write-Host "BOM removed: $rel"
    }

    if ($changed -and $null -ne $text) {
        [System.IO.File]::WriteAllText($_.FullName, $text, $utf8)
        $fixed++
    }
}

Write-Host "Done. $fixed file(s) corrected."
