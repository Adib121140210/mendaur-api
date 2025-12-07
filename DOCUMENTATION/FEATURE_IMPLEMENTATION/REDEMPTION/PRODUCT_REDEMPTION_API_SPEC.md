# 📦 Product Redemption API Specification

## 🎯 Overview

API endpoint to retrieve user's product redemption history for the Riwayat Transaksi (Transaction History) page.

**Endpoint:** `GET /api/tukar-produk`  
**Authentication:** Required (Sanctum/JWT)  
**Purpose:** Show user's history of products redeemed with points

---

## 🔌 API Endpoint Details

### Endpoint

```
GET http://127.0.0.1:8000/api/tukar-produk
```

### Headers

```http
Authorization: Bearer {token}
Accept: application/json
```

### Query Parameters (Optional)

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `status` | string | Filter by status | `pending`, `shipped`, `delivered`, `cancelled` |
| `page` | integer | Page number | `1` |
| `per_page` | integer | Items per page | `10` |

### Request Example

```http
GET /api/tukar-produk?status=shipped&page=1&per_page=10
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

---

## 📊 Response Format

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "produk_id": 5,
        "nama_produk": "Eco Bag Canvas",
        "poin_digunakan": 5000,
        "jumlah": 1,
        "status": "shipped",
        "alamat_pengiriman": "Jl. Sudirman No. 123, Jakarta",
        "no_resi": "JNE123456789",
        "catatan": "Warna hijau",
        "tanggal_penukaran": "2025-11-15T10:30:00.000000Z",
        "tanggal_pengiriman": "2025-11-16T14:00:00.000000Z",
        "tanggal_diterima": null,
        "created_at": "2025-11-15T10:30:00.000000Z",
        "updated_at": "2025-11-16T14:00:00.000000Z",
        "produk": {
          "id": 5,
          "nama": "Eco Bag Canvas",
          "deskripsi": "Tas belanja ramah lingkungan",
          "gambar": "https://example.com/images/ecobag.jpg",
          "poin_harga": 5000
        }
      },
      {
        "id": 2,
        "user_id": 1,
        "produk_id": 3,
        "nama_produk": "Tumbler Stainless Steel",
        "poin_digunakan": 8000,
        "jumlah": 1,
        "status": "delivered",
        "alamat_pengiriman": "Jl. Gatot Subroto No. 45, Jakarta",
        "no_resi": "JNE987654321",
        "catatan": null,
        "tanggal_penukaran": "2025-11-10T08:00:00.000000Z",
        "tanggal_pengiriman": "2025-11-11T10:00:00.000000Z",
        "tanggal_diterima": "2025-11-13T16:30:00.000000Z",
        "created_at": "2025-11-10T08:00:00.000000Z",
        "updated_at": "2025-11-13T16:30:00.000000Z",
        "produk": {
          "id": 3,
          "nama": "Tumbler Stainless Steel",
          "deskripsi": "Botol minum anti karat 500ml",
          "gambar": "https://example.com/images/tumbler.jpg",
          "poin_harga": 8000
        }
      }
    ],
    "per_page": 10,
    "total": 15,
    "last_page": 2
  }
}
```

### Error Response (401 Unauthorized)

```json
{
  "message": "Unauthenticated."
}
```

