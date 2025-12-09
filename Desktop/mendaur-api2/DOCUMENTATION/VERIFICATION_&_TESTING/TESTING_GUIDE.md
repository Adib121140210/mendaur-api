# 🎯 Testing Guide - Points & Redemption System

**Status**: Ready for End-to-End Testing  
**Database**: Fresh, Synced, All Systems Go ✅

---

## 🎬 Quick Test Flow (5 minutes)

### Test Scenario 1: Basic Redemption
```
1. Login as Adib Surya
   Email: adib@example.com
   Password: password
   
2. Dashboard shows: Poinmu = 1000 ✅
   
3. Go to "Tukar Poin" page
   
4. See 8 products available ✅
   
5. Click "Tukar" on "Botol Reusable" (150 points)
   
6. Confirm exchange
   
7. Check points: 1000 → 850 ✅
   
8. Check "Riwayat Transaksi": shows pending exchange ✅
```

### Test Scenario 2: Cancel Redemption
```
1. From "Riwayat Transaksi" page
   
2. Find the pending exchange for Botol Reusable
   
3. Click "Batalkan" (Cancel) button
   
4. Confirm cancellation
   
5. Check points: 850 → 1000 (REFUNDED) ✅
   
6. Check status: "pending" → "cancelled" ✅
```

### Test Scenario 3: Multiple Redemptions
```
1. Start with 1000 points
   
2. Exchange Tas Belanja Kain: 1000 → 800 (200 pts deducted)
   
3. Exchange Botol Reusable: 800 → 650 (150 pts deducted)
   
4. Check "Riwayat Transaksi": 2 pending exchanges shown ✅
   
5. Cancel first exchange: 650 → 850 (200 pts refunded)
   
6. Final check: 850 points ✅
```

---

## 🔍 Debug Console Output

### If Points System Works Correctly

Open DevTools (F12) → Console, you should see:

```
✅ Poinmu: 1000
✅ Dashboard stats loaded
✅ Leaderboard rank: 2/3 users
```

### If There's an Issue

Check for these errors:

```
❌ Error: Extracted user points: 0
   → Solution: Check User::find(1) has total_poin = 1000

❌ Error: 401 Unauthorized on /api/profile
   → Solution: Re-login to get valid token

❌ Error: 500 on POST /api/penukaran-produk
   → Solution: Check Laravel logs in storage/logs/
```

---

## 📊 API Testing (Use Postman/cURL)

### Test 1: Get Products
```bash
GET http://127.0.0.1:8000/api/produk

✅ Should return 8 products
✅ Botol Reusable should be in response (150 points)
```

### Test 2: Get User Profile
```bash
GET http://127.0.0.1:8000/api/profile
Header: Authorization: Bearer YOUR_TOKEN

✅ Should return:
{
  "total_poin": 1000,
  "total_sampah": 50,
  "level": "Bronze"
}
```

### Test 3: Create Redemption
```bash
POST http://127.0.0.1:8000/api/penukaran-produk
Header: Authorization: Bearer YOUR_TOKEN
Body: {
  "produk_id": 7,
  "jumlah": 1
}

✅ Should return 201
✅ Should deduct 150 points
```

### Test 4: Get Redemptions
```bash
GET http://127.0.0.1:8000/api/penukaran-produk
Header: Authorization: Bearer YOUR_TOKEN

✅ Should show your redemptions
✅ Status should be "pending"
```

### Test 5: Cancel Redemption
```bash
PUT http://127.0.0.1:8000/api/penukaran-produk/1/cancel
Header: Authorization: Bearer YOUR_TOKEN

✅ Should return 200
✅ Status should change to "cancelled"
✅ Points should be refunded
```

---

## ✅ Verification Checklist

### Database Level
- [ ] Check User 1 has 1000 points
  ```sql
  SELECT total_poin FROM users WHERE id = 1;
  -- Should return: 1000
  ```

