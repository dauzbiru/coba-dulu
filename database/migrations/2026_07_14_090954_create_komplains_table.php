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
        Schema::create('komplains', function (Blueprint $table) {
            $table->id();
            $table->string('periode')->nullable();
            $table->date('tanggal_komplain');
            $table->string('kode_gerai');
            $table->string('nama_gerai');
            $table->text('uraian');
            $table->string('media_laporan')->nullable();
            $table->string('prioritas')->default('Normal');
            $table->string('pic_penanganan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('status')->default('Open');
            $table->date('tanggal_follow_up')->nullable();
            $table->date('tanggal_close')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komplains');
    }
};
