<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Pembelian extends Model
{
    protected $table = 'pembelians';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_suplayer',
        'no_invoice',
        'no_internal',
        'pembayaran',
        'tanggal_transaksi',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    public function details()
    {
        return $this->hasMany(DetailPembelian::class, 'uuid_pembelian', 'uuid');
    }
}
