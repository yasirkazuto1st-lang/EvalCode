<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->unsignedInteger('token_interval')->default(3)->after('durasi');
            $table->json('bahasa_pemrograman')->nullable()->after('max_attempt');
        });

        // Set default bahasa_pemrograman untuk ujian yang sudah ada
        DB::table('ujians')->whereNull('bahasa_pemrograman')->update([
            'bahasa_pemrograman' => json_encode(['python', 'cpp', 'java', 'dart'])
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn(['token_interval', 'bahasa_pemrograman']);
        });
    }
};
