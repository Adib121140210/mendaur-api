# 📊 Dashboard API Documentation

## ✅ Backend Setup Complete!

All dashboard endpoints are now available and working.

---

## 🎯 Available Dashboard API Endpoints

### 1. **Get User Dashboard Stats**
```
GET http://127.0.0.1:8000/api/dashboard/stats/{userId}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "nama": "Adib Surya",
      "email": "adib@example.com",
      "foto_profil": null,
      "total_poin": 150,
      "total_setor_sampah": 5,
      "level": "Bronze"
    },
    "statistics": {
      "rank": 2,
      "total_users": 3,
      "monthly_poin": 50,
      "monthly_setor": 2,
      "next_level": "Silver",
      "progress_to_next_level": 25.00,
      "poin_needed": 150
    },
    "recent_deposits": [...]
  }
}
```

---

### 2. **Get Leaderboard (Top 10)**
```
GET http://127.0.0.1:8000/api/dashboard/leaderboard
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "rank": 1,
      "id": 2,
      "nama": "Siti Aminah",
      "foto_profil": null,
      "total_poin": 300,
      "total_setor_sampah": 12,
      "level": "Silver"
    },
    {
      "rank": 2,
      "id": 1,
      "nama": "Adib Surya",
      "foto_profil": null,
      "total_poin": 150,
      "total_setor_sampah": 5,
      "level": "Bronze"
    }
  ]
}
```

---

### 3. **Get Global Statistics**
```
GET http://127.0.0.1:8000/api/dashboard/global-stats
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "total_users": 3,
    "total_poin_distributed": 500,
    "total_deposits": 19,
    "total_weight_kg": 45.50,
    "monthly_growth": 25.50,
    "this_month_deposits": 10
  }
}
```

---

## 📊 Level System

The dashboard uses this level progression:

| Level | Min Points | Max Points |
|-------|-----------|-----------|
| Pemula | 0 | 100 |
| Bronze | 100 | 300 |
| Silver | 300 | 600 |
| Gold | 600 | 1000 |
| Platinum | 1000+ | ∞ |

---

## 🧪 Test the Endpoints

### Test in Browser:
1. **User Stats:** http://127.0.0.1:8000/api/dashboard/stats/1
2. **Leaderboard:** http://127.0.0.1:8000/api/dashboard/leaderboard
3. **Global Stats:** http://127.0.0.1:8000/api/dashboard/global-stats

### Test with PowerShell:
```powershell
# Get user stats
Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/dashboard/stats/1' -UseBasicParsing | Select-Object -ExpandProperty Content

# Get leaderboard
Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/dashboard/leaderboard' -UseBasicParsing | Select-Object -ExpandProperty Content

# Get global stats
Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/dashboard/global-stats' -UseBasicParsing | Select-Object -ExpandProperty Content
```

---

## 📝 React Dashboard Component

Copy this to `src/Components/Pages/dashboard/dashboard.jsx`:

See the complete component code in the previous message with:
- ✅ User statistics cards (Poin, Setor, Level, Monthly Poin)
- ✅ Progress bar to next level
- ✅ Recent waste deposits list
- ✅ Leaderboard with top 10 users
- ✅ Global statistics
- ✅ Quick action buttons

---

## 🎨 Features Included

### User Statistics:
- ✅ Total points
- ✅ Total waste deposits
- ✅ Current level
- ✅ User rank (out of total users)
- ✅ Monthly points earned
- ✅ Monthly deposits count
- ✅ Progress to next level (percentage)
- ✅ Points needed to level up

### Leaderboard:
- ✅ Top 10 users by points
- ✅ User profile photo
- ✅ User level badge
- ✅ Highlight current user
- ✅ Gold/Silver/Bronze medals for top 3

### Global Statistics:
- ✅ Total registered users
- ✅ Total points distributed
- ✅ Total waste deposits (approved)
- ✅ Total weight collected (kg)
- ✅ Monthly growth percentage

### Recent Activity:
- ✅ Last 5 waste deposits
- ✅ Status indicators (pending/approved/rejected)
- ✅ Points earned per deposit
- ✅ Date and weight information

---

## ✅ Checklist

Backend:
- [x] DashboardController created
- [x] getUserStats() method
- [x] getLeaderboard() method
- [x] getGlobalStats() method
- [x] Level progression logic
- [x] Routes registered
- [x] Server running

Frontend (Next Steps):
- [ ] Copy Dashboard component code
- [ ] Copy Dashboard CSS
- [ ] Test user stats display
- [ ] Test leaderboard
- [ ] Test global stats
- [ ] Test quick actions

---

## 🚀 Quick Start

1. **Make sure server is running:**
   ```bash
   php artisan serve
   ```

2. **Test an endpoint in browser:**
   ```
   http://127.0.0.1:8000/api/dashboard/stats/1
   ```

3. **Copy the React component** from previous message

4. **Login and navigate to dashboard:**
   ```
   Email: adib@example.com
   Password: password
   ```

5. **See your dashboard come to life!** 🎉

---

## 📈 What the Dashboard Shows

For user **Adib** (ID: 1):
- ✅ Rank: #2 (Siti is #1 with 300 points)
- ✅ Level: Bronze (150/300 points = 50% progress)
- ✅ Needs: 150 more points to reach Silver
- ✅ Recent deposits: Last 5 submissions
- ✅ Badges: Pemula Peduli, Eco Warrior, Bronze Collector

For user **Siti** (ID: 2):
- ✅ Rank: #1 (Top of leaderboard)
- ✅ Level: Silver (300 points)
- ✅ Needs: 300 more points to reach Gold
- ✅ Badges: All 5 badges unlocked

---

**Your Dashboard backend is now complete and ready to use!** 🚀

Just copy the React component code to your frontend and you're done!
