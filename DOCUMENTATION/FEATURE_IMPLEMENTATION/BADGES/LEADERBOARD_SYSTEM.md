# 🏆 Leaderboard System - Complete Documentation

## ✅ System Overview

The Leaderboard System ranks users based on their performance metrics: total points, deposit count, or badge achievements. Users can compete and see where they stand among other community members.

---

## 🎯 **Features**

✅ **Multiple Ranking Types**:
- 🏅 **By Points** (`type=poin`) - Rank by total_poin (default)
- ♻️ **By Deposits** (`type=setor`) - Rank by total_setor_sampah
- 🎖️ **By Badges** (`type=badge`) - Rank by badge count

✅ **Flexible Limits**:
- Default: Top 10 users
- Customizable: 1-50 users
- Prevents performance issues with max limit

✅ **Rich User Data**:
- Rank position (1, 2, 3, etc.)
- User profile info
- All stats (points, deposits, level)
- Badge count

---

## 🔌 **API Endpoint**

### **GET** `/api/dashboard/leaderboard`

Get the top-ranked users based on selected criteria.

#### **Query Parameters:**

| Parameter | Type | Default | Values | Description |
|-----------|------|---------|--------|-------------|
| `limit` | integer | 10 | 1-50 | Number of users to return |
| `type` | string | poin | `poin`, `setor`, `badge` | Ranking criteria |

---

## 📊 **Response Format**

### **Success Response (200 OK):**

```json
{
  "status": "success",
  "data": [
    {
      "rank": 1,
      "user_id": 2,
      "nama": "Siti Aminah",
      "foto_profil": null,
      "total_poin": 300,
      "total_setor_sampah": 12,
      "level": "Silver",
      "badge_count": 5
    },
    {
      "rank": 2,
      "user_id": 1,
      "nama": "Adib Surya",
      "foto_profil": null,
      "total_poin": 150,
      "total_setor_sampah": 5,
      "level": "Bronze",
      "badge_count": 3
    },
    {
      "rank": 3,
      "user_id": 3,
      "nama": "Budi Santoso",
      "foto_profil": null,
      "total_poin": 50,
      "total_setor_sampah": 2,
      "level": "Pemula",
      "badge_count": 1
    }
  ]
}
```

### **Error Response (400 Bad Request):**

```json
{
  "status": "error",
  "message": "Invalid type. Must be: poin, setor, or badge"
}
```

---

## 🧪 **API Examples**

### 1. **Get Top 10 by Points (Default)**

```bash
GET http://127.0.0.1:8000/api/dashboard/leaderboard
```

**Response:**
- Rank 1: Highest total_poin
- Rank 2: Second highest total_poin
- ... up to 10 users

---

### 2. **Get Top 5 by Deposits**

```bash
GET http://127.0.0.1:8000/api/dashboard/leaderboard?type=setor&limit=5
```

**Response:**
- Rank 1: Most deposits (total_setor_sampah)
- Rank 2: Second most deposits
- ... up to 5 users

---

### 3. **Get Top 20 by Badges**

```bash
GET http://127.0.0.1:8000/api/dashboard/leaderboard?type=badge&limit=20
```

**Response:**
- Rank 1: Most badges unlocked
- Rank 2: Second most badges
- ... up to 20 users

---

### 4. **Invalid Type (Error)**

```bash
GET http://127.0.0.1:8000/api/dashboard/leaderboard?type=invalid
```

**Response (400):**
```json
{
  "status": "error",
  "message": "Invalid type. Must be: poin, setor, or badge"
}
```

---

## 💻 **PowerShell Testing**

```powershell
# Test 1: Default leaderboard (by points, top 10)
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/dashboard/leaderboard' | ConvertTo-Json -Depth 10

# Test 2: By deposits (top 5)
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/dashboard/leaderboard?type=setor&limit=5' | ConvertTo-Json -Depth 10

# Test 3: By badges (top 3)
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/dashboard/leaderboard?type=badge&limit=3' | ConvertTo-Json -Depth 10

# Test 4: Custom limit (top 2)
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/dashboard/leaderboard?limit=2' | ConvertTo-Json -Depth 10
```

---

## 🔍 **Implementation Details**

### **Database Query Logic:**

```php
// Join with user_badges to count badges
User::select('users.*')
    ->selectRaw('COALESCE(COUNT(user_badges.id), 0) as badge_count')
    ->leftJoin('user_badges', 'users.id', '=', 'user_badges.user_id')
    ->groupBy('users.id');

// Order by selected type
switch ($type) {
    case 'poin':
        $query->orderBy('total_poin', 'desc');
        break;
    case 'setor':
        $query->orderBy('total_setor_sampah', 'desc');
        break;
    case 'badge':
        $query->orderByRaw('COUNT(user_badges.id) DESC');
        break;
}

// Apply limit (max 50)
$query->limit(min($limit, 50));
```

### **Ranking Logic:**

- Rank is calculated sequentially (1, 2, 3, ...)
- Based on array index after sorting
- No tie-breaking logic (users with same values keep order)

---

## 📱 **Frontend Integration**

### **React Example:**

