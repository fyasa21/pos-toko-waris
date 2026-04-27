<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Stok;
use App\Models\StokMovement;
use App\Services\StokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StokController extends Controller
{
    use ApiResponse;

    /**
     * Menginisialisasi service stok yang dipakai oleh controller ini.
     */
    public function __construct(private StokService $stokService) {}

    /**
     * GET /api/stok
     * Daftar stok semua produk.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Stok::with(['produk:produk_id,kode_produk,nama_produk,satuan,harga_jual,kategori_id',
                             'produk.kategori:kategori_id,nama_kategori'])
            ->when($request->stok_rendah, fn($q) => $q->lowStock())
            ->when($request->kedaluwarsa, fn($q) => $q->expiringSoon(
                (int) $request->get('hari', 30)
            ));

        $perPage = min((int) $request->get('per_page', 20), 100);
        return $this->paginatedResponse($query->paginate($perPage));
    }

    /**
     * PUT /api/stok/{produkId}
     * Update metadata stok (stok_minimal, lokasi_rak, kedaluwarsa).
     */
    public function update(Request $request, int $produkId): JsonResponse
    {
        $validated = $request->validate([
            'stok_minimal'        => 'sometimes|integer|min:0',
            'tanggal_kedaluwarsa' => 'nullable|date',
            'lokasi_rak'          => 'nullable|string|max:50',
        ]);

        $stok = Stok::where('produk_id', $produkId)->firstOrFail();
        $stok->update($validated);

        return $this->successResponse($stok->load('produk:produk_id,nama_produk'), 'Data stok berhasil diperbarui.');
    }

    /**
     * POST /api/stok/{produkId}/tambah
     * Input stok masuk secara manual (tidak melalui PO pemasok).
     */
    public function tambah(Request $request, int $produkId): JsonResponse
    {
        $validated = $request->validate([
            'jumlah'     => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            $this->stokService->tambahStok(
                produkId: $produkId,
                jumlah: $validated['jumlah'],
                referensi: 'MANUAL-' . now()->format('YmdHis'),
                userId: $request->user()->user_id,
                keterangan: $validated['keterangan'] ?? 'Penambahan stok manual'
            );

            $stok = Stok::with('produk:produk_id,nama_produk')->where('produk_id', $produkId)->first();
            ActivityLog::record($request->user()->user_id, 'tambah_stok', 'stok',
                "Stok manual +{$validated['jumlah']} untuk produk ID {$produkId}");

            return $this->successResponse($stok, "Stok berhasil ditambah {$validated['jumlah']} unit.");
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /api/stok/{produkId}/penyesuaian
     * Penyesuaian stok (stock opname) - set ke jumlah baru.
     */
    public function penyesuaian(Request $request, int $produkId): JsonResponse
    {
        $validated = $request->validate([
            'jumlah_baru' => 'required|integer|min:0',
            'keterangan'  => 'nullable|string|max:255',
        ]);

        try {
            $this->stokService->penyesuaianStok(
                produkId: $produkId,
                jumlahBaru: $validated['jumlah_baru'],
                userId: $request->user()->user_id,
                keterangan: $validated['keterangan'] ?? 'Penyesuaian stok (opname)'
            );

            $stok = Stok::with('produk:produk_id,nama_produk')->where('produk_id', $produkId)->first();
            ActivityLog::record($request->user()->user_id, 'penyesuaian_stok', 'stok',
                "Penyesuaian stok produk ID {$produkId} menjadi {$validated['jumlah_baru']}");

            return $this->successResponse($stok, 'Penyesuaian stok berhasil.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /api/stok/notifikasi
     * Notifikasi stok minimal & kedaluwarsa.
     */
    public function notifikasi(Request $request): JsonResponse
    {
        $days   = (int) $request->get('hari', config('pos.expiry_days_warn', 30));
        $stokMin  = $this->stokService->getNotifikasiStokMinimal();
        $expired  = $this->stokService->getNotifikasiKedaluwarsa($days);

        return $this->successResponse([
            'stok_minimal' => [
                'count' => $stokMin->count(),
                'items' => $stokMin,
            ],
            'kedaluwarsa' => [
                'akan_kedaluwarsa' => [
                    'count' => $expired['akan_kedaluwarsa']->count(),
                    'items' => $expired['akan_kedaluwarsa'],
                ],
                'sudah_kedaluwarsa' => [
                    'count' => $expired['sudah_kedaluwarsa']->count(),
                    'items' => $expired['sudah_kedaluwarsa'],
                ],
            ],
        ]);
    }

    /**
     * GET /api/stok/{produkId}/riwayat
     * Riwayat pergerakan stok sebuah produk.
     */
    public function riwayat(Request $request, int $produkId): JsonResponse
    {
        $movements = StokMovement::with('user:user_id,nama_lengkap')
            ->where('produk_id', $produkId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->paginatedResponse($movements);
    }
}
