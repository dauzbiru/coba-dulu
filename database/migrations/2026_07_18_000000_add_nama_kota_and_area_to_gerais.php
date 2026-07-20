<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gerais', function (Blueprint $table) {
            $table->string('nama_kota')->nullable()->after('opening_at');
            $table->string('area')->nullable()->after('nama_kota');
        });
    }

    public function down(): void
    {
        Schema::table('gerais', function (Blueprint $table) {
            $table->dropColumn(['nama_kota', 'area']);
        });
    }
};
