# 🎯 ADMIN DASHBOARD API - QUICK REFERENCE FOR FRONTEND

**Status**: ✅ **READY FOR INTEGRATION**  
**Date**: November 22, 2025  
**Version**: 1.0  

---

## 🔑 Quick Setup

### **1. Get Admin Token**
```javascript
// Login as admin
const response = await fetch('https://api.mendaur.local/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'admin@mendaur.local',
    password: 'admin_password'
  })
});

const data = await response.json();
const adminToken = data.data.token; // Store this token
const userRole = data.data.user.role; // Will be 'admin' or 'user'

// Now you can use adminToken for admin endpoints
```

### **2. Check if User is Admin**
```javascript
// After login, check role
if (user.role === 'admin') {
  // Show admin dashboard
} else {
  // Show user dashboard
}
```

---

## 📡 The 4 Endpoints

### **1️⃣ Stats - System Overview**

**Get system statistics and recent activity**

```javascript
const response = await fetch('https://api.mendaur.local/api/poin/admin/stats', {
  headers: {
    'Authorization': `Bearer ${adminToken}`,
    'Accept': 'application/json'
  }
});

const { data } = await response.json();
// data.total_points_in_system → Number
// data.active_users → Number
// data.total_distributions → Number
// data.recent_activity → Array of activities with user_name
```

**Use for**:
- Display key metrics in dashboard header
- Show recent activity feed
- Display active user count

---

### **2️⃣ History - Transaction Log**

**View all point transactions with filtering**

```javascript
// Example 1: Get first 10 transactions
const response = await fetch(
  'https://api.mendaur.local/api/poin/admin/history?page=1&per_page=10',
  {
    headers: {
      'Authorization': `Bearer ${adminToken}`,
      'Accept': 'application/json'
    }
  }
);
const { data, total, page, total_pages } = await response.json();

// Example 2: Filter by specific user
const response = await fetch(
  'https://api.mendaur.local/api/poin/admin/history?user_id=5',
  { headers: { 'Authorization': `Bearer ${adminToken}` } }
);

// Example 3: Filter by date range
const response = await fetch(
  'https://api.mendaur.local/api/poin/admin/history?start_date=2025-01-01&end_date=2025-01-31',
  { headers: { 'Authorization': `Bearer ${adminToken}` } }
);

// Example 4: Filter by transaction type
const response = await fetch(
  'https://api.mendaur.local/api/poin/admin/history?type=setor_sampah',
  { headers: { 'Authorization': `Bearer ${adminToken}` } }
);

// Each transaction has:
// - id, user_id, user_name (always included!)
// - points (can be positive or negative)
// - source (setor_sampah, bonus, badge, redemption, manual, event)
// - waste_type, weight_kg (if from deposit)
// - description
// - created_at (ISO 8601 format)
```

**Use for**:
- Display transaction history table
- Search by user/date/type
- Export transaction reports
- Show user transaction details

**Query Parameters**:
| Parameter | Type | Default | Notes |
|-----------|------|---------|-------|
| page | number | 1 | Which page to fetch |
| per_page | number | 10 | Results per page |
| user_id | number | - | Filter by user |
| type | string | - | Filter by source (setor_sampah, bonus, etc) |
| start_date | YYYY-MM-DD | - | Filter from date |
| end_date | YYYY-MM-DD | - | Filter to date |

---

### **3️⃣ Redemptions - Product Exchanges**

**View all product redemptions**

```javascript
// Example 1: Get all pending redemptions
const response = await fetch(
  'https://api.mendaur.local/api/poin/admin/redemptions?status=pending',
  { headers: { 'Authorization': `Bearer ${adminToken}` } }
);
const { data, total, page, total_pages } = await response.json();

// Example 2: Get all redemptions from specific user
const response = await fetch(
  'https://api.mendaur.local/api/poin/admin/redemptions?user_id=5',
  { headers: { 'Authorization': `Bearer ${adminToken}` } }
);

// Each redemption has:
// - id, user_id, user_name (always included!)
// - product_id, product_name
// - product_image (FULL URL - ready to display!)
// - points_used, quantity
// - status (pending, approved, cancelled)
// - pickup_method
// - created_at, pickup_date (ISO 8601)
```

