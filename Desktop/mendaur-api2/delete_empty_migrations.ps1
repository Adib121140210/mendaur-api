$migrationsPath = "c:\Users\Adib\OneDrive\Desktop\mendaur-api\database\migrations"

$emptyFiles = @(
    "2025_11_28_100001_standardize_users_columns.php",
    "2025_11_28_100002_standardize_kategori_sampah_columns.php",
    "2025_11_28_100003_standardize_jenis_sampah_columns.php",
    "2025_11_28_100004_standardize_tabung_sampah_columns.php",
    "2025_11_28_100005_standardize_produk_columns.php",
    "2025_11_28_100006_standardize_penukaran_produk_columns.php",
    "2025_11_28_100007_standardize_penarikan_tunai_columns.php",
    "2025_11_28_100008_standardize_badges_columns.php",
    "2025_11_28_100009_standardize_artikels_columns.php",
    "2025_11_28_100010_standardize_log_poin_columns.php",
    "2025_11_28_100011_standardize_log_user_activity_columns.php"
)

Write-Host "Deleting empty migration files..." -ForegroundColor Yellow
$deleted = 0

foreach ($file in $emptyFiles) {
    $path = Join-Path $migrationsPath $file
    if (Test-Path $path) {
        Remove-Item $path -Force
        Write-Host ("Deleted: " + $file)
        $deleted++
    }
}

Write-Host ""
Write-Host ("Total deleted: " + $deleted + " files") -ForegroundColor Green
Write-Host ""
Write-Host "Empty migrations cleaned up!" -ForegroundColor Green
