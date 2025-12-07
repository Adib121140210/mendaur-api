$user = \App\Models\User::create([
    'name' => 'Test Badge User',
    'no_hp' => '0812345678',
    'email' => 'badge@test.com',
    'password' => bcrypt('password123'),
]);

$token = $user->createToken('api-token')->plainTextToken;

echo "✅ User Created Successfully!\n";
echo "ID: " . $user->id . "\n";
echo "Email: " . $user->email . "\n";
echo "Token: " . $token . "\n";
