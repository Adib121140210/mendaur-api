# 🔧 PANDUAN MIGRASI FRONTEND - API FIELD CHANGES

## 📋 **MAPPING FIELD LAMA KE BARU**

### User Object Changes

```json
{
  "// FIELD CHANGES": "total_poin → actual_poin + display_poin",
  
  "OLD_RESPONSE": {
    "user_id": 1,
    "nama": "John Doe", 
    "email": "john@example.com",
    "total_poin": 15000,  "// ❌ DIHAPUS"
    "level": "Gold"
  },
  
  "NEW_RESPONSE": {
    "user_id": 1,
    "nama": "John Doe",
    "email": "john@example.com", 
    "actual_poin": 15000,   "// ✅ UNTUK SALDO/TRANSAKSI"
    "display_poin": 15000,  "// ✅ UNTUK LEADERBOARD/RANKING"
    "level": "Gold"
  }
}
```

---

## 🎯 **FIND & REPLACE GUIDE**

### 1. TypeScript Interfaces

```typescript
// ❌ FIND AND DELETE
interface User {
  total_poin: number;
}

// ✅ REPLACE WITH
interface User {
  actual_poin: number;   // For balance/transactions
  display_poin: number;  // For leaderboard ranking
}
```

### 2. Balance Display Components

```typescript
// ❌ FIND THESE PATTERNS:
user.total_poin
userData.total_poin
profile.total_poin
balance = user.total_poin

// ✅ REPLACE WITH:
user.actual_poin
userData.actual_poin  
profile.actual_poin
balance = user.actual_poin
```

### 3. Transaction Validation

```typescript
// ❌ FIND:
if (user.total_poin >= amount)
user.total_poin < withdrawAmount
balance = user.total_poin

// ✅ REPLACE WITH:
if (user.actual_poin >= amount)
user.actual_poin < withdrawAmount  
balance = user.actual_poin
```

### 4. State Management (Redux/Zustand)

```typescript
// ❌ FIND:
state.user.total_poin
updateUserPoin(total_poin: number)
{ ...user, total_poin: newAmount }

// ✅ REPLACE WITH:
state.user.actual_poin
updateUserPoin(actual_poin: number, display_poin: number)
{ ...user, actual_poin: newAmount, display_poin: newRanking }
```

---

## 📱 **COMPONENT UPDATES**

### UserProfile.tsx
```tsx
// ❌ OLD
<div>Saldo: {user.total_poin} poin</div>

// ✅ NEW  
<div>Saldo: {user.actual_poin} poin</div>
```

### LeaderboardCard.tsx
```tsx
// ✅ TIDAK PERLU UBAH - Backend sudah handle
<div>Poin: {user.total_poin}</div>  // Backend map display_poin ke total_poin
```

### WithdrawForm.tsx
```tsx
// ❌ OLD
const maxAmount = user.total_poin;
const canWithdraw = user.total_poin >= amount;

// ✅ NEW
const maxAmount = user.actual_poin;
const canWithdraw = user.actual_poin >= amount;
```

### ProductExchange.tsx
```tsx
// ❌ OLD
const userBalance = user.total_poin;
const insufficient = user.total_poin < productPrice;

// ✅ NEW
const userBalance = user.actual_poin;
const insufficient = user.actual_poin < productPrice;
```

---

## 🔍 **SEARCH PATTERNS**

### VS Code Find & Replace

**Pattern 1**: `\.total_poin`
**Replace**: `.actual_poin`

**Pattern 2**: `"total_poin"`  
**Replace**: `"actual_poin"`

**Pattern 3**: `total_poin:`
**Replace**: `actual_poin:`

**Pattern 4**: `{total_poin}`
**Replace**: `{actual_poin}`

---

## ⚡ **TESTING SCENARIOS**

### Scenario 1: User Balance
```typescript
// Test user dengan actual_poin: 15000, display_poin: 0 (setelah reset)
expect(userBalance).toBe(user.actual_poin); // Should be 15000
expect(userRanking).toBe(user.display_poin); // Should be 0
```

### Scenario 2: Transaction Flow
```typescript
// Test withdrawal dengan actual_poin
const canWithdraw = user.actual_poin >= 10000;
expect(canWithdraw).toBe(true); // User masih bisa withdraw
```

### Scenario 3: Leaderboard Reset
```typescript
// After admin reset
expect(leaderboard[0].total_poin).toBe(0); // All rankings reset
expect(user.actual_poin).toBe(15000); // But balance preserved
```

---

## 🚨 **CRITICAL WARNINGS**

### ⚠️ DO NOT CHANGE:
- Leaderboard API response format (backend sudah handle)
- Authentication headers format
- Admin endpoint URLs

### ⚠️ MUST CHANGE:
- All `total_poin` references untuk balance/transactions
- User profile display components
- Transaction validation logic
- State management schemas

---

## 📞 **VERIFICATION CHECKLIST**

- [ ] ✅ User profile shows correct balance (`actual_poin`)
- [ ] ✅ Withdrawal uses `actual_poin` for validation
- [ ] ✅ Product exchange uses `actual_poin` for validation  
- [ ] ✅ Leaderboard ranking still displays correctly
- [ ] ✅ After admin reset: balance preserved, ranking reset
- [ ] ✅ New point earnings update both fields correctly

---

## 🎯 **ROLLBACK PLAN**

Jika ada masalah critical, backend bisa temporary add `total_poin` field alias:

```sql
-- Emergency rollback (temporary)
ALTER TABLE users ADD COLUMN total_poin INT AS (actual_poin) VIRTUAL;
```

**Tapi lebih baik fix frontend sesuai dokumentasi ini! 🚀**
