<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_reports', function (Blueprint $table) {
            $table->dropColumn(['periode_start', 'periode_end']);
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_reports', function (Blueprint $table) {
            $table->date('periode_start')->nullable()->after('submit_at');
            $table->date('periode_end')->nullable()->after('periode_start');
        });
    }
};
