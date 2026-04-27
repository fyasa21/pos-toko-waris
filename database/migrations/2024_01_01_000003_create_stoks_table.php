<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel stok dan riwayat pergerakan stok barang.
     */
    public function up(): void
    {
        Schema::create('stoks', function (Blueprint $table) {
            $table->id('stok_id');
            $table->foreignId('produk_id')->unique()->constrained('produks', 'produk_id')->cascadeOnDelete();
            $table->integer('jumlah_stok')->default(0);
            $table->integer('stok_minimal')->default(5)->comment('Batas minimum stok sebelum notifikasi');
            $table->date('tanggal_kedaluwarsa')->nullable();
            $table->string('lokasi_rak', 50)->nullable();
            $table->timestamps();
        });

        // Riwayat pergerakan stok (masuk/keluar)
        Schema::create('stok_movements', function (Blueprint $table) {
            $table->id('movement_id');
            $table->foreignId('produk_id')->constrained('produks', 'produk_id')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->enum('tipe', ['masuk', 'keluar', 'penyesuaian'])->comment('masuk=stok bertambah, keluar=terjual/terpakai');
            $table->integer('jumlah')->comment('Jumlah perubahan stok');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->string('referensi', 100)->nullable()->comment('Nomor transaksi/pembelian rujukan');
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Menghapus tabel stok dan riwayat pergerakan stok saat rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_movements');
        Schema::dropIfExists('stoks');
    }
};
