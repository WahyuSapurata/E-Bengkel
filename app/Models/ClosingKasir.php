<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ClosingKasir extends Model
{
    protected $table = 'closing_kasirs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_kasir_outlet',
        'tanggal_closing',
        'total_penjualan',
        'total_cash',
        'total_transfer',
        'total_fisik',
        'selisih',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    public function kasirOutlet()
    {
        return $this->belongsTo(KasirOutlet::class, 'uuid_kasir_outlet', 'uuid_user');
    }
}
