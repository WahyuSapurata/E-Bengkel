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
        Schema::create('pengiriman_barangs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_outlet');
            $table->uuid('uuid_po_outlet');
            $table->string('no_do');
            $table->string('tanggal_kirim');
            $table->string('status')->default('dikirim');
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_pengiriman_barangs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_pengiriman_barang');
            $table->uuid('uuid_produk');
            $table->integer('qty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman_barangs');
        Schema::dropIfExists('detail_pengiriman_barangs');
    }
};
