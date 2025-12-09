<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalPenyetoran;
use App\Http\Resources\JadwalPenyetoranResource;

class JadwalPenyetoranController extends Controller
{
    // List semua jadwal
    public function index()
    {
        $data = JadwalPenyetoran::orderBy('tanggal', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => JadwalPenyetoranResource::collection($data)
        ]);
    }

    // Tambah jadwal baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'lokasi' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $jadwal = JadwalPenyetoran::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => new JadwalPenyetoranResource($jadwal)
        ], 201);
    }

    // Jadwal penyetoran aktif
        public function aktif()
    {
        $data = JadwalPenyetoran::whereDate('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => JadwalPenyetoranResource::collection($data)
        ]);
    }

    // Daftar setoran pada jadwal tertentu
        public function setoran(JadwalPenyetoran $jadwalPenyetoran)
    {
        return response()->json([
            'status' => 'success',
            'data' => $jadwalPenyetoran->setoran()->with('user')->get()
        ]);
    }

    // Detail jadwal
    public function show(JadwalPenyetoran $jadwalPenyetoran)
    {
        return response()->json([
            'status' => 'success',
            'data' => new JadwalPenyetoranResource($jadwalPenyetoran)
        ]);
    }

    // Update jadwal
    public function update(Request $request, JadwalPenyetoran $jadwalPenyetoran)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'waktu_mulai' => 'nullable|date_format:H:i',
            'waktu_selesai' => 'nullable|date_format:H:i|after:waktu_mulai',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $jadwalPenyetoran->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => new JadwalPenyetoranResource($jadwalPenyetoran)
        ]);
    }

    // Hapus jadwal
    public function destroy(JadwalPenyetoran $jadwalPenyetoran)
    {
        $jadwalPenyetoran->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil dihapus'
        ]);
    }
}
