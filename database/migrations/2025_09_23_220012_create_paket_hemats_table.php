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
        Schema::create('paket_hemats', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->json('uuid_produk');
            $table->string('nama_paket');
            $table->string('total_modal');
            $table->string('profit');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_hemats');
    }
};
