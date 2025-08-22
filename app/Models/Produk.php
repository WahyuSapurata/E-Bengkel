<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Produk extends Model
{
    protected $table = 'produks';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_kategori',
        'uuid_sub_kategori',
        'uuid_suplayer',
        'kode',
        'nama_barang',
        'merek',
        'hrg_modal',
        'profit',
        'minstock',
        'maxstock',
        'satuan',
        'profit_a',
        'profit_b',
        'profit_c',
        'foto',
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
