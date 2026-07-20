<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('komplains', function (Blueprint $table) {
            $table->text('wa_template')->nullable()->after('tanggal_close');
        });

        $default = "Selamat malam, Pak Yantje\n\nBerikut ini hasil tindak lanjut di Gerai [nama_gerai] ([kode_gerai]) mengenai [kategori_laporan] sebagai berikut:\n\n*Kronologi di gerai*\nTanggal komplain: [tanggal_komplain]\nMedia Laporan: [media_laporan]\n\n[uraian]\n\n*Tindak lanjut di gerai*\nPIC: [pic_penanganan]\nTanggal Follow up: [tanggal_follow_up]\nTanggal Close: [tanggal_close]\n\n[tindak_lanjut]\n\nDemikian informasi yang saya berikan.\n\nTerlampir:\n1. Bukti call wa permintaan maaf kpd pelanggan\n2. formulir briefing karyawan.\n\nTerima kasih\nElyas";

        DB::table('komplains')->whereNull('wa_template')->update(['wa_template' => $default]);
    }

    public function down(): void
    {
        Schema::table('komplains', function (Blueprint $table) {
            $table->dropColumn('wa_template');
        });
    }
};
