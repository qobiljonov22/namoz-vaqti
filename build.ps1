# Vue-style build: netlify-site -> dist + Desktop zip
# Usage:
#   .\build.ps1

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
$Src = Join-Path $Root "netlify-site"
$Dist = Join-Path $Root "dist"
$DesktopZip = Join-Path $env:USERPROFILE "Desktop\namoz-vaqti-netlify.zip"

Write-Host "[build] Static HTML..." -ForegroundColor Cyan

if (-not (Test-Path $Src)) {
    Write-Host "[error] netlify-site/ not found" -ForegroundColor Red
    exit 1
}

if (Test-Path $Dist) {
    Remove-Item $Dist -Recurse -Force
}
New-Item -ItemType Directory -Path $Dist -Force | Out-Null

Get-ChildItem $Src -Force | Where-Object { $_.Name -ne "README.md" } | ForEach-Object {
    Copy-Item $_.FullName (Join-Path $Dist $_.Name) -Recurse -Force
}

$files = Get-ChildItem $Dist -Recurse -File
Write-Host ("[ok] dist/ ready ({0} files)" -f $files.Count) -ForegroundColor Green
$files | ForEach-Object {
    $rel = $_.FullName.Substring($Dist.Length + 1) -replace "\\", "/"
    Write-Host ("  - {0}" -f $rel)
}

Write-Host "[build] Creating zip..." -ForegroundColor Cyan
if (Test-Path $DesktopZip) { Remove-Item $DesktopZip -Force }
Compress-Archive -Path (Join-Path $Dist "*") -DestinationPath $DesktopZip -Force
$kb = [math]::Round((Get-Item $DesktopZip).Length / 1KB)
Write-Host ("[ok] Zip: {0} ({1} KB)" -f $DesktopZip, $kb) -ForegroundColor Green

Write-Host ""
Write-Host "Netlify: upload namoz-vaqti-netlify.zip from Desktop" -ForegroundColor Yellow
Write-Host "WordPress: http://ramazon-taqvim.local (unchanged)" -ForegroundColor Yellow
