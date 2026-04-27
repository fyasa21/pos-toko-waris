<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokMovement extends Model
{
    protected $primaryKey = 'movement_id';
    public $timestamps = false;

    protected $fillable = [
        'produk_id',
        'user_id',
        'tipe',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'referensi',
        'keterangan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Mendefinisikan relasi riwayat pergerakan stok dengan produk terkait.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }

    /**
     * Mendefinisikan relasi riwayat pergerakan stok dengan pengguna yang memicu perubahan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
