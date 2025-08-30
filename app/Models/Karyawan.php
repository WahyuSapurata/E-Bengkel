<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Karyawan extends Model
{
    protected $table = 'karyawans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'nama',
        'alamat',
        'nomor',
        'jabatan',
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
