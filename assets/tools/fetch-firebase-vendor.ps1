<#
fetch-firebase-vendor.ps1

Usage:
  Open PowerShell, change directory to your project root (example):
    Set-Location C:\xampp\htdocs\BCSTR
  Run the script:
    .\assets\tools\fetch-firebase-vendor.ps1

This script will download Firebase SDK vendor files into `assets/js/vendor`.
It tries multiple CDN mirrors and versions and validates the downloaded files.
#>

$ErrorActionPreference = 'SilentlyContinue'

$dest = Join-Path (Get-Location) 'assets\js\vendor'
if (-not (Test-Path $dest)) { New-Item -ItemType Directory -Path $dest | Out-Null }

$versions = @('9.24.0','9.23.0','9.22.0','9.21.0')
$cdnBases = @('https://www.gstatic.com/firebasejs/{0}/','https://cdn.jsdelivr.net/npm/firebase@{0}/','https://unpkg.com/firebase@{0}/dist/')
$files = @('firebase-app.js','firebase-auth.js','firebase-firestore.js')

foreach ($file in $files) {
  $ok = $false
  foreach ($v in $versions) {
    foreach ($baseFmt in $cdnBases) {
      $base = $baseFmt -f $v
      $url = $base + $file
      $out = Join-Path $dest $file
      Write-Host "Trying $url ..."
      try {
        Invoke-WebRequest -Uri $url -UseBasicParsing -OutFile $out -ErrorAction Stop -TimeoutSec 30
        $head = Get-Content -Path $out -TotalCount 1 -ErrorAction Stop
        if ($head -match '<!DOCTYPE' -or $head -match '<html') {
          Write-Warning "Downloaded $file looks like HTML (error page). Removing and continuing."
          Remove-Item $out -ErrorAction SilentlyContinue
        } else {
          Write-Host "Downloaded $file from $url"
          $ok = $true
          break
        }
      } catch {
        Write-Warning "Failed to download $url : $_"
      }
    }
    if ($ok) { break }
  }
  if (-not $ok) {
    Write-Error "Failed to download $file from all CDNs."
  }
}
Write-Host "Done. Files in $dest :"
Get-ChildItem $dest -Name
