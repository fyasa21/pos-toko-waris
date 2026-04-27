<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'data',
    ];

    protected $casts = [
        'data'       => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Mendefinisikan relasi log aktivitas dengan pengguna yang melakukan aksi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Helper statis untuk mencatat aktivitas dengan mudah.
     */
    public static function record(
        ?int $userId,
        string $action,
        string $module,
        string $description,
        ?array $data = null,
        ?string $ip = null
    ): void {
        static::create([
            'user_id'     => $userId,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => $ip ?? request()->ip(),
            'data'        => $data,
        ]);
    }
}
