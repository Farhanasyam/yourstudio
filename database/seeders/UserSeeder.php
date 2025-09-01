<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

  class UserSeeder extends Seeder
  {
      public function run()
      {
          // Admin (approved)
          User::create([
              'name' => 'Administrator',
              'email' => 'admin@yourstudio.com',
              'password' => Hash::make('password123'),
              'role' => 'admin',
              'is_active' => true,
              'approval_status' => 'approved',
              'approved_at' => now(),
              'approved_by' => 1, // Will be approved by super admin
              'email_verified_at' => now(),
          ]);

          // Kasir 1 (approved)
          User::create([
              'name' => 'Kasir 1',
              'email' => 'kasir1@yourstudio.com',
              'password' => Hash::make('password123'),
              'role' => 'kasir',
              'is_active' => true,
              'approval_status' => 'approved',
              'approved_at' => now(),
              'approved_by' => 1, // Will be approved by super admin
              'email_verified_at' => now(),
          ]);

          // Kasir 2 (pending - untuk demo approval system)
          User::create([
              'name' => 'Kasir 2',
              'email' => 'kasir2@yourstudio.com',
              'password' => Hash::make('password123'),
              'role' => 'kasir',
              'is_active' => true,
              'approval_status' => 'pending',
              'email_verified_at' => now(),
          ]);

          // Admin Pending (untuk demo approval system)
          User::create([
              'name' => 'Admin Pending',
              'email' => 'admin.pending@yourstudio.com',
              'password' => Hash::make('password123'),
              'role' => 'admin',
              'is_active' => true,
              'approval_status' => 'pending',
              'email_verified_at' => now(),
          ]);
      }
  }
