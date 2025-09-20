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
        Schema::create('harga_backup_penjualans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_detail_penjualan');
            $table->string('harga_modal');
            $table->string('harga_jual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_backup_penjualans');
    }
};
