<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailPengirimanBarang extends Model
{
    protected $table = 'detail_pengiriman_barangs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_pengiriman_barang',
        'uuid_produk',
        'qty',
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
