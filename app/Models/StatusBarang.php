<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class StatusBarang extends Model
{
    protected $table = 'status_barangs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_log_barang',
        'ref',
        'ketarangan',
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
