<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel transaksi penjualan beserta detail item yang dijual.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id('transaksi_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->restrictOnDelete();
            $table->string('nomor_transaksi', 50)->unique()->comment('Format: TRX-YYYYMMDD-XXXXX');
            $table->timestamp('tanggal_transaksi')->useCurrent();
            $table->decimal('total_harga', 12, 2)->default(0)->comment('Total sebelum diskon & pajak');
            $table->decimal('total_diskon', 12, 2)->default(0);
            $table->decimal('total_pajak', 12, 2)->default(0);
            $table->decimal('total_pembayaran', 12, 2)->default(0)->comment('Jumlah akhir yg harus dibayar');
            $table->decimal('jumlah_bayar', 12, 2)->default(0)->comment('Uang yang diserahkan pelanggan');
            $table->decimal('kembalian', 12, 2)->default(0);
            $table->enum('metode_pembayaran', ['cash', 'cashless', 'qris', 'transfer'])->default('cash');
            $table->enum('status', ['selesai', 'pending', 'batal'])->default('pending');
            $table->text('catatan')->nullable();
            $table->string('device_id', 50)->nullable()->comment('ID komputer kasir, untuk sinkronisasi offline');
            $table->boolean('is_synced')->default(true)->comment('Untuk mode offline, apakah sudah tersinkronkan');
            $table->timestamps();

            $table->index(['tanggal_transaksi', 'status']);
            $table->index('user_id');
        });

        Schema::create('detail_transaksis', function (Blueprint $table) {
            $table->id('detail_id');
            $table->foreignId('transaksi_id')->constrained('transaksis', 'transaksi_id')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks', 'produk_id')->restrictOnDelete();
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 12, 2)->comment('Harga per unit saat transaksi (snapshot)');
            $table->decimal('diskon_item', 12, 2)->default(0)->comment('Diskon per item');
            $table->decimal('subtotal', 12, 2)->comment('jumlah * harga_satuan - diskon_item');
            $table->timestamps();

            $table->index('transaksi_id');
        });
    }

    /**
     * Menghapus tabel transaksi penjualan beserta detail item saat rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksis');
        Schema::dropIfExists('transaksis');
    }
};
