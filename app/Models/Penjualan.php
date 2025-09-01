<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Penjualan extends Model
{
    protected $table = 'penjualans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_outlet',
        'uuid_jasa',
        'no_bukti',
        'tanggal_transaksi',
        'pembayaran',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'uuid_penjualans', 'uuid');
    }
}
