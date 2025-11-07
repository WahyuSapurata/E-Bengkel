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
        Schema::table('costumers', function (Blueprint $table) {
            $table->uuid('uuid_penjualan')->nullable()->after('uuid');
            $table->uuid('uuid_outlet')->nullable()->after('uuid_penjualan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costumers', function (Blueprint $table) {
            $table->dropColumn('uuid_penjualan');
            $table->dropColumn('uuid_outlet');
        });
    }
};
