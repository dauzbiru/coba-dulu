<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gerais', function (Blueprint $table) {
            $table->string('alamat')->nullable()->after('franchisee');
            $table->string('email')->nullable()->after('alamat');
            $table->string('no_telepon')->nullable()->after('email');
        });

        DB::table('gerais')->where('is_active', true)->delete();
    }

    public function down(): void
    {
        Schema::table('gerais', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'email', 'no_telepon']);
        });
    }
};
