<?php
// Test script to verify admin user setup

$conn = new mysqli(
    '127.0.0.1',
    'root',
    '',
    'mendaur_api'
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check admin user
echo "=== ADMIN USER CHECK ===\n";
$result = $conn->query("SELECT u.id, u.nama, u.email, u.role_id, r.nama_role FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email='admin@test.com'");
if ($result && $row = $result->fetch_assoc()) {
    echo "✓ Admin found:\n";
    echo "  ID: " . $row['id'] . "\n";
    echo "  Name: " . $row['nama'] . "\n";
    echo "  Email: " . $row['email'] . "\n";
    echo "  Role ID: " . $row['role_id'] . "\n";
    echo "  Role Name: " . ($row['nama_role'] ?: 'NULL - PROBLEM!') . "\n";
} else {
    echo "✗ Admin user not found\n";
}

// Check roles table
echo "\n=== ROLES TABLE ===\n";
$result = $conn->query("SELECT id, nama_role, level_akses FROM roles ORDER BY id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID {$row['id']}: {$row['nama_role']} (level {$row['level_akses']})\n";
    }
}

// Check role_permissions for admin
echo "\n=== ADMIN PERMISSIONS ===\n";
$result = $conn->query("SELECT COUNT(*) as count FROM role_permissions WHERE role_id=2");
if ($result && $row = $result->fetch_assoc()) {
    echo "Admin role has {$row['count']} permissions\n";
}

$conn->close();
echo "\n✓ Check complete\n";
