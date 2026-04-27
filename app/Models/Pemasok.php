<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemasok extends Model
{
    use HasFactory;

    protected $primaryKey = 'pemasok_id';

    protected $fillable = [
        'nama_pemasok',
        'kontak_person',
        'nomor_telepon',
        'email',
        'alamat',
        'kota',
        'is_active',
        'catatan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Menerapkan filter query untuk pemasok yang masih aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Mendefinisikan relasi pemasok dengan daftar pembelian yang tercatat.
     */
    public function pembelians(): HasMany
    {
        return $this->hasMany(PembelianPemasok::class, 'pemasok_id', 'pemasok_id');
    }
}
