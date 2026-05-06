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
        // Ujian -> ujian_id, user_id, judul, status(active/closed), passing_grade
        Schema::create('ujians', function (Blueprint $table) {
            $table->id('ujian_id');
            $table->unsignedBigInteger('user_id'); // Admin/Pengawas
            $table->string('judul');
            $table->enum('status', ['active', 'closed'])->default('closed');
            $table->decimal('passing_grade', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Soal -> soal_id, ujian_id, soal_pdf, bobot_nilai
        Schema::create('soals', function (Blueprint $table) {
            $table->id('soal_id');
            $table->unsignedBigInteger('ujian_id');
            $table->string('soal_pdf')->nullable(); // path to pdf
            $table->decimal('bobot_nilai', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('ujian_id')->references('ujian_id')->on('ujians')->onDelete('cascade');
        });

        // TestCase -> test_case_id, soal_id, input, expected_output
        Schema::create('test_cases', function (Blueprint $table) {
            $table->id('test_case_id');
            $table->unsignedBigInteger('soal_id');
            $table->text('input');
            $table->text('expected_output');
            $table->timestamps();

            $table->foreign('soal_id')->references('soal_id')->on('soals')->onDelete('cascade');
        });

        // Token -> token_id, ujian_id, kode_token, status_aktif, created_at
        Schema::create('tokens', function (Blueprint $table) {
            $table->id('token_id');
            $table->unsignedBigInteger('ujian_id');
            $table->string('kode_token');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->foreign('ujian_id')->references('ujian_id')->on('ujians')->onDelete('cascade');
        });

        // Submission -> submission_id, user_id, soal_id, source_code, skor, status(Acc, WA, TLE), similarity_index, status_akhir, justification_note, execution_time, memory
        Schema::create('submissions', function (Blueprint $table) {
            $table->id('submission_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('soal_id');
            $table->text('source_code');
            $table->decimal('skor', 5, 2)->nullable();
            $table->string('status')->nullable(); // Acc, WA, TLE
            $table->decimal('similarity_index', 5, 2)->nullable();
            $table->string('status_akhir')->nullable();
            $table->text('justification_note')->nullable();
            $table->string('execution_time')->nullable();
            $table->string('memory')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('soal_id')->references('soal_id')->on('soals')->onDelete('cascade');
        });

        // NilaiAkhir -> nilai_id, user_id, ujian_id, total_score, status_lulus
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_akhirs');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('tokens');
        Schema::dropIfExists('test_cases');
        Schema::dropIfExists('soals');
        Schema::dropIfExists('ujians');
    }
};
