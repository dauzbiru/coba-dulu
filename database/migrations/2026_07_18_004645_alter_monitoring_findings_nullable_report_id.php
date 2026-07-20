<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_findings', function ($table) {
            $table->integer('monitoring_report_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_findings', function ($table) {
            $table->integer('monitoring_report_id')->nullable(false)->change();
        });
    }
};
