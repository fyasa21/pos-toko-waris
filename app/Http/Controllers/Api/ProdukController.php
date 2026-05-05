<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/produk
     */
    public function index(Request $request): JsonResponse
    {
        $query = Produk::with(['kategori:kategori_id,nama_kategori', 'stok'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->kategori_id, fn($q) => $q->where('kategori_id', $request->kategori_id))
            ->when($request->boolean('aktif_saja', true), fn($q) => $q->active())
            ->when($request->stok_rendah, fn($q) => $q->whereHas('stok', fn($s) => $s->lowStock()));

        $perPage = min((int) $request->get('per_page', 20), 100);
        $produks = $query->orderBy('nama_produk')->paginate($perPage);

        return $this->paginatedResponse($produks);
    }

    /**
     * POST /api/produk
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_produk'  => 'required|string|max:20|unique:produks',
            'nama_produk'  => 'required|string|max:100',
            'kategori_id'  => 'nullable|exists:kategoris,kategori_id',
            'harga_beli'   => 'required|numeric|min:0',
            'harga_jual'   => 'required|numeric|min:0|gte:harga_beli',
            'diskon_persen'=> 'nullable|numeric|min:0|max:100',
            'barcode'      => 'nullable|string|max:50|unique:produks',
            'satuan'       => 'nullable|string|max:20',
            'deskripsi'    => 'nullable|string',
            // Stok awal
            'stok_awal'         => 'nullable|integer|min:0',
            'stok_minimal'      => 'nullable|integer|min:0',
            'tanggal_kedaluwarsa' => 'nullable|date',
            'lokasi_rak'        => 'nullable|string|max:50',
        ]);

        $produk = Produk::create([
            'kode_produk'   => $validated['kode_produk'],
            'nama_produk'   => $validated['nama_produk'],
            'kategori_id'   => $validated['kategori_id'] ?? null,
            'harga_beli'    => $validated['harga_beli'],
            'harga_jual'    => $validated['harga_jual'],
            'diskon_persen' => $validated['diskon_persen'] ?? 0,
            'barcode'       => $validated['barcode'] ?? null,
            'satuan'        => $validated['satuan'] ?? 'pcs',
            'deskripsi'     => $validated['deskripsi'] ?? null,
            'is_active'     => true,
        ]);

        // Buat record stok awal
        Stok::create([
            'produk_id'           => $produk->produk_id,
            'jumlah_stok'         => $validated['stok_awal'] ?? 0,
            'stok_minimal'        => $validated['stok_minimal'] ?? 5,
            'tanggal_kedaluwarsa' => $validated['tanggal_kedaluwarsa'] ?? null,
            'lokasi_rak'          => $validated['lokasi_rak'] ?? null,
        ]);

        ActivityLog::record($request->user()->user_id, 'tambah_produk', 'produk', "Produk baru: {$produk->nama_produk}");

        return $this->createdResponse(
            $produk->load('kategori', 'stok'),
            'Produk berhasil ditambahkan.'
        );
    }

    /**
     * GET /api/produk/{id}
     */
    public function show(int $id): JsonResponse
    {
        $produk = Produk::with(['kategori', 'stok'])->find($id);
        if (!$produk) return $this->notFoundResponse('Produk tidak ditemukan.');

        return $this->successResponse($produk);
    }

    /**
     * GET /api/produk/barcode/{barcode}
     * Lookup cepat untuk scanner barcode saat transaksi.
     */
    public function findByBarcode(string $barcode): JsonResponse
    {
        $produk = Produk::with(['stok', 'kategori:kategori_id,nama_kategori'])
            ->where('barcode', $barcode)
            ->where('is_active', true)
            ->first();

        if (!$produk) {
            return $this->notFoundResponse("Produk dengan barcode '{$barcode}' tidak ditemukan.");
        }

        return $this->successResponse([
            'produk_id'    => $produk->produk_id,
            'kode_produk'  => $produk->kode_produk,
            'nama_produk'  => $produk->nama_produk,
            'barcode'      => $produk->barcode,
            'harga_jual'   => $produk->harga_jual,
            'harga_efektif'=> $produk->harga_efektif,
            'diskon_persen'=> $produk->diskon_persen,
            'satuan'       => $produk->satuan,
            'stok'         => $produk->stok?->jumlah_stok ?? 0,
            'kategori'     => $produk->kategori?->nama_kategori,
        ]);
    }

    /**
     * PUT /api/produk/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $produk = Produk::find($id);
        if (!$produk) return $this->notFoundResponse('Produk tidak ditemukan.');

        $validated = $request->validate([
            'kode_produk'  => 'sometimes|string|max:20|unique:produks,kode_produk,' . $id . ',produk_id',
            'nama_produk'  => 'sometimes|string|max:100',
            'kategori_id'  => 'nullable|exists:kategoris,kategori_id',
            'harga_beli'   => 'sometimes|numeric|min:0',
            'harga_jual'   => 'sometimes|numeric|min:0|gte:harga_beli',
            'diskon_persen'=> 'nullable|numeric|min:0|max:100',
            'barcode'      => 'nullable|string|max:50|unique:produks,barcode,' . $id . ',produk_id',
            'satuan'       => 'nullable|string|max:20',
            'deskripsi'    => 'nullable|string',
            'is_active'    => 'nullable|boolean',
            // Stok update
            'stok_minimal'        => 'nullable|integer|min:0',
            'tanggal_kedaluwarsa' => 'nullable|date',
            'lokasi_rak'          => 'nullable|string|max:50',
        ]);

        $stokMetadataKeys = ['stok_minimal', 'tanggal_kedaluwarsa', 'lokasi_rak'];
        $produkFields = collect($validated)
            ->except($stokMetadataKeys)
            ->all();

        if ($produkFields !== []) {
            $produk->update($produkFields);
        }

        // Update stok metadata jika ada
        $stokFields = [];
        foreach ($stokMetadataKeys as $key) {
            if (array_key_exists($key, $validated)) {
                $stokFields[$key] = $validated[$key];
            }
        }

        if (!empty($stokFields)) {
            $produk->stok()->updateOrCreate(['produk_id' => $produk->produk_id], $stokFields);
        }

        ActivityLog::record($request->user()->user_id, 'update_produk', 'produk', "Update produk: {$produk->nama_produk}");

        return $this->successResponse($produk->load('kategori', 'stok'), 'Produk berhasil diperbarui.');
    }

    /**
     * DELETE /api/produk/{id}
     * Soft delete via is_active = false.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $produk = Produk::find($id);
        if (!$produk) return $this->notFoundResponse('Produk tidak ditemukan.');

        $produk->update(['is_active' => false]);

        ActivityLog::record($request->user()->user_id, 'hapus_produk', 'produk', "Produk dinonaktifkan: {$produk->nama_produk}");

        return $this->successResponse(null, 'Produk berhasil dinonaktifkan.');
    }
}
