<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The receipt used to hard-code the store details while the settings still held the
 * seeder placeholders. Now that the receipt reads from settings, copy the real store
 * details in — but only where the value is still the untouched placeholder.
 */
return new class extends Migration
{
    private array $placeholders = [
        // key => [placeholder from the seeder, value the receipt printed before]
        'store_name' => ['YourStudio', 'YOUR STUDIO'],
        'store_address' => ['Jl. Contoh No. 123', 'Jl. Raya Sawojajar Ruko WOW Paris, Kav PA-1 12, Malang'],
        'store_phone' => ['08123456789', ''],
        'receipt_header' => ['Terima kasih telah berbelanja', 'Create your own studio'],
        'receipt_footer' => ['Barang yang sudah dibeli tidak dapat dikembalikan', 'Terima kasih telah berbelanja di YOUR STUDIO. Sampai jumpa lagi!'],
    ];

    public function up(): void
    {
        foreach ($this->placeholders as $key => [$placeholder, $real]) {
            DB::table('system_settings')->where('key', $key)->where('value', $placeholder)
                ->update(['value' => $real, 'updated_at' => now()]);
        }

        if (!DB::table('system_settings')->where('key', 'store_instagram')->exists()) {
            DB::table('system_settings')->insert([
                'key' => 'store_instagram',
                'value' => '@your__studio',
                'type' => 'text',
                'group' => 'store',
                'label' => 'Instagram Toko',
                'description' => 'Akun Instagram yang ditampilkan di struk (kosongkan untuk menyembunyikan)',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->placeholders as $key => [$placeholder, $real]) {
            DB::table('system_settings')->where('key', $key)->where('value', $real)
                ->update(['value' => $placeholder, 'updated_at' => now()]);
        }

        DB::table('system_settings')->where('key', 'store_instagram')->delete();
    }
};