```javascript
// Fetch leaderboard
const fetchLeaderboard = async (type = 'poin', limit = 10) => {
  const response = await fetch(
    `http://127.0.0.1:8000/api/dashboard/leaderboard?type=${type}&limit=${limit}`
  );
  const data = await response.json();
  return data.data;
};

// Display leaderboard
const Leaderboard = () => {
  const [leaders, setLeaders] = useState([]);
  const [type, setType] = useState('poin');

  useEffect(() => {
    fetchLeaderboard(type, 10).then(setLeaders);
  }, [type]);

  return (
    <div>
      <h2>🏆 Leaderboard</h2>
      
      {/* Filter buttons */}
      <div>
        <button onClick={() => setType('poin')}>By Points</button>
        <button onClick={() => setType('setor')}>By Deposits</button>
        <button onClick={() => setType('badge')}>By Badges</button>
      </div>

      {/* Leaderboard table */}
      <table>
        <thead>
          <tr>
            <th>Rank</th>
            <th>Name</th>
            <th>Level</th>
            <th>Points</th>
            <th>Deposits</th>
            <th>Badges</th>
          </tr>
        </thead>
        <tbody>
          {leaders.map((user) => (
            <tr key={user.user_id}>
              <td>
                {user.rank === 1 && '🥇'}
                {user.rank === 2 && '🥈'}
                {user.rank === 3 && '🥉'}
                {user.rank > 3 && `#${user.rank}`}
              </td>
              <td>{user.nama}</td>
              <td>{user.level}</td>
              <td>{user.total_poin}</td>
              <td>{user.total_setor_sampah}</td>
              <td>{user.badge_count}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};
```

---

## 🎨 **UI/UX Suggestions**

### **Rank Display:**
```
🥇 #1 - Gold medal (first place)
🥈 #2 - Silver medal (second place)
🥉 #3 - Bronze medal (third place)
#4, #5, ... - Regular numbering
```

### **Highlight Current User:**
- If current user is in top 10, highlight their row
- Show "You" badge next to their name
- Different background color

### **Show User's Position:**
- If user is not in top 10, show their rank separately
- Example: "Your rank: #24 out of 150 users"

### **Tab Navigation:**
```
[ By Points ] [ By Deposits ] [ By Badges ]
  (active)      (inactive)      (inactive)
```

---

## 📊 **Test Results**

### **Actual Test Data:**

| Rank | Name | Points | Deposits | Level | Badges |
|------|------|--------|----------|-------|--------|
| 🥇 1 | Siti Aminah | 300 | 12 | Silver | 5 |
| 🥈 2 | Adib Surya | 150 | 5 | Bronze | 3 |
| 🥉 3 | Budi Santoso | 50 | 2 | Pemula | 1 |

### **Test Cases Passed:**

✅ **By Points**: Ordered correctly (300 → 150 → 50)  
✅ **By Deposits**: Ordered correctly (12 → 5 → 2)  
✅ **By Badges**: Ordered correctly (5 → 3 → 1)  
✅ **Custom Limit**: Returns only requested number  
✅ **Invalid Type**: Returns 400 error with message  
✅ **Badge Count**: Correctly joined from user_badges table  

---

## 🚀 **Performance Optimization**

### **Current Implementation:**
- ✅ Uses LEFT JOIN (includes users with 0 badges)
- ✅ Single query with grouping
- ✅ Max limit of 50 to prevent large queries
- ✅ Indexed columns (total_poin, total_setor_sampah)

### **Future Enhancements:**
- 🔄 **Caching**: Cache leaderboard for 5-10 minutes
- 🔄 **Pagination**: Add offset/page support
- 🔄 **Time Periods**: Monthly/weekly leaderboards
- 🔄 **Real-time**: WebSocket updates for live rankings

---

## 📝 **Related Endpoints**

### **User Stats:**
```
GET /api/dashboard/stats/{userId}
- Get specific user's rank
- See progress to next level
- Monthly statistics
```

### **Global Stats:**
```
GET /api/dashboard/global-stats
- Total users
- Total points distributed
- Platform growth metrics
```

### **User Profile:**
```
GET /api/users/{id}
- Full user details
- All stats and badges
```

---

## ✅ **System Status**

### **Files Modified:**
- ✅ `app/Http/Controllers/DashboardController.php`
  - Enhanced `getLeaderboard()` method
  - Added query parameters support
  - Added badge count via LEFT JOIN
  - Added validation for type parameter
  - Added limit capping (max 50)

### **Routes:**
- ✅ `GET /api/dashboard/leaderboard` - Already registered in routes/api.php

### **Features Implemented:**
- [x] Multiple ranking types (poin, setor, badge)
- [x] Customizable limit (1-50 users)
- [x] Badge count included
- [x] Sequential ranking (1, 2, 3, ...)
- [x] Input validation
- [x] Error handling
- [x] Performance optimization (query grouping)

---

## 🎉 **Leaderboard System is COMPLETE!**

Users can now compete and see rankings based on:
- 🏅 **Total Points** - Who has earned the most points
- ♻️ **Deposits** - Who has contributed the most waste
- 🎖️ **Badges** - Who has unlocked the most achievements

**The leaderboard updates in real-time as users earn points, make deposits, and unlock badges!** 🏆
