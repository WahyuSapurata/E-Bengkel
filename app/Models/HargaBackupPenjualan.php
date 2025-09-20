<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class HargaBackupPenjualan extends Model
{
    protected $table = 'harga_backup_penjualans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_detail_penjualan',
        'harga_modal',
        'harga_jual',
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
