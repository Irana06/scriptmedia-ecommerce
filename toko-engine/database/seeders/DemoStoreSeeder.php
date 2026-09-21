<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
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

                $hasVariants = isset($productData['variants']);

                $product = Product::query()->updateOrCreate(
                    ['slug' => $storeSlug.'-'.Str::slug($productData['name'])],
                    [
                        'category_id' => $category->id,
                        'name' => $productData['name'],
                        'description' => 'Produk pilihan '.$store['store_name'].' dengan kualitas terkurasi dan dukungan layanan terpercaya.',
                        'price' => $productData['price'],
                        // Variant products keep their stock on the variants themselves.
                        'stock' => $hasVariants ? 0 : 25 - $index,
                        'is_featured' => $index < 3,
                        'is_active' => true,
                    ],
                );

                if ($hasVariants) {
                    $this->seedVariants($product, $productData['variants']);
                }
            }
        }
    }

    /**
     * @param  array{groups: list<string>, rows: list<array{values: list<string>, price: int, stock: int}>}  $definition
     */
    private function seedVariants(Product $product, array $definition): void
    {
        $position = 0;

        foreach ($definition['rows'] as $row) {
            $options = [];

            foreach ($definition['groups'] as $groupIndex => $group) {
                $options[$group] = $row['values'][$groupIndex];
            }

            ProductVariant::query()->updateOrCreate(
                ['product_id' => $product->id, 'name' => implode(' / ', $row['values'])],
                [
                    'options' => ProductVariant::encodeOptions($options),
                    'price' => $row['price'],
                    'stock' => $row['stock'],
                    'is_active' => true,
                    'position' => $position++,
                ],
            );
        }
    }
}
