# Dokumentasi Controller Admin - Aplikasi Bank Sampah Mendaur

Dokumen ini berisi ringkasan kode controller admin untuk keperluan laporan Tugas Akhir/Skripsi.

---

## 4.3.3 Implementasi Controller Admin

### 1. Admin User Management Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // Menampilkan daftar user dengan filter dan pagination
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    // Mengupdate status user (aktif/nonaktif/banned)
    public function updateStatus(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive,banned'
        ]);

        $user->update(['status' => $validated['status']]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status user berhasil diubah'
        ]);
    }
}
```

---

### 2. Admin Leaderboard Management Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminLeaderboardController extends Controller
{
    // Menampilkan leaderboard dengan filter periode
    public function index(Request $request)
    {
        $period = $request->get('period', 'all_time');

        $query = User::where('role', 'nasabah')
                     ->where('status', 'active')
                     ->with(['tabungSampah' => function($q) {
                         $q->where('status', 'approved');
                     }])
                     ->withSum('tabungSampah as total_berat', 'berat_kg');

        // Filter berdasarkan periode
        if ($period === 'monthly') {
            $query->whereHas('tabungSampah', function($q) {
                $q->whereMonth('created_at', now()->month);
            });
        } elseif ($period === 'weekly') {
            $query->whereHas('tabungSampah', function($q) {
                $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            });
        }

        $leaderboard = $query->orderBy('display_poin', 'desc')
                             ->take(50)
                             ->get();

        return response()->json([
            'status' => 'success',
            'data' => $leaderboard->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'user_id' => $user->user_id,
                    'nama' => $user->nama,
                    'poin' => $user->display_poin,
                    'total_berat' => round($user->total_berat ?? 0, 2),
                    'foto_profil' => $user->foto_profil
                ];
            })
        ]);
    }

    // Reset leaderboard manual
    public function resetLeaderboard(Request $request)
    {
        $request->validate([
            'confirm' => 'required|boolean|accepted',
            'season_name' => 'required|string|max:100'
        ]);

        $affectedUsers = User::where('role', 'nasabah')
                             ->where('display_poin', '>', 0)
                             ->update(['display_poin' => 0]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leaderboard berhasil direset',
            'data' => [
                'users_affected' => $affectedUsers,
                'season_name' => $request->season_name
            ]
        ]);
    }
}
```

---

### 3. Admin Waste Management Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TabungSampah;
use App\Models\Notifikasi;
use App\Services\PointService;
use Illuminate\Http\Request;

class AdminWasteController extends Controller
{
    // Menampilkan semua penyetoran sampah
    public function index(Request $request)
    {
        $deposits = TabungSampah::with('user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $deposits
        ]);
    }

    // Approve penyetoran sampah
    public function approve(Request $request, $id)
    {
        $request->validate([
            'poin_diberikan' => 'required|integer|min:1'
        ]);

        $deposit = TabungSampah::findOrFail($id);

        $deposit->update([
            'status' => 'approved',
            'poin_didapat' => $request->poin_diberikan
        ]);

        // Tambah poin ke user
        PointService::earnPoints(
            $deposit->user_id,
            $request->poin_diberikan,
            'penyetoran_sampah',
            'Poin dari penyetoran sampah'
        );

        // Kirim notifikasi auto
        Notifikasi::create([
            'user_id' => $deposit->user_id,
            'judul' => 'Penyetoran Sampah Disetujui ✅',
            'pesan' => "Penyetoran {$deposit->berat_kg} kg disetujui. +{$request->poin_diberikan} poin!",
            'tipe' => 'success',
            'related_id' => $deposit->tabung_sampah_id,
            'related_type' => 'penyetoran_sampah'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penyetoran berhasil disetujui'
        ]);
    }

    // Reject penyetoran sampah
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500'
        ]);

        $deposit = TabungSampah::findOrFail($id);
        $deposit->update(['status' => 'rejected']);

        // Kirim notifikasi auto
        Notifikasi::create([
            'user_id' => $deposit->user_id,
            'judul' => 'Penyetoran Sampah Ditolak ❌',
            'pesan' => "Penyetoran ditolak. Alasan: {$request->alasan_penolakan}",
            'tipe' => 'warning',
            'related_id' => $deposit->tabung_sampah_id,
            'related_type' => 'penyetoran_sampah'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penyetoran berhasil ditolak'
        ]);
    }
}
```

---

### 4. Admin Product Exchange Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenukaranProduk;
use App\Models\Notifikasi;
use App\Services\PointService;
use Illuminate\Http\Request;

class AdminPenukaranProdukController extends Controller
{
    // Menampilkan semua penukaran produk
    public function index(Request $request)
    {
        $exchanges = PenukaranProduk::with(['user', 'produk'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $exchanges
        ]);
    }

    // Approve penukaran produk
    public function approve(Request $request, $id)
    {
        $exchange = PenukaranProduk::findOrFail($id);

        $exchange->update([
            'status' => 'approved',
            'tanggal_diambil' => now()
        ]);

        // Kurangi stok produk
        $exchange->produk->decrement('stok', $exchange->jumlah);

        // Kirim notifikasi auto
        Notifikasi::create([
            'user_id' => $exchange->user_id,
            'judul' => 'Penukaran Produk Disetujui ✅',
            'pesan' => "Penukaran \"{$exchange->nama_produk}\" telah disetujui.",
            'tipe' => 'success',
            'related_id' => $exchange->penukaran_produk_id,
            'related_type' => 'penukaran_produk'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penukaran berhasil disetujui'
        ]);
    }

    // Reject penukaran produk (dengan refund poin)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500'
        ]);

        $exchange = PenukaranProduk::findOrFail($id);

        // Refund poin ke user
        PointService::refundPoints(
            $exchange->user_id,
            $exchange->poin_digunakan,
            'refund_penukaran',
            "Refund penukaran: {$exchange->nama_produk}"
        );

        $exchange->update(['status' => 'cancelled']);

        // Kirim notifikasi auto
        Notifikasi::create([
            'user_id' => $exchange->user_id,
            'judul' => 'Penukaran Produk Ditolak ❌',
            'pesan' => "Penukaran ditolak. Poin {$exchange->poin_digunakan} dikembalikan.",
            'tipe' => 'warning',
            'related_id' => $exchange->penukaran_produk_id,
            'related_type' => 'penukaran_produk'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penukaran ditolak, poin dikembalikan'
        ]);
    }
}
```

