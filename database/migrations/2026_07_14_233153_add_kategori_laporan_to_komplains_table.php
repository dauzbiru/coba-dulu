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
        Schema::table('komplains', function (Blueprint $table) {
            $table->string('kategori_laporan')->nullable()->after('media_laporan');
        });
    }

    public function down(): void
    {
        Schema::table('komplains', function (Blueprint $table) {
            $table->dropColumn('kategori_laporan');
        });
    }
};
