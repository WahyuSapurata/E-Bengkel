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
        Schema::create('closing_kasirs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_kasir_outlet');
            $table->string('tanggal_closing');
            $table->string('total_penjualan');
            $table->string('total_cash');
            $table->string('total_transfer');
            $table->string('total_fisik');
            $table->string('selisih')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closing_kasirs');
    }
};
