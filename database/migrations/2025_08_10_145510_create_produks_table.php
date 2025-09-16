<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_kategori');
            $table->uuid('uuid_suplayer');
            $table->string('sub_kategori');
            $table->string('kode');
            $table->string('nama_barang');
            $table->string('merek');
            $table->string('hrg_modal');
            $table->string('profit');
            $table->string('minstock');
            $table->string('maxstock');
            $table->string('satuan');
            $table->string('profit_a')->nullable();
            $table->string('profit_b')->nullable();
            $table->string('profit_c')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        Schema::create('price_historys', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_produk');
            $table->string('harga');
            $table->timestamps();
        });

        Schema::create('opnames', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_user');
            $table->uuid('uuid_produk');
            $table->string('stock');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
        Schema::dropIfExists('price_historys');
        Schema::dropIfExists('opnames');
    }
};
