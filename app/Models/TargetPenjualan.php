<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class TargetPenjualan extends Model
{
    protected $table = 'target_penjualans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'tanggal',
        'target',
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
