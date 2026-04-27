<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembelianPemasok extends Model
{
    protected $primaryKey = 'pembelian_id';

    protected $fillable = [
        'pemasok_id',
        'user_id',
        'nomor_pembelian',
        'tanggal_pembelian',
        'total_harga',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pembelian' => 'datetime',
        'total_harga'       => 'decimal:2',
    ];

    /**
     * Menerapkan filter query untuk pembelian pemasok yang sudah selesai.
     */
    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    /**
     * Mendefinisikan relasi pembelian dengan pemasok terkait.
     */
    public function pemasok(): BelongsTo
    {
        return $this->belongsTo(Pemasok::class, 'pemasok_id', 'pemasok_id');
    }

    /**
     * Mendefinisikan relasi pembelian dengan pengguna yang membuatnya.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Mendefinisikan relasi pembelian dengan seluruh detail item yang dibeli.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailPembelianPemasok::class, 'pembelian_id', 'pembelian_id');
    }
}
