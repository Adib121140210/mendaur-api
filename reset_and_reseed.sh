#!/bin/bash
# Reset Database & Reseed with Corrected Data

echo "========================================="
echo "RESET DATABASE & RESEED (CORRECTED DATA)"
echo "========================================="
echo ""

# Step 1: Fresh migrate
echo "Step 1: Running fresh migration..."
php artisan migrate:fresh --seed

# Step 2: Run specific user seeder again
echo ""
echo "Step 2: Running UserSeeder specifically..."
php artisan db:seed --class=UserSeeder

# Step 3: Verify data
echo ""
echo "Step 3: Verifying seed data..."
php verify_user_seed.php

echo ""
echo "========================================="
echo "RESET COMPLETE!"
echo "========================================="
