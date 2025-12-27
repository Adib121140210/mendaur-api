# 📋 CATATAN PERUBAHAN JADWAL PENYETORAN
## Untuk Frontend Team - ScheduleManagement

**Tanggal:** 23 Desember 2025  
**Update:** Perubahan struktur data Jadwal Penyetoran

---

## ⚠️ BREAKING CHANGES

### 1. Kolom `tanggal` → `hari`

**SEBELUM:**
```json
{
  "tanggal": "2025-12-25"  // format: YYYY-MM-DD (date)
}
```

**SESUDAH:**
```json
{
  "hari": "Senin"  // enum: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu
}
```

### 2. Kolom `status` - Perubahan Value

**SEBELUM:**
```json
{
  "status": "buka"  // lowercase: buka, tutup
}
```

**SESUDAH:**
```json
{
  "status": "Buka"  // Capitalized: Buka, Tutup
}
```

### 3. Kolom `kapasitas` - DIHAPUS

Field `kapasitas` sudah tidak ada lagi di database dan tidak perlu dikirim.

---

## 📝 API REQUEST FORMAT

### POST `/api/admin/jadwal-penyetoran` (Create)

```json
{
  "hari": "Senin",           // REQUIRED - enum: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu
  "waktu_mulai": "08:00",    // REQUIRED - format: HH:mm atau HH:mm:ss
  "waktu_selesai": "12:00",  // REQUIRED - format: HH:mm atau HH:mm:ss
  "lokasi": "Bank Sampah Pusat",  // REQUIRED - string
  "status": "Buka"           // OPTIONAL - default: "Buka", enum: Buka, Tutup
}
```

### PUT `/api/admin/jadwal-penyetoran/{id}` (Update)

```json
{
  "hari": "Selasa",          // OPTIONAL
  "waktu_mulai": "09:00",    // OPTIONAL
  "waktu_selesai": "13:00",  // OPTIONAL
  "lokasi": "Lokasi Baru",   // OPTIONAL
  "status": "Tutup"          // OPTIONAL
}
```

---

## 📤 API RESPONSE FORMAT

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "jadwal_penyetoran_id": 1,
    "hari": "Senin",
    "waktu_mulai": "08:00:00",
    "waktu_selesai": "12:00:00",
    "lokasi": "Bank Sampah Pusat",
    "status": "Buka",
    "created_at": "2025-12-23T10:00:00.000000Z",
    "updated_at": "2025-12-23T10:00:00.000000Z"
  }
}
```

---

## 🔧 PERUBAHAN YANG PERLU DILAKUKAN DI FRONTEND

### 1. Update Form Input

```jsx
// SEBELUM - Date picker
<input type="date" name="tanggal" />

// SESUDAH - Select/Dropdown hari
<select name="hari">
  <option value="Senin">Senin</option>
  <option value="Selasa">Selasa</option>
  <option value="Rabu">Rabu</option>
  <option value="Kamis">Kamis</option>
  <option value="Jumat">Jumat</option>
  <option value="Sabtu">Sabtu</option>
  <option value="Minggu">Minggu</option>
</select>
```

### 2. Hapus Field Kapasitas

```jsx
// HAPUS dari form:
// <input type="number" name="kapasitas" />
```

### 3. Update Status Dropdown

```jsx
// SEBELUM
<select name="status">
  <option value="buka">Buka</option>
  <option value="tutup">Tutup</option>
</select>

// SESUDAH (perhatikan huruf kapital)
<select name="status">
  <option value="Buka">Buka</option>
  <option value="Tutup">Tutup</option>
</select>
```

### 4. Update Interface/Type

```typescript
// SEBELUM
interface JadwalPenyetoran {
  jadwal_penyetoran_id: number;
  tanggal: string;        // "2025-12-25"
  waktu_mulai: string;
  waktu_selesai: string;
  lokasi: string;
  kapasitas?: number;
  status?: 'buka' | 'tutup';
}

// SESUDAH
interface JadwalPenyetoran {
  jadwal_penyetoran_id: number;
  hari: 'Senin' | 'Selasa' | 'Rabu' | 'Kamis' | 'Jumat' | 'Sabtu' | 'Minggu';
  waktu_mulai: string;
  waktu_selesai: string;
  lokasi: string;
  status: 'Buka' | 'Tutup';
}
```

### 5. Update Table Display

```jsx
// SEBELUM - menampilkan tanggal
<td>{formatDate(jadwal.tanggal)}</td>
<td>{jadwal.kapasitas}</td>

// SESUDAH - menampilkan hari
<td>{jadwal.hari}</td>
// kapasitas sudah tidak ada
```

---

## 🌐 AVAILABLE ENDPOINTS

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/jadwal-penyetoran` | List semua jadwal | No |
| GET | `/api/jadwal-penyetoran/{id}` | Detail jadwal | No |
| GET | `/api/jadwal-penyetoran-aktif` | Jadwal dengan status Buka | No |
| POST | `/api/jadwal-penyetoran` | Create jadwal | Admin |
| PUT | `/api/jadwal-penyetoran/{id}` | Update jadwal | Admin |
| DELETE | `/api/jadwal-penyetoran/{id}` | Delete jadwal | Admin |
| POST | `/api/admin/jadwal-penyetoran` | Create jadwal (admin prefix) | Admin |
| PUT | `/api/admin/jadwal-penyetoran/{id}` | Update jadwal (admin prefix) | Admin |
| DELETE | `/api/admin/jadwal-penyetoran/{id}` | Delete jadwal (admin prefix) | Admin |

---

## ✅ ENUM VALUES REFERENCE

### Hari (Required)
- `Senin`
- `Selasa`
- `Rabu`
- `Kamis`
- `Jumat`
- `Sabtu`
- `Minggu`

### Status (Optional, default: Buka)
- `Buka`
- `Tutup`

---

## 📊 DATABASE SCHEMA (NEW)

```sql
CREATE TABLE `jadwal_penyetorans` (
  `jadwal_penyetoran_id` bigint PRIMARY KEY AUTO_INCREMENT,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `status` enum('Buka','Tutup') DEFAULT 'Buka',
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL
);
```

---

**End of Document**
