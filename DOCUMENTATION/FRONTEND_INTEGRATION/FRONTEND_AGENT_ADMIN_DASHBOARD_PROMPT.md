# 📊 FRONTEND AGENT - ADMIN DASHBOARD PROMPT

You are tasked with building an **Admin Dashboard UI** for the Mendaur waste management system. This dashboard displays comprehensive data analytics about users, waste deposits, and point/money distribution.

---

## 🎯 PROJECT BRIEF

**Project Name:** Mendaur Admin Dashboard  
**Type:** React SPA Component  
**Purpose:** Provide admin staff with real-time insights into system performance  
**Data Source:** RESTful API endpoints (documented below)  
**Target Users:** Admin staff managing waste collection operations  

---

## 📋 DASHBOARD FEATURES

### **Feature 1: User Management Panel**
Display all users in the system with filtering and search capabilities.

**What to Show:**
- User list with pagination
- Search by name or email
- User profile info (nama, email, no_hp)
- Total points user has accumulated
- Total waste deposits count
- User level/status
- User creation date

**UI Requirements:**
- Table format with sortable columns
- Search box for filtering
- Pagination controls
- Action buttons (View Profile, Edit, etc.)

---

### **Feature 2: Waste Summary by Period**
Show total waste collected per month, by waste type, and distribution trends.

**What to Show:**
- Total waste in kg (by month/daily/yearly)
- Waste breakdown by type (Kertas, Plastik, Logam, Kaca, Organik)
- Number of deposits per period
- Chart visualization of waste trends
- Filter options (monthly, daily, yearly)
- Date/period selection

**UI Requirements:**
- Bar/Line chart showing waste trends
- Pie chart for waste type distribution
- Period selector (Monthly, Daily, Yearly)
- Year and month date pickers
- Summary cards showing key metrics (Total kg, Total Deposits)

---

### **Feature 3: Points Distribution by Period**
Show how many points are given out per month, by source (waste deposits, bonuses, badges, etc.)

**What to Show:**
- Total points given per period (monthly/daily)
- Points breakdown by source:
  - `setor_sampah` (from waste deposits)
  - `tukar_poin` (from redemptions)
  - `badge` (from achievements)
  - `bonus` (from admin bonuses)
  - `manual` (from manual adjustments)
- Number of transactions per period
- Average points per transaction
- Chart showing points trend

**UI Requirements:**
- Line/Area chart for points trend
- Stacked bar chart for points by source
- Summary cards (Total Points, Avg per Transaction)
- Period selector (Monthly, Daily)
- Filter by point source

---

### **Feature 4: Waste & Points by User**
Detailed breakdown of waste and points earned for each user.

**What to Show:**
- User name and ID
- Waste deposited (kg) by user and period
- Points earned by user and period
- Waste type breakdown per user
- Number of deposits per user
- Period filter (monthly/daily)

**UI Requirements:**
- Table showing user waste stats
- Filter by user (dropdown or search)
- Date range selector
- Export button for specific user data

---

### **Feature 5: Reports (Daily & Monthly)**
Generate comprehensive reports showing all system activity.

**What to Show - Daily Report:**
- Report date
- Total waste collected (kg)
- Number of deposits
- Approved vs pending count
- Total points distributed
- Points by source breakdown
- Number of active users that day
- Waste type breakdown

**What to Show - Monthly Report:**
- Month and year
- Total waste collected (kg and count)
- Unique users active
- Total points distributed
- Total redemptions (if applicable)
- Total cash withdrawn
- Points breakdown by source
- Daily breakdown (mini chart or collapsible list)

**UI Requirements:**
- Report type selector (Daily/Monthly)
- Date picker for report date
- Report summary section
- Detailed breakdown section
- Export to PDF/Excel buttons
- Print functionality

---

## 🔌 API ENDPOINTS

**Base URL:** `http://127.0.0.1:8000/api`  
**Authentication:** Bearer Token (Sanctum - include token in Authorization header)  
**Admin Middleware:** All endpoints require `admin` role

