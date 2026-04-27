<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\StokMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StokTest extends TestCase
{
    use RefreshDatabase;

    public function test_tambah_stok_membuat_record_stok_jika_belum_ada(): void
    {
        $pemilik = User::create([
            'username' => 'pemilik',
            'email' => 'pemilik@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'pemilik',
            'nama_lengkap' => 'Test Pemilik',
            'is_active' => true,
        ]);

        $kategori = Kategori::create(['nama_kategori' => 'Minuman']);
        $produk = Produk::create([
            'kode_produk' => 'PRD-002',
            'nama_produk' => 'Air Mineral',
            'kategori_id' => $kategori->kategori_id,
            'harga_beli' => 2000,
            'harga_jual' => 3000,
            'diskon_persen' => 0,
            'satuan' => 'pcs',
            'is_active' => true,
        ]);

        $token = $pemilik->createToken('test', ['*'])->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/stok/{$produk->produk_id}/tambah", [
            'jumlah' => 7,
            'keterangan' => 'Restok awal',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.jumlah_stok', 7)
            ->assertJsonPath('data.stok_minimal', 5);

        $stok = Stok::where('produk_id', $produk->produk_id)->firstOrFail();
        $this->assertSame(7, $stok->jumlah_stok);
        $this->assertSame(5, $stok->stok_minimal);

        $movement = StokMovement::where('produk_id', $produk->produk_id)
            ->latest('movement_id')
            ->first();

        $this->assertNotNull($movement);
        $this->assertSame('masuk', $movement->tipe);
        $this->assertSame(7, $movement->jumlah);
    }
}
