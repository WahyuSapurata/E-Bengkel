<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Jurnal extends Model
{
    protected $table = 'jurnals';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_coa',
        'uuid_outlet',
        'tanggal',
        'ref',
        'deskripsi',
        'debit',
        'kredit',
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