### **1. Dashboard Overview**
```
GET /admin/dashboard/overview
Query Parameters:
  - year (optional): Year (default: current year)
  - month (optional): Month (default: current month)

Response:
{
  "status": "success",
  "data": {
    "waste": {
      "yearly_total_kg": 250.5,
      "yearly_total_count": 45,
      "monthly_total_kg": 85.25
    },
    "points": {
      "yearly_total": 2500,
      "monthly_total": 450
    },
    "users": {
      "total": 6,
      "active_30days": 4
    },
    "redemptions": {
      "yearly_total_points_redeemed": 800
    }
  }
}
```

---

### **2. User List**
```
GET /admin/dashboard/users
Query Parameters:
  - page (optional): Page number (default: 1)
  - per_page (optional): Items per page (default: 10)
  - search (optional): Search by name or email

Response:
{
  "status": "success",
  "data": {
    "users": [
      {
        "id": 1,
        "nama": "John Doe",
        "email": "john@example.com",
        "no_hp": "08123456789",
        "total_poin": 250,
        "level": "Menengah",
        "created_at": "2025-11-01T10:00:00Z",
        "updated_at": "2025-11-28T15:30:00Z",
        "tabung_sampah": [
          {
            "id": 1,
            "jenis_sampah": "Kertas",
            "berat_kg": 5.5,
            "status": "approved",
            "poin_didapat": 27,
            "created_at": "2025-11-15T09:00:00Z"
          }
        ]
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 6,
      "total_pages": 1
    }
  }
}
```

---

### **3. Waste Summary**
```
GET /admin/dashboard/waste-summary
Query Parameters:
  - period (optional): 'daily', 'monthly', or 'yearly' (default: 'monthly')
  - year (optional): Year (default: current year)
  - month (optional): Month number (default: current month) - used with daily period

Response:
{
  "status": "success",
  "data": {
    "summary": [
      {
        "jenis_sampah": "Kertas",
        "total_berat": 45.5,
        "jumlah_setor": 8,
        "tanggal": "2025-12-01",
        "bulan": 12,
        "tahun": 2025,
        "periode_bulan": "2025-12",
        "periode_tahun": "2025"
      },
      {
        "jenis_sampah": "Plastik",
        "total_berat": 32.3,
        "jumlah_setor": 6,
        "tanggal": "2025-12-01",
        "bulan": 12,
        "tahun": 2025,
        "periode_bulan": "2025-12",
        "periode_tahun": "2025"
      }
    ],
    "chart_data": [
      {
        "label": "2025-12",
        "total_berat": 77.8,
        "types": {
          "Kertas": 45.5,
          "Plastik": 32.3
        }
      }
    ],
    "total_berat": 77.8,
    "total_setor": 14
  }
}
```

---

### **4. Point Summary**
```
GET /admin/dashboard/point-summary
Query Parameters:
  - period (optional): 'daily', 'monthly', or 'yearly' (default: 'monthly')
  - year (optional): Year (default: current year)
  - month (optional): Month number (default: current month) - used with daily period

Response:
{
  "status": "success",
  "data": {
    "summary": [
      {
        "source": "setor_sampah",
        "total_poin": 450,
        "jumlah_transaksi": 12,
        "tanggal": "2025-12-01",
        "bulan": 12,
        "tahun": 2025,
        "periode_bulan": "2025-12",
        "periode_tahun": "2025"
      },
      {
        "source": "bonus",
        "total_poin": 100,
        "jumlah_transaksi": 2,
        "tanggal": "2025-12-01",
        "bulan": 12,
        "tahun": 2025,
        "periode_bulan": "2025-12",
        "periode_tahun": "2025"
      }
    ],
    "chart_data": [
      {
        "label": "2025-12",
        "total_poin": 550,
        "sources": {
          "setor_sampah": 450,
          "bonus": 100
        }
      }
    ],
    "total_poin": 550,
    "total_transaksi": 14
  }
}
```

---

