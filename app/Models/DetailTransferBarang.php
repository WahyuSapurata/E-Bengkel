<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailTransferBarang extends Model
{
    protected $table = 'detail_transfer_barangs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_transfer_barangs',
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
