<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Store Settings
            [
                'key' => 'store_name',
                'value' => 'YourStudio',
                'type' => 'text',
                'group' => 'store',
                'label' => 'Nama Toko',
                'description' => 'Nama toko yang akan ditampilkan di struk dan laporan',
                'is_public' => true
            ],
            [
                'key' => 'store_address',
                'value' => 'Jl. Contoh No. 123',
                'type' => 'text',
                'group' => 'store',
                'label' => 'Alamat Toko',
                'description' => 'Alamat lengkap toko',
                'is_public' => true
            ],
            [
                'key' => 'store_phone',
                'value' => '08123456789',
                'type' => 'text',
                'group' => 'store',
                'label' => 'Nomor Telepon',
                'description' => 'Nomor telepon toko',
                'is_public' => true
            ],
            
            // Receipt Settings
            [
                'key' => 'receipt_header',
                'value' => 'Terima kasih telah berbelanja',
                'type' => 'text',
                'group' => 'receipt',
                'label' => 'Header Struk',
                'description' => 'Teks yang akan ditampilkan di bagian atas struk',
                'is_public' => false
            ],
            [
                'key' => 'receipt_footer',
                'value' => 'Barang yang sudah dibeli tidak dapat dikembalikan',
                'type' => 'text',
                'group' => 'receipt',
                'label' => 'Footer Struk',
                'description' => 'Teks yang akan ditampilkan di bagian bawah struk',
                'is_public' => false
            ],
            
            // Inventory Settings
            [
                'key' => 'low_stock_threshold',
                'value' => '10',
                'type' => 'number',
                'group' => 'inventory',
                'label' => 'Batas Stok Minimum',
                'description' => 'Jumlah minimal stok sebelum notifikasi stok menipis',
                'is_public' => false
            ],
            [
                'key' => 'enable_low_stock_notification',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notification',
                'label' => 'Notifikasi Stok Menipis',
                'description' => 'Aktifkan notifikasi ketika stok mendekati batas minimum',
                'is_public' => false
            ],
            
            // Printer Settings
            [
                'key' => 'printer_name',
                'value' => 'POS-58',
                'type' => 'text',
                'group' => 'printer',
                'label' => 'Nama Printer',
                'description' => 'Nama printer yang digunakan untuk mencetak struk',
                'is_public' => false
            ],
            [
                'key' => 'auto_print_receipt',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'printer',
                'label' => 'Cetak Struk Otomatis',
                'description' => 'Otomatis mencetak struk setelah transaksi selesai',
                'is_public' => false
            ]
        ];

        foreach ($settings as $setting) {
            \App\Models\SystemSetting::create($setting);
        }
    }
}
