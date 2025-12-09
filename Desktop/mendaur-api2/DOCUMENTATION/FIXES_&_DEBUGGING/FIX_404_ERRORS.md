# 🔧 CORRECT API ENDPOINTS FOR YOUR REACT APP

## ❌ WRONG URLs (What you're currently calling):
```javascript
// ❌ WRONG - Missing 's' in 'user'
GET http://127.0.0.1:8000/api/user/1/badges
GET http://127.0.0.1:8000/api/user/1/aktivitas

// ❌ WRONG - This route doesn't exist
GET http://127.0.0.1:8000/api/badges
```

## ✅ CORRECT URLs (What you should call):
```javascript
// ✅ CORRECT - Note 'users' with 's'
GET http://127.0.0.1:8000/api/users/1/badges
GET http://127.0.0.1:8000/api/users/1/aktivitas
GET http://127.0.0.1:8000/api/users/1/tabung-sampah
GET http://127.0.0.1:8000/api/users/1
PUT http://127.0.0.1:8000/api/users/1
POST http://127.0.0.1:8000/api/users/1/update-photo
```

---

## 🔧 FIX YOUR REACT COMPONENTS:

### 1. **ProfilHeader.jsx** - Line 12 (approximately)
```javascript
// ❌ WRONG
const response = await fetch(`http://127.0.0.1:8000/api/user/${user.id}/badges`);

// ✅ CORRECT - Add 's' to 'users'
const response = await fetch(`http://127.0.0.1:8000/api/users/${user.id}/badges`);
```

---

### 2. **AchievementList.jsx** - Line 83 (approximately)
```javascript
// ❌ WRONG
const response = await fetch('http://127.0.0.1:8000/api/badges');

// ✅ CORRECT - Use user-specific endpoint
const response = await fetch(`http://127.0.0.1:8000/api/users/${user.id}/badges`);
```

---

### 3. **UserData.jsx** - Lines 19 & 32 (approximately)
```javascript
// ❌ WRONG
const response = await fetch(`http://127.0.0.1:8000/api/user/${user.id}/aktivitas`);
const response = await fetch(`http://127.0.0.1:8000/api/user/${user.id}/badges`);

// ✅ CORRECT - Add 's' to 'users'
const response = await fetch(`http://127.0.0.1:8000/api/users/${user.id}/aktivitas`);
const response = await fetch(`http://127.0.0.1:8000/api/users/${user.id}/badges`);
```

---

## 🧪 TEST THE CORRECT ENDPOINTS:

Open your browser and test these URLs directly:

1. **Get User Profile:**
   ```
   http://127.0.0.1:8000/api/users/1
   ```

2. **Get User Badges:**
   ```
   http://127.0.0.1:8000/api/users/1/badges
   ```

3. **Get Waste History:**
   ```
   http://127.0.0.1:8000/api/users/1/tabung-sampah
   ```

4. **Get Activity Log:**
   ```
   http://127.0.0.1:8000/api/users/1/aktivitas
   ```

All these should return JSON data, not 404 errors!

---

## 🔍 SEARCH & REPLACE IN YOUR REACT PROJECT:

Use VS Code's Find & Replace (Ctrl+Shift+H):

### Replace #1:
- **Find:** `api/user/`
- **Replace:** `api/users/`

### Replace #2:
- **Find:** `http://127.0.0.1:8000/api/badges`
- **Replace:** `http://127.0.0.1:8000/api/users/${user.id}/badges`

---

## 📋 COMPLETE CORRECT CODE:

