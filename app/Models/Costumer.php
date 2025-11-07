<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Costumer extends Model
{
    protected $table = 'costumers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_penjualan',
        'uuid_outlet',
        'nama',
        'alamat',
        'nomor',
        'plat',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    // Relasi ke tabel penjualan
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'uuid_penjualan', 'uuid');
    }

    // Relasi ke tabel outlet
    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'uuid_outlet', 'uuid_user');
    }
}
