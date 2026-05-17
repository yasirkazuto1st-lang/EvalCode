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
        Schema::table('ujians', function (Blueprint $table) {
            $table->integer('passing_grade')->default(0)->change();
        });

        Schema::table('soals', function (Blueprint $table) {
            $table->integer('bobot_nilai')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->decimal('passing_grade', 5, 2)->default(0)->change();
        });

        Schema::table('soals', function (Blueprint $table) {
            $table->decimal('bobot_nilai', 5, 2)->default(0)->change();
        });
    }
};
