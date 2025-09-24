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
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_outlet');
            $table->json('uuid_jasa')->nullable();
            $table->string('no_bukti');
            $table->string('tanggal_transaksi');
            $table->string('pembayaran');
            $table->string('created_by');
            $table->timestamps();
        });

        Schema::create('detail_penjualans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_penjualans');
            $table->uuid('uuid_produk');
            $table->integer('qty');
            $table->string('total_harga');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
        Schema::dropIfExists('detail_penjualans');
    }
};
