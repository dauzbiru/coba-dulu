<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $kotaMap = [
            'AMB' => ['Ambon', 'Timur'],
            'BDG' => ['Bandung', 'Barat'],
            'BDL' => ['Bandar Lampung', 'Barat'],
            'BGR' => ['Bogor', 'Barat'],
            'BJM' => ['Banjarmasin', 'Timur'],
            'BKS' => ['Bekasi', 'Barat'],
            'BLT' => ['Blitar', 'Timur'],
            'BPP' => ['Balikpapan', 'Timur'],
            'CBN' => ['Cirebon', 'Barat'],
            'CLG' => ['Cilegon', 'Barat'],
            'CMH' => ['Cimahi', 'Barat'],
            'DMK' => ['Demak', 'Timur'],
            'DPK' => ['Depok', 'Barat'],
            'DPR' => ['Denpasar', 'Timur'],
            'GRT' => ['Garut', 'Barat'],
            'GSK' => ['Gresik', 'Timur'],
            'JKT' => ['Jakarta', 'Barat'],
            'KWG' => ['Karawang', 'Barat'],
            'MDN' => ['Medan', 'Barat'],
            'MJK' => ['Mojokerto', 'Timur'],
            'MKS' => ['Makassar', 'Timur'],
            'MLG' => ['Malang', 'Timur'],
            'MND' => ['Manado', 'Timur'],
            'PTK' => ['Pontianak', 'Timur'],
            'PWK' => ['Purwakarta', 'Barat'],
            'SBY' => ['Surabaya', 'Timur'],
            'SDA' => ['Sidoarjo', 'Timur'],
            'SKT' => ['Surakarta', 'Timur'],
            'SMG' => ['Semarang', 'Timur'],
            'SNG' => ['Subang', 'Barat'],
            'SRG' => ['Serang', 'Barat'],
            'TGL' => ['Tegal', 'Timur'],
            'TNG' => ['Tangerang', 'Barat'],
            'TSM' => ['Tasikmalaya', 'Barat'],
            'YYK' => ['Yogyakarta', 'Timur'],
        ];

        $gerais = DB::table('gerais')->where('is_active', true)->get();
        foreach ($gerais as $g) {
            $prefix = strtoupper(substr($g->kode_gerai, 0, 3));
            if (isset($kotaMap[$prefix]) && (!$g->nama_kota || !$g->area)) {
                DB::table('gerais')->where('id', $g->id)->update([
                    'nama_kota' => $kotaMap[$prefix][0],
                    'area' => $kotaMap[$prefix][1],
                ]);
            }
        }
    }

    public function down(): void {}
};
