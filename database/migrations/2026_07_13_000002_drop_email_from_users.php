<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter(['email', 'email_verified_at'], fn ($col) => Schema::hasColumn('users', $col));
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->after('username');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }
};
