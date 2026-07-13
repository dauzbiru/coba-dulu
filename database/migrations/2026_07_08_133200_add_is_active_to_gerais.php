<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gerais', function (Blueprint $table) {
            $table->dropUnique('gerais_kode_gerai_unique');
            $table->boolean('is_active')->default(true);
            $table->date('closed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gerais', function (Blueprint $table) {
            $table->string('kode_gerai')->unique()->change();
            $table->dropColumn('is_active');
            $table->dropColumn('closed_at');
        });
    }
};
