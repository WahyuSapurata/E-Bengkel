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
        Schema::create('pemindahan_danas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('no_bukti');
            $table->string('deskripsi');
            $table->string('sumber_dana');
            $table->string('tujuan_dana');
            $table->string('nominal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemindahan_danas');
    }
};
