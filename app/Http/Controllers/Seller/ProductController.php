<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $sellerId = $request->user()->id;
        $search = trim((string) $request->query('q', ''));
        $categoryFilter = $request->query('category_id', '');

        $products = Product::with('category')
            ->where('seller_id', $sellerId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($categoryFilter !== '', fn ($q) => $q->where('category_id', $categoryFilter))
            ->latest('id')
            ->get();

        $allSellerProducts = Product::where('seller_id', $sellerId);

        $stats = [
            'total' => (clone $allSellerProducts)->count(),
            'ready' => (clone $allSellerProducts)->where('stock', '>', 0)->count(),
            'empty' => (clone $allSellerProducts)->where('stock', 0)->count(),
            'featured' => (clone $allSellerProducts)->where('is_featured', true)->count(),
        ];

        $categories = Category::orderBy('name')->get();

        return view('seller.products.index', compact('products', 'categories', 'stats', 'search', 'categoryFilter'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $data['seller_id'] = $request->user()->id;

        Product::create($data);

        return redirect()->route('seller.products.index')
            ->with('success', "Produk {$data['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->seller_id === $request->user()->id, 403, 'Anda tidak berhak mengubah produk ini.');

        $data = $this->validateProduct($request);
        $product->update($data);

        return redirect()->route('seller.products.index')
            ->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->seller_id === $request->user()->id, 403, 'Anda tidak berhak menghapus produk ini.');

        if ($product->orders()->exists()) {
            return redirect()->route('seller.products.index')
                ->with('error', "Produk {$product->name} tidak dapat dihapus karena sudah memiliki riwayat pesanan. Set stok ke 0 untuk menonaktifkannya.");
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', "Produk {$name} berhasil dihapus.");
    }

    private function validateProduct(Request $request): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'price_per_day' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:65000'],
            'image_file' => ['nullable', 'image', 'max:2048'],
        ]);

        unset($validated['image_file']);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $dir = public_path('uploads/products');
            File::ensureDirectoryExists($dir);

            $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $validated['image_url'] = '/uploads/products/'.$filename;
        } else {
            $validated['image_url'] = isset($validated['image_url']) && trim($validated['image_url']) !== ''
                ? trim($validated['image_url'])
                : null;
        }

        $validated['is_featured'] = $request->boolean('is_featured');

        return $validated;
    }
}
