<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    protected $primaryKey = 'kategori_id';

    protected $fillable = ['nama_kategori', 'deskripsi'];

    /**
     * Mendefinisikan relasi kategori dengan seluruh produk yang berada di dalamnya.
     */
    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'kategori_id', 'kategori_id');
    }
}
