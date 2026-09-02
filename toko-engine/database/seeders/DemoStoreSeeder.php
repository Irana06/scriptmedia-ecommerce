<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('demo-stores') as $storeSlug => $store) {
            foreach ($store['products'] as $index => $productData) {
                $categorySlug = $storeSlug.'-'.Str::slug($productData['category']);
                $category = Category::query()->updateOrCreate(
                    ['slug' => $categorySlug],
                    ['name' => $productData['category'], 'is_active' => true],
                );

                Product::query()->updateOrCreate(
                    ['slug' => $storeSlug.'-'.Str::slug($productData['name'])],
                    [
                        'category_id' => $category->id,
                        'name' => $productData['name'],
                        'description' => 'Produk pilihan '.$store['store_name'].' dengan kualitas terkurasi dan dukungan layanan terpercaya.',
                        'price' => $productData['price'],
                        'stock' => 25 - $index,
                        'is_featured' => $index < 3,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
