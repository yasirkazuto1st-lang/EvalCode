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
        // Drop the entirely unused table
        Schema::dropIfExists('nilai_akhirs');

        // Drop the unused column from submissions
        if (Schema::hasColumn('submissions', 'status_akhir')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropColumn('status_akhir');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the unused table (if rolling back)
        Schema::create('nilai_akhirs', function (Blueprint $table) {
            $table->id('nilai_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ujian_id');
            $table->decimal('total_score', 5, 2)->nullable();
            $table->string('status_lulus')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('ujian_id')->references('ujian_id')->on('ujians')->onDelete('cascade');
        });

        // Add back the unused column
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('status_akhir')->nullable();
        });
    }
};
