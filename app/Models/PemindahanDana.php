<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PemindahanDana extends Model
{
    protected $table = 'pemindahan_danas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'no_bukti',
        'deskripsi',
        'sumber_dana',
        'tujuan_dana',
        'nominal',
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
