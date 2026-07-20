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
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gerai_id')->constrained()->cascadeOnDelete();
            $table->string('periode_label');
            $table->unsignedSmallInteger('rank');
            $table->unsignedSmallInteger('total');
            $table->timestamps();

            $table->unique(['gerai_id', 'periode_label']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
