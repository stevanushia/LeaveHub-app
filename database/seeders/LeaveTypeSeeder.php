<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('leave_types')->insert([
            [
                'name' => 'Annual Leave',
                'default_quota' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sick Leave',
                'default_quota' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
