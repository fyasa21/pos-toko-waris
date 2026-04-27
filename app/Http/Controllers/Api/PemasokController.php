<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Pemasok;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PemasokController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/pemasok
     */
    public function index(Request $request): JsonResponse
    {
        $pemasoks = Pemasok::when($request->search, fn($q) =>
                $q->where('nama_pemasok', 'like', "%{$request->search}%")
                  ->orWhere('kota', 'like', "%{$request->search}%"))
            ->when(!$request->boolean('semua'), fn($q) => $q->active())
            ->withCount('pembelians')
            ->orderBy('nama_pemasok')
            ->paginate(20);

        return $this->paginatedResponse($pemasoks);
    }

    /**
     * POST /api/pemasok
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_pemasok'  => 'required|string|max:100',
            'kontak_person' => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:100',
            'alamat'        => 'nullable|string|max:255',
            'kota'          => 'nullable|string|max:100',
            'catatan'       => 'nullable|string',
        ]);

        $pemasok = Pemasok::create($validated);
        ActivityLog::record($request->user()->user_id, 'tambah_pemasok', 'pemasok', "Pemasok baru: {$pemasok->nama_pemasok}");

        return $this->createdResponse($pemasok, 'Pemasok berhasil ditambahkan.');
    }

    /**
     * GET /api/pemasok/{id}
     */
    public function show(int $id): JsonResponse
    {
        $pemasok = Pemasok::withCount('pembelians')->find($id);
        if (!$pemasok) return $this->notFoundResponse('Pemasok tidak ditemukan.');

        return $this->successResponse($pemasok);
    }

    /**
     * PUT /api/pemasok/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $pemasok = Pemasok::find($id);
        if (!$pemasok) return $this->notFoundResponse('Pemasok tidak ditemukan.');

        $validated = $request->validate([
            'nama_pemasok'  => 'sometimes|string|max:100',
            'kontak_person' => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:100',
            'alamat'        => 'nullable|string|max:255',
            'kota'          => 'nullable|string|max:100',
            'is_active'     => 'nullable|boolean',
            'catatan'       => 'nullable|string',
        ]);

        $pemasok->update($validated);
        ActivityLog::record($request->user()->user_id, 'update_pemasok', 'pemasok', "Update pemasok: {$pemasok->nama_pemasok}");

        return $this->successResponse($pemasok, 'Pemasok berhasil diperbarui.');
    }

    /**
     * DELETE /api/pemasok/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $pemasok = Pemasok::find($id);
        if (!$pemasok) return $this->notFoundResponse('Pemasok tidak ditemukan.');

        $pemasok->update(['is_active' => false]);
        ActivityLog::record($request->user()->user_id, 'hapus_pemasok', 'pemasok', "Pemasok dinonaktifkan: {$pemasok->nama_pemasok}");

        return $this->successResponse(null, 'Pemasok berhasil dinonaktifkan.');
    }

    /**
     * GET /api/pemasok/{id}/riwayat-pembelian
     */
    public function riwayatPembelian(int $id): JsonResponse
    {
        $pemasok = Pemasok::find($id);
        if (!$pemasok) return $this->notFoundResponse('Pemasok tidak ditemukan.');

        $pembelians = $pemasok->pembelians()
            ->with(['user:user_id,nama_lengkap', 'details.produk:produk_id,nama_produk'])
            ->orderByDesc('tanggal_pembelian')
            ->paginate(15);

        return $this->paginatedResponse($pembelians);
    }
}