### **5. Waste by User**
```
GET /admin/dashboard/waste-by-user
Query Parameters:
  - period (optional): 'daily' or 'monthly' (default: 'monthly')
  - year (optional): Year (default: current year)
  - month (optional): Month number - used with daily period
  - user_id (optional): Filter by specific user ID

Response:
{
  "status": "success",
  "data": [
    {
      "user_id": 1,
      "user_name": "John Doe",
      "jenis_sampah": "Kertas",
      "total_berat": 25.5,
      "total_poin": 127,
      "jumlah_setor": 5,
      "periode_bulan": "2025-12",
      "tanggal": "2025-12-01"
    },
    {
      "user_id": 1,
      "user_name": "John Doe",
      "jenis_sampah": "Plastik",
      "total_berat": 10.3,
      "total_poin": 103,
      "jumlah_setor": 2,
      "periode_bulan": "2025-12",
      "tanggal": "2025-12-01"
    }
  ]
}
```

---

### **6. Reports (Daily & Monthly)**
```
GET /admin/dashboard/report
Query Parameters:
  - type (optional): 'daily' or 'monthly' (default: 'monthly')
  - year (required): Year
  - month (required): Month number (1-12)
  - day (optional): Day of month - required for daily type (1-31)

Response (Daily):
{
  "status": "success",
  "data": {
    "report_type": "daily",
    "date": "2025-12-01",
    "waste": {
      "total_kg": 77.8,
      "total_count": 14,
      "by_type": {
        "Kertas": {
          "count": 8,
          "total_kg": 45.5
        },
        "Plastik": {
          "count": 6,
          "total_kg": 32.3
        }
      }
    },
    "points": {
      "total": 550,
      "by_source": {
        "setor_sampah": {
          "count": 12,
          "total_poin": 450
        },
        "bonus": {
          "count": 2,
          "total_poin": 100
        }
      }
    },
    "users_active": 4
  }
}

Response (Monthly):
{
  "status": "success",
  "data": {
    "report_type": "monthly",
    "month": "2025-12",
    "month_name": "December 2025",
    "waste": {
      "total_kg": 250.5,
      "total_count": 45,
      "by_type": {
        "Kertas": {
          "count": 20,
          "total_kg": 110.0
        },
        "Plastik": {
          "count": 15,
          "total_kg": 95.5
        },
        "Logam": {
          "count": 10,
          "total_kg": 45.0
        }
      }
    },
    "points": {
      "total": 2500,
      "by_source": {
        "setor_sampah": {
          "count": 40,
          "total_poin": 2200
        },
        "bonus": {
          "count": 5,
          "total_poin": 300
        }
      }
    },
    "users_active": 6,
    "daily_breakdown": {
      "2025-12-01": {
        "waste_kg": 77.8,
        "waste_count": 14
      },
      "2025-12-02": {
        "waste_kg": 45.2,
        "waste_count": 9
      }
    }
  }
}
```

---

## 📝 IMPLEMENTATION NOTES

### **Authentication**
All endpoints require:
```
Authorization: Bearer {token}
Content-Type: application/json
```

Get token from login API:
```
POST /login
{
  "email": "admin@example.com",
  "password": "password"
}
```

### **Error Handling**
All failed responses follow this format:
```json
{
  "status": "error",
  "message": "Descriptive error message",
  "error": "Exception details"
}
```

### **Data Types**
- `total_poin`, `total_berat`: Numbers (decimals with 2 decimal places)
- `jumlah_setor`, `jumlah_transaksi`: Integers (count)
- `periode_bulan`: String format "YYYY-MM"
- `tanggal`: String format "YYYY-MM-DD"
- Timestamps: ISO 8601 format "YYYY-MM-DDTHH:mm:ssZ"

### **Pagination**
User list endpoint supports pagination:
- Default: 10 items per page
- Max page size recommended: 50 items
- Use `current_page` and `total_pages` for navigation

### **Date Filtering**
- All period queries group by month/day/year
- Year defaults to current year
- Month defaults to current month (1-12)
- Day defaults to current day (1-31) for daily reports

---

## 🎨 UI/UX REQUIREMENTS

