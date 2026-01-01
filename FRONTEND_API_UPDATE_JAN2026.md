# Frontend API Update - 1 Januari 2026

## 🔄 Perubahan yang Memerlukan Update Frontend

---

## 1. Leaderboard Dashboard - Endpoint Baru

### Endpoint: `GET /api/admin/leaderboard/overview`

Gunakan endpoint ini untuk menampilkan card statistik di halaman Manajemen Leaderboard.

**Response:**
```json
{
  "status": "success",
  "data": {
    "total_peserta": 10,
    "total_poin": 5000,
    "total_sampah": 150.50,
    "total_sampah_formatted": "150,50 Kg",
    "days_to_reset": 31,
    "next_reset_date": "2026-02-01",
    "reset_period": "monthly",
    "auto_reset": true,
    "reset_count": 0,
    "current_season": "January 2026"
  }
}
```

**Penggunaan di Frontend:**
```typescript
// Fetch overview stats
const response = await api.get('/admin/leaderboard/overview');
const { data } = response.data;

// Display di cards
<Card title="Total Peserta" value={data.total_peserta} />
<Card title="Total Poin" value={data.total_poin.toLocaleString()} />
<Card title="Total Sampah" value={data.total_sampah_formatted} />  // ✅ Sudah diformat
<Card title="Hari Menuju Reset" value={data.days_to_reset} />
```

**PENTING:** Gunakan `total_sampah_formatted` untuk menghindari angka panjang seperti `03.0030.0025...`

---

## 2. Pengaturan Reset Leaderboard - Lebih Fleksibel

### Endpoint: `PUT /api/admin/leaderboard/settings`

Sekarang mendukung tanggal custom untuk reset.

**Request Body (Opsi Baru):**
```json
{
  "reset_period": "custom",
  "auto_reset": true,
  "custom_reset_date": "2026-02-15"
}
```

**Nilai `reset_period` yang valid:**
- `weekly` - Reset setiap Senin
- `monthly` - Reset setiap awal bulan
- `quarterly` - Reset setiap awal kuartal
- `yearly` - Reset setiap awal tahun
- `custom` - Reset pada tanggal yang ditentukan

**Implementasi di Frontend:**
```tsx
// State
const [resetPeriod, setResetPeriod] = useState('monthly');
const [customDate, setCustomDate] = useState<Date | null>(null);
const [autoReset, setAutoReset] = useState(true);

// Form
<Select value={resetPeriod} onChange={setResetPeriod}>
  <Option value="weekly">Mingguan</Option>
  <Option value="monthly">Bulanan</Option>
  <Option value="quarterly">Per Kuartal</Option>
  <Option value="yearly">Tahunan</Option>
  <Option value="custom">Tanggal Custom</Option>
</Select>

{resetPeriod === 'custom' && (
  <DatePicker 
    value={customDate}
    onChange={setCustomDate}
    minDate={new Date()} // Minimal hari ini
    placeholder="Pilih tanggal reset"
  />
)}

// Submit
const handleSave = async () => {
  await api.put('/admin/leaderboard/settings', {
    reset_period: resetPeriod,
    auto_reset: autoReset,
    custom_reset_date: resetPeriod === 'custom' ? customDate?.toISOString().split('T')[0] : null
  });
};
```

---

## 3. Statistik Jenis Sampah - Endpoint Baru

### Endpoint: `GET /api/jenis-sampah/stats`

Gunakan untuk menampilkan statistik di halaman Manajemen Jenis Sampah.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_jenis": 21,
    "total_kategori": 8,
    "harga_tertinggi": 70000,
    "harga_terendah": 500,
    "harga_rata_rata": 15000
  }
}
```

**Penggunaan di Frontend:**
```typescript
const response = await api.get('/jenis-sampah/stats');
const stats = response.data.data;

<Card title="Total Jenis Sampah" value={stats.total_jenis} />
<Card title="Kategori" value={stats.total_kategori} />
<Card title="Harga Tertinggi" value={`Rp ${stats.harga_tertinggi.toLocaleString()}`} />
<Card title="Harga Rata-rata" value={`Rp ${stats.harga_rata_rata.toLocaleString()}`} />  // ✅ Tidak NaN lagi
```

---

## 4. User Resource - Field Baru

### Endpoint yang Terpengaruh:
- `GET /api/profile`
- `GET /api/me`
- Login response

**Response User Baru:**
```json
{
  "user": {
    "user_id": 1,
    "nama": "John Doe",
    "email": "john@example.com",
    "actual_poin": 5000,
    "display_poin": 10000,
    "usable_poin": 5000,
    "tipe_nasabah": "modern",
    // ... field lainnya
  }
}
```

**Field Baru:**
| Field | Deskripsi |
|-------|-----------|
| `actual_poin` | Saldo poin untuk transaksi (withdrawal, tukar produk) |
| `display_poin` | Poin untuk ranking leaderboard |
| `usable_poin` | Poin yang bisa digunakan (sama dengan actual_poin) |
| `tipe_nasabah` | "modern" atau "konvensional" |

**Penggunaan:**
```typescript
// Untuk tampilan saldo poin (di halaman withdrawal/tukar produk)
const saldoPoin = user.actual_poin;

