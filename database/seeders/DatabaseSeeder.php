<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
  {
      public function run()
      {
          $this->call([
              SuperAdminSeeder::class,  // SuperAdmin harus dibuat pertama
              UserSeeder::class,        // User lain menggunakan approved_by = 1 (SuperAdmin ID)
              CategorySeeder::class,
              SupplierSeeder::class,
              SystemSettingSeeder::class,
              ItemSeeder::class,
          ]);
      }
  }
