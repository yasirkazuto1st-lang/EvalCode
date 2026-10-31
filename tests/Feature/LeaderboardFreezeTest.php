<?php

namespace Tests\Feature;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeaderboardFreezeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_and_update_freeze_leaderboard_configuration(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nim_username' => 'adminfreeze',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        // 1. Create with freeze enabled and threshold 75%
        $response = $this->actingAs($admin)->post(route('admin.ujian.store'), [
            'judul' => 'Ujian Freeze Test',
            'deskripsi' => 'Deskripsi Ujian Freeze',
            'durasi' => 60,
            'token_interval' => 3,
            'passing_grade' => 70,
            'max_attempt' => 3,
            'is_freeze_enabled' => '1',
            'freeze_threshold' => 75,
            'bahasa_pemrograman' => ['python', 'c'],
        ]);

        $response->assertRedirect(route('admin.ujian.index'));
        $exam = Ujian::where('judul', 'Ujian Freeze Test')->first();
        $this->assertNotNull($exam);
        $this->assertTrue((bool)$exam->is_freeze_enabled);
        $this->assertEquals(75, $exam->freeze_threshold);

        // 2. Update to freeze disabled via AJAX/JSON
        $updateResponse = $this->actingAs($admin)->put(route('admin.ujian.update', $exam->ujian_id), [
            'judul' => 'Ujian Freeze Test Updated',
            'deskripsi' => 'Deskripsi Ujian Freeze Updated',
            'durasi' => 90,
            'token_interval' => 5,
            'passing_grade' => 65,
            'max_attempt' => 4,
            'status' => 'closed',
            'freeze_threshold' => 80,
            // is_freeze_enabled is omitted (unchecked)
        ], ['Accept' => 'application/json']);

        $updateResponse->assertStatus(200);
        $exam->refresh();
        $this->assertFalse((bool)$exam->is_freeze_enabled);
        $this->assertEquals('Ujian Freeze Test Updated', $exam->judul);
    }

    public function test_is_currently_frozen_helper_logic(): void
    {
        $admin = User::create([
            'name' => 'Admin Math',
            'nim_username' => 'adminmath',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $startTime = Carbon::now()->subMinutes(50);

        $exam = Ujian::create([
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Freeze Math',
            'deskripsi' => 'Test freeze math',
            'durasi' => 60, // 60 menit
            'passing_grade' => 60,
            'max_attempt' => 3,
            'status' => 'active',
            'started_at' => $startTime,
            'is_freeze_enabled' => true,
            'freeze_threshold' => 80, // Freeze pada 80% * 60 menit = 48 menit
        ]);

        // Sesi dimulai 50 menit lalu, threshold adalah 48 menit. Sudah lewat => frozen!
        $this->assertTrue($exam->isCurrentlyFrozen());
        $this->assertNotNull($exam->getFreezeTime());
        $this->assertEquals($startTime->copy()->addMinutes(48)->setTimezone('Asia/Makassar')->format('H:i') . ' WITA', $exam->getFreezeTimeFormatted());

        // Jika ujian belum aktif
        $exam->status = 'closed';
        $this->assertFalse($exam->isCurrentlyFrozen());

        // Jika freeze dinonaktifkan
        $exam->status = 'active';
        $exam->is_freeze_enabled = false;
        $this->assertFalse($exam->isCurrentlyFrozen());

        // Jika waktu belum mencapai threshold (misal baru berjalan 30 menit)
        $exam->is_freeze_enabled = true;
        $exam->started_at = Carbon::now()->subMinutes(30);
        $this->assertFalse($exam->isCurrentlyFrozen());
    }

    public function test_mahasiswa_leaderboard_freezes_other_participants_but_keeps_own_score_realtime(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'nim_username' => 'adminleaderboard',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $mhsA = User::create([
            'name' => 'Mahasiswa A (Me)',
            'nim_username' => 'mhsA1234',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        $mhsB = User::create([
            'name' => 'Mahasiswa B (Other)',
            'nim_username' => 'mhsB1234',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password'),
        ]);

        $baseTime = Carbon::now()->subMinutes(50);

        $exam = Ujian::create([
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Freeze Score Test',
            'deskripsi' => 'Test score freeze',
            'durasi' => 60,
            'passing_grade' => 60,
            'max_attempt' => 5,
            'status' => 'active',
            'started_at' => $baseTime,
            'is_freeze_enabled' => true,
            'freeze_threshold' => 80, // Freeze starts at baseTime + 48 minutes (-2 minutes from now)
        ]);

        // Create UjianSession
        UjianSession::create([
            'ujian_id' => $exam->ujian_id,
            'session_number' => 1,
            'started_at' => $baseTime,
            'is_active' => true,
        ]);

        $soal = Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => 'Soal 1',
            'soal_pdf' => 'soals/test.pdf',
            'bobot_nilai' => 100,
        ]);

        $freezeTime = $exam->getFreezeTime(); // baseTime + 48 minutes

        // 1. Submissions BEFORE freeze
        // Mahasiswa B: 70 pts at freezeTime - 5 mins
        DB::table('submissions')->insert([
            'soal_id' => $soal->soal_id,
            'user_id' => $mhsB->user_id,
            'source_code' => 'print(1)',
            'status' => 'Partially Accepted',
            'skor' => 70,
            'created_at' => $freezeTime->copy()->subMinutes(5),
            'updated_at' => $freezeTime->copy()->subMinutes(5),
        ]);

        // Mahasiswa A (me): 50 pts at freezeTime - 10 mins
        DB::table('submissions')->insert([
            'soal_id' => $soal->soal_id,
            'user_id' => $mhsA->user_id,
            'source_code' => 'print(2)',
            'status' => 'Partially Accepted',
            'skor' => 50,
            'created_at' => $freezeTime->copy()->subMinutes(10),
            'updated_at' => $freezeTime->copy()->subMinutes(10),
        ]);

        // 2. Submissions AFTER freeze
        // Mahasiswa B submits 100 pts at freezeTime + 1 min
        DB::table('submissions')->insert([
            'soal_id' => $soal->soal_id,
            'user_id' => $mhsB->user_id,
            'source_code' => 'print(3)',
            'status' => 'Accepted',
            'skor' => 100,
            'created_at' => $freezeTime->copy()->addMinute(),
            'updated_at' => $freezeTime->copy()->addMinute(),
        ]);

        // Mahasiswa A (me) submits 90 pts at freezeTime + 1 min
        DB::table('submissions')->insert([
            'soal_id' => $soal->soal_id,
            'user_id' => $mhsA->user_id,
            'source_code' => 'print(4)',
            'status' => 'Accepted',
            'skor' => 90,
            'created_at' => $freezeTime->copy()->addMinute(),
            'updated_at' => $freezeTime->copy()->addMinute(),
        ]);

        // Mahasiswa A accesses detail view
        $response = $this->actingAs($mhsA)->get(route('ujian.detail', $exam->ujian_id));
        $response->assertStatus(200);

        // Assert view received freeze state
        $response->assertViewHas('isFrozen', true);
        $response->assertSee('Leaderboard telah dibekukan');

        $leaderboard = $response->viewData('leaderboard');
        $this->assertCount(2, $leaderboard);

        // Mahasiswa A should see their OWN up-to-date score of 90 pts!
        $aInLeaderboard = $leaderboard->firstWhere('user_id', $mhsA->user_id);
        $this->assertNotNull($aInLeaderboard);
        $this->assertEquals(90, $aInLeaderboard->total_skor);

        // Mahasiswa A should see Mahasiswa B frozen at 70 pts (NOT 100 pts)!
        $bInLeaderboard = $leaderboard->firstWhere('user_id', $mhsB->user_id);
        $this->assertNotNull($bInLeaderboard);
        $this->assertEquals(70, $bInLeaderboard->total_skor);

        // And Mahasiswa A (90) is ranked above Mahasiswa B (70)!
        $this->assertEquals($mhsA->user_id, $leaderboard->first()->user_id);

        // 3. ADMIN VIEW: Admin must see 100% real-time (Mahasiswa B has 100, Mahasiswa A has 90)
        $adminResponse = $this->actingAs($admin)->get(route('admin.ujian.detail', $exam->ujian_id));
        $adminResponse->assertStatus(200);
        $adminParticipants = $adminResponse->viewData('participants');
        $adminB = $adminParticipants->firstWhere('user_id', $mhsB->user_id);
        $this->assertEquals(100, $adminB->total_skor);
    }
}
