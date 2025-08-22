<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PoPusat extends Model
{
    protected $table = 'po_pusats';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_suplayer',
        'no_po',
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
}
