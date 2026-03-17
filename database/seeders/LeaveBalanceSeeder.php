<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class LeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employee = User::where('role', 'employee')->first();
        // Sesuaikan nama pencarian menjadi 'Annual Leave'
        $annualLeave = DB::table('leave_types')->where('name', 'Annual Leave')->first();

        if ($employee && $annualLeave) {
            DB::table('leave_balances')->insert([
                'user_id' => $employee->id,
                'leave_type_id' => $annualLeave->id,
                'year' => date('Y'),
                'total_quota' => $annualLeave->default_quota,
                'used' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
