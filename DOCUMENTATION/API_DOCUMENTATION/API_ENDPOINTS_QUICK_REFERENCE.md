# 🔗 ADMIN DASHBOARD API REFERENCE - QUICK SHEET

## 📌 BASE URL
```
http://127.0.0.1:8000/api
```

## 🔐 AUTHENTICATION
All endpoints require Bearer token in header:
```
Authorization: Bearer {token}
```

Get token from:
```
POST /login
```

---

## 📊 DASHBOARD ENDPOINTS

### 1️⃣ **DASHBOARD OVERVIEW**
```
GET /admin/dashboard/overview
  ?year=2025&month=12

Returns: Yearly/monthly waste, points, users, redemptions
```

---

### 2️⃣ **USER LIST**
```
GET /admin/dashboard/users
  ?page=1&per_page=10&search=john

Returns: All users with waste history, paginated
```

---

### 3️⃣ **WASTE SUMMARY**
```
GET /admin/dashboard/waste-summary
  ?period=monthly&year=2025&month=12

Parameters:
  period: 'monthly' | 'daily' | 'yearly'
  year: 2025
  month: 1-12 (for daily period)

Returns: Waste by type, totals, chart data
```

---

### 4️⃣ **POINT SUMMARY**
```
GET /admin/dashboard/point-summary
  ?period=monthly&year=2025&month=12

Parameters:
  period: 'monthly' | 'daily' | 'yearly'
  year: 2025
  month: 1-12 (for daily period)

Returns: Points by source (setor_sampah, bonus, etc), totals, chart data
```

---

### 5️⃣ **WASTE BY USER**
```
GET /admin/dashboard/waste-by-user
  ?period=monthly&year=2025&month=12&user_id=1

Parameters:
  period: 'monthly' | 'daily'
  year: 2025
  month: 1-12
  user_id: (optional) Filter by user

Returns: Waste and points per user
```

---

### 6️⃣ **REPORTS**
```
GET /admin/dashboard/report
  ?type=monthly&year=2025&month=12&day=1

Parameters:
  type: 'monthly' | 'daily' (default: monthly)
  year: 2025 (required)
  month: 1-12 (required)
  day: 1-31 (required for daily)

Returns: Comprehensive daily/monthly report
```

---

## 📈 RESPONSE STRUCTURE

All responses follow this format:
```json
{
  "status": "success",
  "data": {
    ...
  }
}
```

Error responses:
```json
{
  "status": "error",
  "message": "Error description",
  "error": "Exception details"
}
```

---

## 📊 KEY DATA FIELDS

### **Overview Response**
```
waste.yearly_total_kg        - Total waste for year (kg)
waste.yearly_total_count     - Number of deposits for year
waste.monthly_total_kg       - Total waste for month (kg)
points.yearly_total          - Total points given for year
points.monthly_total         - Total points given for month
users.total                  - Total users in system
users.active_30days          - Users active in last 30 days
redemptions.yearly_total_points_redeemed - Points redeemed this year
```

### **Waste Summary Response**
```
summary[]                    - Array of waste records
  ├─ jenis_sampah            - Waste type (Kertas, Plastik, etc)
  ├─ total_berat             - Total kg
  ├─ jumlah_setor            - Number of deposits
  ├─ periode_bulan            - "2025-12" format
  └─ periode_tahun            - "2025" format

chart_data[]                 - Formatted data for charting
  ├─ label                   - X-axis label
  ├─ total_berat             - Total for period
  └─ types{}                 - Breakdown by waste type

total_berat                  - Sum of all waste
total_setor                  - Sum of all deposits
```

### **Point Summary Response**
```
summary[]                    - Array of point records
  ├─ source                  - "setor_sampah", "bonus", "tukar_poin", etc
  ├─ total_poin              - Total points
  ├─ jumlah_transaksi        - Count of transactions
  ├─ periode_bulan           - "2025-12" format
  └─ periode_tahun           - "2025" format

chart_data[]                 - Formatted data for charting
  ├─ label                   - X-axis label
  ├─ total_poin              - Total for period
  └─ sources{}               - Breakdown by source

total_poin                   - Sum of all points
total_transaksi             - Sum of all transactions
```

