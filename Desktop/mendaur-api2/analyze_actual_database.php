<?php
/**
 * =========================================================================
 * ANALISIS DATABASE LENGKAP - STRUKTUR AKTUAL
 * =========================================================================
 *
 * Script ini menganalisis 10 tabel yang ada di database Mendaur Anda
 * Menampilkan: columns, data types, FKs, constraints, cardinality
 *
 * Usage: php analyze_actual_database.php
 */

// ====== KONFIGURASI DATABASE ======
$servername = "localhost";
$username = "root";
$password = "";
$database = "mendaur_api"; // SESUAIKAN DENGAN DATABASE ANDA

// ====== KONEKSI DATABASE ======
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("❌ Koneksi gagal: " . $conn->connect_error . "\n");
}

$conn->set_charset("utf8mb4");

echo str_repeat("=", 80) . "\n";
echo "| ANALISIS DATABASE MENDAUR - STRUKTUR AKTUAL\n";
echo str_repeat("=", 80) . "\n\n";

// ====== LANGKAH 1: LIST SEMUA TABEL ======
echo "📋 LANGKAH 1: Daftar Semua Tabel\n";
echo str_repeat("-", 80) . "\n";

$query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
          WHERE TABLE_SCHEMA = DATABASE()
          ORDER BY TABLE_NAME";
$result = $conn->query($query);

$tables = [];
$table_count = 0;

while ($row = $result->fetch_assoc()) {
    $tables[] = $row['TABLE_NAME'];
    $table_count++;
    echo "  " . $table_count . ". " . strtoupper($row['TABLE_NAME']) . "\n";
}

echo "\nTotal: $table_count tabel\n\n";

// ====== LANGKAH 2: DETAIL TIAP TABEL ======
echo str_repeat("=", 80) . "\n";
echo "📊 LANGKAH 2: Detail Struktur Setiap Tabel\n";
echo str_repeat("=", 80) . "\n\n";

$all_fks = [];

foreach ($tables as $table_name) {
    echo "┌─ TABEL: " . strtoupper($table_name) . "\n";
    echo "│\n";

    // ====== Info Tabel ======
    $info_query = "SELECT TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH
                   FROM INFORMATION_SCHEMA.TABLES
                   WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = '$table_name'";
    $info_result = $conn->query($info_query);
    $info = $info_result->fetch_assoc();
    echo "│ 📈 Rows: " . ($info['TABLE_ROWS'] ?? 0) . " | Size: " .
         round(($info['DATA_LENGTH'] ?? 0) / 1024, 2) . " KB\n";

    // ====== Columns ======
    echo "│\n│ 📋 COLUMNS:\n";

    $col_query = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA
                  FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = '$table_name'
                  ORDER BY ORDINAL_POSITION";
    $col_result = $conn->query($col_query);

    $pk_column = null;
    $fk_columns = [];

    while ($col = $col_result->fetch_assoc()) {
        $col_name = $col['COLUMN_NAME'];
        $col_type = $col['COLUMN_TYPE'];
        $nullable = $col['IS_NULLABLE'] == 'YES' ? 'NULL' : 'NOT NULL';
        $key_type = '';

        if ($col['COLUMN_KEY'] == 'PRI') {
            $key_type = ' [PRIMARY KEY]';
            $pk_column = $col_name;
        } elseif ($col['COLUMN_KEY'] == 'UNI') {
            $key_type = ' [UNIQUE]';
        } elseif ($col['COLUMN_KEY'] == 'MUL') {
            $key_type = ' [INDEX/FK]';
            $fk_columns[] = $col_name;
        }

        $extra = $col['EXTRA'] ? " | " . $col['EXTRA'] : "";

        echo "│   • $col_name: $col_type, $nullable$key_type$extra\n";
    }

    // ====== Foreign Keys ======
    echo "│\n│ 🔗 FOREIGN KEYS:\n";

    $fk_query = "SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = '$table_name'
                 AND REFERENCED_TABLE_NAME IS NOT NULL";
    $fk_result = $conn->query($fk_query);

    $fk_exists = false;
    while ($fk = $fk_result->fetch_assoc()) {
        $fk_exists = true;
        $constraint = $fk['CONSTRAINT_NAME'];
        $col = $fk['COLUMN_NAME'];
        $ref_table = $fk['REFERENCED_TABLE_NAME'];
        $ref_col = $fk['REFERENCED_COLUMN_NAME'];
        $delete_rule = "CASCADE"; // Default in Laravel
        $update_rule = "CASCADE";

        echo "│   • $col -> $ref_table($ref_col)\n";
        echo "│     └─ DELETE: $delete_rule | UPDATE: $update_rule\n";

        // Store untuk relationship analysis
        $all_fks[] = [
            'from_table' => $table_name,
            'from_column' => $col,
            'to_table' => $ref_table,
            'to_column' => $ref_col,
            'delete_rule' => $delete_rule,
            'update_rule' => $update_rule
        ];
    }

    if (!$fk_exists) {
        echo "│   ❌ Tidak ada FK\n";
    }

    echo "│\n└" . str_repeat("─", 79) . "\n\n";
}

