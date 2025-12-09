$root = "c:\Users\Adib\OneDrive\Desktop\mendaur-api"
$doc = Join-Path $root "DOCUMENTATION"
$docOrg = Join-Path $doc "DOCUMENTATION_ORGANIZATION"

# Move all index files to DOCUMENTATION_ORGANIZATION
$files = @(
    "00_MASTER_DOCUMENTATION_INDEX_LENGKAP.md",
    "00_START_HERE_DOCUMENTATION_ORGANIZED.md",
    "COMPLETE_DOCUMENTATION_INDEX.md",
    "DOCUMENTATION_FINAL_SUMMARY.md",
    "DOCUMENTATION_INDEX.md",
    "DOCUMENTATION_ORGANIZATION_COMPLETION.md",
    "DOCUMENTATION_ORGANIZATION_SUCCESS.md",
    "DOCUMENTATION_PACKAGE_INDEX.md",
    "DROP_UNUSED_TABLES_DOCUMENTATION_INDEX.md",
    "ERD_DOCUMENTATION_FILE_INDEX.md",
    "FINAL_DOCUMENTATION_STATUS.md",
    "REGISTER_DOCUMENTATION_INDEX.md",
    "BADGE_DOCUMENTATION_INDEX.md",
    "DOCUMENTATION_INDEX_ADMIN_DASHBOARD.md"
)

foreach ($file in $files) {
    $src = Join-Path $root $file
    if (Test-Path $src) {
        $dst = Join-Path $docOrg $file
        Move-Item $src $dst -Force
        Write-Host ("Moved: " + $file)
    }
}

# Move API_DOCUMENTATION.md to API_DOCUMENTATION folder
$src = Join-Path $root "API_DOCUMENTATION.md"
if (Test-Path $src) {
    $apiDoc = Join-Path $doc "API_DOCUMENTATION"
    $dst = Join-Path $apiDoc "API_DOCUMENTATION.md"
    Move-Item $src $dst -Force
    Write-Host "Moved: API_DOCUMENTATION.md"
}

# Move API_RESPONSE_DOCUMENTATION.md
$src = Join-Path $root "API_RESPONSE_DOCUMENTATION.md"
if (Test-Path $src) {
    $apiDoc = Join-Path $doc "API_DOCUMENTATION"
    $dst = Join-Path $apiDoc "API_RESPONSE_DOCUMENTATION.md"
    Move-Item $src $dst -Force
    Write-Host "Moved: API_RESPONSE_DOCUMENTATION.md"
}

# Move PENUKARAN
$src = Join-Path $root "PENUKARAN_PRODUK_API_DOCUMENTATION.md"
if (Test-Path $src) {
    $redemp = Join-Path $doc "FEATURE_IMPLEMENTATION\REDEMPTION"
    $dst = Join-Path $redemp "PENUKARAN_PRODUK_API_DOCUMENTATION.md"
    Move-Item $src $dst -Force
    Write-Host "Moved: PENUKARAN_PRODUK_API_DOCUMENTATION.md"
}

Write-Host ""
Write-Host "Done!" -ForegroundColor Green
