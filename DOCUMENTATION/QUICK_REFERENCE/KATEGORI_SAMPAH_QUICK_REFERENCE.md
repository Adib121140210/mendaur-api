# 🚀 Kategori Sampah API - Quick Reference

## Base URL
```
http://127.0.0.1:8000/api
```

---

## 📋 Public Endpoints (No Auth Required)

### 1. Get All Categories (Hierarchical)
```bash
GET /kategori-sampah

# Returns categories with nested waste types
```

### 2. Get Flat List (For Dropdowns)
```bash
GET /jenis-sampah-all

# Returns flat array with:
# - id, nama_jenis, kategori, full_name, harga_per_kg, satuan, kode
```

### 3. Get Category by ID
```bash
GET /kategori-sampah/{id}

# Example: GET /kategori-sampah/1 (Plastik)
```

### 4. Get Waste Types by Category
```bash
GET /kategori-sampah/{id}/jenis

# Example: GET /kategori-sampah/1/jenis (All plastic types)
```

### 5. Legacy Endpoint (Old System)
```bash
GET /jenis-sampah-legacy
GET /jenis-sampah

# Both return old flat structure
```

---

## 🔒 Protected Endpoints (Requires Auth)

### Admin - Manage Categories

```bash
# Create Category
POST /kategori-sampah
Authorization: Bearer {token}
Content-Type: application/json

{
  "nama_kategori": "Organik",
  "deskripsi": "Sampah organik",
  "icon": "🌱",
  "warna": "#8BC34A"
}

# Update Category
PUT /kategori-sampah/{id}
Authorization: Bearer {token}

{
  "nama_kategori": "Updated Name",
  "is_active": false
}

# Delete Category
DELETE /kategori-sampah/{id}
Authorization: Bearer {token}
```

### Admin - Manage Waste Types

```bash
# Create Waste Type
POST /jenis-sampah-new
Authorization: Bearer {token}

{
  "kategori_sampah_id": 1,
  "nama_jenis": "LDPE",
  "deskripsi": "Kantong plastik",
  "harga_per_kg": 1500,
  "satuan": "kg",
  "kode": "LDPE"
}

# Update Waste Type
PUT /jenis-sampah-new/{id}
Authorization: Bearer {token}

{
  "harga_per_kg": 2000
}

# Delete Waste Type
DELETE /jenis-sampah-new/{id}
Authorization: Bearer {token}
```

---

## 📊 Categories & Pricing

| Category    | Icon | Color   | Waste Types | Price Range      |
|-------------|------|---------|-------------|------------------|
| Plastik     | ♻️   | Blue    | 5 types     | Rp 500 - 3,000   |
| Kertas      | 📄   | Orange  | 4 types     | Rp 1,000 - 2,500 |
| Logam       | 🔩   | Gray    | 4 types     | Rp 2,000 - 70,000|
| Kaca        | 🍾   | Cyan    | 3 types     | Rp 500 - 1,500   |
| Elektronik  | 🔌   | Green   | 4 types     | Rp 3,000 - 15,000|

**Highest Value:** Tembaga (Rp 70,000/kg)  
**Second Highest:** PCB/Motherboard (Rp 15,000/kg)

---

## 💻 Frontend Examples

### React - Hierarchical Display
```jsx
const { data } = await axios.get('/api/kategori-sampah');

data.data.map(kategori => (
  <div key={kategori.id} style={{ borderLeft: `4px solid ${kategori.warna}` }}>
    <h3>{kategori.icon} {kategori.nama_kategori}</h3>
    {kategori.active_jenis_sampah.map(jenis => (
      <p key={jenis.id}>
        {jenis.nama_jenis} - Rp {jenis.harga_per_kg}/kg
      </p>
    ))}
  </div>
))
```

### React - Simple Dropdown
```jsx
const { data } = await axios.get('/api/jenis-sampah-all');

<select>
  {data.data.map(jenis => (
    <option key={jenis.id} value={jenis.id}>
      {jenis.full_name} - Rp {jenis.harga_per_kg}/kg
    </option>
  ))}
</select>
```

---

## ⚡ PowerShell Testing

```powershell
# Get all categories
Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/kategori-sampah" -Headers @{"Accept"="application/json"}

# Get flat list
Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/jenis-sampah-all" -Headers @{"Accept"="application/json"}

# Get specific category
Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/kategori-sampah/1" -Headers @{"Accept"="application/json"}
```

---

## 🔑 Response Structure

### Hierarchical Response (`/kategori-sampah`)
```json
{
  "success": true,
  "message": "Kategori sampah berhasil diambil",
  "data": [
    {
      "id": 1,
      "nama_kategori": "Plastik",
      "icon": "♻️",
      "warna": "#2196F3",
      "active_jenis_sampah": [
        {
          "id": 1,
          "nama_jenis": "PET",
          "harga_per_kg": "3000.00",
          "kode": "PET"
        }
      ]
    }
  ]
}
```

### Flat Response (`/jenis-sampah-all`)
```json
{
  "success": true,
  "message": "Semua jenis sampah berhasil diambil",
  "data": [
    {
      "id": 1,
      "nama_jenis": "PET (Polyethylene Terephthalate)",
      "kategori": "Plastik",
      "full_name": "Plastik - PET (Polyethylene Terephthalate)",
      "harga_per_kg": "3000.00",
      "satuan": "kg",
      "kode": "PET"
    }
  ]
}
```

---

## ✅ Status: Production Ready

- ✅ Database migrated
- ✅ Data seeded (5 categories, 20 types)
- ✅ APIs tested and working
- ✅ Backward compatible
- ✅ Documentation complete

**Ready for frontend integration!** 🚀
