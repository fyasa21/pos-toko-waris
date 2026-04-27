<?php

namespace App\Exports;

use App\Models\Transaksi;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPenjualanExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    /**
     * Menginisialisasi parameter periode laporan yang akan diekspor.
     */
    public function __construct(
        private string $dari,
        private string $sampai
    ) {}

    /**
     * Mengambil data transaksi penjualan yang akan dimasukkan ke file Excel.
     */
    public function collection()
    {
        return Transaksi::with(['user:user_id,nama_lengkap', 'details'])
            ->selesai()
            ->whereBetween('tanggal_transaksi', [
                Carbon::parse($this->dari)->startOfDay(),
                Carbon::parse($this->sampai)->endOfDay(),
            ])
            ->orderBy('tanggal_transaksi')
            ->get();
    }

    /**
     * Menentukan judul kolom yang tampil pada sheet laporan.
     */
    public function headings(): array
    {
        return [
            'No. Transaksi',
            'Tanggal',
            'Kasir',
            'Jumlah Item',
            'Total Harga',
            'Total Diskon',
            'Total Pajak',
            'Total Pembayaran',
            'Metode Bayar',
            'Jumlah Bayar',
            'Kembalian',
        ];
    }

    /**
     * Memetakan setiap transaksi menjadi satu baris data pada hasil ekspor.
     */
    public function map($transaksi): array
    {
        return [
            $transaksi->nomor_transaksi,
            $transaksi->tanggal_transaksi->format('d/m/Y H:i'),
            $transaksi->user->nama_lengkap ?? '-',
            $transaksi->details->sum('jumlah'),
            'Rp ' . number_format($transaksi->total_harga, 0, ',', '.'),
            'Rp ' . number_format($transaksi->total_diskon, 0, ',', '.'),
            'Rp ' . number_format($transaksi->total_pajak, 0, ',', '.'),
            'Rp ' . number_format($transaksi->total_pembayaran, 0, ',', '.'),
            strtoupper($transaksi->metode_pembayaran),
            'Rp ' . number_format($transaksi->jumlah_bayar, 0, ',', '.'),
            'Rp ' . number_format($transaksi->kembalian, 0, ',', '.'),
        ];
    }

    /**
     * Menentukan nama sheet pada file Excel hasil ekspor.
     */
    public function title(): string
    {
        return "Laporan {$this->dari} sd {$this->sampai}";
    }

    /**
     * Menerapkan gaya dasar agar tampilan sheet lebih mudah dibaca.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