---

### 5. Admin Cash Withdrawal Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenarikanTunai;
use App\Models\Notifikasi;
use App\Services\PointService;
use Illuminate\Http\Request;

class AdminPenarikanTunaiController extends Controller
{
    // Menampilkan semua penarikan tunai
    public function index(Request $request)
    {
        $withdrawals = PenarikanTunai::with('user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $withdrawals
        ]);
    }

    // Approve penarikan tunai
    public function approve(Request $request, $id)
    {
        $withdrawal = PenarikanTunai::findOrFail($id);

        $withdrawal->update([
            'status' => 'approved',
            'processed_by' => $request->user()->user_id,
            'processed_at' => now()
        ]);

        // Kirim notifikasi auto
        Notifikasi::create([
            'user_id' => $withdrawal->user_id,
            'judul' => 'Penarikan Tunai Disetujui ✅',
            'pesan' => "Penarikan Rp " . number_format($withdrawal->jumlah_rupiah) . " disetujui.",
            'tipe' => 'success',
            'related_id' => $withdrawal->penarikan_tunai_id,
            'related_type' => 'penarikan_tunai'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penarikan berhasil disetujui'
        ]);
    }

    // Reject penarikan tunai (dengan refund poin)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500'
        ]);

        $withdrawal = PenarikanTunai::findOrFail($id);

        // Refund poin ke user
        PointService::refundPoints(
            $withdrawal->user_id,
            $withdrawal->jumlah_poin,
            'refund_penarikan',
            'Refund penarikan ditolak'
        );

        $withdrawal->update([
            'status' => 'rejected',
            'catatan_admin' => $request->catatan_admin,
            'processed_at' => now()
        ]);

        // Kirim notifikasi auto
        Notifikasi::create([
            'user_id' => $withdrawal->user_id,
            'judul' => 'Penarikan Tunai Ditolak ❌',
            'pesan' => "Penarikan ditolak. Poin {$withdrawal->jumlah_poin} dikembalikan.",
            'tipe' => 'warning',
            'related_id' => $withdrawal->penarikan_tunai_id,
            'related_type' => 'penarikan_tunai'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penarikan ditolak, poin dikembalikan'
        ]);
    }
}
```

---

## Ringkasan Endpoint API Admin

| No | Controller | Endpoint | Method | Fungsi |
|----|------------|----------|--------|--------|
| **User Management** |
| 1 | AdminUserController | `/api/admin/users` | GET | Daftar semua user |
| 2 | AdminUserController | `/api/admin/users/{id}/status` | PATCH | Update status user |
| **Leaderboard Management** |
| 3 | AdminLeaderboardController | `/api/admin/leaderboard` | GET | Leaderboard dengan filter |
| 4 | AdminLeaderboardController | `/api/admin/leaderboard/reset` | POST | Reset leaderboard manual |
| **Waste Management** |
| 5 | AdminWasteController | `/api/admin/penyetoran-sampah` | GET | Daftar penyetoran |
| 6 | AdminWasteController | `/api/admin/penyetoran-sampah/{id}/approve` | PATCH | Approve penyetoran |
| 7 | AdminWasteController | `/api/admin/penyetoran-sampah/{id}/reject` | PATCH | Reject penyetoran |
| **Product Exchange Management** |
| 8 | AdminPenukaranProdukController | `/api/admin/penukaran-produk` | GET | Daftar penukaran |
| 9 | AdminPenukaranProdukController | `/api/admin/penukaran-produk/{id}/approve` | PATCH | Approve penukaran |
| 10 | AdminPenukaranProdukController | `/api/admin/penukaran-produk/{id}/reject` | PATCH | Reject penukaran |
| **Cash Withdrawal Management** |
| 11 | AdminPenarikanTunaiController | `/api/admin/penarikan-tunai` | GET | Daftar penarikan |
| 12 | AdminPenarikanTunaiController | `/api/admin/penarikan-tunai/{id}/approve` | PATCH | Approve penarikan |
| 13 | AdminPenarikanTunaiController | `/api/admin/penarikan-tunai/{id}/reject` | PATCH | Reject penarikan |

---

## Fitur Inti Admin

### ✅ **Fitur Utama yang Diimplementasi:**

1. **User Management** - Kelola status user (aktif/nonaktif/banned)
2. **Leaderboard Management** - Monitoring peringkat dengan reset manual
3. **Waste Management** - Approve/reject penyetoran sampah dengan auto-notification
4. **Product Exchange Management** - Approve/reject penukaran dengan refund system
5. **Cash Withdrawal Management** - Approve/reject penarikan tunai dengan refund system

### 🔄 **Sistem Auto-Notification:**
- Notifikasi otomatis saat approve/reject
- Sistem refund poin untuk reject
- Log aktivitas admin

### 🛡️ **Keamanan & Validasi:**
- Validasi input lengkap
- Authorization admin only
- Transaction handling
- Error handling

---

*Dokumentasi Controller Admin untuk Tugas Akhir/Skripsi*
*Aplikasi: Bank Sampah Mendaur*
*Tanggal: Januari 2026*

### 1. Penyetoran Sampah Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\TabungSampah;
use App\Models\Notifikasi;
use App\Services\PointService;
use Illuminate\Http\Request;

class TabungSampahController extends Controller
{
    // Menampilkan daftar penyetoran sampah
    public function index(Request $request)
    {
        $deposits = TabungSampah::where('user_id', $request->user()->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'status' => 'success',
            'data' => $deposits
        ]);
    }

    // Menyimpan penyetoran sampah baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_sampah' => 'required|string',
            'berat_kg' => 'required|numeric|min:0.1',
            'foto_sampah' => 'required|image|max:5120',
            'titik_lokasi' => 'nullable|string',
            'jadwal_penyetoran_id' => 'nullable|exists:jadwal_penyetoran,jadwal_penyetoran_id'
        ]);

        // Upload foto ke Cloudinary
        $fotoUrl = $this->uploadToCloudinary($request->file('foto_sampah'));

        $deposit = TabungSampah::create([
            'user_id' => $request->user()->user_id,
            'nama_lengkap' => $request->user()->nama,
            'no_hp' => $request->user()->no_hp,
            'jenis_sampah' => $validated['jenis_sampah'],
            'berat_kg' => $validated['berat_kg'],
            'foto_sampah' => $fotoUrl,
            'titik_lokasi' => $validated['titik_lokasi'],
            'jadwal_penyetoran_id' => $validated['jadwal_penyetoran_id'],
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penyetoran sampah berhasil diajukan',
            'data' => $deposit
        ], 201);
    }

    // Detail penyetoran sampah
    public function show($id)
    {
        $deposit = TabungSampah::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $deposit
        ]);
    }

    // Approve penyetoran (Admin)
    public function approve(Request $request, $id)
    {
        $request->validate([
            'poin_diberikan' => 'required|integer|min:1'
        ]);

        $deposit = TabungSampah::findOrFail($id);

        if ($deposit->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Penyetoran sudah diproses'
            ], 400);
        }

        // Update status penyetoran
        $deposit->update([
            'status' => 'approved',
            'poin_didapat' => $request->poin_diberikan
        ]);

        // Tambah poin ke user
        PointService::earnPoints(
            $deposit->user_id,
            $request->poin_diberikan,
            'penyetoran_sampah',
            'Poin dari penyetoran sampah'
        );

        // Kirim notifikasi ke user
        Notifikasi::create([
            'user_id' => $deposit->user_id,
            'judul' => 'Penyetoran Sampah Disetujui ✅',
            'pesan' => "Penyetoran {$deposit->berat_kg} kg disetujui. +{$request->poin_diberikan} poin!",
            'tipe' => 'success',
            'related_id' => $deposit->tabung_sampah_id,
            'related_type' => 'penyetoran_sampah'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penyetoran berhasil disetujui'
        ]);
    }

    // Reject penyetoran (Admin)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500'
        ]);

        $deposit = TabungSampah::findOrFail($id);

        $deposit->update([
            'status' => 'rejected',
            'poin_didapat' => 0
        ]);

        // Kirim notifikasi ke user
        Notifikasi::create([
            'user_id' => $deposit->user_id,
            'judul' => 'Penyetoran Sampah Ditolak ❌',
            'pesan' => "Penyetoran ditolak. Alasan: {$request->alasan_penolakan}",
            'tipe' => 'warning',
            'related_id' => $deposit->tabung_sampah_id,
            'related_type' => 'penyetoran_sampah'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penyetoran berhasil ditolak'
        ]);
    }
}
```

