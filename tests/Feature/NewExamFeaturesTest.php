<?php

namespace Tests\Feature;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewExamFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_interval_and_bahasa_pemrograman_stored_on_create_and_update(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'nim_username' => 'admintest',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        // 1. Create with custom token interval and subset of languages
        $response = $this->actingAs($admin)->post(route('admin.ujian.store'), [
            'judul' => 'Ujian Python Dart Only',
            'deskripsi' => 'Ujian khusus Python dan Dart',
            'durasi' => 60,
            'token_interval' => 5,
            'passing_grade' => 70,
            'max_attempt' => 3,
            'bahasa_pemrograman' => ['python', 'dart'],
        ]);

        $response->assertRedirect(route('admin.ujian.index'));
        $this->assertDatabaseHas('ujians', [
            'judul' => 'Ujian Python Dart Only',
            'token_interval' => 5,
        ]);

        $exam = Ujian::where('judul', 'Ujian Python Dart Only')->first();
        $this->assertEquals(['python', 'dart'], $exam->getAllowedLanguages());

        // 2. Update to 10 minutes and add cpp
        $updateResponse = $this->actingAs($admin)->put(route('admin.ujian.update', $exam->ujian_id), [
            'judul' => 'Ujian Python Dart CPP',
            'deskripsi' => 'Diperbarui',
            'durasi' => 90,
            'token_interval' => 10,
            'passing_grade' => 60,
            'max_attempt' => 5,
            'bahasa_pemrograman' => ['python', 'cpp', 'dart'],
        ]);

        $updateResponse->assertRedirect(route('admin.ujian.index'));
        $exam->refresh();
        $this->assertEquals(10, $exam->token_interval);
        $this->assertEquals(['python', 'cpp', 'dart'], $exam->getAllowedLanguages());
    }

    public function test_disallowed_language_rejected_on_submission(): void
    {
        $admin = User::create([
            'name' => 'Admin Exam',
            'nim_username' => 'adminexam',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $student = User::create([
            'name' => 'Mahasiswa Test',
            'nim_username' => 'mhs01',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        $exam = Ujian::create([
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Hanya Python',
            'durasi' => 60,
            'token_interval' => 3,
            'passing_grade' => 50,
            'max_attempt' => 3,
            'status' => 'active',
            'bahasa_pemrograman' => ['python'],
        ]);

        $soal = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal 1',
            'bobot_nilai' => 100,
        ]);

        // Attempting to submit C++ when only python is allowed
        $response = $this->actingAs($student)->postJson(route('workspace.submit', [$exam->ujian_id, $soal->soal_id]), [
            'code' => '#include <iostream>',
            'language' => 'cpp',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('CPP', $response->json('message'));
    }

    public function test_tie_breaker_sorts_by_faster_completion_time_when_scores_are_tied(): void
    {
        $admin = User::create([
            'name' => 'Admin Tie',
            'nim_username' => 'admintie',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $now = Carbon::now();
        $exam = Ujian::create([
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Tie Breaker',
            'durasi' => 60,
            'token_interval' => 3,
            'passing_grade' => 50,
            'max_attempt' => 5,
            'status' => 'active',
            'started_at' => $now->copy()->subMinutes(30),
        ]);

        $soal = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal A',
            'bobot_nilai' => 100,
        ]);

        // Student A finishes in 10 minutes (faster)
        $studentA = User::create([
            'name' => 'Budi Santoso',
            'nim_username' => 'mhsA',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        // Student B finishes in 20 minutes (slower)
        $studentB = User::create([
            'name' => 'Andi Wijaya',
            'nim_username' => 'mhsB',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $studentA->user_id,
            'soal_id' => $soal->soal_id,
            'source_code' => 'print(1)',
            'status' => 'Accepted',
            'skor' => 100,
            'created_at' => $now->copy()->subMinutes(20), // 10 mins after start
            'updated_at' => $now->copy()->subMinutes(20),
        ]);

        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $studentB->user_id,
            'soal_id' => $soal->soal_id,
            'source_code' => 'print(2)',
            'status' => 'Accepted',
            'skor' => 100,
            'created_at' => $now->copy()->subMinutes(10), // 20 mins after start
            'updated_at' => $now->copy()->subMinutes(10),
        ]);

        // Check Admin Detail View
        $response = $this->actingAs($admin)->get(route('admin.ujian.detail', $exam->ujian_id));
        $response->assertOk();

        $participants = $response->viewData('participants');
        $this->assertGreaterThanOrEqual(2, $participants->count());

        // Find both students in participants
        $pA = $participants->firstWhere('user_id', $studentA->user_id);
        $pB = $participants->firstWhere('user_id', $studentB->user_id);

        $this->assertNotNull($pA);
        $this->assertNotNull($pB);

        // Student A should be ranked higher (earlier) than Student B because faster completion time
        $indexA = $participants->search(fn($p) => $p->user_id == $studentA->user_id);
        $indexB = $participants->search(fn($p) => $p->user_id == $studentB->user_id);
        $this->assertLessThan($indexB, $indexA, 'Student A (faster) should be ranked before Student B (slower)');

        $this->assertEquals('10m 00s', $pA->total_waktu_formatted);
        $this->assertEquals('20m 00s', $pB->total_waktu_formatted);
    }

    public function test_completion_time_uses_first_correct_submission_and_caps_at_duration(): void
    {
        $admin = User::create([
            'name' => 'Admin Test 2',
            'nim_username' => 'admin_test2',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $now = now();
        $exam = Ujian::create([
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Cap Duration Test',
            'durasi' => 30, // 30 mins
            'passing_grade' => 50,
            'max_attempt' => 5,
            'status' => 'active',
            'started_at' => $now->copy()->subMinutes(60), // started 60 mins ago
        ]);

        $soal = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal Cap',
            'bobot_nilai' => 100,
        ]);

        $student = User::create([
            'name' => 'Peserta First Correct',
            'nim_username' => 'mhs_first_corr',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        // Attempt 1: Wrong Answer at 10 mins
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $student->user_id,
            'soal_id' => $soal->soal_id,
            'source_code' => 'print(0)',
            'status' => 'Wrong Answer',
            'skor' => 0,
            'created_at' => $now->copy()->subMinutes(50),
            'updated_at' => $now->copy()->subMinutes(50),
        ]);

        // Attempt 2: First Accepted at 15 mins
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $student->user_id,
            'soal_id' => $soal->soal_id,
            'source_code' => 'print(1)',
            'status' => 'Accepted',
            'skor' => 100,
            'created_at' => $now->copy()->subMinutes(45), // 15 mins after start
            'updated_at' => $now->copy()->subMinutes(45),
        ]);

        // Attempt 3: Another Accepted later (e.g. at 25 mins)
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $student->user_id,
            'soal_id' => $soal->soal_id,
            'source_code' => 'print(1)',
            'status' => 'Accepted',
            'skor' => 100,
            'created_at' => $now->copy()->subMinutes(35), // 25 mins after start
            'updated_at' => $now->copy()->subMinutes(35),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ujian.detail', $exam->ujian_id));
        $response->assertOk();

        $participants = $response->viewData('participants');
        $p = $participants->firstWhere('user_id', $student->user_id);
        $this->assertNotNull($p);
        // Should use the FIRST correct submission (15m 00s), not the later one (25m 00s)
        $this->assertEquals('15m 00s', $p->total_waktu_formatted);
    }
}

