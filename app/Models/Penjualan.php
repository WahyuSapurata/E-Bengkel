<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Penjualan extends Model
{
    protected $table = 'penjualans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_outlet',
        'uuid_jasa',
        'no_bukti',
        'tanggal_transaksi',
        'pembayaran',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    // ✅ Relasi yang benar: Penjualan punya banyak detail
    public function detailPenjualans()
    {
        return $this->hasMany(DetailPenjualan::class, 'uuid_penjualans', 'uuid');
    }

    public function jasa()
    {
        return $this->belongsTo(Jasa::class, 'uuid_jasa', 'uuid');
    }
}