### **Dashboard Layout**
```
┌─────────────────────────────────────────────────┐
│  ADMIN DASHBOARD - MENDAUR WASTE SYSTEM          │
├─────────────────────────────────────────────────┤
│                                                  │
│  📊 OVERVIEW CARDS                               │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │  Users   │  │  Waste   │  │  Points  │      │
│  │    6     │  │ 250.5kg  │  │  2500    │      │
│  └──────────┘  └──────────┘  └──────────┘      │
│                                                  │
│  📈 CHARTS & ANALYTICS                           │
│  ┌────────────────────────────────────────────┐ │
│  │  Waste Trend (Monthly)         [Chart]     │ │
│  └────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────┐ │
│  │  Points Distribution          [Chart]      │ │
│  └────────────────────────────────────────────┘ │
│                                                  │
│  👥 USER MANAGEMENT                              │
│  ┌────────────────────────────────────────────┐ │
│  │  [Search] [Filter] [Export]                │ │
│  │  ┌──────────────────────────────────────┐  │ │
│  │  │ User | Email | Points | Level | ... │  │ │
│  │  │ John │ j@... │  250   │ Mid   │ ... │  │ │
│  │  │ Jane │ j@... │  380   │ High  │ ... │  │ │
│  │  └──────────────────────────────────────┘  │ │
│  │  [Prev] Page 1/1 [Next]                    │ │
│  └────────────────────────────────────────────┘ │
│                                                  │
│  📋 REPORTS                                      │
│  [Daily] [Monthly] | Date: [Picker] [Generate] │
│  ┌────────────────────────────────────────────┐ │
│  │  Report Summary                            │ │
│  │  - Total Waste: 77.8 kg                    │ │
│  │  - Deposits: 14                            │ │
│  │  - Points Given: 550                       │ │
│  │  [Export PDF] [Export Excel] [Print]       │ │
│  └────────────────────────────────────────────┘ │
│                                                  │
└─────────────────────────────────────────────────┘
```

### **Color Scheme**
- Primary: Green (#10B981) - Represents environmental theme
- Secondary: Blue (#3B82F6) - For data visualization
- Success: Green (#22C55E)
- Warning: Yellow (#F59E0B)
- Danger: Red (#EF4444)
- Neutral: Gray (#6B7280)

### **Charts**
- Use Chart.js, D3.js, or Recharts
- Line charts for trends (waste kg per month, points per month)
- Pie/Doughnut charts for distribution (waste by type, points by source)
- Bar charts for comparisons (user stats, daily breakdown)
- All charts should be responsive

### **Responsive Design**
- Desktop: Full dashboard view
- Tablet: Stacked sections, 2-column layout
- Mobile: Single column, collapsible sections

---

## 🔄 REAL-TIME CONSIDERATIONS

- Implement auto-refresh (optional, every 5-10 minutes)
- Cache API responses to reduce server load
- Show loading states during data fetch
- Display timestamps of last update
- Handle network errors gracefully

---

## ✅ DELIVERABLES

1. ✅ Dashboard component (main component)
2. ✅ Child components (Overview, Users, Waste, Points, Reports)
3. ✅ API service/hooks for data fetching
4. ✅ State management (Redux, Context, or Vuex)
5. ✅ Responsive CSS/styling
6. ✅ Error handling
7. ✅ Loading states
8. ✅ Documentation (README.md)

---

## 📞 SUPPORT

**API Endpoint:** http://127.0.0.1:8000/api  
**Admin Token:** Required (Bearer authentication)  
**Database Tables:** users, tabung_sampah, poin_transaksis, penukaran_produk  
**Backend Status:** ✅ Fully implemented and ready

**Questions or Issues?**
- Check API responses for error details
- Verify token is included in requests
- Ensure all required query parameters are provided
- Check backend logs for server-side errors

---

## 🚀 QUICK START

1. **Get Auth Token:**
   ```bash
   curl -X POST http://127.0.0.1:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"admin@example.com","password":"password"}'
   ```

2. **Test Dashboard Overview:**
   ```bash
   curl -X GET "http://127.0.0.1:8000/api/admin/dashboard/overview" \
     -H "Authorization: Bearer {token}"
   ```

3. **Fetch Users:**
   ```bash
   curl -X GET "http://127.0.0.1:8000/api/admin/dashboard/users" \
     -H "Authorization: Bearer {token}"
   ```

4. **Get Waste Summary (Monthly):**
   ```bash
   curl -X GET "http://127.0.0.1:8000/api/admin/dashboard/waste-summary?period=monthly" \
     -H "Authorization: Bearer {token}"
   ```

---

**Good luck building an amazing admin dashboard! 🎉**

