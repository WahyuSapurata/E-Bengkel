<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ProdukPrice extends Model
{
    protected $table = 'produk_prices';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_produk',
        'qty',
        'harga_jual',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }
}
