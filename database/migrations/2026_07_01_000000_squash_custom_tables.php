<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('bobot', 8, 2)->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('gerais', function (Blueprint $table) {
            $table->id();
            $table->string('kode_gerai')->unique();
            $table->string('nama_gerai');
            $table->string('franchisee');
            $table->date('opening_at')->nullable();
            $table->timestamps();
        });

        Schema::create('monitoring_reports', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('monitoring');
            $table->foreignId('gerai_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location')->nullable();
            $table->decimal('nilai', 10, 2)->nullable();
            $table->char('grade', 1)->nullable();
            $table->dateTime('checkin_at')->nullable();
            $table->dateTime('submit_at')->nullable();
            $table->date('periode_start')->nullable();
            $table->date('periode_end')->nullable();
            $table->string('periode_label')->nullable();
            $table->timestamps();
        });

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monitoring_report_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('criterion_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['item_id', 'user_id', 'monitoring_report_id']);
        });

        Schema::create('monitoring_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_report_id')->constrained()->cascadeOnDelete();
            $table->text('major')->nullable();
            $table->text('minor')->nullable();
            $table->text('peringatan_awal')->nullable();
            $table->string('ttd_petugas')->nullable();
            $table->string('ttd_pimpinan')->nullable();
            $table->json('penjelasan_isi')->nullable();
            $table->json('penjelasan_isi_3')->nullable();
            $table->text('pengawas')->nullable();
            $table->text('rata_rata_aj')->nullable();
            $table->text('tds')->nullable();
            $table->text('mesin_ozon')->nullable();
            $table->text('note')->nullable();
            $table->text('kondisi_cat')->nullable();
            $table->text('kondisi_awning')->nullable();
            $table->text('kondisi_vinyl')->nullable();
            $table->text('kondisi_stiker_kaca')->nullable();
            $table->timestamps();
        });

        Schema::create('semester_periods', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('start_month');
            $table->tinyInteger('end_month');
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('penjelasan_formulir', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('formulir')->comment('2 or 3');
            $table->text('kondisi')->nullable();
            $table->text('penjelasan')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjelasan_formulir');
        Schema::dropIfExists('semester_periods');
        Schema::dropIfExists('monitoring_findings');
        Schema::dropIfExists('results');
        Schema::dropIfExists('monitoring_reports');
        Schema::dropIfExists('gerais');
        Schema::dropIfExists('criteria');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
    }
};
