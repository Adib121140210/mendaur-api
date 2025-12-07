#!/bin/bash
# API Verification Script - Post Drop Tables
# Usage: bash verify_api.sh

echo "════════════════════════════════════════════════════════════════"
echo "  API VERIFICATION - POST DROP TABLES"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Start Laravel server in background
echo "Starting Laravel server..."
php artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &
SERVER_PID=$!
sleep 3

echo "✓ Server started (PID: $SERVER_PID)"
echo ""

# Test API endpoints
echo "✓ Testing API Endpoints"
echo "────────────────────────────────────────────────────────────────"
echo ""

# Array of endpoints to test
endpoints=(
    "http://127.0.0.1:8000/api/user/profile"
    "http://127.0.0.1:8000/api/points"
    "http://127.0.0.1:8000/api/products"
    "http://127.0.0.1:8000/api/badges"
)

passed=0
failed=0

for endpoint in "${endpoints[@]}"; do
    echo -n "Testing: $endpoint ... "
    status=$(curl -s -o /dev/null -w "%{http_code}" "$endpoint")

    if [[ $status == "200" ]] || [[ $status == "401" ]]; then
        echo "✅ $status"
        ((passed++))
    else
        echo "❌ $status"
        ((failed++))
    fi
done

echo ""
echo "────────────────────────────────────────────────────────────────"
echo "Results: $passed passed, $failed failed"
echo ""

# Stop server
kill $SERVER_PID 2>/dev/null

echo "✅ API verification complete!"
echo ""