### **User List Response**
```
users[]
  ├─ id                      - User ID
  ├─ nama                    - User name
  ├─ email                   - Email
  ├─ no_hp                   - Phone number
  ├─ total_poin              - Total points accumulated
  ├─ level                   - User level
  ├─ created_at              - Account creation date
  ├─ updated_at              - Last update
  └─ tabung_sampah[]         - Waste deposits
      ├─ id                  - Deposit ID
      ├─ jenis_sampah        - Waste type
      ├─ berat_kg            - Weight
      ├─ status              - pending/approved/rejected
      ├─ poin_didapat        - Points awarded
      └─ created_at          - Deposit date

pagination
  ├─ current_page            - Current page number
  ├─ per_page                - Items per page
  ├─ total                   - Total items
  └─ total_pages             - Number of pages
```

### **Daily Report Response**
```
report_type                  - "daily"
date                         - "2025-12-01"

waste
  ├─ total_kg                - Total waste (kg)
  ├─ total_count             - Number of deposits
  └─ by_type{}
      └─ {type}
          ├─ count           - Deposits of this type
          └─ total_kg        - Weight for this type

points
  ├─ total                   - Total points
  └─ by_source{}
      └─ {source}
          ├─ count           - Transactions of this source
          └─ total_poin      - Points from this source

users_active                 - Number of unique users that day
```

### **Monthly Report Response**
```
report_type                  - "monthly"
month                        - "2025-12"
month_name                   - "December 2025"

waste
  ├─ total_kg                - Total waste (kg)
  ├─ total_count             - Number of deposits
  └─ by_type{}               - Same structure as daily

points
  ├─ total                   - Total points
  └─ by_source{}             - Same structure as daily

users_active                 - Unique users active in month

daily_breakdown{}            - Day-by-day breakdown
  └─ "2025-12-01"
      ├─ waste_kg            - Waste for that day
      └─ waste_count         - Deposits for that day
```

---

## 🎯 COMMON USE CASES

### **Get Current Month Overview**
```
GET /admin/dashboard/overview
```

### **Get Last 12 Months Waste Trend**
```
GET /admin/dashboard/waste-summary?period=monthly&year=2025
```

### **Get Today's Waste Breakdown**
```
GET /admin/dashboard/waste-summary?period=daily&year=2025&month=12
```

### **Get Specific User's December Waste**
```
GET /admin/dashboard/waste-by-user?period=monthly&year=2025&month=12&user_id=1
```

### **Generate December Monthly Report**
```
GET /admin/dashboard/report?type=monthly&year=2025&month=12
```

### **Generate December 1st Daily Report**
```
GET /admin/dashboard/report?type=daily&year=2025&month=12&day=1
```

### **Search Users**
```
GET /admin/dashboard/users?search=john&page=1&per_page=20
```

---

## 💡 FRONTEND IMPLEMENTATION TIPS

1. **Caching**: Cache overview data for 5 minutes to reduce requests
2. **Pagination**: Users endpoint returns paginated results (10 per page default)
3. **Date Filtering**: Always provide year, month defaults to current if not specified
4. **Error Handling**: All failed requests return `status: "error"` with message
5. **Loading States**: Show loading spinner while fetching data
6. **Timestamp Format**: All dates are ISO 8601 format (YYYY-MM-DDTHH:mm:ssZ)
7. **Decimal Numbers**: Waste totals have 2 decimal places (kg), points are integers

---

## 🧪 TESTING ENDPOINTS

Use curl or Postman to test:

```bash
# 1. Login (get token)
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# 2. Test dashboard overview
curl -X GET "http://127.0.0.1:8000/api/admin/dashboard/overview" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# 3. Get users
curl -X GET "http://127.0.0.1:8000/api/admin/dashboard/users" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# 4. Get waste summary
curl -X GET "http://127.0.0.1:8000/api/admin/dashboard/waste-summary?period=monthly" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 📞 TROUBLESHOOTING

| Issue | Solution |
|-------|----------|
| 401 Unauthorized | Check token, make sure it's included in Authorization header |
| 403 Forbidden | User doesn't have admin role |
| Empty results | Check year/month parameters, data might not exist for that period |
| Slow response | Try caching results, data is grouped/aggregated server-side |
| Wrong date format | Use YYYY-MM-DD format, year as 4 digits, month as 1-12 |

---

**API Ready! Your Frontend Agent can now build the dashboard.** ✅

