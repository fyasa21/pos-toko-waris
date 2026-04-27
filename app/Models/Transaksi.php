<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaksi_id';

    protected $fillable = [
        'user_id',
        'nomor_transaksi',
        'tanggal_transaksi',
        'total_harga',
        'total_diskon',
        'total_pajak',
        'total_pembayaran',
        'jumlah_bayar',
        'kembalian',
        'metode_pembayaran',
        'status',
        'catatan',
        'device_id',
        'is_synced',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'total_harga'       => 'decimal:2',
        'total_diskon'      => 'decimal:2',
        'total_pajak'       => 'decimal:2',
        'total_pembayaran'  => 'decimal:2',
        'jumlah_bayar'      => 'decimal:2',
        'kembalian'         => 'decimal:2',
        'is_synced'         => 'boolean',
    ];

    // â”€â”€â”€ Scopes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Menerapkan filter query untuk transaksi yang sudah selesai.
     */
    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    /**
     * Menerapkan filter query untuk transaksi yang masih pending.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Menerapkan filter query transaksi berdasarkan rentang tanggal tertentu.
     */
    public function scopeByPeriode($query, string $dari, string $sampai)
    {
        return $query->whereBetween('tanggal_transaksi', [$dari, $sampai]);
    }

    // â”€â”€â”€ Relations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Mendefinisikan relasi transaksi dengan kasir atau pengguna yang membuatnya.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Mendefinisikan relasi transaksi dengan seluruh item yang dijual.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id', 'transaksi_id');
    }
}