---

### 2. Penukaran Produk Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\PenukaranProduk;
use App\Models\Produk;
use App\Models\Notifikasi;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenukaranProdukController extends Controller
{
    // Menampilkan riwayat penukaran user
    public function index(Request $request)
    {
        $exchanges = PenukaranProduk::where('user_id', $request->user()->user_id)
            ->with('produk')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'status' => 'success',
            'data' => $exchanges
        ]);
    }

    // Membuat penukaran produk baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produk,produk_id',
            'jumlah' => 'required|integer|min:1',
            'metode_ambil' => 'required|in:ambil_sendiri,dikirim',
            'catatan' => 'nullable|string'
        ]);

        $user = $request->user();
        $produk = Produk::findOrFail($validated['produk_id']);

        // Hitung total poin yang dibutuhkan
        $totalPoin = $produk->harga_poin * $validated['jumlah'];

        // Validasi poin user
        if ($user->actual_poin < $totalPoin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Poin tidak mencukupi'
            ], 400);
        }

        // Validasi stok
        if ($produk->stok < $validated['jumlah']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stok produk tidak mencukupi'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Kurangi poin user
            PointService::deductPoints(
                $user->user_id,
                $totalPoin,
                'penukaran_produk',
                "Penukaran produk: {$produk->nama}"
            );

            // Buat record penukaran
            $exchange = PenukaranProduk::create([
                'user_id' => $user->user_id,
                'produk_id' => $produk->produk_id,
                'nama_produk' => $produk->nama,
                'jumlah' => $validated['jumlah'],
                'poin_digunakan' => $totalPoin,
                'metode_ambil' => $validated['metode_ambil'],
                'catatan' => $validated['catatan'],
                'status' => 'pending',
                'tanggal_penukaran' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penukaran berhasil diajukan',
                'data' => $exchange
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal melakukan penukaran'
            ], 500);
        }
    }

    // Approve penukaran (Admin)
    public function approve(Request $request, $id)
    {
        $exchange = PenukaranProduk::findOrFail($id);

        if ($exchange->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Penukaran sudah diproses'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Update status
            $exchange->update([
                'status' => 'approved',
                'tanggal_diambil' => now()
            ]);

            // Kurangi stok produk
            $exchange->produk->decrement('stok', $exchange->jumlah);

            // Kirim notifikasi
            Notifikasi::create([
                'user_id' => $exchange->user_id,
                'judul' => 'Penukaran Produk Disetujui ✅',
                'pesan' => "Penukaran \"{$exchange->nama_produk}\" telah disetujui.",
                'tipe' => 'success',
                'related_id' => $exchange->penukaran_produk_id,
                'related_type' => 'penukaran_produk'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penukaran berhasil disetujui'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal approve penukaran'
            ], 500);
        }
    }

    // Reject penukaran (Admin)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500'
        ]);

        $exchange = PenukaranProduk::findOrFail($id);

        DB::beginTransaction();
        try {
            // Refund poin ke user
            PointService::refundPoints(
                $exchange->user_id,
                $exchange->poin_digunakan,
                'refund_penukaran',
                "Refund penukaran: {$exchange->nama_produk}"
            );

            // Update status
            $exchange->update([
                'status' => 'cancelled',
                'catatan' => $request->alasan_penolakan
            ]);

            // Kirim notifikasi
            Notifikasi::create([
                'user_id' => $exchange->user_id,
                'judul' => 'Penukaran Produk Ditolak ❌',
                'pesan' => "Penukaran \"{$exchange->nama_produk}\" ditolak. Poin dikembalikan.",
                'tipe' => 'warning',
                'related_id' => $exchange->penukaran_produk_id,
                'related_type' => 'penukaran_produk'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penukaran berhasil ditolak, poin dikembalikan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal reject penukaran'
            ], 500);
        }
    }
}
```

---

### 3. Penarikan Tunai Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\PenarikanTunai;
use App\Models\Notifikasi;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenarikanTunaiController extends Controller
{
    // Menampilkan riwayat penarikan user
    public function index(Request $request)
    {
        $withdrawals = PenarikanTunai::where('user_id', $request->user()->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'status' => 'success',
            'data' => $withdrawals
        ]);
    }

    // Mengajukan penarikan tunai
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jumlah_poin' => 'required|integer|min:1000',
            'nama_bank' => 'required|string',
            'nomor_rekening' => 'required|string',
            'nama_penerima' => 'required|string'
        ]);

        $user = $request->user();

        // Validasi poin minimum
        if ($user->actual_poin < $validated['jumlah_poin']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Poin tidak mencukupi'
            ], 400);
        }

        // Konversi poin ke rupiah (1 poin = Rp 100)
        $jumlahRupiah = $validated['jumlah_poin'] * 100;

        DB::beginTransaction();
        try {
            // Kurangi poin user
            PointService::deductPoints(
                $user->user_id,
                $validated['jumlah_poin'],
                'penarikan_tunai',
                'Penarikan tunai'
            );

            // Buat record penarikan
            $withdrawal = PenarikanTunai::create([
                'user_id' => $user->user_id,
                'jumlah_poin' => $validated['jumlah_poin'],
                'jumlah_rupiah' => $jumlahRupiah,
                'nama_bank' => $validated['nama_bank'],
                'nomor_rekening' => $validated['nomor_rekening'],
                'nama_penerima' => $validated['nama_penerima'],
                'status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penarikan tunai berhasil diajukan',
                'data' => $withdrawal
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengajukan penarikan'
            ], 500);
        }
    }

    // Approve penarikan (Admin)
    public function approve(Request $request, $id)
    {
        $withdrawal = PenarikanTunai::findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Penarikan sudah diproses'
            ], 400);
        }

        $withdrawal->update([
            'status' => 'approved',
            'processed_by' => $request->user()->user_id,
            'processed_at' => now()
        ]);

        // Kirim notifikasi
        Notifikasi::create([
            'user_id' => $withdrawal->user_id,
            'judul' => 'Penarikan Tunai Disetujui ✅',
            'pesan' => "Penarikan Rp " . number_format($withdrawal->jumlah_rupiah) . " disetujui.",
            'tipe' => 'success',
            'related_id' => $withdrawal->penarikan_tunai_id,
            'related_type' => 'penarikan_tunai'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penarikan berhasil disetujui'
        ]);
    }

    // Reject penarikan (Admin)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500'
        ]);

        $withdrawal = PenarikanTunai::findOrFail($id);

        DB::beginTransaction();
        try {
            // Refund poin ke user
            PointService::refundPoints(
                $withdrawal->user_id,
                $withdrawal->jumlah_poin,
                'refund_penarikan',
                'Refund penarikan ditolak'
            );

            $withdrawal->update([
                'status' => 'rejected',
                'catatan_admin' => $request->catatan_admin,
                'processed_by' => $request->user()->user_id,
                'processed_at' => now()
            ]);

            // Kirim notifikasi
            Notifikasi::create([
                'user_id' => $withdrawal->user_id,
                'judul' => 'Penarikan Tunai Ditolak ❌',
                'pesan' => "Penarikan ditolak. Alasan: {$request->catatan_admin}. Poin dikembalikan.",
                'tipe' => 'warning',
                'related_id' => $withdrawal->penarikan_tunai_id,
                'related_type' => 'penarikan_tunai'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penarikan ditolak, poin dikembalikan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal reject penarikan'
            ], 500);
        }
    }
}
```

