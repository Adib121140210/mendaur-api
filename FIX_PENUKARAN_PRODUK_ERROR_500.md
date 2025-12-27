# 🔧 FIX: Error 500 pada Penukaran Produk
## POST /api/penukaran-produk

**Tanggal Fix:** 24 Desember 2025  
**Status:** ✅ DIPERBAIKI

---

## 🐛 MASALAH

Frontend mendapat error 500 saat melakukan penukaran produk:

```
POST http://127.0.0.1:8000/api/penukaran-produk 500 (Internal Server Error)
Backend error (status 500): {
  status: 'error', 
  message: 'Terjadi kesalahan saat membuat penukaran produk'
}
```

---

## 🔍 ROOT CAUSE

### Error 1: Validasi Primary Key Salah
```php
// ❌ SEBELUM (SALAH)
'produk_id' => 'required|exists:produks,id'

// ✅ SESUDAH (BENAR)
'produk_id' => 'required|exists:produks,produk_id'
```

**Penjelasan:**
- Tabel `produks` menggunakan primary key `produk_id`, bukan `id`
- Validasi Laravel mencari kolom `id` yang tidak ada
- Menyebabkan error: `Unknown column 'id' in 'where clause'`

### Error 2: Field Name Salah
```php
// ❌ SEBELUM (SALAH)
$totalPoin = $produk->poin * $jumlah;

// ✅ SESUDAH (BENAR)
$totalPoin = $produk->harga_poin * $jumlah;
```

**Penjelasan:**
- Field harga produk di tabel adalah `harga_poin`, bukan `poin`
- Menyebabkan error saat kalkulasi total poin

### Error 3: Query FindOrFail
```php
// ❌ SEBELUM (SALAH)
$produk = \App\Models\Produk::findOrFail($validated['produk_id']);

// ✅ SESUDAH (BENAR)
$produk = \App\Models\Produk::where('produk_id', $validated['produk_id'])->firstOrFail();
```

**Penjelasan:**
- `findOrFail()` secara default mencari kolom `id`
- Harus explicitly menggunakan `where('produk_id', ...)` untuk custom PK

---

## ✅ PERUBAHAN YANG DILAKUKAN

File: `app/Http/Controllers/PenukaranProdukController.php`

### 1. Fix Validasi (Line 106)
```php
$validated = $request->validate([
    'produk_id' => 'required|exists:produks,produk_id',  // FIXED
    'metode_ambil' => 'required|string',
]);
```

### 2. Fix Query Produk (Line 111)
```php
$produk = \App\Models\Produk::where('produk_id', $validated['produk_id'])->firstOrFail();
```

### 3. Fix Field Name (Line 115)
```php
if ($totalPoin === null) {
    $totalPoin = $produk->harga_poin * $jumlah;  // FIXED: poin → harga_poin
}
```

---

## 📋 REQUEST FORMAT (TIDAK BERUBAH)

Endpoint tetap sama, tidak ada perubahan di sisi frontend:

```javascript
POST /api/penukaran-produk

Headers:
{
  "Authorization": "Bearer {token}",
  "Content-Type": "application/json"
}

Body:
{
  "produk_id": 6,
  "metode_ambil": "ambil_ditempat",
  "jumlah": 1,
  "catatan": "Optional catatan"
}
```

---

## ✅ RESPONSE FORMAT (TIDAK BERUBAH)

### Success Response (201)
```json
{
  "status": "success",
  "message": "Penukaran produk berhasil dibuat",
  "data": {
    "penukaran_produk_id": 1,
    "user_id": 3,
    "produk_id": 6,
    "nama_produk": "Tas Ramah Lingkungan",
    "poin_digunakan": 500,
    "jumlah": 1,
    "status": "pending",
    "metode_ambil": "ambil_ditempat",
    "catatan": null,
    "tanggal_penukaran": "2025-12-24",
    "tanggal_diambil": null,
    "created_at": "2025-12-24T12:00:00.000000Z",
    "updated_at": "2025-12-24T12:00:00.000000Z"
  }
}
```

