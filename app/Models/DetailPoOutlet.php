<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailPoOutlet extends Model
{
    protected $table = 'detail_po_outlets';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_po_outlet',
        'uuid_produk',
        'qty',
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
