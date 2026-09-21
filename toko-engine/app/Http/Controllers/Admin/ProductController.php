<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StoreLimitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private readonly StoreLimitService $storeLimits) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $products = Product::query()
            ->with(['category', 'media', 'variants'])
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search,
            'canAddProduct' => $this->storeLimits->canAddProduct(),
            'productLimit' => $this->storeLimits->productLimit(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'canAddProduct' => $this->storeLimits->canAddProduct(),
            'productLimit' => $this->storeLimits->productLimit(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->storeLimits->canAddProduct()) {
            return back()
                ->withInput()
                ->withErrors(['limit' => $this->productLimitMessage()]);
        }

        $validated = $this->validateProduct($request);
        $product = Product::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name']),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('product-images');
        }

        $this->syncVariants($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        $product->load('media');

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $product->update([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], $product),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('product-images');
        }

        $this->syncVariants($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    /**
     * Rewrite a product's variants from the submitted rows.
     *
     * Rows are matched on the option values they carry, so editing a price keeps
     * the same variant row instead of replacing it.
     */
    private function syncVariants(Request $request, Product $product): void
    {
        $validated = $request->validate([
            'option_names' => ['nullable', 'array', 'max:2'],
            'option_names.*' => ['nullable', 'string', 'max:50'],
            'variants' => ['nullable', 'array', 'max:100'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.values' => ['nullable', 'array', 'max:2'],
            'variants.*.values.*' => ['nullable', 'string', 'max:50'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $groups = array_values(array_filter(
            array_map(fn (mixed $name): string => is_string($name) ? trim($name) : '', $validated['option_names'] ?? []),
            fn (string $name): bool => $name !== '',
        ));

        $keptIds = [];
        $position = 0;

        if ($groups !== []) {
            foreach ($validated['variants'] ?? [] as $index => $row) {
                $values = array_map(
                    fn (mixed $value): string => is_string($value) ? trim($value) : '',
                    array_values($row['values'] ?? []),
                );
                $options = [];

                foreach ($groups as $groupIndex => $group) {
                    $value = $values[$groupIndex] ?? '';

                    if ($value === '') {
                        continue 2; // A row missing one of its option values is not a variant.
                    }

                    $options[$group] = $value;
                }

                $variant = $product->variants()->firstOrNew(['name' => implode(' / ', array_values($options))]);
                $variant->fill([
                    'options' => ProductVariant::encodeOptions($options),
                    'price' => $row['price'] ?? $product->price,
                    'stock' => $row['stock'] ?? 0,
                    'is_active' => $request->boolean("variants.{$index}.is_active"),
                    'position' => $position++,
                ]);
                $product->variants()->save($variant);
                $keptIds[] = $variant->id;
            }
        }

        // Variants that already carry order history are retired, not deleted, so
        // past orders keep pointing at what was actually bought.
        foreach ($product->variants()->whereNotIn('id', $keptIds ?: [0])->get() as $stale) {
            if ($stale->orderItems()->exists()) {
                $stale->forceFill(['is_active' => false])->save();

                continue;
            }

            $stale->delete();
        }

        $product->unsetRelation('variants');
    }

    private function uniqueSlug(string $name, ?Product $ignoredProduct = null): string
    {
        $baseSlug = Str::slug($name) ?: 'produk';
        $slug = $baseSlug;
        $counter = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($ignoredProduct, fn ($query) => $query->whereKeyNot($ignoredProduct->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function productLimitMessage(): string
    {
        $limit = $this->storeLimits->productLimit();

        return "Batas paket tercapai ({$limit} produk). Hapus produk atau tingkatkan paket untuk menambah produk baru.";
    }
}
