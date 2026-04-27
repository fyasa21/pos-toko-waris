<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Services\TransaksiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    use ApiResponse;

    /**
     * Menginisialisasi service transaksi yang dipakai oleh controller ini.
     */
    public function __construct(private TransaksiService $transaksiService) {}

    /**
     * GET /api/transaksi
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaksi::with(['user:user_id,nama_lengkap,role'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->dari && $request->sampai,
                fn($q) => $q->byPeriode($request->dari . ' 00:00:00', $request->sampai . ' 23:59:59'))
            ->when($request->user_id && $request->user()?->isPemilik(),
                fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->user()?->isKasir(),
                fn($q) => $q->where('user_id', $request->user()->user_id));

        // Kasir hanya lihat transaksinya sendiri
        if ($request->user()->isKasir()) {
            $query->where('user_id', $request->user()->user_id);
        }

        $perPage     = min((int) $request->get('per_page', 15), 100);
        $transaksis  = $query->orderByDesc('tanggal_transaksi')->paginate($perPage);

        return $this->paginatedResponse($transaksis);
    }

    /**
     * POST /api/transaksi
     * Buat transaksi baru (status: pending).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.produk_id'      => 'required|integer|exists:produks,produk_id',
            'items.*.jumlah'         => 'required|integer|min:1',
            'items.*.diskon_persen'  => 'nullable|numeric|min:0|max:100',
            'metode_pembayaran'      => 'nullable|in:cash,cashless,qris,transfer',
            'catatan'                => 'nullable|string|max:500',
            'device_id'              => 'nullable|string|max:50',
        ]);

        try {
            $transaksi = $this->transaksiService->buatTransaksi(
                $request->all(),
                $request->user()->user_id
            );
            return $this->createdResponse($transaksi, 'Transaksi berhasil dibuat.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /api/transaksi/{id}
     */
    public function show(int $id): JsonResponse
    {
        $transaksi = Transaksi::with([
            'user:user_id,nama_lengkap,role',
            'details.produk:produk_id,kode_produk,nama_produk,satuan,barcode',
        ])->find($id);

        if (!$transaksi) return $this->notFoundResponse('Transaksi tidak ditemukan.');

        if ($response = $this->authorizeTransaksiAccess($transaksi)) {
            return $response;
        }

        return $this->successResponse($transaksi);
    }

    /**
     * POST /api/transaksi/{id}/selesaikan
     * Bayar dan selesaikan transaksi, kurangi stok.
     */
    public function selesaikan(Request $request, int $id): JsonResponse
    {
        $transaksi = Transaksi::with('details')->find($id);
        if (!$transaksi) return $this->notFoundResponse('Transaksi tidak ditemukan.');

        if ($response = $this->authorizeTransaksiAccess($transaksi)) {
            return $response;
        }

        $request->validate([
            'jumlah_bayar'      => 'required|numeric|min:0',
            'metode_pembayaran' => 'nullable|in:cash,cashless,qris,transfer',
        ]);

        try {
            $transaksi = $this->transaksiService->selesaikanTransaksi($transaksi, $request->all());
            return $this->successResponse($transaksi, 'Transaksi berhasil diselesaikan.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /api/transaksi/{id}/batalkan
     * Batalkan transaksi (sebelum selesai).
     */
    public function batalkan(Request $request, int $id): JsonResponse
    {
        $transaksi = Transaksi::find($id);
        if (!$transaksi) return $this->notFoundResponse('Transaksi tidak ditemukan.');

        if ($response = $this->authorizeTransaksiAccess($transaksi)) {
            return $response;
        }

        $request->validate(['alasan' => 'nullable|string|max:255']);

        try {
            $transaksi = $this->transaksiService->batalkanTransaksi(
                $transaksi,
                $request->alasan
            );
            return $this->successResponse($transaksi, 'Transaksi berhasil dibatalkan.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /api/transaksi/{id}/item
     * Tambah item ke transaksi dari halaman detail.
     */
    public function tambahItem(Request $request, int $id): JsonResponse
    {
        $transaksi = Transaksi::with('details')->find($id);
        if (!$transaksi) return $this->notFoundResponse('Transaksi tidak ditemukan.');

        if ($response = $this->authorizeTransaksiAccess($transaksi)) {
            return $response;
        }

        $request->validate([
            'produk_id' => 'required|integer|exists:produks,produk_id',
            'jumlah' => 'required|integer|min:1',
        ]);

        try {
            $transaksi = $this->transaksiService->tambahItemTransaksi(
                $transaksi,
                (int) $request->produk_id,
                (int) $request->jumlah
            );

            return $this->successResponse($transaksi, 'Item berhasil ditambahkan ke transaksi.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * DELETE /api/transaksi/{id}/item/{detailId}
     * Hapus item tertentu dari transaksi yang masih bisa diedit.
     */
    public function hapusItem(Request $request, int $id, int $detailId): JsonResponse
    {
        $transaksi = Transaksi::with('details')->find($id);
        if (!$transaksi) return $this->notFoundResponse('Transaksi tidak ditemukan.');

        if ($response = $this->authorizeTransaksiAccess($transaksi)) {
            return $response;
        }

        try {
            $transaksi = $this->transaksiService->hapusItemTransaksi($transaksi, $detailId);
            return $this->successResponse($transaksi, 'Item berhasil dihapus dari transaksi.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * PATCH /api/transaksi/{id}/item/{detailId}
     * Ubah jumlah item tertentu pada transaksi yang masih bisa diedit.
     */
    public function updateItem(Request $request, int $id, int $detailId): JsonResponse
    {
        $transaksi = Transaksi::with('details')->find($id);
        if (!$transaksi) return $this->notFoundResponse('Transaksi tidak ditemukan.');

        if ($response = $this->authorizeTransaksiAccess($transaksi)) {
            return $response;
        }

        $request->validate([
            'jumlah' => 'required|integer|min:1',
        ]);

        try {
            $transaksi = $this->transaksiService->updateJumlahItemTransaksi(
                $transaksi,
                $detailId,
                (int) $request->jumlah
            );

            return $this->successResponse($transaksi, 'Jumlah item berhasil diperbarui.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /api/transaksi/{id}/struk
     * Data struk untuk dicetak (semua detail + ringkasan).
     */
    public function struk(int $id): JsonResponse
    {
        $transaksi = Transaksi::with([
            'user:user_id,nama_lengkap',
            'details.produk:produk_id,nama_produk,satuan',
        ])->find($id);

        if (!$transaksi) return $this->notFoundResponse('Transaksi tidak ditemukan.');

        if ($response = $this->authorizeTransaksiAccess($transaksi)) {
            return $response;
        }

        if ($transaksi->status !== 'selesai') {
            return $this->errorResponse('Struk hanya tersedia untuk transaksi yang sudah selesai.');
        }

        return $this->successResponse([
            'toko' => [
                'nama'    => config('pos.store_name', 'Toko Waris'),
                'alamat'  => config('pos.store_address', ''),
                'telepon' => config('pos.store_phone', ''),
                'footer'  => config('pos.receipt_footer', 'Terima kasih!'),
            ],
            'transaksi'         => $transaksi,
            'tanggal_cetak'     => now()->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Memeriksa apakah pengguna saat ini berhak mengakses transaksi tersebut.
     */
    private function authorizeTransaksiAccess(Transaksi $transaksi): ?JsonResponse
    {
        $user = request()->user();

        if ($user->isKasir() && $transaksi->user_id !== $user->user_id) {
            return $this->forbiddenResponse('Anda tidak bisa mengakses transaksi kasir lain.');
        }

        return null;
    }
}