// Untuk tampilan ranking leaderboard
const poinLeaderboard = user.display_poin;

// Untuk form tarik tunai
<Input 
  max={user.usable_poin}
  helperText={`Saldo tersedia: ${user.usable_poin.toLocaleString()} poin`}
/>
```

---

## 5. Penarikan Tunai - Pembatasan Dihilangkan

### Perubahan:
- ❌ **Sebelum:** Hanya nasabah "modern" yang bisa tarik tunai
- ✅ **Sekarang:** Semua nasabah bisa tarik tunai

**Tidak perlu cek `tipe_nasabah` lagi di frontend untuk fitur ini.**

---

## 6. Approve Penyetoran Sampah - Fix 500 Error

### Endpoint: `PATCH /api/admin/penyetoran-sampah/{id}/approve`

Error 500 sudah diperbaiki. Tidak ada perubahan request/response format.

**Request:**
```json
{
  "poin_diberikan": 100
}
```

---

## 7. Reject Penyetoran Sampah - Reminder

### Endpoint: `PATCH /api/admin/penyetoran-sampah/{id}/reject`

**Request WAJIB menyertakan alasan:**
```json
{
  "alasan_penolakan": "Foto tidak jelas, silakan upload ulang"
}
```

Jika tidak ada `alasan_penolakan`, akan return 422 Unprocessable Entity.

---

## 📋 Checklist Update Frontend

- [ ] Update halaman **Manajemen Leaderboard**:
  - [ ] Panggil `/api/admin/leaderboard/overview` untuk stats
  - [ ] Gunakan `total_sampah_formatted` untuk card Total Sampah
  - [ ] Tampilkan `days_to_reset` untuk countdown
  
- [ ] Update form **Pengaturan Reset**:
  - [ ] Tambah opsi "Tanggal Custom" di dropdown periode
  - [ ] Tambah DatePicker untuk custom date
  - [ ] Kirim `custom_reset_date` jika period = "custom"

- [ ] Update halaman **Manajemen Jenis Sampah**:
  - [ ] Panggil `/api/jenis-sampah/stats` untuk card statistik
  - [ ] Gunakan `harga_rata_rata` dari API (bukan hitung manual)

- [ ] Update tampilan **Poin User**:
  - [ ] Gunakan `actual_poin` untuk saldo
  - [ ] Gunakan `display_poin` untuk leaderboard

- [ ] Update form **Reject Penyetoran**:
  - [ ] Pastikan field `alasan_penolakan` required

---

## 🔗 Quick Reference - New Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/admin/leaderboard/overview` | Stats leaderboard (total peserta, poin, sampah) |
| GET | `/api/jenis-sampah/stats` | Stats jenis sampah (harga rata-rata, dll) |

---

## 📞 Contoh Implementasi TypeScript

```typescript
// types.ts
interface LeaderboardOverview {
  total_peserta: number;
  total_poin: number;
  total_sampah: number;
  total_sampah_formatted: string;
  days_to_reset: number;
  next_reset_date: string;
  reset_period: 'weekly' | 'monthly' | 'quarterly' | 'yearly' | 'custom';
  auto_reset: boolean;
  reset_count: number;
  current_season: string;
}

interface LeaderboardSettings {
  reset_period: 'weekly' | 'monthly' | 'quarterly' | 'yearly' | 'custom';
  auto_reset: boolean;
  custom_reset_date?: string; // Format: YYYY-MM-DD
}

interface JenisSampahStats {
  total_jenis: number;
  total_kategori: number;
  harga_tertinggi: number;
  harga_terendah: number;
  harga_rata_rata: number;
}

interface User {
  user_id: number;
  nama: string;
  email: string;
  actual_poin: number;    // Saldo untuk transaksi
  display_poin: number;   // Untuk leaderboard
  usable_poin: number;    // Poin yang bisa digunakan
  tipe_nasabah: 'modern' | 'konvensional';
  // ...
}

// api.ts
export const leaderboardApi = {
  getOverview: () => api.get<{ data: LeaderboardOverview }>('/admin/leaderboard/overview'),
  updateSettings: (settings: LeaderboardSettings) => api.put('/admin/leaderboard/settings', settings),
};

export const jenisSampahApi = {
  getStats: () => api.get<{ data: JenisSampahStats }>('/jenis-sampah/stats'),
};
```

---

*Dokumen ini dibuat: 1 Januari 2026*
*Backend commit: 23e0403*
