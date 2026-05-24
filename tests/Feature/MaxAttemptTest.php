<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\TestCase as TC;
use Illuminate\Support\Facades\DB;

class MaxAttemptTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper untuk membuat environment ujian lengkap.
     */
    private function createExamEnvironment(int $maxAttempt = 3): array
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'nim_username' => 'admintest',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $student = User::create([
            'name' => 'Mahasiswa Test',
            'nim_username' => 'A1234567',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        $exam = Ujian::create([
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Max Attempt',
            'deskripsi' => 'Tes batas submisi dinamis',
            'durasi' => 60,
            'status' => 'active',
            'passing_grade' => 50,
            'max_attempt' => $maxAttempt,
            'started_at' => now(),
            'sisa_waktu' => 3600,
        ]);

        $soal = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal Tes',
            'bobot_nilai' => 100,
        ]);

        // Buat minimal 1 test case agar submitCode tidak return error "tidak ada test case"
        TC::create([
            'soal_id' => $soal->soal_id,
            'input' => '1',
            'expected_output' => '1',
        ]);

        return compact('admin', 'student', 'exam', 'soal');
    }

    /**
     * Helper untuk menyisipkan submisi dummy langsung ke database.
     */
    private function insertSubmissions(int $userId, int $soalId, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            DB::table('submissions')->insert([
                'user_id' => $userId,
                'soal_id' => $soalId,
                'source_code' => 'print("test")',
                'status' => 'Wrong Answer',
                'skor' => 0,
                'is_reset' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_rejects_submission_when_max_attempt_reached_with_default_3()
    {
        $env = $this->createExamEnvironment(3);

        // Sisipkan 3 submisi (sudah mencapai batas)
        $this->insertSubmissions($env['student']->user_id, $env['soal']->soal_id, 3);

        // Coba submit yang ke-4 → harus ditolak
        $response = $this->actingAs($env['student'])
            ->postJson("/ujian/{$env['exam']->ujian_id}/soal/{$env['soal']->soal_id}/workspace/submit", [
                'code' => 'print("hello")',
                'language' => 'python',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('3', $response->json('message'));
    }

    public function test_accepts_submission_when_max_attempt_is_5_and_only_3_used()
    {
        $env = $this->createExamEnvironment(5);

        // Sisipkan 3 submisi (belum mencapai batas 5)
        $this->insertSubmissions($env['student']->user_id, $env['soal']->soal_id, 3);

        // Submit ke-4 → harus diterima (batas 5, baru terpakai 3)
        // Catatan: Ini akan gagal di Judge0 API call (tidak ada key),
        // tapi cukup untuk membuktikan bahwa validasi batas tidak memblokir
        $response = $this->actingAs($env['student'])
            ->postJson("/ujian/{$env['exam']->ujian_id}/soal/{$env['soal']->soal_id}/workspace/submit", [
                'code' => 'print("hello")',
                'language' => 'python',
            ]);

        $response->assertOk();
        // Tidak boleh mengembalikan pesan "Batas submit maksimal"
        if ($response->json('success') === false) {
            $this->assertStringNotContainsString('Batas submit maksimal', $response->json('message') ?? '');
        }
    }

    public function test_rejects_submission_when_custom_max_attempt_5_reached()
    {
        $env = $this->createExamEnvironment(5);

        // Sisipkan 5 submisi (sudah mencapai batas)
        $this->insertSubmissions($env['student']->user_id, $env['soal']->soal_id, 5);

        // Coba submit yang ke-6 → harus ditolak
        $response = $this->actingAs($env['student'])
            ->postJson("/ujian/{$env['exam']->ujian_id}/soal/{$env['soal']->soal_id}/workspace/submit", [
                'code' => 'print("hello")',
                'language' => 'python',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('5', $response->json('message'));
    }

    public function test_max_attempt_stored_correctly_on_exam_creation()
    {
        $admin = User::create([
            'name' => 'Admin',
            'nim_username' => 'adminuser',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.ujian.store'), [
            'judul' => 'Ujian Custom Limit',
            'deskripsi' => 'Test custom max attempt',
            'durasi' => 90,
            'passing_grade' => 60,
            'max_attempt' => 7,
        ]);

        $response->assertRedirect(route('admin.ujian.index'));
        $this->assertDatabaseHas('ujians', [
            'judul' => 'Ujian Custom Limit',
            'max_attempt' => 7,
        ]);
    }
}
