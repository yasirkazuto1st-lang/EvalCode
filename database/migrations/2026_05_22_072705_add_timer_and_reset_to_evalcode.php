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
            $table->timestamp('started_at')->nullable()->after('durasi');
            $table->integer('sisa_waktu')->nullable()->after('started_at');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->boolean('is_reset')->default(false)->after('memory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'sisa_waktu']);
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('is_reset');
        });
    }
};
