<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Console\Command;

class AssignItemsCategory extends Command
{
    protected $signature = 'items:assign-category {category_id? : ID kategori (kosongkan = kategori pertama)}';
    protected $description = 'Set semua item ke satu kategori (untuk perbaikan category_id yang tidak cocok)';

    public function handle(): int
    {
        $categoryId = $this->argument('category_id');
        if ($categoryId !== null) {
            $categoryId = (int) $categoryId;
            $category = Category::find($categoryId);
        } else {
            $category = Category::orderBy('id')->first();
        }

        if (!$category) {
            $this->error('Kategori tidak ditemukan. Buat kategori dulu atau beri ID yang benar.');
            return 1;
        }

        $updated = Item::query()->update(['category_id' => $category->id]);
        $total = Item::count();

        $this->info("Semua {$total} item sekarang memakai kategori: {$category->name} (ID: {$category->id}).");
        return 0;
    }
}
