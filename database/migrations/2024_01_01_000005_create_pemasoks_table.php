<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel pemasok, pembelian pemasok, dan detail barang yang dibeli.
     */
    public function up(): void
    {
        Schema::create('pemasoks', function (Blueprint $table) {
            $table->id('pemasok_id');
            $table->string('nama_pemasok', 100);
            $table->string('kontak_person', 100)->nullable();
            $table->string('nomor_telepon', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->string('kota', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pembelian_pemasoks', function (Blueprint $table) {
            $table->id('pembelian_id');
            $table->foreignId('pemasok_id')->constrained('pemasoks', 'pemasok_id')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id')->restrictOnDelete();
            $table->string('nomor_pembelian', 50)->unique()->comment('Format: PO-YYYYMMDD-XXXXX');
            $table->timestamp('tanggal_pembelian')->useCurrent();
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->enum('status', ['selesai', 'pending', 'batal'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tanggal_pembelian', 'status']);
        });

        Schema::create('detail_pembelian_pemasoks', function (Blueprint $table) {
            $table->id('detail_pembelian_id');
            $table->foreignId('pembelian_id')->constrained('pembelian_pemasoks', 'pembelian_id')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks', 'produk_id')->restrictOnDelete();
            $table->integer('jumlah');
            $table->decimal('harga_beli', 12, 2);
            $table->decimal('subtotal', 12, 2)->comment('jumlah * harga_beli');
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel pemasok dan pembelian pemasok saat rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelian_pemasoks');
        Schema::dropIfExists('pembelian_pemasoks');
        Schema::dropIfExists('pemasoks');
    }
};
