<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_with_arbitrary_username(): void
    {
        // Test with a username that previously would have failed (not 8 characters, starts with number)
        $response = $this->post(route('register'), [
            'name' => 'Budi Santoso',
            'nim_username' => '123_budi',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'nim_username' => '123_budi',
            'role' => 'Mahasiswa',
        ]);
    }

    public function test_registration_fails_if_username_already_taken(): void
    {
        User::create([
            'name' => 'Existing User',
            'nim_username' => 'budi_santoso',
            'role' => 'Mahasiswa',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('register'), [
            'name' => 'New User',
            'nim_username' => 'budi_santoso',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['nim_username' => 'Username sudah terdaftar.']);
    }

    public function test_admin_can_create_student_with_arbitrary_username(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nim_username' => 'admin_test',
            'role' => 'Admin',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Siswa Baru',
            'nim_username' => 'siswa_01',
            'role' => 'Mahasiswa',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users', ['tab' => 'mahasiswa']));

        $this->assertDatabaseHas('users', [
            'name' => 'Siswa Baru',
            'nim_username' => 'siswa_01',
            'role' => 'Mahasiswa',
        ]);
    }
}
