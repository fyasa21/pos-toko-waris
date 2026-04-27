<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    use ApiResponse;

    /**
     * Menampilkan seluruh data kategori yang tersedia.
     */
    public function index(): JsonResponse
    {
        $kategoris = Kategori::withCount('produks')->orderBy('nama_kategori')->get();
        return $this->successResponse($kategoris);
    }

    /**
     * Menyimpan data kategori baru ke database.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:50|unique:kategoris',
            'deskripsi'     => 'nullable|string',
        ]);
        $kategori = Kategori::create($validated);
        return $this->createdResponse($kategori, 'Kategori berhasil ditambahkan.');
    }

    /**
     * Memperbarui data kategori berdasarkan ID yang dipilih.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $kategori = Kategori::find($id);
        if (!$kategori) return $this->notFoundResponse('Kategori tidak ditemukan.');

        $validated = $request->validate([
            'nama_kategori' => 'sometimes|string|max:50|unique:kategoris,nama_kategori,' . $id . ',kategori_id',
            'deskripsi'     => 'nullable|string',
        ]);
        $kategori->update($validated);
        return $this->successResponse($kategori, 'Kategori berhasil diperbarui.');
    }

    /**
     * Menghapus data kategori yang tidak lagi digunakan.
     */
    public function destroy(int $id): JsonResponse
    {
        $kategori = Kategori::withCount('produks')->find($id);
        if (!$kategori) return $this->notFoundResponse('Kategori tidak ditemukan.');

        if ($kategori->produks_count > 0) {
            return $this->errorResponse("Kategori tidak bisa dihapus karena masih memiliki {$kategori->produks_count} produk.");
        }
        $kategori->delete();
        return $this->successResponse(null, 'Kategori berhasil dihapus.');
    }
}
