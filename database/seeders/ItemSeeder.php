<?php

namespace Database\Seeders;

use App\Models\Barcode;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Produk Seni & Kerajinan (102 item), tiap item dikategorikan sesuai nama barang.
     */
    public function run(): void
    {
        $categoriesByName = Category::all()->keyBy('name');
        if ($categoriesByName->isEmpty()) {
            $this->command->warn('Tidak ada kategori. Jalankan CategorySeeder dulu.');
            return;
        }

        $createdBy = User::first()?->id ?? 1;
        $defaultCategory = $categoriesByName->first();

        // Nama barang => nama kategori (sesuai CategorySeeder)
        $itemCategoryMap = [
            // Clay
            'Clay White 250 Gram' => 'Clay',
            'Clay White 500 Gram' => 'Clay',
            'Clay White 1000 Gram' => 'Clay',
            'Clay Teracotta 500 Gram' => 'Clay',
            'Clay Teracotta 1000 Gram' => 'Clay',
            'Clay Wood 350 Gram' => 'Clay',
            'Clay Wood 700 Gram' => 'Clay',
            'Clay Stone 1000 Gram' => 'Clay',
            'Foam Clay' => 'Clay',
            // Kuas
            'Kuas Datar' => 'Kuas',
            'Kuas Runcing' => 'Kuas',
            'Kuas isi 10' => 'Kuas',
            'Pinsel' => 'Kuas',
            'VTEC Kuas 101 Kasar' => 'Kuas',
            'VTEC Kuas 102 Halus' => 'Kuas',
            // Cat Air
            'Giotto Acquarelli' => 'Cat Air',
            'VTEC Cat Air 3mlx12 warna' => 'Cat Air',
            'Watercolour Pad A5' => 'Cat Air',
            'Watercolour Pad A4' => 'Cat Air',
            // Cat Akrilik
            'Cat Akrilik-Bearbrick' => 'Cat Akrilik',
            'VTEC Cat Akrilik Basic' => 'Cat Akrilik',
            'VTEC Cat Akrilik Pastel' => 'Cat Akrilik',
            'Cat Akrilik 33 Set' => 'Cat Akrilik',
            'VTEC Cat Akrilik 5mlx12 warna' => 'Cat Akrilik',
            'VTEC Cat Akrilik 35mlx6 warna' => 'Cat Akrilik',
            'VTEC Cat Akrilik 3mlx12 warna' => 'Cat Akrilik',
            'Cat Pastel' => 'Cat Akrilik',
            'Cat Basic' => 'Cat Akrilik',
            // Kanvas
            'Kanvas Bulat 15 cm' => 'Kanvas',
            'Kanvas Bulat 20 cm' => 'Kanvas',
            'Kanvas Bulat 25 cm' => 'Kanvas',
            'Kanvas Persegi 15x20' => 'Kanvas',
            'Kanvas Persegi 20x20' => 'Kanvas',
            'Kanvas Persegi 20x25' => 'Kanvas',
            'Kanvas Persegi 20x30' => 'Kanvas',
            'Kanvas Persegi 30x30' => 'Kanvas',
            'Kanvas Mont Marte Easel' => 'Kanvas',
            'Kanvas Panel 10x10 isi 5' => 'Kanvas',
            'Kanvas Panel 12x17 isi 3' => 'Kanvas',
            'Kanvas Panel 20x25 isi 2' => 'Kanvas',
            'Kanvas Panel 20x20 with standing board' => 'Kanvas',
            'Paint By Number' => 'Kanvas',
            // Easel
            'Easel' => 'Easel',
            // Palet
            'Palete Knive Segitiga S' => 'Palet',
            'Palete Knive Segitiga M' => 'Palet',
            'Palete Knive Segitiga L' => 'Palet',
            'Palete Knive Persegi M' => 'Palet',
            'Pallet Lukis Mini' => 'Palet',
            // Alat Pembentuk
            'Alat Ukir isi 14' => 'Alat Pembentuk',
            'Alat Ukir isi 8' => 'Alat Pembentuk',
            'Alat Ukir isi 10' => 'Alat Pembentuk',
            // Finishing Clay
            'Vernish 30 ml' => 'Finishing Clay',
            'Vernish 100ml' => 'Finishing Clay',
            'Varnish VTCC 100ml' => 'Finishing Clay',
            'Varnish Reeves 75ml' => 'Finishing Clay',
            'Amplas' => 'Finishing Clay',
            // Aksesoris Clay / peralatan & aksesoris umum
            'Akrilik Bulat' => 'Aksesoris Clay',
            'Cutter Knive' => 'Aksesoris Clay',
            'Cutter Knive + Refill' => 'Aksesoris Clay',
            'Scrapper Motif' => 'Aksesoris Clay',
            'Spackling Paste' => 'Aksesoris Clay',
            'Sponge' => 'Aksesoris Clay',
            'Roller' => 'Aksesoris Clay',
            'Scrapper' => 'Aksesoris Clay',
            'Pengait Paku' => 'Aksesoris Clay',
            'Ring Keychain S' => 'Aksesoris Clay',
            'Ring Keychain M' => 'Aksesoris Clay',
            'Sambungan Keychain' => 'Aksesoris Clay',
            'Mirror' => 'Aksesoris Clay',
            'Coaster Bulat, Pita' => 'Aksesoris Clay',
            'Coaster Bubble, Bintang' => 'Aksesoris Clay',
            'Coaster Abstrak Cloud' => 'Aksesoris Clay',
            'Journalling set blue' => 'Aksesoris Clay',
            'Journalling set pink' => 'Aksesoris Clay',
            'Journalling set purple' => 'Aksesoris Clay',
            'Capybara magic' => 'Aksesoris Clay',
            'Capybara super' => 'Aksesoris Clay',
            'Capybara Notebook' => 'Aksesoris Clay',
            'Capybara stickynotes orange' => 'Aksesoris Clay',
            'Capybara stickynotes sleep' => 'Aksesoris Clay',
            'Capybara Stickynotes Book' => 'Aksesoris Clay',
            'Pencilcase brown' => 'Aksesoris Clay',
            'Pencilcase white' => 'Aksesoris Clay',
            'Marker Pad Mont Marte' => 'Aksesoris Clay',
            'Acrylic Marker 12' => 'Aksesoris Clay',
            'Acrylic Marker 24' => 'Aksesoris Clay',
            'Acrylic Marker 36' => 'Aksesoris Clay',
            'Premium Marker 12' => 'Aksesoris Clay',
            'Premium Marker 24' => 'Aksesoris Clay',
            'Premium Marker 36' => 'Aksesoris Clay',
            'Totebag' => 'Aksesoris Clay',
            'Frame' => 'Aksesoris Clay',
            'Stiker' => 'Aksesoris Clay',
            'Bag Gift Capybara' => 'Aksesoris Clay',
            'Notes Capybara' => 'Aksesoris Clay',
            'Cutting Mat A3' => 'Aksesoris Clay',
            'Cutting Mat A2' => 'Aksesoris Clay',
            'Cutting Mat A1' => 'Aksesoris Clay',
            'Canson Kertas A6' => 'Aksesoris Clay',
            'Canson Kertas M' => 'Aksesoris Clay',
            'Canson Kertas A5' => 'Aksesoris Clay',
            'Schoolplast LYRA' => 'Aksesoris Clay',
        ];

        $baseBarcode = 1000000000;
        $index = 0;

        foreach ($itemCategoryMap as $name => $categoryName) {
            $category = $categoriesByName->get($categoryName) ?? $defaultCategory;

            $item = Item::create([
                'name' => $name,
                'description' => null,
                'category_id' => $category->id,
                'supplier_id' => null,
                'purchase_price' => 0,
                'selling_price' => 0,
                'stock_quantity' => 0,
                'minimum_stock' => 0,
                'unit' => 'pcs',
                'image' => null,
                'is_active' => true,
            ]);

            $barcodeValue = (string) ($baseBarcode + $index + 1);
            Barcode::create([
                'item_id' => $item->id,
                'barcode_type' => 'CODE128',
                'barcode_value' => $barcodeValue,
                'barcode_image_path' => null,
                'is_active' => true,
                'is_printed' => false,
                'printed_at' => null,
                'created_by' => $createdBy,
            ]);
            $index++;
        }

        $this->command->info('ItemSeeder: ' . count($itemCategoryMap) . ' produk Seni & Kerajinan (per kategori) beserta barcode CODE128 telah dibuat.');
    }
}