### ✅ AchievementList.jsx (CORRECTED)
```javascript
import React, { useState, useEffect } from "react";
import { useAuth } from "../context/AuthContext";
import "./achievementList.css";

export default function AchievementList() {
  const { user } = useAuth();
  const [badges, setBadges] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (user?.id) {
      fetchBadges();
    }
  }, [user?.id]);

  const fetchBadges = async () => {
    try {
      // ✅ CORRECT URL with 'users' (plural) and user.id
      const response = await fetch(
        `http://127.0.0.1:8000/api/users/${user.id}/badges`
      );
      const result = await response.json();

      if (result.status === "success") {
        setBadges(result.data);
      }
    } catch (error) {
      console.error("Error fetching badges:", error);
    } finally {
      setLoading(false);
    }
  };

  const getSyaratText = (badge) => {
    if (badge.tipe === 'setor') {
      return `Setor ${badge.syarat_setor}x`;
    } else if (badge.tipe === 'poin') {
      return `${badge.syarat_poin}+ poin`;
    } else {
      return `${badge.syarat_setor}x setor & ${badge.syarat_poin}+ poin`;
    }
  };

  if (loading) {
    return <div className="loading">Loading achievements...</div>;
  }

  return (
    <div className="achievementList">
      <h3>Achievement & Badges</h3>
      
      {badges.length === 0 ? (
        <div className="emptyState">
          <p>Belum ada badge yang didapatkan</p>
          <p className="emptyHint">Setor sampah untuk mendapatkan badge!</p>
        </div>
      ) : (
        <div className="badgeGrid">
          {badges.map((badge) => (
            <div key={badge.id} className="badgeCard">
              <div className="badgeIcon">{badge.icon}</div>
              <h4 className="badgeName">{badge.nama}</h4>
              <p className="badgeDescription">{badge.deskripsi}</p>
              <p className="badgeSyarat">{getSyaratText(badge)}</p>
              <span className="badgeDate">
                {new Date(badge.tanggal_dapat).toLocaleDateString('id-ID')}
              </span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
```

### ✅ UserData.jsx (CORRECTED)
```javascript
import React, { useState, useEffect } from "react";
import { useAuth } from "../context/AuthContext";
import "./userData.css";

export default function UserData() {
  const { user } = useAuth();
  const [history, setHistory] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (user?.id) {
      fetchUserHistory();
    }
  }, [user?.id]);

  const fetchUserHistory = async () => {
    try {
      // ✅ CORRECT URL with 'users' (plural)
      const response = await fetch(
        `http://127.0.0.1:8000/api/users/${user.id}/tabung-sampah`
      );
      const result = await response.json();

      if (result.status === "success") {
        setHistory(result.data);
      }
    } catch (error) {
      console.error("Error fetching history:", error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Loading history...</div>;
  }

  return (
    <div className="userData">
      <h3>Riwayat Penyetoran Sampah</h3>
      
      {history.length === 0 ? (
        <p className="emptyState">Belum ada riwayat penyetoran</p>
      ) : (
        <div className="historyList">
          {history.map((item) => (
            <div key={item.id} className="historyItem">
              <div className="historyHeader">
                <span className="historyDate">
                  {new Date(item.created_at).toLocaleDateString('id-ID')}
                </span>
                <span className={`historyStatus status-${item.status}`}>
                  {item.status}
                </span>
              </div>
              
              <div className="historyBody">
                <p><strong>Jenis:</strong> {item.jenis_sampah}</p>
                <p><strong>Berat:</strong> {item.berat_kg} kg</p>
                <p><strong>Lokasi:</strong> {item.titik_lokasi}</p>
                {item.poin_didapat > 0 && (
                  <p className="historyPoin">
                    <strong>Poin:</strong> +{item.poin_didapat}
                  </p>
                )}
              </div>

              {item.foto_sampah && (
                <img
                  src={`http://127.0.0.1:8000/storage/${item.foto_sampah}`}
                  alt="Sampah"
                  className="historyPhoto"
                />
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
```

---

## 🎯 SUMMARY:

The issue is simple: **You're calling `/api/user/` but the routes are `/api/users/` (with 's')**

**Fix these 3 files in your React project:**
1. ✅ ProfilHeader.jsx - Change `api/user/` to `api/users/`
2. ✅ AchievementList.jsx - Change `api/badges` to `api/users/${user.id}/badges`
3. ✅ UserData.jsx - Change `api/user/` to `api/users/`

After fixing, refresh your React app and all 404 errors will be gone! 🚀
