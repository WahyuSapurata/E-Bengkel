<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailPembelian extends Model
{
    protected $table = 'detail_pembelians';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_pembelian',
        'uuid_produk',
        'qty',
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

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'uuid_produk', 'uuid');
    }
}
