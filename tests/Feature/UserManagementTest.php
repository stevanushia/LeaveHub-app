<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\LeaveType;
use Laravel\Sanctum\Sanctum;

class UserManagementTest extends TestCase
{
    // Menggunakan RefreshDatabase agar testing tidak mengotori database utama
    use RefreshDatabase;

    public function test_admin_can_create_user_and_auto_assign_leave_balances()
    {
        // 1. Setup Data Awal (Kondisi Database)
        $admin = User::create([
            'name' => 'Admin HR',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'default_quota' => 12,
        ]);

        // Autentikasi sebagai Admin menggunakan Sanctum
        Sanctum::actingAs($admin, ['*']);

        // 2. Action (Hit endpoint API)
        $response = $this->postJson('/api/users', [
            'name' => 'Pegawai Baru',
            'email' => 'pegawai@test.com',
            'password' => 'password123'
        ]);

        // 3. Assert (Verifikasi Hasil)
        // Pastikan response API adalah 201 (Created)
        $response->assertStatus(201);

        // Pastikan user baru benar-benar masuk ke database
        $this->assertDatabaseHas('users', [
            'email' => 'pegawai@test.com',
            'role' => 'employee'
        ]);

        // Pastikan trigger otomatis leave_balances berjalan dan kuota terisi 12
        $newUser = User::where('email', 'pegawai@test.com')->first();
        $this->assertDatabaseHas('leave_balances', [
            'user_id' => $newUser->id,
            'leave_type_id' => $leaveType->id,
            'total_quota' => 12,
            'used' => 0
        ]);
    }
}
