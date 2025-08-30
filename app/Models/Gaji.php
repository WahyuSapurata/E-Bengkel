<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Gaji extends Model
{
    protected $table = 'gajis';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_karyawan',
        'tanggal',
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
