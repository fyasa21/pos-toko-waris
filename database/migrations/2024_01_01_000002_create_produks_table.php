<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel kategori dan produk yang dipakai pada modul katalog barang.
     */
    public function up(): void
    {
        Schema::create('kategoris', function (Blueprint $table) {
            $table->id('kategori_id');
            $table->string('nama_kategori', 50)->unique();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('produks', function (Blueprint $table) {
            $table->id('produk_id');
            $table->string('kode_produk', 20)->unique();
            $table->string('nama_produk', 100);
            $table->foreignId('kategori_id')->nullable()->constrained('kategoris', 'kategori_id')->nullOnDelete();
            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('harga_jual', 12, 2);
            $table->decimal('diskon_persen', 5, 2)->default(0)->comment('Diskon dalam persen (0-100)');
            $table->string('barcode', 50)->unique()->nullable();
            $table->string('satuan', 20)->default('pcs')->comment('pcs, kg, liter, dll');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel kategori dan produk saat rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
        Schema::dropIfExists('kategoris');
    }
};