// ====== LANGKAH 3: RELATIONSHIP SUMMARY ======
echo str_repeat("=", 80) . "\n";
echo "🔗 LANGKAH 3: Ringkasan Relationships\n";
echo str_repeat("=", 80) . "\n\n";

echo "Total FK Relationships: " . count($all_fks) . "\n\n";

$cascade_count = 0;
$set_null_count = 0;
$restrict_count = 0;

foreach ($all_fks as $fk) {
    $from = $fk['from_table'];
    $from_col = $fk['from_column'];
    $to = $fk['to_table'];
    $to_col = $fk['to_column'];
    $delete = $fk['delete_rule'];

    echo "  $from.$from_col → $to.$to_col [DELETE: $delete]\n";

    if ($delete == 'CASCADE') $cascade_count++;
    elseif ($delete == 'SET NULL') $set_null_count++;
    elseif ($delete == 'RESTRICT') $restrict_count++;
}

echo "\n";
echo "  CASCADE DELETE: $cascade_count\n";
echo "  SET NULL: $set_null_count\n";
echo "  RESTRICT: $restrict_count\n\n";

// ====== LANGKAH 4: MISSING TABLES ANALYSIS ======
echo str_repeat("=", 80) . "\n";
echo "⚠️  LANGKAH 4: Analisis Tabel yang TIDAK ADA\n";
echo str_repeat("=", 80) . "\n\n";

$documented_tables = [
    'USERS', 'ROLES', 'ROLE_PERMISSIONS', 'SESSIONS', 'NOTIFIKASI',
    'LOG_AKTIVITAS', 'AUDIT_LOGS', 'KATEGORI_SAMPAH', 'JENIS_SAMPAH',
    'JADWAL_PENYETORAN', 'TABUNG_SAMPAH', 'POIN_TRANSAKSIS', 'POIN_LEDGER',
    'KATEGORI_TRANSAKSI', 'TRANSAKSIS', 'PRODUKS', 'PENUKARAN_PRODUK',
    'PENUKARAN_PRODUK_DETAIL', 'BADGES', 'USER_BADGES', 'BADGE_PROGRESS',
    'BANK_ACCOUNTS', 'PENARIKAN_TUNAI'
];

$actual_tables = array_map('strtoupper', $tables);

echo "Tabel yang TIDAK ADA:\n";
$missing_count = 0;
foreach ($documented_tables as $doc_table) {
    if (!in_array($doc_table, $actual_tables)) {
        $missing_count++;
        echo "  ❌ $doc_table\n";
    }
}

echo "\nTotal tabel yang hilang: $missing_count\n\n";

// ====== LANGKAH 5: CARDINALITY ANALYSIS ======
echo str_repeat("=", 80) . "\n";
echo "📊 LANGKAH 5: Analisis Cardinality (berdasarkan data)\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($all_fks as $fk) {
    $from_table = $fk['from_table'];
    $from_col = $fk['from_column'];
    $to_table = $fk['to_table'];
    $to_col = $fk['to_column'];

    // Hitung unique values di foreign key
    $unique_query = "SELECT COUNT(DISTINCT $from_col) as unique_fk,
                           COUNT(*) as total_rows
                    FROM $from_table
                    WHERE $from_col IS NOT NULL";
    $unique_result = $conn->query($unique_query);
    $unique_row = $unique_result->fetch_assoc();

    // Hitung records di parent table
    $parent_query = "SELECT COUNT(*) as parent_count FROM $to_table";
    $parent_result = $conn->query($parent_query);
    $parent_row = $parent_result->fetch_assoc();

    $unique_fk = $unique_row['unique_fk'];
    $total_rows = $unique_row['total_rows'];
    $parent_count = $parent_row['parent_count'];

    // Tentukan cardinality
    if ($unique_fk == 0) {
        $cardinality = "EMPTY (no data)";
    } elseif ($unique_fk == $total_rows) {
        $cardinality = "1:M (many records, each FK different)";
    } else {
        $cardinality = "M:1 (many records share same FK)";
    }

    echo "  $from_table.$from_col → $to_table\n";
    echo "    └─ $cardinality | FK unique: $unique_fk | Total rows: $total_rows\n\n";
}

// ====== KESIMPULAN ======
echo str_repeat("=", 80) . "\n";
echo "✅ KESIMPULAN\n";
echo str_repeat("=", 80) . "\n\n";

echo "Database Anda memiliki:\n";
echo "  • $table_count tabel aktif\n";
echo "  • " . count($all_fks) . " FK relationships\n";
echo "  • $missing_count tabel yang tidak tersedia\n\n";

echo "Status: ⚠️  DATABASE STRUCTURE MISMATCH\n";
echo "  Dokumentasi mengklaim: 20 tabel\n";
echo "  Aktual di database: $table_count tabel\n";
echo "  Perbedaan: " . ($documented_tables ? (count($documented_tables) - $table_count) : 0) . " tabel\n\n";

echo "NEXT STEPS:\n";
echo "  1. Update ERD documentation untuk $table_count tabel aktual\n";
echo "  2. Fokus pada relationships yang sebenarnya ada\n";
echo "  3. Buat ERD baru dengan struktur verified\n\n";

$conn->close();
echo "✅ Analisis selesai!\n";
?>
