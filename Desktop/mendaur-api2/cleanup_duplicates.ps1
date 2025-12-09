$root = "c:\Users\Adib\OneDrive\Desktop\mendaur-api"
$doc = Join-Path $root "DOCUMENTATION"

# Get all .md files in root that are NOT in DOCUMENTATION folder
$rootFiles = @(Get-ChildItem -Path $root -Filter "*.md" -File -ErrorAction SilentlyContinue | Where-Object { -not ($_.FullName -like "*DOCUMENTATION*") })

Write-Host "Found $($rootFiles.Count) duplicate files in root" -ForegroundColor Yellow
Write-Host "Deleting all..." -ForegroundColor Red
Write-Host ""

$deleted = 0
foreach ($file in $rootFiles) {
    Remove-Item $file.FullName -Force -ErrorAction SilentlyContinue
    $deleted++
    if ($deleted % 50 -eq 0) {
        Write-Host "  Deleted $deleted files..."
    }
}

Write-Host ""
Write-Host "Deleted $deleted files total" -ForegroundColor Green
Write-Host ""

# Verify
$remaining = @(Get-ChildItem -Path $root -Filter "*.md" -File -ErrorAction SilentlyContinue | Where-Object { -not ($_.FullName -like "*DOCUMENTATION*") })

Write-Host "Remaining .md files in root: $($remaining.Count)" -ForegroundColor Cyan

if ($remaining.Count -eq 0) {
    Write-Host "✅ SUCCESS: All duplicates removed!" -ForegroundColor Green
} else {
    Write-Host "❌ Files still in root:" -ForegroundColor Yellow
    $remaining | ForEach-Object { Write-Host ("  - " + $_.Name) }
}
