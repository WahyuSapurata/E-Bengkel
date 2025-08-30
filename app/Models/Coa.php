<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Coa extends Model
{
    protected $table = 'coas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'kode',
        'nama',
        'tipe',
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
