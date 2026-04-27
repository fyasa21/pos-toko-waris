<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role',
        'nama_lengkap',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'last_login_at' => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Override getAuthPassword for Sanctum to use password_hash column.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // â”€â”€â”€ Scopes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Menerapkan filter query untuk pengguna yang masih aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Menerapkan filter query berdasarkan role pengguna.
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // â”€â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Memeriksa apakah pengguna memiliki role pemilik.
     */
    public function isPemilik(): bool
    {
        return $this->role === 'pemilik';
    }

    /**
     * Memeriksa apakah pengguna memiliki role kasir.
     */
    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    // â”€â”€â”€ Relations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Mendefinisikan relasi pengguna dengan transaksi yang pernah dibuat.
     */
    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'user_id', 'user_id');
    }

    /**
     * Mendefinisikan relasi pengguna dengan data pembelian yang pernah dibuat.
     */
    public function pembelians(): HasMany
    {
        return $this->hasMany(PembelianPemasok::class, 'user_id', 'user_id');
    }

    /**
     * Mendefinisikan relasi pengguna dengan riwayat aktivitas yang tercatat.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id', 'user_id');
    }
}