- [ ] Check 8 products exist
  ```sql
  SELECT COUNT(*) FROM produks WHERE status = 'tersedia';
  -- Should return: 8
  ```

- [ ] Check no redemptions exist yet
  ```sql
  SELECT COUNT(*) FROM penukaran_produk;
  -- Should return: 0
  ```

### API Level
- [ ] `/api/produk` returns 8 products
- [ ] `/api/dashboard/stats/1` returns 1000 points
- [ ] `/api/leaderboard` shows 3 users
- [ ] Login returns valid auth token

### Frontend Level
- [ ] Dashboard shows "Poinmu: 1000"
- [ ] Tukar Poin page shows 8 products
- [ ] Can click to see product details
- [ ] Can click "Tukar" button

### Points System Level
- [ ] Create exchange: Points decrease
- [ ] Check profile: Updated points shown
- [ ] Cancel exchange: Points refunded
- [ ] Create 2 exchanges: Both work correctly

### Bug Fixes Level
- [ ] Points decrease correctly (not using wrong column)
- [ ] Points display refreshes (not stuck at 1000)
- [ ] Cancel refunds points (not stay at 0)
- [ ] Delete refunds points (not stay at 0)

---

## 🐛 Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| Points show 0 | Database not synced | Run `php setup_data.php` |
| Can't create exchange | Invalid token | Re-login |
| Exchange doesn't deduct points | `$user->refresh()` missing | Already fixed in code |
| Cancel doesn't refund | `cancel()` method missing | Already fixed in code |
| 500 on redemption | Product stock error | Check Produk model |
| 404 on products | No seeded products | Run migrations with seed |

---

## 🚀 Production Checklist

Before going live:

- [ ] ✅ Test redemption works (deducts points)
- [ ] ✅ Test cancel works (refunds points)
- [ ] ✅ Test delete works (refunds points)
- [ ] ✅ Test multiple exchanges
- [ ] ✅ Test points refresh
- [ ] ✅ Test leaderboard ranking
- [ ] ✅ Test history display
- [ ] ✅ Test auth token expiry
- [ ] ✅ Test with different users
- [ ] ✅ Load test concurrent exchanges

---

## 📝 Test Results Template

Copy and fill this after testing:

```
═══════════════════════════════════════
    TESTING RESULTS - [DATE]
═══════════════════════════════════════

SCENARIO 1: Basic Redemption
User: Adib Surya
Initial Points: 1000
Product: Botol Reusable (150 pts)
Final Points: 850
Status: ✅ PASS / ❌ FAIL
Notes: _________________________________

SCENARIO 2: Cancel Redemption
Initial Points: 850
After Cancel: 1000
Status: ✅ PASS / ❌ FAIL
Notes: _________________________________

SCENARIO 3: Multiple Exchanges
Exchange 1: Tas (200 pts) - Status: _____
Exchange 2: Botol (150 pts) - Status: _____
Cancel Exchange 1: Refunded? ✅ YES / ❌ NO
Final Points: _____
Status: ✅ PASS / ❌ FAIL
Notes: _________________________________

OVERALL: ✅ ALL PASS / ⚠️  NEEDS FIXES
```

---

## 📞 Quick Reference

**Test User**: adib@example.com / password  
**API Base**: http://127.0.0.1:8000/api  
**Products**: 8 available (30-500 points each)  
**User Points**: 1000 (enough for any single product)  
**Test Database**: Fresh, all seeders run  

---

## 🎉 Success Criteria

You'll know it's working when:

1. ✅ Login shows 1000 points
2. ✅ See 8 products available
3. ✅ Can create exchange (points decrease)
4. ✅ Can cancel exchange (points refund)
5. ✅ Points display refreshes immediately
6. ✅ History shows all transactions
7. ✅ Leaderboard shows correct ranking
8. ✅ No errors in browser console
9. ✅ No 500 errors in API
10. ✅ All refunds working correctly

---

*Testing guide prepared: November 19, 2025*  
*All systems ready for QA ✅*
