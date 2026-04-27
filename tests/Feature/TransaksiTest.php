<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransaksiTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;
    private User $pemilik;
    private Produk $produk;
    private string $kasirToken;
    private string $pemilikToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasir = User::create([
            'username' => 'kasir1', 'email' => 'kasir1@test.com',
            'password_hash' => Hash::make('password123'), 'role' => 'kasir',
            'nama_lengkap' => 'Test Kasir', 'is_active' => true,
        ]);
        $this->pemilik = User::create([
            'username' => 'pemilik', 'email' => 'pemilik@test.com',
            'password_hash' => Hash::make('password123'), 'role' => 'pemilik',
            'nama_lengkap' => 'Test Pemilik', 'is_active' => true,
        ]);

        $kategori = Kategori::create(['nama_kategori' => 'Test Kategori']);
        $this->produk = Produk::create([
            'kode_produk' => 'TST-001', 'nama_produk' => 'Produk Test',
            'kategori_id' => $kategori->kategori_id, 'harga_beli' => 5000,
            'harga_jual' => 8000, 'diskon_persen' => 0, 'satuan' => 'pcs', 'is_active' => true,
        ]);
        Stok::create([
            'produk_id' => $this->produk->produk_id, 'jumlah_stok' => 100,
            'stok_minimal' => 10,
        ]);

        $this->kasirToken   = $this->kasir->createToken('test')->plainTextToken;
        $this->pemilikToken = $this->pemilik->createToken('test', ['*'])->plainTextToken;
    }

    public function test_kasir_dapat_membuat_transaksi_baru(): void
    {
        $response = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 2]],
                'metode_pembayaran' => 'cash',
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'pending')
                 ->assertJsonPath('data.total_pembayaran', '16000.00');
    }

    public function test_transaksi_selesai_mengurangi_stok(): void
    {
        // Buat transaksi
        $res = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 3]],
            ]);
        $res->assertStatus(201);
        $trxId = $res->json('data.transaksi_id');

        // Bayar / selesaikan
        $res2 = $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/selesaikan", [
                'jumlah_bayar' => 30000,
            ]);
        $res2->assertStatus(200)
             ->assertJsonPath('data.status', 'selesai');

        // Cek stok berkurang
        $stok = Stok::where('produk_id', $this->produk->produk_id)->first();
        $this->assertEquals(97, $stok->jumlah_stok); // 100 - 3
    }

    public function test_tidak_bisa_selesaikan_dengan_bayar_kurang(): void
    {
        $res = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 2]],
            ]);
        $trxId = $res->json('data.transaksi_id');

        $res2 = $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/selesaikan", [
                'jumlah_bayar' => 100, // terlalu kecil
                'metode_pembayaran' => 'cash',
            ]);
        $res2->assertStatus(400);
    }

    public function test_transaksi_non_cash_otomatis_dianggap_lunas_sesuai_total_saat_tanpa_input_bayar(): void
    {
        $res = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 2]],
                'metode_pembayaran' => 'qris',
            ]);
        $trxId = $res->json('data.transaksi_id');

        $res2 = $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/selesaikan", [
                'jumlah_bayar' => 0,
                'metode_pembayaran' => 'qris',
            ]);

        $res2->assertStatus(200)
            ->assertJsonPath('data.status', 'selesai')
            ->assertJsonPath('data.jumlah_bayar', '16000.00')
            ->assertJsonPath('data.kembalian', '0.00');
    }

    public function test_transaksi_non_cash_tetap_ditolak_jika_pembayaran_kurang(): void
    {
        $res = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 2]],
                'metode_pembayaran' => 'transfer',
            ]);
        $trxId = $res->json('data.transaksi_id');

        $res2 = $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/selesaikan", [
                'jumlah_bayar' => 15000,
                'metode_pembayaran' => 'transfer',
            ]);

        $res2->assertStatus(400);
    }

    public function test_kasir_dapat_batalkan_transaksi_pending(): void
    {
        $res = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 1]],
            ]);
        $trxId = $res->json('data.transaksi_id');

        $res2 = $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/batalkan", ['alasan' => 'Pelanggan batal']);

        $res2->assertStatus(200)
             ->assertJsonPath('data.status', 'batal');
    }

    public function test_kasir_dapat_mengubah_jumlah_item_transaksi_pending(): void
    {
        $res = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 2]],
            ]);

        $trxId = $res->json('data.transaksi_id');
        $detailId = $res->json('data.details.0.detail_id');

        $res2 = $this->withToken($this->kasirToken)
            ->patchJson("/api/transaksi/{$trxId}/item/{$detailId}", [
                'jumlah' => 5,
            ]);

        $res2->assertStatus(200)
            ->assertJsonPath('data.details.0.jumlah', 5)
            ->assertJsonPath('data.total_harga', '40000.00')
            ->assertJsonPath('data.total_pembayaran', '40000.00');
    }

    public function test_kasir_dapat_mengubah_jumlah_item_pada_transaksi_selesai_selama_pembayaran_masih_mencukupi(): void
    {
        $res = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 2]],
            ]);

        $trxId = $res->json('data.transaksi_id');
        $detailId = $res->json('data.details.0.detail_id');

        $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/selesaikan", [
                'jumlah_bayar' => 30000,
            ])
            ->assertStatus(200);

        $res2 = $this->withToken($this->kasirToken)
            ->patchJson("/api/transaksi/{$trxId}/item/{$detailId}", [
                'jumlah' => 3,
            ]);

        $res2->assertStatus(200)
            ->assertJsonPath('data.status', 'selesai')
            ->assertJsonPath('data.details.0.jumlah', 3)
            ->assertJsonPath('data.total_pembayaran', '24000.00')
            ->assertJsonPath('data.kembalian', '6000.00');

        $stok = Stok::where('produk_id', $this->produk->produk_id)->first();
        $this->assertEquals(97, $stok->jumlah_stok);
    }

    public function test_transaksi_selesai_dibuka_ulang_jadi_pending_jika_total_baru_melebihi_pembayaran_lama(): void
    {
        $res = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 2]],
            ]);

        $trxId = $res->json('data.transaksi_id');
        $detailId = $res->json('data.details.0.detail_id');

        $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/selesaikan", [
                'jumlah_bayar' => 16000,
            ])
            ->assertStatus(200);

        $res2 = $this->withToken($this->kasirToken)
            ->patchJson("/api/transaksi/{$trxId}/item/{$detailId}", [
                'jumlah' => 3,
            ]);

        $res2->assertStatus(200)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.jumlah_bayar', '16000.00')
            ->assertJsonPath('data.total_pembayaran', '24000.00')
            ->assertJsonPath('data.kembalian', '0.00');

        $stok = Stok::where('produk_id', $this->produk->produk_id)->first();
        $this->assertEquals(97, $stok->jumlah_stok);

        $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/selesaikan", [
                'jumlah_bayar' => 8000,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'selesai')
            ->assertJsonPath('data.jumlah_bayar', '24000.00');

        $stok->refresh();
        $this->assertEquals(97, $stok->jumlah_stok);
    }

    public function test_kasir_dapat_menambah_item_ke_transaksi_selesai_dari_halaman_detail(): void
    {
        $res = $this->withToken($this->kasirToken)
            ->postJson('/api/transaksi', [
                'items' => [['produk_id' => $this->produk->produk_id, 'jumlah' => 1]],
            ]);

        $trxId = $res->json('data.transaksi_id');

        $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/selesaikan", [
                'jumlah_bayar' => 20000,
            ])
            ->assertStatus(200);

        $res2 = $this->withToken($this->kasirToken)
            ->postJson("/api/transaksi/{$trxId}/item", [
                'produk_id' => $this->produk->produk_id,
                'jumlah' => 1,
            ]);

        $res2->assertStatus(200)
            ->assertJsonPath('data.status', 'selesai')
            ->assertJsonPath('data.details.0.jumlah', 2)
            ->assertJsonPath('data.total_pembayaran', '16000.00')
            ->assertJsonPath('data.kembalian', '4000.00');

        $stok = Stok::where('produk_id', $this->produk->produk_id)->first();
        $this->assertEquals(98, $stok->jumlah_stok);
    }

    public function test_kasir_tidak_bisa_akses_laporan(): void
    {
        $response = $this->withToken($this->kasirToken)
            ->getJson('/api/laporan/dashboard');

        $response->assertStatus(403);
    }

    public function test_pemilik_bisa_akses_laporan_dashboard(): void
    {
        $response = $this->withToken($this->pemilikToken)
            ->getJson('/api/laporan/dashboard');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['hari_ini', 'bulan_ini']]);
    }

    public function test_barcode_lookup_berhasil(): void
    {
        Produk::where('produk_id', $this->produk->produk_id)
            ->update(['barcode' => '1234567890123']);

        $response = $this->withToken($this->kasirToken)
            ->getJson('/api/produk/barcode/1234567890123');

        $response->assertStatus(200)
                 ->assertJsonPath('data.nama_produk', 'Produk Test');
    }

    public function test_notifikasi_stok_minimal(): void
    {
        // Set stok di bawah minimal
        Stok::where('produk_id', $this->produk->produk_id)
            ->update(['jumlah_stok' => 2, 'stok_minimal' => 10]);

        $response = $this->withToken($this->pemilikToken)
            ->getJson('/api/stok/notifikasi');

        $response->assertStatus(200)
                 ->assertJsonPath('data.stok_minimal.count', 1);
    }
}
