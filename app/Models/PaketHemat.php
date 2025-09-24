<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PaketHemat extends Model
{
    protected $table = 'paket_hemats';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_produk',
        'nama_paket',
        'total_modal',
        'profit',
        'keterangan',
    ];

    protected $casts = [
        'uuid_produk' => 'array', // otomatis cast ke array pas ambil/simpan
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
