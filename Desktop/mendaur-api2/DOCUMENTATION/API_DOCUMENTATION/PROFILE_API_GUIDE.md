# 📱 Profile Page API Integration Guide

## ✅ Backend Setup Complete!

All backend endpoints are ready and tested. Here's what's been created:

### 🎯 Available API Endpoints

#### 1. **Get User Profile**
```
GET http://127.0.0.1:8000/api/users/{id}
```
Returns complete user information.

#### 2. **Update User Profile**
```
PUT http://127.0.0.1:8000/api/users/{id}
Content-Type: application/json

{
  "nama": "Updated Name",
  "email": "new@example.com",
  "no_hp": "081234567890",
  "alamat": "New Address"
}
```

#### 3. **Update Profile Photo**
```
POST http://127.0.0.1:8000/api/users/{id}/update-photo
Content-Type: multipart/form-data

foto_profil: [file]
```

#### 4. **Get User's Waste Deposit History**
```
GET http://127.0.0.1:8000/api/users/{id}/tabung-sampah
```
Returns all waste deposits by user.

#### 5. **Get User's Badges/Achievements**
```
GET http://127.0.0.1:8000/api/users/{id}/badges
```
Returns all badges earned by user.

#### 6. **Get User's Activity Log**
```
GET http://127.0.0.1:8000/api/users/{id}/aktivitas
```
Returns user activity history.

---

## 📦 Sample Data Available

### Test Users:
1. **Adib** (ID: 1) - Bronze Level, 150 points, 5 deposits
   - Email: adib@example.com
   - Password: password
   - Badges: Pemula Peduli, Eco Warrior, Bronze Collector

2. **Siti** (ID: 2) - Silver Level, 300 points, 12 deposits
   - Email: siti@example.com
   - Password: password
   - Badges: All 5 badges unlocked

3. **Budi** (ID: 3) - Pemula Level, 50 points, 2 deposits
   - Email: budi@example.com
   - Password: password
   - Badges: Pemula Peduli

### Badges Available:
- 🌱 **Pemula Peduli** - First deposit
- ♻️ **Eco Warrior** - 5 deposits
- 🦸 **Green Hero** - 10 deposits
- 🌍 **Planet Saver** - 25 deposits
- 🥉 **Bronze Collector** - 100+ points
- 🥈 **Silver Collector** - 250+ points

---

## 🔧 React Components Setup

### 1. **AuthContext.jsx** (Update your existing)

```javascript
import React, { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const storedUserId = localStorage.getItem('id_user');
    if (storedUserId) {
      setUser({
        id: parseInt(storedUserId),
        nama: localStorage.getItem('user_name'),
        email: localStorage.getItem('user_email'),
        level: localStorage.getItem('user_level'),
        poin: parseInt(localStorage.getItem('user_poin') || 0),
        no_hp: localStorage.getItem('user_phone'),
        alamat: localStorage.getItem('user_address'),
        foto_profil: localStorage.getItem('user_photo'),
        total_setor_sampah: parseInt(localStorage.getItem('user_total_setor') || 0),
      });
    }
    setLoading(false);
  }, []);

  const login = (userData) => {
    localStorage.setItem('id_user', userData.id);
    localStorage.setItem('user_name', userData.nama);
    localStorage.setItem('user_email', userData.email);
    localStorage.setItem('user_level', userData.level);
    localStorage.setItem('user_poin', userData.total_poin);
    localStorage.setItem('user_phone', userData.no_hp || '');
    localStorage.setItem('user_address', userData.alamat || '');
    localStorage.setItem('user_photo', userData.foto_profil || '');
    localStorage.setItem('user_total_setor', userData.total_setor_sampah || 0);
    
    setUser({
      id: userData.id,
      nama: userData.nama,
      email: userData.email,
      level: userData.level,
      poin: userData.total_poin,
      no_hp: userData.no_hp,
      alamat: userData.alamat,
      foto_profil: userData.foto_profil,
      total_setor_sampah: userData.total_setor_sampah,
    });
  };

  const logout = () => {
    localStorage.clear();
    setUser(null);
  };

  const updateUser = (updatedData) => {
    const newUser = { ...user, ...updatedData };
    setUser(newUser);
    
    // Update localStorage
    Object.keys(updatedData).forEach(key => {
      if (key === 'nama') localStorage.setItem('user_name', updatedData[key]);
      if (key === 'email') localStorage.setItem('user_email', updatedData[key]);
      if (key === 'level') localStorage.setItem('user_level', updatedData[key]);
      if (key === 'total_poin') localStorage.setItem('user_poin', updatedData[key]);
      if (key === 'no_hp') localStorage.setItem('user_phone', updatedData[key]);
      if (key === 'alamat') localStorage.setItem('user_address', updatedData[key]);
      if (key === 'foto_profil') localStorage.setItem('user_photo', updatedData[key]);
    });
  };

  return (
    <AuthContext.Provider value={{ 
      user, 
      login, 
      logout, 
      updateUser,
      loading,
      isAuthenticated: !!user 
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
```

---

### 2. **ProfilHeader.jsx**

