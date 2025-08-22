<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PriceHistory extends Model
{
    protected $table = 'price_historys';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_produk',
        'harga',
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
