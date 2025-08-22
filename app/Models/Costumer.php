<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Costumer extends Model
{
    protected $table = 'costumers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'nama',
        'alamat',
        'nomor',
        'plat',
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
