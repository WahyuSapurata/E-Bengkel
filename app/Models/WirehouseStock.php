<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class WirehouseStock extends Model
{
    protected $table = 'wirehouse_stocks';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_warehouse',
        'uuid_produk',
        'qty',
        'jenis',
        'sumber',
        'keterangan',
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