---

## 4.3.3.3 Content Management

### 1. Produk Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    // Menampilkan daftar produk
    public function index(Request $request)
    {
        $query = Produk::query();

        // Filter berdasarkan kategori
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter produk tersedia
        if ($request->has('available')) {
            $query->where('stok', '>', 0);
        }

        // Pencarian
        if ($request->has('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $produk = $query->orderBy('created_at', 'desc')
                        ->paginate($request->per_page ?? 10);

        return response()->json([
            'status' => 'success',
            'data' => $produk
        ]);
    }

    // Detail produk
    public function show($id)
    {
        $produk = Produk::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $produk
        ]);
    }

    // Menambah produk baru (Admin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_poin' => 'required|integer|min:1',
            'stok' => 'required|integer|min:0',
            'kategori' => 'required|string',
            'foto' => 'nullable|image|max:5120'
        ]);

        // Upload foto jika ada
        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->uploadToCloudinary($request->file('foto'));
        }

        $produk = Produk::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan',
            'data' => $produk
        ], 201);
    }

    // Update produk (Admin)
    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'deskripsi' => 'sometimes|string',
            'harga_poin' => 'sometimes|integer|min:1',
            'stok' => 'sometimes|integer|min:0',
            'kategori' => 'sometimes|string',
            'foto' => 'nullable|image|max:5120'
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->uploadToCloudinary($request->file('foto'));
        }

        $produk->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diupdate',
            'data' => $produk
        ]);
    }

    // Hapus produk (Admin)
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
```

---

### 2. Artikel Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArtikelController extends Controller
{
    // Menampilkan daftar artikel
    public function index(Request $request)
    {
        $query = Artikel::query();

        // Filter berdasarkan kategori
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Pencarian
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                  ->orWhere('konten', 'like', '%' . $request->search . '%');
            });
        }

        $artikel = $query->where('status', 'published')
                         ->orderBy('created_at', 'desc')
                         ->paginate($request->per_page ?? 10);

        return response()->json([
            'status' => 'success',
            'data' => $artikel
        ]);
    }

    // Detail artikel
    public function show($slug)
    {
        $artikel = Artikel::where('slug', $slug)->firstOrFail();

        // Increment view count
        $artikel->increment('views');

        return response()->json([
            'status' => 'success',
            'data' => $artikel
        ]);
    }

    // Menambah artikel baru (Admin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'kategori' => 'required|string',
            'thumbnail' => 'nullable|image|max:5120',
            'status' => 'nullable|in:draft,published'
        ]);

        $validated['slug'] = Str::slug($validated['judul']);
        $validated['author_id'] = $request->user()->user_id;

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->uploadToCloudinary($request->file('thumbnail'));
        }

        $artikel = Artikel::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Artikel berhasil ditambahkan',
            'data' => $artikel
        ], 201);
    }

    // Update artikel (Admin)
    public function update(Request $request, $slug)
    {
        $artikel = Artikel::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'judul' => 'sometimes|string|max:255',
            'konten' => 'sometimes|string',
            'kategori' => 'sometimes|string',
            'thumbnail' => 'nullable|image|max:5120',
            'status' => 'nullable|in:draft,published'
        ]);

        if (isset($validated['judul'])) {
            $validated['slug'] = Str::slug($validated['judul']);
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->uploadToCloudinary($request->file('thumbnail'));
        }

        $artikel->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Artikel berhasil diupdate',
            'data' => $artikel
        ]);
    }

    // Hapus artikel (Admin)
    public function destroy($slug)
    {
        $artikel = Artikel::where('slug', $slug)->firstOrFail();
        $artikel->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Artikel berhasil dihapus'
        ]);
    }
}
```

