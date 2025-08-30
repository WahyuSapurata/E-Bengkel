<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Biaya extends Model
{
    protected $table = 'biayas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_coa',
        'tanggal',
        'deskripsi',
        'jumlah',
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
