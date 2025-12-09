<?php
// Simple test to show what data should be returned for each user
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\TabungSampah;
use App\Models\User;

echo "===  ENDPOINT BEHAVIOR TEST ===\n\n";

echo "SCENARIO 1: User 3 (Adib Surya) calls /users/3/tabung-sampah\n";
echo "Expected: 1 record (Deposit ID 3)\n";
$deposits = TabungSampah::where('user_id', 3)->get();
echo "Actual: " . $deposits->count() . " records\n";
foreach ($deposits as $d) {
    echo "  - ID: {$d->id}, User: {$d->user->nama}\n";
}

echo "\nSCENARIO 2: User 3 (Adib Surya) calls /users/5/tabung-sampah\n";
echo "Expected: 403 Forbidden error\n";
echo "Reason: Cannot access other user's data\n";

echo "\nSCENARIO 3: User 3 calls /tabung-sampah (list all)\n";
$all = TabungSampah::get();
echo "Data returned: " . $all->count() . " records\n";
foreach ($all as $d) {
    echo "  - ID: {$d->id}, User ID: {$d->user_id}, User: {$d->user->nama}\n";
}
echo "⚠ IF FRONTEND IS USING THIS ENDPOINT, they see ALL users' data!\n";

echo "\n=== RECOMMENDATION ===\n";
echo "Frontend should call: GET /api/users/{userId}/tabung-sampah\n";
echo "Where {userId} = authenticated user's ID\n";
echo "NOT: GET /api/tabung-sampah (this returns all records)\n";
