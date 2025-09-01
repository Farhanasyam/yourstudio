<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Elektronik',
                'description' => 'Peralatan elektronik dan gadget',
                'code' => 'ELK',
            ],
            [
                'name' => 'Fashion',
                'description' => 'Pakaian dan aksesoris fashion',
                'code' => 'FSH',
            ],
            [
                'name' => 'Makanan & Minuman',
                'description' => 'Produk makanan dan minuman',
                'code' => 'FNB',
            ],
            [
                'name' => 'Kesehatan & Kecantikan',
                'description' => 'Produk kesehatan dan kecantikan',
                'code' => 'HLT',
            ],
            [
                'name' => 'Rumah Tangga',
                'description' => 'Peralatan rumah tangga',
                'code' => 'HOM',
            ],
            [
                'name' => 'Olahraga',
                'description' => 'Peralatan dan perlengkapan olahraga',
                'code' => 'SPT',
            ],
            [
                'name' => 'Buku & Alat Tulis',
                'description' => 'Buku dan alat tulis kantor',
                'code' => 'BOK',
            ],
            [
                'name' => 'Mainan & Hobi',
                'description' => 'Mainan anak dan perlengkapan hobi',
                'code' => 'TOY',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
