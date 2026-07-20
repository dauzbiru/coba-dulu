<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pra_monitoring_reports', function (Blueprint $table) {
            $table->dropColumn('periode_label');
        });

        Schema::table('re_monitoring_reports', function (Blueprint $table) {
            $table->dropColumn('periode_label');
        });

        Schema::table('evaluasi_reports', function (Blueprint $table) {
            $table->dropColumn(['checkin_at', 'submit_at']);
            $table->date('tanggal')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('pra_monitoring_reports', function (Blueprint $table) {
            $table->string('periode_label')->nullable()->after('grade');
        });

        Schema::table('re_monitoring_reports', function (Blueprint $table) {
            $table->string('periode_label')->nullable()->after('grade');
        });

        Schema::table('evaluasi_reports', function (Blueprint $table) {
            $table->dropColumn('tanggal');
            $table->timestamp('checkin_at')->nullable()->after('user_id');
            $table->timestamp('submit_at')->nullable()->after('checkin_at');
        });
    }
};
