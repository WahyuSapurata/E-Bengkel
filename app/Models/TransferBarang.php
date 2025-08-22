<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class TransferBarang extends Model
{
    protected $table = 'transfer_barangs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_outlet',
        'no_bukti',
        'tanggal_transfer',
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
