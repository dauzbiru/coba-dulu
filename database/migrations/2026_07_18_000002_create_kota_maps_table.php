<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kota_maps', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama_kota');
            $table->string('area');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kota_maps');
    }
};
