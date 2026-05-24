<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ujian;

class UjianStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_check_exam_status()
    {
        // 1. Create an Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'nim_username' => 'admin',
            'role' => 'Admin',
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
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Algoritma',
            'deskripsi' => 'Deskripsi ujian',
            'durasi' => 60,
            'status' => 'active',
            'passing_grade' => 70,
            'started_at' => now(),
            'sisa_waktu' => 3600,
        ]);

        // 4. Act as Mahasiswa and call checkStatus
        $response = $this->actingAs($student)
            ->get(route('ujian.status', $exam->ujian_id));

        // 5. Assert response is successful and contains correct details
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'active',
        ]);
        $this->assertGreaterThan(0, $response->json('remainingSeconds'));
    }

    public function test_student_gets_redirected_if_not_authenticated()
    {
        $response = $this->get('/ujian/1/status');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_start_exam_via_update_status()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nim_username' => 'admin',
            'role' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $exam = Ujian::create([
            'user_id' => $admin->user_id,
            'judul' => 'Ujian Algoritma',
            'deskripsi' => 'Deskripsi ujian',
            'durasi' => 60,
            'status' => 'closed',
            'passing_grade' => 70,
            'max_attempt' => 3,
            'started_at' => null,
            'sisa_waktu' => 0,
        ]);

        // Put request to update the status to active
        $response = $this->actingAs($admin)
            ->put(route('admin.ujian.update', $exam->ujian_id), [
                'judul' => 'Ujian Algoritma Terupdate',
                'deskripsi' => 'Deskripsi terupdate',
                'durasi' => 90,
                'passing_grade' => 75,
                'max_attempt' => 3,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.ujian.index'));
        
        $exam->refresh();
        $this->assertEquals('active', $exam->status);
        $this->assertEquals(90, $exam->durasi);
        $this->assertNotNull($exam->started_at);
        $this->assertEquals(90 * 60, $exam->sisa_waktu);

        // Verify active token exists
        $token = \App\Models\Token::where('ujian_id', $exam->ujian_id)
            ->where('status_aktif', true)
            ->first();
        $this->assertNotNull($token);
        $this->assertNotEmpty($token->kode_token);
    }
}
