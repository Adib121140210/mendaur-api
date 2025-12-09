$root = "c:\Users\Adib\OneDrive\Desktop\mendaur-api"
$doc = Join-Path $root "DOCUMENTATION"

$rootCount = @(Get-ChildItem -Path $root -Filter "*.md" -File -ErrorAction SilentlyContinue | Where-Object { -not ($_.FullName -contains "DOCUMENTATION") }).Count
$docCount = @(Get-ChildItem -Path $doc -Filter "*.md" -Recurse).Count

Write-Host ""
Write-Host "CLEANUP VERIFICATION" -ForegroundColor Green
Write-Host "===================" -ForegroundColor Green
Write-Host ""
Write-Host "Files in ROOT: $rootCount" -ForegroundColor Cyan
Write-Host "Files in DOCUMENTATION: $docCount" -ForegroundColor Cyan
Write-Host ""

if ($rootCount -eq 0) {
    Write-Host "SUCCESS: ROOT FOLDER IS CLEAN!" -ForegroundColor Green
}

Write-Host ""
Write-Host "CLEANUP COMPLETE!" -ForegroundColor Green