### Error Responses
```json
// Poin tidak cukup (400)
{
  "status": "error",
  "message": "Poin tidak mencukupi",
  "data": {
    "required_points": 500,
    "current_points": 200,
    "shortage": 300
  }
}

// Stok habis (400)
{
  "status": "error",
  "message": "Stok produk tidak mencukupi",
  "data": {
    "requested": 5,
    "available": 2
  }
}

// Validasi gagal (422)
{
  "status": "error",
  "message": "Validasi gagal",
  "errors": {
    "produk_id": ["The selected produk id is invalid."],
    "metode_ambil": ["The metode ambil field is required."]
  }
}
```

---

## 🧪 TESTING

### Test Case 1: Penukaran Normal
```bash
curl -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "produk_id": 6,
    "metode_ambil": "ambil_ditempat",
    "jumlah": 1
  }'
```

**Expected:** Status 201, penukaran berhasil dibuat

### Test Case 2: Poin Tidak Cukup
```bash
# User dengan poin 100, produk harga 500
curl -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "produk_id": 6,
    "metode_ambil": "ambil_ditempat",
    "jumlah": 1
  }'
```

**Expected:** Status 400, error "Poin tidak mencukupi"

### Test Case 3: Produk ID Invalid
```bash
curl -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "produk_id": 999,
    "metode_ambil": "ambil_ditempat",
    "jumlah": 1
  }'
```

**Expected:** Status 422, validation error

---

## 📝 CATATAN UNTUK FRONTEND

### ✅ Tidak Ada Perubahan di Frontend

Frontend **TIDAK PERLU** melakukan perubahan apapun:
- ✅ Request format tetap sama
- ✅ Response format tetap sama
- ✅ Field names tetap sama
- ✅ Endpoint URL tetap sama

### 🎯 Yang Sudah Diperbaiki di Backend

1. ✅ Validasi primary key `produk_id`
2. ✅ Query produk menggunakan custom PK
3. ✅ Field name `harga_poin` (bukan `poin`)
4. ✅ Error handling tetap konsisten

---

## 🔄 FLOW PENUKARAN PRODUK

```
1. User submit penukaran
   ↓
2. Validasi input (produk_id, metode_ambil) ✅ FIXED
   ↓
3. Load produk dari database ✅ FIXED
   ↓
4. Cek stok produk
   ↓
5. Kalkulasi total poin (harga_poin × jumlah) ✅ FIXED
   ↓
6. Validasi poin user (via PointService)
   ↓
7. Deduct poin dari user
   ↓
8. Create record penukaran_produk (status: pending)
   ↓
9. Kurangi stok produk
   ↓
10. Return success response
```

---

## 📊 STRUKTUR TABEL TERKAIT

### Tabel: `produks`
```sql
produk_id         BIGINT PRIMARY KEY
nama              VARCHAR(255)
deskripsi         TEXT
harga_poin        INT          -- ✅ FIXED: Field yang benar
stok              INT
kategori          VARCHAR(255)
foto              VARCHAR(255)
status            ENUM('tersedia', 'habis', 'nonaktif')
created_at        TIMESTAMP
updated_at        TIMESTAMP
```

### Tabel: `penukaran_produk`
```sql
penukaran_produk_id  BIGINT PRIMARY KEY
user_id              BIGINT (FK → users.user_id)
produk_id            BIGINT (FK → produks.produk_id)
nama_produk          VARCHAR(255)
poin_digunakan       INT
jumlah               INT
status               ENUM('pending', 'approved', 'rejected', 'completed')
metode_ambil         ENUM('ambil_ditempat', 'dikirim')
catatan              TEXT
tanggal_penukaran    DATE
tanggal_diambil      DATE
created_at           TIMESTAMP
updated_at           TIMESTAMP
```

---

## ✅ STATUS

**FIXED & TESTED**

Error 500 pada penukaran produk sudah diperbaiki. Frontend dapat melanjutkan testing tanpa perubahan kode.

---

**End of Fix Report**
