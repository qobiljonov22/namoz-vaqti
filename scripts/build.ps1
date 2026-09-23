# Node kerak emas — Vue'dagi npm run build o'rniga
# Ishlatish:  .\scripts\build.ps1

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
$src = Join-Path $root "netlify-site"
$dist = Join-Path $root "dist"
$zip = Join-Path $env:USERPROFILE "Desktop\namoz-vaqti-netlify.zip"

Write-Host "Building static HTML..." -ForegroundColor Cyan
if (-not (Test-Path $src)) { throw "netlify-site/ topilmadi" }

if (Test-Path $dist) { Remove-Item $dist -Recurse -Force }
New-Item -ItemType Directory -Path $dist | Out-Null
Copy-Item (Join-Path $src "*") $dist -Recurse -Force
Get-ChildItem $dist -Filter README.md -Recurse | Remove-Item -Force -ErrorAction SilentlyContinue

$count = (Get-ChildItem $dist -Recurse -File).Count
Write-Host "dist/ tayyor ($count fayl)" -ForegroundColor Green

if (Test-Path $zip) { Remove-Item $zip -Force }
Compress-Archive -Path (Join-Path $dist "*") -DestinationPath $zip -Force
Write-Host "Zip: $zip" -ForegroundColor Green
Write-Host "Netlifyga shu zip ni yuklang. WordPress OpenServerda qoladi."
