<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['kode' => 'AMB', 'nama_kota' => 'Ambon', 'area' => 'Timur'],
            ['kode' => 'BDG', 'nama_kota' => 'Bandung', 'area' => 'Barat'],
            ['kode' => 'BDL', 'nama_kota' => 'Bandar Lampung', 'area' => 'Barat'],
            ['kode' => 'BGR', 'nama_kota' => 'Bogor', 'area' => 'Barat'],
            ['kode' => 'BJM', 'nama_kota' => 'Banjarmasin', 'area' => 'Timur'],
            ['kode' => 'BKS', 'nama_kota' => 'Bekasi', 'area' => 'Barat'],
            ['kode' => 'BLT', 'nama_kota' => 'Blitar', 'area' => 'Timur'],
            ['kode' => 'BPP', 'nama_kota' => 'Balikpapan', 'area' => 'Timur'],
            ['kode' => 'CBN', 'nama_kota' => 'Cirebon', 'area' => 'Barat'],
            ['kode' => 'CLG', 'nama_kota' => 'Cilegon', 'area' => 'Barat'],
            ['kode' => 'CMH', 'nama_kota' => 'Cimahi', 'area' => 'Barat'],
            ['kode' => 'DMK', 'nama_kota' => 'Demak', 'area' => 'Timur'],
            ['kode' => 'DPK', 'nama_kota' => 'Depok', 'area' => 'Barat'],
            ['kode' => 'DPR', 'nama_kota' => 'Denpasar', 'area' => 'Timur'],
            ['kode' => 'GRT', 'nama_kota' => 'Garut', 'area' => 'Barat'],
            ['kode' => 'GSK', 'nama_kota' => 'Gresik', 'area' => 'Timur'],
            ['kode' => 'JKT', 'nama_kota' => 'Jakarta', 'area' => 'Barat'],
            ['kode' => 'KWG', 'nama_kota' => 'Karawang', 'area' => 'Barat'],
            ['kode' => 'MDN', 'nama_kota' => 'Medan', 'area' => 'Barat'],
            ['kode' => 'MJK', 'nama_kota' => 'Mojokerto', 'area' => 'Timur'],
            ['kode' => 'MKS', 'nama_kota' => 'Makassar', 'area' => 'Timur'],
            ['kode' => 'MLG', 'nama_kota' => 'Malang', 'area' => 'Timur'],
            ['kode' => 'MND', 'nama_kota' => 'Manado', 'area' => 'Timur'],
            ['kode' => 'PTK', 'nama_kota' => 'Pontianak', 'area' => 'Timur'],
            ['kode' => 'PWK', 'nama_kota' => 'Purwakarta', 'area' => 'Barat'],
            ['kode' => 'SBY', 'nama_kota' => 'Surabaya', 'area' => 'Timur'],
            ['kode' => 'SDA', 'nama_kota' => 'Sidoarjo', 'area' => 'Timur'],
            ['kode' => 'SKT', 'nama_kota' => 'Surakarta', 'area' => 'Timur'],
            ['kode' => 'SMG', 'nama_kota' => 'Semarang', 'area' => 'Timur'],
            ['kode' => 'SNG', 'nama_kota' => 'Subang', 'area' => 'Barat'],
            ['kode' => 'SRG', 'nama_kota' => 'Serang', 'area' => 'Barat'],
            ['kode' => 'TGL', 'nama_kota' => 'Tegal', 'area' => 'Timur'],
            ['kode' => 'TNG', 'nama_kota' => 'Tangerang', 'area' => 'Barat'],
            ['kode' => 'TSM', 'nama_kota' => 'Tasikmalaya', 'area' => 'Barat'],
            ['kode' => 'YYK', 'nama_kota' => 'Yogyakarta', 'area' => 'Timur'],
        ];

        foreach ($rows as $row) {
            $row['created_at'] = now();
            $row['updated_at'] = now();
            DB::table('kota_maps')->insert($row);
        }
    }

    public function down(): void
    {
        DB::table('kota_maps')->truncate();
    }
};
