<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Produk extends Model
{
    use HasFactory;

    protected $primaryKey = 'produk_id';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'kategori_id',
        'harga_beli',
        'harga_jual',
        'diskon_persen',
        'barcode',
        'satuan',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'harga_beli'    => 'decimal:2',
        'harga_jual'    => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    // â”€â”€â”€ Scopes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Menerapkan filter query untuk produk yang masih aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Menerapkan filter pencarian produk berdasarkan nama, kode, atau barcode.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nama_produk', 'like', "%{$term}%")
              ->orWhere('kode_produk', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%");
        });
    }

    // â”€â”€â”€ Computed â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Harga jual setelah diskon produk.
     */
    public function getHargaEfektifAttribute(): float
    {
        $diskon = ($this->diskon_persen / 100) * $this->harga_jual;
        return round($this->harga_jual - $diskon, 2);
    }

    // â”€â”€â”€ Relations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Mendefinisikan relasi produk dengan kategori tempat produk dikelompokkan.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'kategori_id');
    }

    /**
     * Mendefinisikan relasi produk dengan data stok aktifnya.
     */
    public function stok(): HasOne
    {
        return $this->hasOne(Stok::class, 'produk_id', 'produk_id');
    }

    /**
     * Mendefinisikan relasi produk dengan seluruh detail transaksi penjualan.
     */
    public function detailTransaksis(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'produk_id', 'produk_id');
    }

    /**
     * Mendefinisikan relasi produk dengan riwayat pergerakan stoknya.
     */
    public function stokMovements(): HasMany
    {
        return $this->hasMany(StokMovement::class, 'produk_id', 'produk_id');
    }

    /**
     * Mendefinisikan relasi produk dengan detail pembelian dari pemasok.
     */
    public function detailPembelians(): HasMany
    {
        return $this->hasMany(DetailPembelianPemasok::class, 'produk_id', 'produk_id');
    }
}