---

### 3. Badge dan Pencapaian Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\UserBadge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    // Menampilkan semua badge
    public function index()
    {
        $badges = Badge::orderBy('urutan')->get();

        return response()->json([
            'status' => 'success',
            'data' => $badges
        ]);
    }

    // Detail badge
    public function show($id)
    {
        $badge = Badge::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $badge
        ]);
    }

    // Badge yang dimiliki user
    public function getUserBadges(Request $request, $userId = null)
    {
        $userId = $userId ?? $request->user()->user_id;

        $userBadges = UserBadge::where('user_id', $userId)
            ->with('badge')
            ->orderBy('tanggal_diperoleh', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $userBadges
        ]);
    }

    // Progress badge user
    public function getUserProgress(Request $request, $userId = null)
    {
        $userId = $userId ?? $request->user()->user_id;

        $badges = Badge::all();
        $userBadges = UserBadge::where('user_id', $userId)->pluck('badge_id');

        $progress = $badges->map(function ($badge) use ($userBadges, $userId) {
            $isUnlocked = $userBadges->contains($badge->badge_id);
            $currentProgress = $this->calculateProgress($userId, $badge);

            return [
                'badge' => $badge,
                'is_unlocked' => $isUnlocked,
                'current_progress' => $currentProgress,
                'target' => $badge->syarat_nilai,
                'percentage' => min(100, ($currentProgress / $badge->syarat_nilai) * 100)
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ]);
    }

    // Tambah badge baru (Admin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'icon' => 'required|string',
            'syarat_tipe' => 'required|string',
            'syarat_nilai' => 'required|integer',
            'poin_reward' => 'nullable|integer'
        ]);

        $badge = Badge::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Badge berhasil ditambahkan',
            'data' => $badge
        ], 201);
    }

    // Assign badge ke user (Admin)
    public function assignToUser(Request $request, $badgeId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id'
        ]);

        $exists = UserBadge::where('user_id', $request->user_id)
                           ->where('badge_id', $badgeId)
                           ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'User sudah memiliki badge ini'
            ], 400);
        }

        UserBadge::create([
            'user_id' => $request->user_id,
            'badge_id' => $badgeId,
            'tanggal_diperoleh' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Badge berhasil diberikan ke user'
        ]);
    }

    // Helper: Hitung progress user
    private function calculateProgress($userId, $badge)
    {
        switch ($badge->syarat_tipe) {
            case 'total_berat':
                return TabungSampah::where('user_id', $userId)
                    ->where('status', 'approved')
                    ->sum('berat_kg');
            case 'jumlah_setor':
                return TabungSampah::where('user_id', $userId)
                    ->where('status', 'approved')
                    ->count();
            case 'total_poin':
                return User::find($userId)->display_poin ?? 0;
            default:
                return 0;
        }
    }
}
```

---

### 4. Jadwal Penyetoran Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\JadwalPenyetoran;
use Illuminate\Http\Request;

class JadwalPenyetoranController extends Controller
{
    // Menampilkan semua jadwal
    public function index()
    {
        $jadwal = JadwalPenyetoran::orderBy('tanggal', 'asc')
                                   ->orderBy('jam_mulai', 'asc')
                                   ->get();

        return response()->json([
            'status' => 'success',
            'data' => $jadwal
        ]);
    }

    // Jadwal yang masih aktif
    public function aktif()
    {
        $jadwal = JadwalPenyetoran::where('tanggal', '>=', now()->toDateString())
            ->where('status', 'aktif')
            ->orderBy('tanggal', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $jadwal
        ]);
    }

    // Detail jadwal
    public function show($id)
    {
        $jadwal = JadwalPenyetoran::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $jadwal
        ]);
    }

    // Menambah jadwal baru (Admin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'lokasi' => 'required|string|max:255',
            'kuota' => 'required|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        $validated['status'] = 'aktif';

        $jadwal = JadwalPenyetoran::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil ditambahkan',
            'data' => $jadwal
        ], 201);
    }

    // Update jadwal (Admin)
    public function update(Request $request, $id)
    {
        $jadwal = JadwalPenyetoran::findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'sometimes|date',
            'jam_mulai' => 'sometimes|date_format:H:i',
            'jam_selesai' => 'sometimes|date_format:H:i',
            'lokasi' => 'sometimes|string|max:255',
            'kuota' => 'sometimes|integer|min:1',
            'keterangan' => 'nullable|string',
            'status' => 'sometimes|in:aktif,nonaktif,selesai'
        ]);

        $jadwal->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil diupdate',
            'data' => $jadwal
        ]);
    }

    // Hapus jadwal (Admin)
    public function destroy($id)
    {
        $jadwal = JadwalPenyetoran::findOrFail($id);
        $jadwal->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil dihapus'
        ]);
    }
}
```

