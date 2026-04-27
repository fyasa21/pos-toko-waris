<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PembelianPemasok;
use App\Services\PembelianService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    use ApiResponse;

    /**
     * Menginisialisasi service pembelian pemasok untuk controller ini.
     */
    public function __construct(private PembelianService $pembelianService) {}

    /**
     * GET /api/pembelian
     */
    public function index(Request $request): JsonResponse
    {
        $pembelians = PembelianPemasok::with(['pemasok:pemasok_id,nama_pemasok', 'user:user_id,nama_lengkap'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->pemasok_id, fn($q) => $q->where('pemasok_id', $request->pemasok_id))
            ->when($request->dari && $request->sampai,
                fn($q) => $q->whereBetween('tanggal_pembelian',
                    [$request->dari . ' 00:00:00', $request->sampai . ' 23:59:59']))
            ->orderByDesc('tanggal_pembelian')
            ->paginate(15);

        return $this->paginatedResponse($pembelians);
    }

    /**
     * POST /api/pembelian
     * Buat purchase order baru.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'pemasok_id'         => 'required|exists:pemasoks,pemasok_id',
            'items'              => 'required|array|min:1',
            'items.*.produk_id'  => 'required|integer|exists:produks,produk_id',
            'items.*.jumlah'     => 'required|integer|min:1',
            'items.*.harga_beli' => 'required|numeric|min:0',
            'catatan'            => 'nullable|string|max:500',
        ]);

        try {
            $pembelian = $this->pembelianService->buatPembelian(
                $request->all(),
                $request->user()->user_id
            );
            return $this->createdResponse($pembelian, 'Purchase order berhasil dibuat.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /api/pembelian/{id}
     */
    public function show(int $id): JsonResponse
    {
        $pembelian = PembelianPemasok::with([
            'pemasok',
            'user:user_id,nama_lengkap',
            'details.produk:produk_id,kode_produk,nama_produk,satuan',
        ])->find($id);

        if (!$pembelian) return $this->notFoundResponse('Data pembelian tidak ditemukan.');

        return $this->successResponse($pembelian);
    }

    /**
     * POST /api/pembelian/{id}/terima
     * Terima barang: selesaikan PO & otomatis update stok.
     */
    public function terima(int $id): JsonResponse
    {
        $pembelian = PembelianPemasok::with('details')->find($id);
        if (!$pembelian) return $this->notFoundResponse('Data pembelian tidak ditemukan.');

        try {
            $pembelian = $this->pembelianService->terimaPembelian($pembelian);
            return $this->successResponse($pembelian, 'Pembelian diterima. Stok berhasil diperbarui.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /api/pembelian/{id}/batalkan
     */
    public function batalkan(int $id): JsonResponse
    {
        $pembelian = PembelianPemasok::find($id);
        if (!$pembelian) return $this->notFoundResponse('Data pembelian tidak ditemukan.');

        try {
            $pembelian = $this->pembelianService->batalkanPembelian($pembelian);
            return $this->successResponse($pembelian, 'Pembelian berhasil dibatalkan.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
