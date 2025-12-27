<?php
$dsn = 'mysql:host=127.0.0.1;dbname=mendaur_api;charset=utf8mb4';
$pdo = new PDO($dsn, 'root', '');

echo "📊 Updating display_poin to match actual_poin...\n";

$stmt = $pdo->prepare('UPDATE users SET display_poin = actual_poin');
$result = $stmt->execute();

echo "✅ Updated display_poin for all users\n\n";

// Show current state
$stmt = $pdo->prepare('
SELECT user_id, nama, display_poin, actual_poin
FROM users
WHERE user_id IN (24,25,26,27,28,29)
ORDER BY actual_poin DESC
');
$stmt->execute();

printf("%-15s | %-8s | %-11s | %-11s\n", 'Name', 'User ID', 'Display', 'Actual');
echo str_repeat('-', 55) . "\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("%-15s | %-8s | %-11s | %-11s\n",
        substr($row['nama'], 0, 15),
        $row['user_id'],
        $row['display_poin'],
        $row['actual_poin']
    );
}

echo "\n=== PENJELASAN SISTEM BARU ===\n";
echo "display_poin: Untuk leaderboard ranking (bisa direset)\n";
echo "actual_poin:  Untuk transaksi/withdrawal (tidak pernah direset)\n";
echo "Keduanya sekarang sinkron sampai leaderboard direset\n";
?>