```javascript
import React, { useState } from "react";
import { useAuth } from "../context/AuthContext";
import { Camera } from "lucide-react";
import "./profilHeader.css";

export default function ProfilHeader({ isEditing }) {
  const { user, updateUser } = useAuth();
  const [uploading, setUploading] = useState(false);
  const [formData, setFormData] = useState({
    nama: user?.nama || "",
    email: user?.email || "",
    no_hp: user?.no_hp || "",
    alamat: user?.alamat || "",
  });

  const handlePhotoUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setUploading(true);
    const data = new FormData();
    data.append("foto_profil", file);

    try {
      const response = await fetch(`http://127.0.0.1:8000/api/users/${user.id}/update-photo`, {
        method: "POST",
        body: data,
      });

      const result = await response.json();

      if (result.status === "success") {
        updateUser({ foto_profil: result.data.foto_profil });
        alert("Foto profil berhasil diupdate!");
      }
    } catch (error) {
      console.error("Error uploading photo:", error);
      alert("Gagal upload foto");
    } finally {
      setUploading(false);
    }
  };

  const handleInputChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSave = async () => {
    try {
      const response = await fetch(`http://127.0.0.1:8000/api/users/${user.id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (result.status === "success") {
        updateUser(formData);
        alert("Profile updated successfully!");
      }
    } catch (error) {
      console.error("Error updating profile:", error);
      alert("Failed to update profile");
    }
  };

  const photoUrl = user?.foto_profil 
    ? `http://127.0.0.1:8000/storage/${user.foto_profil}`
    : "https://via.placeholder.com/100";

  return (
    <div className="profilHeader">
      <div className="profilPhotoWrapper">
        <img
          src={photoUrl}
          alt="Profile"
          className="profilPhoto"
        />
        {isEditing && (
          <label className="photoUploadLabel">
            <Camera size={20} />
            <input
              type="file"
              accept="image/*"
              onChange={handlePhotoUpload}
              style={{ display: "none" }}
              disabled={uploading}
            />
          </label>
        )}
      </div>

      <div className="profilInfo">
        {isEditing ? (
          <>
            <input
              type="text"
              name="nama"
              value={formData.nama}
              onChange={handleInputChange}
              className="editInput"
              placeholder="Nama"
            />
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleInputChange}
              className="editInput"
              placeholder="Email"
            />
            <input
              type="tel"
              name="no_hp"
              value={formData.no_hp}
              onChange={handleInputChange}
              className="editInput"
              placeholder="No. HP"
            />
            <textarea
              name="alamat"
              value={formData.alamat}
              onChange={handleInputChange}
              className="editInput"
              placeholder="Alamat"
              rows="2"
            />
            <button onClick={handleSave} className="saveButton">
              Save Changes
            </button>
          </>
        ) : (
          <>
            <h2 className="profilName">{user?.nama}</h2>
            <p className="profilEmail">{user?.email}</p>
            <p className="profilPhone">{user?.no_hp}</p>
            {user?.alamat && <p className="profilAddress">{user?.alamat}</p>}
          </>
        )}

        <div className="profilStats">
          <div className="statItem">
            <span className="statValue">{user?.poin || 0}</span>
            <span className="statLabel">Poin</span>
          </div>
          <div className="statDivider"></div>
          <div className="statItem">
            <span className="statValue">{user?.total_setor_sampah || 0}</span>
            <span className="statLabel">Setor</span>
          </div>
          <div className="statDivider"></div>
          <div className="statItem">
            <span className={`statValue badge-${user?.level?.toLowerCase()}`}>
              {user?.level || "Pemula"}
            </span>
            <span className="statLabel">Level</span>
          </div>
        </div>
      </div>
    </div>
  );
}
```

---

### 3. **UserData.jsx**

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

### 4. **AchievementList.jsx**

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

---

### 5. **ProfilTabs.jsx**

```javascript
import React from "react";
import "./profilTabs.css";

export default function ProfilTabs({ activeTab, setActiveTab }) {
  return (
    <div className="profilTabs">
      <button
        className={`tabButton ${activeTab === "achievement" ? "active" : ""}`}
        onClick={() => setActiveTab("achievement")}
      >
        🏆 Achievements
      </button>
      <button
        className={`tabButton ${activeTab === "data" ? "active" : ""}`}
        onClick={() => setActiveTab("data")}
      >
        📊 Riwayat
      </button>
    </div>
  );
}
```

---

## 🧪 Testing the API

### Test User Profile Endpoint:
```bash
curl http://127.0.0.1:8000/api/users/1
```

### Test Badges Endpoint:
```bash
curl http://127.0.0.1:8000/api/users/1/badges
```

### Test History Endpoint:
```bash
curl http://127.0.0.1:8000/api/users/1/tabung-sampah
```

---

## ✅ Checklist

- [x] UserController created with all methods
- [x] Badge model and relationships configured
- [x] User model updated with relationships
- [x] API routes registered
- [x] Badge seeder with sample data
- [x] Migrations updated with timestamps
- [x] Database seeded successfully
- [x] Server running on http://127.0.0.1:8000

---

## 🚀 Next Steps

1. Copy the React components above to your frontend project
2. Make sure AuthContext is wrapped around your app
3. Test the login flow
4. Navigate to /profil page
5. View achievements and history tabs
6. Test profile editing
7. Test photo upload

All backend APIs are ready and working! Just copy the React code to your frontend. 🎉
