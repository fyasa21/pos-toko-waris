<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    use ApiResponse;

    /**
     * Menginisialisasi service laporan yang dipakai oleh controller ini.
     */
    public function __construct(private LaporanService $laporanService) {}

    /**
     * GET /api/laporan/dashboard
     * Ringkasan dashboard hari ini + bulan ini.
     */
    public function dashboard(): JsonResponse
    {
        return $this->successResponse($this->laporanService->ringkasanDashboard());
    }

    /**
     * GET /api/laporan/penjualan
     * Laporan penjualan dengan filter periode.
     * ?dari=2025-01-01&sampai=2025-01-31
     */
    public function penjualan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dari'   => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
        ]);

        $data = $this->laporanService->laporanPenjualan($validated['dari'], $validated['sampai']);
        return $this->successResponse($data);
    }

    /**
     * GET /api/laporan/harian
     * Rekap penjualan per hari dalam rentang periode.
     */
    public function harian(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dari'   => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
        ]);

        $data = $this->laporanService->laporanHarian($validated['dari'], $validated['sampai']);
        return $this->successResponse($data);
    }

    /**
     * GET /api/laporan/mingguan
     * Shortcut: laporan 7 hari terakhir.
     */
    public function mingguan(): JsonResponse
    {
        $dari   = now()->subDays(6)->toDateString();
        $sampai = now()->toDateString();
        $data   = $this->laporanService->laporanHarian($dari, $sampai);
        return $this->successResponse($data);
    }

    /**
     * GET /api/laporan/bulanan
     * Shortcut: laporan bulan ini atau bulan tertentu (?bulan=2025-01).
     */
    public function bulanan(Request $request): JsonResponse
    {
        $bulan  = $request->get('bulan', now()->format('Y-m'));
        $dari   = Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
        $sampai = Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();

        $penjualan = $this->laporanService->laporanPenjualan($dari, $sampai);
        $harian    = $this->laporanService->laporanHarian($dari, $sampai);
        $terlaris  = $this->laporanService->produkTerlaris($dari, $sampai, 10);

        return $this->successResponse([
            'periode'          => ['bulan' => $bulan, 'dari' => $dari, 'sampai' => $sampai],
            'ringkasan'        => $penjualan['ringkasan'],
            'harian'           => $harian['harian'],
            'produk_terlaris'  => $terlaris,
        ]);
    }

    /**
     * GET /api/laporan/keuangan
     * Laporan keuangan: pendapatan, HPP, laba bruto.
     */
    public function keuangan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dari'   => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
        ]);

        $data = $this->laporanService->laporanKeuangan($validated['dari'], $validated['sampai']);
        return $this->successResponse($data);
    }

    /**
     * GET /api/laporan/produk-terlaris
     * Top produk terjual dalam periode.
     */
    public function produkTerlaris(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dari'   => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
            'limit'  => 'nullable|integer|min:1|max:50',
        ]);

        $data = $this->laporanService->produkTerlaris(
            $validated['dari'],
            $validated['sampai'],
            (int) ($validated['limit'] ?? 10)
        );

        return $this->successResponse($data);
    }

    /**
     * GET /api/laporan/export/pdf
     * Export laporan penjualan ke PDF.
     */
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'dari'   => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
            'tipe'   => 'nullable|in:penjualan,keuangan',
        ]);

        $tipe = $validated['tipe'] ?? 'penjualan';
        $data = $tipe === 'keuangan'
            ? $this->laporanService->laporanKeuangan($validated['dari'], $validated['sampai'])
            : $this->laporanService->laporanPenjualan($validated['dari'], $validated['sampai']);

        $data['terlaris'] = $this->laporanService->produkTerlaris($validated['dari'], $validated['sampai'], 10);
        $data['store']    = [
            'nama'    => config('pos.store_name', 'Toko Waris'),
            'alamat'  => config('pos.store_address', ''),
            'telepon' => config('pos.store_phone', ''),
        ];
        $data['tipe']         = $tipe;
        $data['generated_at'] = now()->format('d/m/Y H:i:s');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf', $data);
        $filename = "laporan_{$tipe}_{$validated['dari']}_sd_{$validated['sampai']}.pdf";

        return $pdf->download($filename);
    }

    /**
     * GET /api/laporan/export/excel
     * Export laporan penjualan ke Excel.
     */
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'dari'   => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
        ]);

        $filename = "laporan_penjualan_{$validated['dari']}_sd_{$validated['sampai']}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanPenjualanExport($validated['dari'], $validated['sampai']),
            $filename
        );
    }
}
