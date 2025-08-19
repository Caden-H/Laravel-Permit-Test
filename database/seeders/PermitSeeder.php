<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permit;

class PermitSeeder extends Seeder
{
    public function run(): void
    {
        Permit::insert([
            [
                'number' => 'PRM-1001',
                'applicant' => 'Ada Lovelace',
                'phone_number' => '+15555550101',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => 'PRM-1002',
                'applicant' => 'Grace Hopper',
                'phone_number' => '+15555550102',
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => 'PRM-1003',
                'applicant' => 'Alan Turing',
                'phone_number' => '+15555550103',
                'status' => 'rejected',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
