<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Pengeluaran extends Model
{
    protected $table = 'pengeluarans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'no_bukti',
        'deskripsi',
        'sumber_dana',
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