### Error Response (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Terjadi kesalahan saat mengambil data penukaran produk"
}
```

---

## 🗄️ Database Schema

### Table: `penukaran_produk` (or similar name)

```sql
CREATE TABLE penukaran_produk (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    produk_id BIGINT UNSIGNED NOT NULL,
    nama_produk VARCHAR(255) NOT NULL,
    poin_digunakan INT NOT NULL,
    jumlah INT DEFAULT 1,
    status ENUM('pending', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    alamat_pengiriman TEXT NOT NULL,
    no_resi VARCHAR(100) NULL,
    catatan TEXT NULL,
    tanggal_penukaran TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_pengiriman TIMESTAMP NULL,
    tanggal_diterima TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE,
    
    INDEX idx_user_status (user_id, status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_general_ci;
```

### Migration Example (Laravel)

```bash
php artisan make:migration create_penukaran_produk_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penukaran_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('produk_id')->constrained()->onDelete('cascade');
            $table->string('nama_produk');
            $table->integer('poin_digunakan');
            $table->integer('jumlah')->default(1);
            $table->enum('status', ['pending', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('alamat_pengiriman');
            $table->string('no_resi', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_penukaran')->useCurrent();
            $table->timestamp('tanggal_pengiriman')->nullable();
            $table->timestamp('tanggal_diterima')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penukaran_produk');
    }
};
```

---

## 💻 Laravel Implementation

### Model: `PenukaranProduk.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenukaranProduk extends Model
{
    protected $table = 'penukaran_produk';
    
    protected $fillable = [
        'user_id',
        'produk_id',
        'nama_produk',
        'poin_digunakan',
        'jumlah',
        'status',
        'alamat_pengiriman',
        'no_resi',
        'catatan',
        'tanggal_penukaran',
        'tanggal_pengiriman',
        'tanggal_diterima',
    ];
    
    protected $casts = [
        'poin_digunakan' => 'integer',
        'jumlah' => 'integer',
        'tanggal_penukaran' => 'datetime',
        'tanggal_pengiriman' => 'datetime',
        'tanggal_diterima' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }
    
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }
}
```

### Controller: `PenukaranProdukController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenukaranProduk;
use Illuminate\Http\Request;

class PenukaranProdukController extends Controller
{
    /**
     * Get user's product redemption history
     */
    public function index(Request $request)
    {
        try {
            $query = PenukaranProduk::with('produk')
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc');
            
            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'semua') {
                $query->where('status', $request->status);
            }
            
            // Paginate results
            $perPage = $request->per_page ?? 10;
            $redemptions = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $redemptions
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching product redemptions:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data penukaran produk'
            ], 500);
        }
    }
    
    /**
     * Get single redemption detail
     */
    public function show($id)
    {
        try {
            $redemption = PenukaranProduk::with('produk')
                ->where('user_id', auth()->id())
                ->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $redemption
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data penukaran tidak ditemukan'
            ], 404);
        }
    }
}
```

### Routes: `api.php`

```php
use App\Http\Controllers\Api\PenukaranProdukController;

// Product redemption history
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tukar-produk', [PenukaranProdukController::class, 'index']);
    Route::get('/tukar-produk/{id}', [PenukaranProdukController::class, 'show']);
});
```

---

## 🎯 Status Flow

### Status Definitions

| Status | Description | Meaning |
|--------|-------------|---------|
| `pending` | Menunggu Pemrosesan | Order received, awaiting processing |
| `shipped` | Dalam Pengiriman | Package shipped, in transit |
| `delivered` | Sudah Diterima | Package delivered to customer |
| `cancelled` | Dibatalkan | Order cancelled |

### Status Transitions

```
pending → shipped → delivered
   ↓
cancelled
```

---

## 🧪 Testing

### Test Case 1: Get All Redemptions

```bash
GET http://127.0.0.1:8000/api/tukar-produk
Authorization: Bearer {token}

Expected: 200 OK with list of redemptions
```

### Test Case 2: Filter by Status

```bash
GET http://127.0.0.1:8000/api/tukar-produk?status=shipped
Authorization: Bearer {token}

Expected: 200 OK with only shipped items
```

### Test Case 3: Pagination

```bash
GET http://127.0.0.1:8000/api/tukar-produk?page=2&per_page=5
Authorization: Bearer {token}

Expected: 200 OK with page 2 items
```

### Test Case 4: Unauthorized Access

```bash
GET http://127.0.0.1:8000/api/tukar-produk
(No token)

Expected: 401 Unauthorized
```

---

## 📋 Field Descriptions

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | integer | Auto | Redemption ID |
| `user_id` | integer | Yes | User who redeemed |
| `produk_id` | integer | Yes | Product redeemed |
| `nama_produk` | string | Yes | Product name (snapshot) |
| `poin_digunakan` | integer | Yes | Points used for redemption |
| `jumlah` | integer | Yes | Quantity redeemed |
| `status` | enum | Yes | Current order status |
| `alamat_pengiriman` | text | Yes | Delivery address |
| `no_resi` | string | Optional | Tracking number (when shipped) |
| `catatan` | text | Optional | User notes/requests |
| `tanggal_penukaran` | datetime | Auto | When redeemed |
| `tanggal_pengiriman` | datetime | Optional | When shipped |
| `tanggal_diterima` | datetime | Optional | When delivered |

---

## 🚀 Frontend Integration Ready

Once this API is implemented, the frontend will automatically:
- ✅ Fetch product redemption history
- ✅ Display with proper icons and colors
- ✅ Show delivery status
- ✅ Enable search and filtering
- ✅ Combine with other transaction types

**Frontend code is already prepared and waiting for this API!**

---

## 📞 Questions?

**Contact frontend team if:**
- Need different field names
- Need additional data
- Want different response structure
- Have integration questions

**Contact backend team if:**
- Need clarification on requirements
- Have database design questions
- Need help with implementation

---

## 🎯 Priority

**Priority:** Medium-High  
**Depends on:** Tukar Poin feature implementation  
**Blocks:** Complete transaction history view  
**Estimated Time:** 2-3 hours implementation

---

**Created:** November 17, 2025  
**For:** Riwayat Transaksi - Phase 3  
**Status:** Specification Ready for Backend Implementation
