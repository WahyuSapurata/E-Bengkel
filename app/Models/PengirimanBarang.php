<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PengirimanBarang extends Model
{
    protected $table = 'pengiriman_barangs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_outlet',
        'uuid_po_outlet',
        'no_do',
        'tanggal_kirim',
        'status',
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
}
