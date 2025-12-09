<?php
/**
 * Script untuk Verifikasi Tabel PENUKARAN_PRODUK_DETAIL
 *
 * Jalankan: php check_penukaran_produk_detail.php
 */

// ============================================
// KONEKSI DATABASE
// ============================================

$servername = "localhost";  // Sesuaikan dengan server Anda
$username = "root";         // Sesuaikan dengan username
$password = "";             // Sesuaikan dengan password
$database = "mendaur";      // Sesuaikan dengan nama database

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=".str_repeat("=", 78)."=\n";
echo "| VERIFIKASI TABEL PENUKARAN_PRODUK_DETAIL                                    |\n";
echo "=".str_repeat("=", 78)."=\n\n";

// ============================================
// CARI SEMUA TABEL YANG ADA
// ============================================

echo "📋 LANGKAH 1: Cek Semua Tabel yang Ada\n";
echo str_repeat("-", 80)."\n";

$query = "SHOW TABLES";
$result = $conn->query($query);

if ($result) {
    $tables = [];
    while($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    echo "Total tabel ditemukan: ".count($tables)."\n";
    echo "Daftar tabel:\n";
    foreach($tables as $i => $table) {
        echo "  ".($i+1).". ".$table."\n";
    }
} else {
    echo "Error: " . $conn->error;
}

echo "\n";

// ============================================
// CARI TABEL YANG MENGANDUNG 'PENUKARAN'
// ============================================

echo "🔍 LANGKAH 2: Cek Tabel yang Mengandung 'PENUKARAN'\n";
echo str_repeat("-", 80)."\n";

$query = "SHOW TABLES LIKE '%penukaran%'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "✅ Tabel dengan 'penukaran' ditemukan:\n";
    while($row = $result->fetch_row()) {
        echo "   - ".$row[0]."\n";
    }
} else {
    echo "❌ Tidak ada tabel yang mengandung 'penukaran'\n";
}

echo "\n";

// ============================================
// CARI SPESIFIK TABEL PENUKARAN_PRODUK_DETAIL
// ============================================

echo "🎯 LANGKAH 3: Cek Tabel PENUKARAN_PRODUK_DETAIL Secara Spesifik\n";
echo str_repeat("-", 80)."\n";

$table_name = "penukaran_produk_detail";

$query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
          WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '".$table_name."'";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "✅ TABEL DITEMUKAN: ".$table_name."\n\n";

    // ============================================
    // JIKA ADA, TAMPILKAN STRUKTUR
    // ============================================

    echo "📊 LANGKAH 4: Struktur Tabel PENUKARAN_PRODUK_DETAIL\n";
    echo str_repeat("-", 80)."\n";

    $query = "DESCRIBE ".$table_name;
    $result = $conn->query($query);

    if ($result) {
        echo sprintf("%-20s %-20s %-15s %-10s %-30s\n", "Field", "Type", "Null", "Key", "Extra");
        echo str_repeat("-", 95)."\n";

        while($row = $result->fetch_assoc()) {
            echo sprintf("%-20s %-20s %-15s %-10s %-30s\n",
                $row['Field'],
                $row['Type'],
                $row['Null'],
                $row['Key'],
                $row['Extra']
            );
        }
    }

    echo "\n";

    // ============================================
    // TAMPILKAN FOREIGN KEYS
    // ============================================

    echo "🔗 LANGKAH 5: Foreign Keys Tabel PENUKARAN_PRODUK_DETAIL\n";
    echo str_repeat("-", 80)."\n";

    $query = "SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
              FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '".$table_name."'
              AND REFERENCED_TABLE_NAME IS NOT NULL";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "  - FK: ".$row['COLUMN_NAME']." → ".$row['REFERENCED_TABLE_NAME'].".".$row['REFERENCED_COLUMN_NAME']."\n";
        }
    } else {
        echo "  (Tidak ada foreign keys)\n";
    }

    echo "\n";

    // ============================================
    // TAMPILKAN JUMLAH RECORDS
    // ============================================

    echo "📈 LANGKAH 6: Jumlah Records\n";
    echo str_repeat("-", 80)."\n";

    $query = "SELECT COUNT(*) as total_records FROM ".$table_name;
    $result = $conn->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        echo "  Total records: ".$row['total_records']."\n";
    }

} else {
    echo "❌ TABEL TIDAK DITEMUKAN: ".$table_name."\n\n";

    // ============================================
    // JIKA TIDAK ADA, CEK TABEL PENUKARAN_PRODUK
    // ============================================

    echo "🔄 LANGKAH 4: Cek Tabel PENUKARAN_PRODUK (Tanpa _DETAIL)\n";
    echo str_repeat("-", 80)."\n";

    $table_name_alt = "penukaran_produk";

    $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
              WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '".$table_name_alt."'";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo "✅ Tabel yang ada: ".$table_name_alt."\n\n";

        echo "📊 Struktur Tabel PENUKARAN_PRODUK:\n";
        echo str_repeat("-", 80)."\n";

        $query = "DESCRIBE ".$table_name_alt;
        $result = $conn->query($query);

        if ($result) {
            echo sprintf("%-25s %-25s %-15s %-10s %-20s\n", "Field", "Type", "Null", "Key", "Extra");
            echo str_repeat("-", 95)."\n";

            while($row = $result->fetch_assoc()) {
                echo sprintf("%-25s %-25s %-15s %-10s %-20s\n",
                    $row['Field'],
                    $row['Type'],
                    $row['Null'],
                    $row['Key'],
                    $row['Extra']
                );
            }
        }

        echo "\n";
        echo "💡 INSIGHT:\n";
        echo "   Tabel PENUKARAN_PRODUK_DETAIL TIDAK ADA di database.\n";
        echo "   Kemungkinan besar, detail items disimpan langsung di tabel PENUKARAN_PRODUK.\n";
        echo "   (kolom 'jumlah' ada di PENUKARAN_PRODUK, bukan di tabel junction terpisah)\n";

    } else {
        echo "❌ Tabel PENUKARAN_PRODUK juga TIDAK DITEMUKAN\n";
    }
}

echo "\n";

// ============================================
// KESIMPULAN
// ============================================

echo "=".str_repeat("=", 78)."=\n";
echo "| KESIMPULAN                                                                   |\n";
echo "=".str_repeat("=", 78)."=\n";

$query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
          WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penukaran_produk_detail'";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "\n✅ PENUKARAN_PRODUK_DETAIL BENAR ADA di database\n";
    echo "   → Update file dokumentasi: TIDAK PERLU (sudah benar)\n";
    echo "   → Struktur tetap: 20 tabel, 28+ relationships\n";
} else {
    echo "\n❌ PENUKARAN_PRODUK_DETAIL TIDAK ADA di database\n";
    echo "   → Update file dokumentasi: PERLU DIPERBAHARUI\n";
    echo "   → Struktur baru: 19 tabel, 26 relationships\n";
    echo "   → Files yang perlu diupdate:\n";
    echo "      1. ERD_QUICK_REFERENCE_PRINT.md\n";
    echo "      2. TABEL_DATABASE_MENDAUR_LENGKAP.md\n";
    echo "      3. ERD_RELATIONSHIP_LIST_DAN_URUTAN_PEMBUATAN.md\n";
    echo "      4. FK_CONSTRAINTS_DETAILED_EXPLANATION.md\n";
    echo "      5. Dan file referensi lainnya\n";
}

echo "\n";

$conn->close();
?>
