<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stok extends Model
{
    protected $primaryKey = 'stok_id';

    protected $fillable = [
        'produk_id',
        'jumlah_stok',
        'stok_minimal',
        'tanggal_kedaluwarsa',
        'lokasi_rak',
    ];

    protected $casts = [
        'tanggal_kedaluwarsa' => 'date',
        'jumlah_stok'         => 'integer',
        'stok_minimal'        => 'integer',
    ];

    // â”€â”€â”€ Scopes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Menerapkan filter query untuk stok yang sudah berada di bawah batas minimum.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('jumlah_stok', '<=', 'stok_minimal');
    }

    /**
     * Menerapkan filter query untuk stok yang akan segera kedaluwarsa.
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('tanggal_kedaluwarsa')
                     ->whereDate('tanggal_kedaluwarsa', '<=', now()->addDays($days))
                     ->whereDate('tanggal_kedaluwarsa', '>=', now());
    }

    /**
     * Menerapkan filter query untuk stok yang sudah melewati tanggal kedaluwarsa.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('tanggal_kedaluwarsa')
                     ->whereDate('tanggal_kedaluwarsa', '<', now());
    }

    // â”€â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Memeriksa apakah jumlah stok saat ini berada di bawah batas minimum.
     */
    public function isLowStock(): bool
    {
        return $this->jumlah_stok <= $this->stok_minimal;
    }

    /**
     * Memeriksa apakah stok akan kedaluwarsa dalam beberapa hari ke depan.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->tanggal_kedaluwarsa) return false;
        return $this->tanggal_kedaluwarsa->lte(now()->addDays($days));
    }

    /**
     * Memeriksa apakah stok sudah melewati tanggal kedaluwarsa.
     */
    public function isExpired(): bool
    {
        if (!$this->tanggal_kedaluwarsa) return false;
        return $this->tanggal_kedaluwarsa->lt(now());
    }

    // â”€â”€â”€ Relations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Mendefinisikan relasi stok dengan produk yang dimiliki.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }
}
