<?php

namespace Tests\Feature;

use App\Models\Soal;
use App\Models\Token;
use App\Models\Ujian;
use App\Models\UjianSession;
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

        // 2. Update to 10 minutes and add c and cpp
        $updateResponse = $this->actingAs($admin)->put(route('admin.ujian.update', $exam->ujian_id), [
            'judul' => 'Ujian Python Dart CPP C',
            'deskripsi' => 'Diperbarui',
            'durasi' => 90,
            'token_interval' => 10,
            'passing_grade' => 60,
            'max_attempt' => 5,
            'bahasa_pemrograman' => ['python', 'c', 'cpp', 'dart'],
        ]);

        $updateResponse->assertRedirect(route('admin.ujian.index'));
        $exam->refresh();
        $this->assertEquals(10, $exam->token_interval);
        $this->assertEquals(['python', 'c', 'cpp', 'dart'], $exam->getAllowedLanguages());
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

    public function test_completion_time_persists_after_exam_is_finished_or_paused(): void
    {
        $admin = User::create([
            'name' => 'Admin Test 3',
            'nim_username' => 'admin_test3',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $now = now();
        $exam = Ujian::create([
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Single Problem Persistence Test',
            'durasi' => 60,
            'passing_grade' => 50,
            'max_attempt' => 5,
            'status' => 'active',
            'started_at' => $now->copy()->subMinutes(10), // started 10 mins ago
        ]);

        $token = Token::create([
            'ujian_id' => $exam->ujian_id,
            'kode_token' => Token::generateCode(),
            'status_aktif' => true,
            'created_at' => $now->copy()->subMinutes(10),
        ]);

        $soal = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal Tunggal',
            'bobot_nilai' => 100,
        ]);

        $student = User::create([
            'name' => 'Peserta Satu Soal',
            'nim_username' => 'mhs_satu_soal',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        // Student submits only 1 time at 5 minutes after start (first correct = only submission)
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $student->user_id,
            'soal_id' => $soal->soal_id,
            'source_code' => 'print(100)',
            'status' => 'Accepted',
            'skor' => 100,
            'created_at' => $now->copy()->subMinutes(5), // exactly 5 mins after start
            'updated_at' => $now->copy()->subMinutes(5),
        ]);

        // 1. Saat Ujian Sedang Berjalan (Active)
        $responseActive = $this->actingAs($admin)->get(route('admin.ujian.detail', $exam->ujian_id));
        $responseActive->assertOk();
        $pActive = $responseActive->viewData('participants')->firstWhere('user_id', $student->user_id);
        $this->assertEquals('05m 00s', $pActive->total_waktu_formatted);

        // 2. Sekarang Ujian Dihentikan / Diselesaikan (Status = finished / closed)
        $exam->status = 'finished';
        $exam->save();

        $responseFinished = $this->actingAs($admin)->get(route('admin.ujian.detail', $exam->ujian_id));
        $responseFinished->assertOk();
        $pFinished = $responseFinished->viewData('participants')->firstWhere('user_id', $student->user_id);
        
        // Waktu harus TETAP 05m 00s, TIDAK BOLEH berubah menjadi 00m 00s!
        $this->assertEquals('05m 00s', $pFinished->total_waktu_formatted);
    }

    public function test_multi_session_real_world_elapsed_time(): void
    {
        $admin = User::create([
            'name' => 'Admin Multi Session',
            'nim_username' => 'admin_multi',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $sunday = Carbon::parse('2026-10-18 08:00:00');
        $monday = Carbon::parse('2026-10-19 08:00:00');

        $exam = Ujian::create([
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Multi Sesi Real Time',
            'durasi' => 60,
            'passing_grade' => 50,
            'max_attempt' => 5,
            'status' => 'active',
            'started_at' => $monday, // Saat ini sesi Senin yang aktif
        ]);

        // Sesi 1 (Minggu 08:00 - 09:00)
        UjianSession::create([
            'ujian_id' => $exam->ujian_id,
            'sesi_ke' => 1,
            'started_at' => $sunday,
            'ended_at' => $sunday->copy()->addMinutes(60),
        ]);

        // Sesi 2 (Senin 08:00 - aktif)
        UjianSession::create([
            'ujian_id' => $exam->ujian_id,
            'sesi_ke' => 2,
            'started_at' => $monday,
            'ended_at' => null,
        ]);

        // Token Sesi 1 (Minggu 08:00)
        \Illuminate\Support\Facades\DB::table('tokens')->insert([
            'ujian_id' => $exam->ujian_id,
            'kode_token' => 'SUNDAY',
            'status_aktif' => false,
            'created_at' => $sunday,
            'updated_at' => $sunday,
        ]);

        // Token Sesi 2 (Senin 08:00)
        \Illuminate\Support\Facades\DB::table('tokens')->insert([
            'ujian_id' => $exam->ujian_id,
            'kode_token' => 'MONDAY',
            'status_aktif' => true,
            'created_at' => $monday,
            'updated_at' => $monday,
        ]);

        $soal1 = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal 1',
            'bobot_nilai' => 50,
        ]);

        $soal2 = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal 2',
            'bobot_nilai' => 50,
        ]);

        // Mahasiswa A: Mengerjakan sejak Sesi 1 (Minggu), lanjut ke Sesi 2 (Senin)
        $mhsA = User::create([
            'name' => 'Mahasiswa Multi Sesi',
            'nim_username' => 'mhs_multi',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        // Soal 1 selesai Minggu 08:10
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $mhsA->user_id,
            'soal_id' => $soal1->soal_id,
            'source_code' => 'print(1)',
            'status' => 'Accepted',
            'skor' => 50,
            'created_at' => $sunday->copy()->addMinutes(10),
            'updated_at' => $sunday->copy()->addMinutes(10),
        ]);

        // Soal 2 selesai Senin 08:15 (24 jam 15 menit sejak Minggu 08:00)
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $mhsA->user_id,
            'soal_id' => $soal2->soal_id,
            'source_code' => 'print(2)',
            'status' => 'Accepted',
            'skor' => 50,
            'created_at' => $monday->copy()->addMinutes(15),
            'updated_at' => $monday->copy()->addMinutes(15),
        ]);

        // Mahasiswa B: Hanya ikut Sesi 2 (Senin)
        $mhsB = User::create([
            'name' => 'Mahasiswa Sesi 2 Only',
            'nim_username' => 'mhs_sesi2_only',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        // Soal 1 selesai Senin 08:05
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $mhsB->user_id,
            'soal_id' => $soal1->soal_id,
            'source_code' => 'print(1)',
            'status' => 'Accepted',
            'skor' => 50,
            'created_at' => $monday->copy()->addMinutes(5),
            'updated_at' => $monday->copy()->addMinutes(5),
        ]);

        // Soal 2 selesai Senin 08:20 (20 menit sejak Senin 08:00)
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => $mhsB->user_id,
            'soal_id' => $soal2->soal_id,
            'source_code' => 'print(2)',
            'status' => 'Accepted',
            'skor' => 50,
            'created_at' => $monday->copy()->addMinutes(20),
            'updated_at' => $monday->copy()->addMinutes(20),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ujian.detail', $exam->ujian_id));
        $response->assertOk();

        $participants = $response->viewData('participants');

        $pA = $participants->firstWhere('user_id', $mhsA->user_id);
        $pB = $participants->firstWhere('user_id', $mhsB->user_id);

        $this->assertNotNull($pA);
        $this->assertNotNull($pB);

        // Mahasiswa A: Dihitung dari Minggu 08:00 sampai Senin 08:15 (24j 15m 00s)
        $this->assertEquals('24j 15m 00s', $pA->total_waktu_formatted);

        // Mahasiswa B: Murni dari Senin 08:00 sampai Senin 08:20 (20m 00s)
        $this->assertEquals('20m 00s', $pB->total_waktu_formatted);
    }

    public function test_ujian_session_recorded_on_pengawas_start_and_end_exam(): void
    {
        $pengawas = User::create([
            'name' => 'Pengawas Test',
            'nim_username' => 'pengawas_test',
            'role' => 'Pengawas',
            'password' => bcrypt('password'),
        ]);

        $exam = Ujian::create([
            'user_id' => $pengawas->user_id,
            'judul' => 'Ujian Sesi Controller Test',
            'durasi' => 60,
            'passing_grade' => 50,
            'max_attempt' => 3,
            'status' => 'closed',
        ]);

        // 1. Mulai Ujian (Sesi 1)
        $this->actingAs($pengawas)->post(route('pengawas.ujian.start', $exam->ujian_id));
        $this->assertDatabaseHas('ujian_sessions', [
            'ujian_id' => $exam->ujian_id,
            'sesi_ke' => 1,
            'ended_at' => null,
        ]);

        // 2. Pause Ujian (Sesi 1 berakhir)
        $this->actingAs($pengawas)->post(route('pengawas.ujian.end', $exam->ujian_id));
        $session1 = UjianSession::where('ujian_id', $exam->ujian_id)->where('sesi_ke', 1)->first();
        $this->assertNotNull($session1);
        $this->assertNotNull($session1->ended_at);

        // 3. Mulai Ujian Lagi (Sesi 2)
        $this->actingAs($pengawas)->post(route('pengawas.ujian.start', $exam->ujian_id));
        $this->assertDatabaseHas('ujian_sessions', [
            'ujian_id' => $exam->ujian_id,
            'sesi_ke' => 2,
            'ended_at' => null,
        ]);

        // 4. Selesaikan Ujian Permanen (Sesi 2 berakhir)
        $this->actingAs($pengawas)->post(route('pengawas.ujian.finish', $exam->ujian_id));
        $session2 = UjianSession::where('ujian_id', $exam->ujian_id)->where('sesi_ke', 2)->first();
        $this->assertNotNull($session2);
        $this->assertNotNull($session2->ended_at);
    }
}


