<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Cat Air', 'description' => 'Cat air dan watercolour', 'code' => 'CTA'],
            ['name' => 'Cat Minyak', 'description' => 'Cat minyak untuk lukis', 'code' => 'CTM'],
            ['name' => 'Cat Akrilik', 'description' => 'Cat akrilik dan acrylic paint', 'code' => 'CAK'],
            ['name' => 'Kuas', 'description' => 'Kuas lukis dan kuas seni', 'code' => 'KUA'],
            ['name' => 'Kanvas', 'description' => 'Kanvas dan media lukis', 'code' => 'KAN'],
            ['name' => 'Easel', 'description' => 'Easel dan stand lukis', 'code' => 'EAS'],
            ['name' => 'Palet', 'description' => 'Palet lukis dan palet knife', 'code' => 'PAL'],
            ['name' => 'Clay', 'description' => 'Clay dan tanah liat', 'code' => 'CLY'],
            ['name' => 'Alat Pembentuk', 'description' => 'Alat pembentuk dan ukir', 'code' => 'APB'],
            ['name' => 'Oven Clay', 'description' => 'Oven dan perlengkapan clay', 'code' => 'OVC'],
            ['name' => 'Finishing Clay', 'description' => 'Finishing dan vernish clay', 'code' => 'FIC'],
            ['name' => 'Aksesoris Clay', 'description' => 'Aksesoris dan perlengkapan clay', 'code' => 'ACC'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
