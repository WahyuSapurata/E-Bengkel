<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Hutang extends Model
{
    protected $table = 'hutangs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_pembelian',
        'jatuh_tempo',
        'jumlah_terbayarkan',
        'status',
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
