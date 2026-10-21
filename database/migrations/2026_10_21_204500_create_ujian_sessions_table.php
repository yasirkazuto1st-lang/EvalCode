<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ujian_sessions', function (Blueprint $table) {
            $table->id('session_id');
            $table->unsignedBigInteger('ujian_id');
            $table->unsignedInteger('sesi_ke')->default(1);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('ujian_id')->references('ujian_id')->on('ujians')->onDelete('cascade');
        });

        // Backfill otomatis untuk ujian-ujian lama yang sudah pernah dimulai:
        // Ambil data ujian yang pernah memiliki token atau started_at
        $ujians = DB::table('ujians')->get();
        foreach ($ujians as $ujian) {
            $firstToken = DB::table('tokens')
                ->where('ujian_id', $ujian->ujian_id)
                ->orderBy('created_at', 'asc')
                ->first();

            $startedAt = $ujian->started_at 
                ?: ($firstToken ? $firstToken->created_at : null);

            if ($startedAt) {
                DB::table('ujian_sessions')->insert([
                    'ujian_id' => $ujian->ujian_id,
                    'sesi_ke' => 1,
                    'started_at' => $startedAt,
                    'ended_at' => ($ujian->status === 'closed' || $ujian->status === 'finished') 
                        ? ($ujian->updated_at ?: $startedAt) 
                        : null,
                    'created_at' => $startedAt,
                    'updated_at' => $ujian->updated_at ?: $startedAt,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_sessions');
    }
};
