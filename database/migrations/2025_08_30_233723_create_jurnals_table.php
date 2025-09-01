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
        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_coa');
            $table->uuid('uuid_outlet')->nullable();
            $table->string('tanggal');
            $table->string('ref')->nullable();
            $table->text('deskripsi');
            $table->string('debit')->default(0);
            $table->string('kredit')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};
