<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $products = Product::query()
            ->with(['category', 'media'])
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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
}
