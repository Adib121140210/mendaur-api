# 🚀 Quick Reference - API Poin Changes

## TL;DR - What Changed?

❌ **REMOVED:** `total_poin`  
✅ **ADDED:** `actual_poin`, `display_poin`, `poin_tercatat`

---

## 🔄 Quick Migration Guide

### 1. Update Your API Types
```typescript
// ❌ OLD
interface User {
  total_poin: number;
}

// ✅ NEW
interface User {
  actual_poin: number;      // Use for transactions
  display_poin: number;     // Use for display/leaderboard
}
```

### 2. Update UI Components

#### User Balance Display
```jsx
// ❌ OLD
<div>Saldo: {user.total_poin}</div>

// ✅ NEW
<div>Saldo: {user.actual_poin}</div>
```

#### Withdrawal Validation
```javascript
// ❌ OLD
const canWithdraw = user.total_poin >= amount;

// ✅ NEW
const canWithdraw = user.actual_poin >= amount;
```

#### Leaderboard Display
```jsx
// ❌ OLD
{users.map(user => (
  <LeaderboardItem points={user.total_poin} />
))}

// ✅ NEW
{users.map(user => (
  <LeaderboardItem points={user.display_poin} />
))}
```

---

## 📋 Field Usage Cheat Sheet

| Scenario | Use This Field |
|----------|----------------|
| Show balance in wallet | `actual_poin` |
| Show in user profile | `actual_poin` or `display_poin` |
| Withdrawal validation | `actual_poin` |
| Redemption validation | `actual_poin` |
| Leaderboard ranking | `display_poin` |
| Badge progress | `poin_tercatat` |
| Admin user table | Both `actual_poin` & `display_poin` |

---

## 🎯 Critical Changes by Endpoint

### `/api/auth/login` & `/api/user`
```diff
{
  "user": {
-   "total_poin": 1500
+   "actual_poin": 1500,
+   "display_poin": 1500
  }
}
```

### `/api/admin/users`
```diff
{
  "users": [{
-   "total_poin": 1500
+   "actual_poin": 1500,
+   "display_poin": 1500
  }]
}
```

### `/api/user/badges/progress`
```diff
{
  "user": {
-   "total_poin": 2000
+   "poin_tercatat": 2000
  }
}
```

---

## ⚡ Common Mistakes

### ❌ WRONG
```javascript
// Don't use display_poin for validation
if (user.display_poin >= withdrawalAmount) { ... }

// Don't use actual_poin for badge progress
if (user.actual_poin >= badgeRequirement) { ... }
```

### ✅ CORRECT
```javascript
// Use actual_poin for validation
if (user.actual_poin >= withdrawalAmount) { ... }

// Use poin_tercatat for badge progress
if (user.poin_tercatat >= badgeRequirement) { ... }
```

---

## 🧪 Test Checklist

- [ ] Login response shows `actual_poin` & `display_poin`
- [ ] User profile displays correct balance
- [ ] Withdrawal validates against `actual_poin`
- [ ] Redemption validates against `actual_poin`
- [ ] Leaderboard uses `display_poin`
- [ ] Admin user table shows both fields
- [ ] Badge progress uses `poin_tercatat`

---

## 📞 Need Help?

**Backend Commits:**
- `636a8d5` - Main poin migration
- `d1a665a` - Deployment fixes

**Full Documentation:**
- `FRONTEND_API_CHANGES_REPORT.md` - Complete API changes
- `DATABASE_SCHEMA_CHANGES.md` - Database details

---

## 🔗 Quick Links

- **Base URL:** `https://mendaur.up.railway.app/api`
- **Test Account:** Check with backend team
- **Postman Collection:** (Request from backend team)

---

**Status:** ✅ Deployed & Live  
**Priority:** 🔴 URGENT - Breaking Changes  
**Estimated Frontend Work:** 2-4 hours
