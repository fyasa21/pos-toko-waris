<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProdukTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_dapat_set_stok_minimal_nol_dan_mengosongkan_metadata_stok(): void
    {
        $pemilik = User::create([
            'username' => 'pemilik',
            'email' => 'pemilik@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'pemilik',
            'nama_lengkap' => 'Test Pemilik',
            'is_active' => true,
        ]);

        $kategori = Kategori::create(['nama_kategori' => 'Sembako']);
        $produk = Produk::create([
            'kode_produk' => 'PRD-001',
            'nama_produk' => 'Beras',
            'kategori_id' => $kategori->kategori_id,
            'harga_beli' => 10000,
            'harga_jual' => 12000,
            'diskon_persen' => 0,
            'satuan' => 'kg',
            'is_active' => true,
        ]);

        Stok::create([
            'produk_id' => $produk->produk_id,
            'jumlah_stok' => 50,
            'stok_minimal' => 10,
            'tanggal_kedaluwarsa' => '2026-12-31',
            'lokasi_rak' => 'RAK-A1',
        ]);

        $token = $pemilik->createToken('test', ['*'])->plainTextToken;

        $response = $this->withToken($token)->putJson("/api/produk/{$produk->produk_id}", [
            'stok_minimal' => 0,
            'tanggal_kedaluwarsa' => null,
            'lokasi_rak' => null,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.stok.stok_minimal', 0)
            ->assertJsonPath('data.stok.tanggal_kedaluwarsa', null)
            ->assertJsonPath('data.stok.lokasi_rak', null);

        $stok = Stok::where('produk_id', $produk->produk_id)->firstOrFail();
        $this->assertSame(0, $stok->stok_minimal);
        $this->assertNull($stok->tanggal_kedaluwarsa);
        $this->assertNull($stok->lokasi_rak);
    }

        public function test_produk_ditolak_jika_harga_jual_lebih_kecil_dari_harga_beli(): void
    {
        $pemilik = User::create([
            'username' => 'pemilik_validasi',
            'email' => 'pemilik.validasi@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'pemilik',
            'nama_lengkap' => 'Test Pemilik Validasi',
            'is_active' => true,
        ]);

        $kategori = Kategori::create(['nama_kategori' => 'Sembako']);
        $token = $pemilik->createToken('test', ['*'])->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/produk', [
            'kode_produk' => 'PRD-HARGA-001',
            'nama_produk' => 'Produk Harga Tidak Valid',
            'kategori_id' => $kategori->kategori_id,
            'harga_beli' => 10000,
            'harga_jual' => 9000,
            'diskon_persen' => 0,
            'satuan' => 'pcs',
            'stok_awal' => 10,
            'stok_minimal' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['harga_jual']);

        $this->assertDatabaseMissing('produks', [
            'kode_produk' => 'PRD-HARGA-001',
        ]);
    }
}
