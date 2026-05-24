<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ujian;
use App\Models\Soal;
use Illuminate\Support\Facades\DB;

class SubmissionDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_proctor_can_delete_submission()
    {
        // 1. Create a Proctor (Pengawas) user
        $proctor = User::create([
            'name' => 'Pengawas Test',
            'nim_username' => 'pengawas',
            'role' => 'Pengawas',
            'password' => bcrypt('password'),
        ]);

        // 2. Create a Mahasiswa user
        $student = User::create([
            'name' => 'Mahasiswa Test',
            'nim_username' => '12345678',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        // 3. Create an active exam
        $exam = Ujian::create([
            'user_id' => $proctor->user_id,
            'judul' => 'Ujian Algoritma',
            'deskripsi' => 'Deskripsi ujian',
            'durasi' => 60,
            'status' => 'active',
            'passing_grade' => 70,
            'max_attempt' => 3,
            'started_at' => now(),
            'sisa_waktu' => 3600,
        ]);

        // 4. Create a Soal
        $soal = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal 1',
            'deskripsi' => 'Deskripsi Soal 1',
            'bobot_nilai' => 100,
        ]);

        // 5. Insert a submission
        $submissionId = DB::table('submissions')->insertGetId([
            'user_id' => $student->user_id,
            'soal_id' => $soal->soal_id,
            'source_code' => 'print("hello")',
            'status' => 'Accepted',
            'skor' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('submissions', ['submission_id' => $submissionId]);

        // 6. Proctor deletes the submission
        $response = $this->actingAs($proctor)
            ->delete(route('pengawas.submission.destroy', $submissionId));

        $response->assertStatus(302); // Redirect back
        $this->assertDatabaseMissing('submissions', ['submission_id' => $submissionId]);
    }

    public function test_student_cannot_delete_submission()
    {
        // 1. Create a Proctor (Pengawas) user
        $proctor = User::create([
            'name' => 'Pengawas Test',
            'nim_username' => 'pengawas',
            'role' => 'Pengawas',
            'password' => bcrypt('password'),
        ]);

        // 2. Create a Mahasiswa user
        $student = User::create([
            'name' => 'Mahasiswa Test',
            'nim_username' => '12345678',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        // 3. Create an active exam
        $exam = Ujian::create([
            'user_id' => $proctor->user_id,
            'judul' => 'Ujian Algoritma',
            'deskripsi' => 'Deskripsi ujian',
            'durasi' => 60,
            'status' => 'active',
            'passing_grade' => 70,
            'max_attempt' => 3,
            'started_at' => now(),
            'sisa_waktu' => 3600,
        ]);

        // 4. Create a Soal
        $soal = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal 1',
            'deskripsi' => 'Deskripsi Soal 1',
            'bobot_nilai' => 100,
        ]);

        // 5. Insert a submission
        $submissionId = DB::table('submissions')->insertGetId([
            'user_id' => $student->user_id,
            'soal_id' => $soal->soal_id,
            'source_code' => 'print("hello")',
            'status' => 'Accepted',
            'skor' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Student tries to delete
        $response = $this->actingAs($student)
            ->delete(route('pengawas.submission.destroy', $submissionId));

        $response->assertStatus(403); // Forbidden or redirected if middleware denies
    }
}
