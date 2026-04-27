<?php

namespace App\Console\Commands;

use App\Models\Stok;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CekStokCommand extends Command
{
    protected $signature   = 'pos:cek-stok {--days=30 : Jumlah hari ke depan untuk cek kedaluwarsa}';
    protected $description = 'Cek stok minimal dan kedaluwarsa, catat ke log';

    /**
     * Menjalankan command untuk memeriksa stok minimal dan produk yang mendekati kedaluwarsa.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        // Stok minimal
        $stokMin = Stok::with('produk:produk_id,kode_produk,nama_produk')
            ->lowStock()
            ->get();

        if ($stokMin->isNotEmpty()) {
            $this->warn("âš ï¸  {$stokMin->count()} produk stok minimal:");
            foreach ($stokMin as $s) {
                $msg = "[STOK_MIN] {$s->produk->kode_produk} - {$s->produk->nama_produk}: stok={$s->jumlah_stok}, min={$s->stok_minimal}";
                $this->line("   $msg");
                Log::warning($msg);
            }
        } else {
            $this->info('âœ… Semua stok di atas batas minimum.');
        }

        // Kedaluwarsa
        $expiring = Stok::with('produk:produk_id,kode_produk,nama_produk')
            ->expiringSoon($days)
            ->get();

        if ($expiring->isNotEmpty()) {
            $this->warn("âš ï¸  {$expiring->count()} produk akan kedaluwarsa dalam {$days} hari:");
            foreach ($expiring as $s) {
                $msg = "[KEDALUWARSA] {$s->produk->kode_produk} - {$s->produk->nama_produk}: exp={$s->tanggal_kedaluwarsa}";
                $this->line("   $msg");
                Log::warning($msg);
            }
        } else {
            $this->info("âœ… Tidak ada produk kedaluwarsa dalam {$days} hari ke depan.");
        }

        $expired = Stok::with('produk:produk_id,kode_produk,nama_produk')->expired()->get();
        if ($expired->isNotEmpty()) {
            $this->error("ðŸš« {$expired->count()} produk SUDAH kedaluwarsa:");
            foreach ($expired as $s) {
                $msg = "[EXPIRED] {$s->produk->kode_produk} - {$s->produk->nama_produk}: exp={$s->tanggal_kedaluwarsa}";
                $this->line("   $msg");
                Log::error($msg);
            }
        }

        $this->info('Selesai cek stok: ' . now()->format('d/m/Y H:i:s'));
        return Command::SUCCESS;
    }
}
