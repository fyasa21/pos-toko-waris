<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'transaksi_id',
        'produk_id',
        'jumlah',
        'harga_satuan',
        'diskon_item',
        'subtotal',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'diskon_item'  => 'decimal:2',
        'subtotal'     => 'decimal:2',
        'jumlah'       => 'integer',
    ];

    /**
     * Mendefinisikan relasi detail transaksi dengan header transaksinya.
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id', 'transaksi_id');
    }

    /**
     * Mendefinisikan relasi detail transaksi dengan produk yang dijual.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }
}