---

### 5. Daftar Harga Sampah (Jenis Sampah) Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;

class JenisSampahController extends Controller
{
    // Menampilkan semua jenis sampah dengan harga
    public function index(Request $request)
    {
        $query = JenisSampah::with('kategori');

        // Filter berdasarkan kategori
        if ($request->has('kategori_id')) {
            $query->where('kategori_sampah_id', $request->kategori_id);
        }

        // Pencarian
        if ($request->has('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $jenisSampah = $query->orderBy('nama', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $jenisSampah->map(function ($jenis) {
                return [
                    'jenis_sampah_id' => $jenis->jenis_sampah_id,
                    'nama' => $jenis->nama,
                    'kategori' => $jenis->kategori->nama ?? null,
                    'harga_per_kg' => $jenis->harga_per_kg,
                    'poin_per_kg' => $jenis->poin_per_kg,
                    'deskripsi' => $jenis->deskripsi,
                    'icon' => $jenis->icon
                ];
            })
        ]);
    }

    // Detail jenis sampah
    public function show($id)
    {
        $jenisSampah = JenisSampah::with('kategori')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $jenisSampah
        ]);
    }

    // Statistik jenis sampah
    public function stats()
    {
        $stats = JenisSampah::withCount(['tabungSampah as total_setor'])
            ->withSum(['tabungSampah as total_berat' => function ($q) {
                $q->where('status', 'approved');
            }], 'berat_kg')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    // Menambah jenis sampah baru (Admin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_sampah_id' => 'required|exists:kategori_sampah,kategori_sampah_id',
            'harga_per_kg' => 'required|integer|min:0',
            'poin_per_kg' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $jenisSampah = JenisSampah::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Jenis sampah berhasil ditambahkan',
            'data' => $jenisSampah
        ], 201);
    }

    // Update jenis sampah (Admin)
    public function update(Request $request, $id)
    {
        $jenisSampah = JenisSampah::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'kategori_sampah_id' => 'sometimes|exists:kategori_sampah,kategori_sampah_id',
            'harga_per_kg' => 'sometimes|integer|min:0',
            'poin_per_kg' => 'sometimes|integer|min:1',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $jenisSampah->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Jenis sampah berhasil diupdate',
            'data' => $jenisSampah
        ]);
    }

    // Hapus jenis sampah (Admin)
    public function destroy($id)
    {
        $jenisSampah = JenisSampah::findOrFail($id);
        $jenisSampah->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jenis sampah berhasil dihapus'
        ]);
    }
}
```

---

## Ringkasan Endpoint API

| No | Modul | Endpoint | Method | Keterangan |
|----|-------|----------|--------|------------|
| **Management** |
| 1 | User | `/api/admin/users` | GET | Daftar user |
| 2 | User | `/api/admin/users/{id}` | GET/PUT/DELETE | Detail/Update/Hapus user |
| 3 | User | `/api/admin/users/{id}/status` | PATCH | Ubah status user |
| 4 | Leaderboard | `/api/admin/leaderboard` | GET | Daftar leaderboard |
| 5 | Leaderboard | `/api/admin/leaderboard/overview` | GET | Statistik leaderboard |
| 6 | Notification | `/api/notifications` | GET | Daftar notifikasi |
| 7 | Notification | `/api/notifications/unread` | GET | Notifikasi belum dibaca |
| 8 | Notification | `/api/notifications/{id}/read` | PATCH | Tandai sudah dibaca |
| **Tabung & Penukaran** |
| 9 | Penyetoran | `/api/tabung-sampah` | GET/POST | Daftar/Tambah penyetoran |
| 10 | Penyetoran | `/api/admin/penyetoran-sampah/{id}/approve` | PATCH | Approve penyetoran |
| 11 | Penyetoran | `/api/admin/penyetoran-sampah/{id}/reject` | PATCH | Reject penyetoran |
| 12 | Penukaran | `/api/penukaran-produk` | GET/POST | Daftar/Tambah penukaran |
| 13 | Penukaran | `/api/admin/penukar-produk/{id}/approve` | PATCH | Approve penukaran |
| 14 | Penukaran | `/api/admin/penukar-produk/{id}/reject` | PATCH | Reject penukaran |
| 15 | Penarikan | `/api/penarikan-tunai` | GET/POST | Daftar/Ajukan penarikan |
| 16 | Penarikan | `/api/admin/penarikan-tunai/{id}/approve` | PATCH | Approve penarikan |
| 17 | Penarikan | `/api/admin/penarikan-tunai/{id}/reject` | PATCH | Reject penarikan |
| **Content Management** |
| 18 | Produk | `/api/produk` | GET/POST | Daftar/Tambah produk |
| 19 | Produk | `/api/produk/{id}` | GET/PUT/DELETE | Detail/Update/Hapus produk |
| 20 | Artikel | `/api/artikel` | GET/POST | Daftar/Tambah artikel |
| 21 | Artikel | `/api/artikel/{slug}` | GET/PUT/DELETE | Detail/Update/Hapus artikel |
| 22 | Badge | `/api/badges` | GET | Daftar badge |
| 23 | Badge | `/api/user/badges/progress` | GET | Progress badge user |
| 24 | Jadwal | `/api/jadwal-penyetoran` | GET/POST | Daftar/Tambah jadwal |
| 25 | Jadwal | `/api/jadwal-penyetoran-aktif` | GET | Jadwal aktif |
| 26 | Harga Sampah | `/api/jenis-sampah` | GET/POST | Daftar/Tambah jenis sampah |
| 27 | Harga Sampah | `/api/jenis-sampah/{id}` | GET/PUT/DELETE | Detail/Update/Hapus |

---

*Dokumen ini dibuat untuk keperluan dokumentasi Tugas Akhir/Skripsi*
*Aplikasi: Bank Sampah Mendaur*
*Tanggal: Januari 2026*
