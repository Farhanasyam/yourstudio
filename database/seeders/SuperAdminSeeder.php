<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin if not exists
        $superAdmin = User::where('email', 'superadmin@yourstudio.com')->first();
        
        if (!$superAdmin) {
            User::create([
                'name' => 'Super Administrator',
                'email' => 'superadmin@yourstudio.com',
                'password' => Hash::make('superadmin123'),
                'role' => 'superadmin',
                'is_active' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => null, // SuperAdmin tidak perlu approval dari siapa-siapa
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Super Admin created successfully!');
            $this->command->info('Email: superadmin@yourstudio.com');
            $this->command->info('Password: superadmin123');
        } else {
            $this->command->info('Super Admin already exists!');
        }
    }
}