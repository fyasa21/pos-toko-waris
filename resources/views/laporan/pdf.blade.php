<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan {{ ucfirst($tipe) }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; color: #0f766e; }
        .header p { margin: 5px 0 0; color: #666; font-size: 11px; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
        .section-title { font-size: 14px; font-weight: bold; margin: 25px 0 10px; color: #0f766e; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; text-transform: uppercase; font-size: 10px; color: #555; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-box { border: 1px solid #ddd; padding: 15px; background: #fafafa; margin-bottom: 20px; }
        .summary-table { width: 100%; border: none; margin: 0; }
        .summary-table td { border: none; padding: 5px; }
        .summary-val { font-weight: bold; text-align: right; font-size: 14px; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 9px; color: #999; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $store['nama'] ?? 'POS System' }}</h1>
        <p>{{ $store['alamat'] ?? '' }} | Telp: {{ $store['telepon'] ?? '-' }}</p>
    </div>

    <div class="title">
        Laporan {{ ucfirst($tipe) }}<br>
        <span style="font-size: 12px; font-weight: normal; color: #666;">Periode: {{ date('d/m/Y', strtotime($periode['dari'])) }} - {{ date('d/m/Y', strtotime($periode['sampai'])) }}</span>
    </div>

    @if($tipe === 'penjualan' && isset($ringkasan))
        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td>Total Transaksi Selesai</td>
                    <td class="summary-val">{{ number_format($ringkasan['total_transaksi'], 0, ',', '.') }}</td>
                    <td>Item Terjual</td>
                    <td class="summary-val">{{ number_format($ringkasan['total_item_terjual'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Pendapatan</td>
                    <td class="summary-val" style="color: #0f766e;">Rp {{ number_format($ringkasan['total_pendapatan'], 0, ',', '.') }}</td>
                    <td>Total Diskon</td>
                    <td class="summary-val">Rp {{ number_format($ringkasan['total_diskon'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="section-title">Rincian Transaksi</div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No. Transaksi</th>
                    <th>Kasir</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $index => $trx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ date('d-m-Y H:i', strtotime($trx->tanggal_transaksi)) }}</td>
                    <td>{{ $trx->nomor_transaksi }}</td>
                    <td>{{ $trx->user->nama_lengkap ?? 'Unknown' }}</td>
                    <td class="text-right">Rp {{ number_format($trx->total_pembayaran, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">Tidak ada transaksi di periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if($tipe === 'keuangan' && isset($total_penjualan_bersih))
        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td>Penjualan Kotor</td>
                    <td class="summary-val">Rp {{ number_format($total_penjualan_kotor ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Diskon & Potongan</td>
                    <td class="summary-val" style="color: #ef4444;">- Rp {{ number_format($total_diskon ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #ccc; padding-bottom: 10px;">Penjualan Bersih</td>
                    <td class="summary-val" style="border-bottom: 1px solid #ccc; padding-bottom: 10px;">Rp {{ number_format($total_penjualan_bersih ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding-top: 10px;">Harga Pokok Penjualan (HPP)</td>
                    <td class="summary-val" style="padding-top: 10px; color: #ef4444;">- Rp {{ number_format($total_hpp ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-size: 14px; font-weight: bold;">Laba Bruto</td>
                    <td class="summary-val" style="font-size: 16px; color: #0f766e;">Rp {{ number_format($laba_bruto ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right" style="font-size: 11px; color: #666;">
                        Margin: {{ $margin_persen ?? 0 }}%
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <div class="page-break"></div>

    <div class="section-title">Top 10 Produk Terlaris</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th class="text-center">Qty Terjual</th>
                <th class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($terlaris as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->kode_produk }}</td>
                <td>{{ $item->nama_produk }}</td>
                <td class="text-center">{{ number_format($item->total_terjual, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">Belum ada data penjualan produk.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ $generated_at }} oleh Sistem POS
    </div>

</body>
</html>
