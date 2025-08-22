<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class SubKategori extends Model
{
    protected $table = 'sub_kategoris';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'kode',
        'nama_sub_kategori',
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
