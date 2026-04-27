<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPembelianPemasok extends Model
{
    protected $primaryKey = 'detail_pembelian_id';

    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'jumlah',
        'harga_beli',
        'subtotal',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'jumlah'     => 'integer',
    ];

    /**
     * Mendefinisikan relasi detail pembelian dengan header pembelian pemasok.
     */
    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(PembelianPemasok::class, 'pembelian_id', 'pembelian_id');
    }

    /**
     * Mendefinisikan relasi detail pembelian dengan produk yang dibeli.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }
}
