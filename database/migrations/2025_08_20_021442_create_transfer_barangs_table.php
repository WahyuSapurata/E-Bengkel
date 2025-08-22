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
        Schema::create('transfer_barangs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_outlet');
            $table->string('no_bukti');
            $table->string('tanggal_transfer');
            $table->string('created_by');
            $table->timestamps();
        });

        Schema::create('detail_transfer_barangs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_transfer_barangs');
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
        Schema::dropIfExists('transfer_barangs');
        Schema::dropIfExists('detail_transfer_barangs');
    }
};