**Use for**:
- Display pending redemptions to process
- Show redemption history
- Manage redemption statuses
- Display product images (they're full URLs!)

**Query Parameters**:
| Parameter | Type | Default | Notes |
|-----------|------|---------|-------|
| page | number | 1 | Which page to fetch |
| per_page | number | 8 | Results per page |
| user_id | number | - | Filter by user |
| status | string | - | Filter by status (pending, approved, cancelled) |

---

### **4️⃣ Breakdown - Point Analysis**

**See where points come from**

```javascript
const response = await fetch(
  'https://api.mendaur.local/api/poin/breakdown/all',
  { headers: { 'Authorization': `Bearer ${adminToken}` } }
);

const { data } = await response.json();
// data.total_points → System total
// data.sources → Array of:
//   - source (setor_sampah, bonus, badge, redemption, etc)
//   - total_points
//   - transaction_count
//   - percentage (0-100)
```

**Use for**:
- Create pie chart showing point distribution
- Display source breakdown table
- Show which sources are most active
- Analyze system point flows

---

## 🎨 Common UI Implementations

### **Stats Dashboard Card**
```javascript
// Component: StatCard
import { useEffect, useState } from 'react';

export function StatCard({ adminToken }) {
  const [stats, setStats] = useState(null);

  useEffect(() => {
    fetch('https://api.mendaur.local/api/poin/admin/stats', {
      headers: { 'Authorization': `Bearer ${adminToken}` }
    })
    .then(r => r.json())
    .then(res => setStats(res.data));
  }, []);

  if (!stats) return <div>Loading...</div>;

  return (
    <div className="stats-grid">
      <Card title="Total Points" value={stats.total_points_in_system} />
      <Card title="Active Users" value={stats.active_users} />
      <Card title="Distributions" value={stats.total_distributions} />
    </div>
  );
}
```

### **Transaction History Table**
```javascript
// Component: TransactionTable
import { useState, useEffect } from 'react';

export function TransactionTable({ adminToken }) {
  const [transactions, setTransactions] = useState([]);
  const [page, setPage] = useState(1);
  const [filters, setFilters] = useState({});

  const fetchTransactions = async () => {
    const params = new URLSearchParams({
      page,
      per_page: 10,
      ...filters
    });

    const response = await fetch(
      `https://api.mendaur.local/api/poin/admin/history?${params}`,
      { headers: { 'Authorization': `Bearer ${adminToken}` } }
    );

    const res = await response.json();
    setTransactions(res.data);
  };

  useEffect(() => { fetchTransactions(); }, [page, filters]);

  return (
    <table>
      <thead>
        <tr>
          <th>User Name</th>
          <th>Points</th>
          <th>Source</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        {transactions.map(t => (
          <tr key={t.id}>
            <td>{t.user_name}</td>
            <td>{t.points}</td>
            <td>{t.source}</td>
            <td>{new Date(t.created_at).toLocaleDateString()}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}
```

### **Redemption Status Manager**
```javascript
// Component: RedemptionList
export function RedemptionList({ adminToken }) {
  const [redemptions, setRedemptions] = useState([]);
  const [statusFilter, setStatusFilter] = useState('pending');

  useEffect(() => {
    fetch(
      `https://api.mendaur.local/api/poin/admin/redemptions?status=${statusFilter}`,
      { headers: { 'Authorization': `Bearer ${adminToken}` } }
    )
    .then(r => r.json())
    .then(res => setRedemptions(res.data));
  }, [statusFilter]);

  return (
    <div>
      {redemptions.map(r => (
        <div key={r.id} className="redemption-card">
          <img src={r.product_image} alt={r.product_name} />
          <h3>{r.product_name}</h3>
          <p>User: {r.user_name}</p>
          <p>Points: {r.points_used}</p>
          <span className={`status ${r.status}`}>{r.status}</span>
        </div>
      ))}
    </div>
  );
}
```

### **Point Breakdown Chart**
```javascript
// Component: PointChart
import { PieChart, Pie, Cell } from 'recharts';

export function PointChart({ adminToken }) {
  const [breakdown, setBreakdown] = useState(null);

  useEffect(() => {
    fetch('https://api.mendaur.local/api/poin/breakdown/all', {
      headers: { 'Authorization': `Bearer ${adminToken}` }
    })
    .then(r => r.json())
    .then(res => {
      // Transform for recharts
      const data = res.data.sources.map(s => ({
        name: s.source,
        value: s.percentage
      }));
      setBreakdown(data);
    });
  }, []);

  if (!breakdown) return <div>Loading...</div>;

  return (
    <PieChart width={400} height={400}>
      <Pie data={breakdown} dataKey="value" label>
        {breakdown.map((entry, index) => (
          <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
        ))}
      </Pie>
    </PieChart>
  );
}
```

---

## ⚠️ Important Points

### **Always Include These Headers**
```javascript
{
  'Authorization': `Bearer ${token}`,
  'Accept': 'application/json',
  'Content-Type': 'application/json'
}
```

### **Response Format**
Every response looks like:
```json
{
  "status": "success",
  "data": { /* ... */ },
  "total": 123,
  "page": 1,
  "per_page": 10,
  "total_pages": 13
}
```

### **Error Responses**
```json
{
  "status": "error",
  "message": "Anda tidak memiliki akses ke fitur ini. Hanya admin yang diizinkan."
}
```

Status codes:
- `200` = Success
- `401` = Not authenticated or not admin
- `422` = Invalid input
- `500` = Server error

### **Timestamps**
All dates are ISO 8601 format:
```javascript
const date = new Date('2025-01-15T10:30:00Z');
// Use this directly - JavaScript handles it!
```

### **User Names**
**IMPORTANT**: All responses include `user_name` from the users table. You DON'T need to make separate calls to get user info - it's already included!

### **Product Images**
Product images are **FULL URLs** ready to use:
```javascript
// This is a FULL URL, just use it directly:
<img src={redemption.product_image} alt={redemption.product_name} />

// NOT a relative path, NOT just a filename
// Example: https://api.mendaur.local/storage/products/voucher-1.jpg
```

---

## 🔍 Debugging Tips

### **Getting 401 Unauthorized?**
1. Check token is valid (login again if needed)
2. Check Authorization header is included
3. Check user role is 'admin' (after login, check user.role)

### **Getting empty results?**
1. Check filters are correct
2. Check date format is YYYY-MM-DD
3. Try without filters first
4. Check pagination (page, per_page)

### **Images not loading?**
1. Check product_image URL is complete
2. Verify storage is accessible
3. Check CORS settings if frontend is on different domain

### **Dates showing wrong?**
1. All dates are ISO 8601 UTC
2. Use `new Date(created_at)` directly
3. JavaScript handles timezone conversion

---

## 🚀 Quick Test

```bash
# 1. Copy this curl command and test:
curl -X GET "https://api.mendaur.local/api/poin/admin/stats" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Accept: application/json"

# 2. You should get JSON response with status: "success"

# 3. If you get 401, the token isn't admin:
# Make sure user.level = 'admin' in database
```

---

## 📞 Common Questions

**Q: Do I need to call /api/profile to get user info?**
A: No! Each response includes user_name. You already have it.

**Q: Can I filter by multiple sources at once?**
A: Not currently - use separate requests for each type.

**Q: What's the max per_page?**
A: No limit set, but 100 recommended for performance.

**Q: Can I sort differently (not newest first)?**
A: Currently sorted by created_at DESC. Modify backend if needed.

**Q: Are product images cached?**
A: They're served from Laravel storage. Standard caching applies.

---

## ✨ Success Checklist

Before going live:
- [ ] Admin user can login and gets role: 'admin'
- [ ] Non-admin users get 401 on admin endpoints
- [ ] Stats endpoint returns all 4 fields
- [ ] History endpoint filters work correctly
- [ ] Redemptions show product images as full URLs
- [ ] Breakdown shows percentages
- [ ] Pagination works on all endpoints
- [ ] All timestamps display correctly

---

**Ready to integrate!** 🎉

For more details, see:
- **ADMIN_DASHBOARD_IMPLEMENTATION.md** - Full implementation details
- **TEST_ADMIN_DASHBOARD_API.md** - Complete test cases
- **routes/api.php** - Route definitions
