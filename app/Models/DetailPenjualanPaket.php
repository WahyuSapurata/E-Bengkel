<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailPenjualanPaket extends Model
{
    protected $table = 'detail_penjualan_pakets';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_penjualans',
        'uuid_paket',
        'qty',
        'total_harga',
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
