<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Halaman Beranda publik (menampilkan preview beberapa produk unggulan + tautan ke Marketplace).
     */
    public function index(Request $request): View
    {
        $readyCount = Product::where('stock', '>', 0)->count();

        $featuredProducts = Product::with(['category', 'seller'])
            ->orderByDesc('is_featured')
            ->latest('id')
            ->take(4)
            ->get();

        $favoriteIds = [];
        if ($request->user()?->isPembeli()) {
            $favoriteIds = Favorite::where('user_id', $request->user()->id)
                ->pluck('product_id')
                ->all();
        }

        return view('buyer.jelajah', compact('readyCount', 'featuredProducts', 'favoriteIds'));
    }

    /**
     * Halaman Marketplace khusus daftar & penyewaan produk.
     */
    public function marketplace(Request $request): View
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::with(['category', 'seller'])
            ->orderByDesc('is_featured')
            ->latest('id')
            ->get();

        $favoriteIds = [];
        if ($request->user()?->isPembeli()) {
            $favoriteIds = Favorite::where('user_id', $request->user()->id)
                ->pluck('product_id')
                ->all();
        }

        $initialSearch = trim((string) $request->query('q', ''));
        $initialCategory = (string) $request->query('category', 'all');
        $initialSort = (string) $request->query('sort', 'featured');

        return view('buyer.marketplace', compact(
            'categories',
            'products',
            'favoriteIds',
            'initialSearch',
            'initialCategory',
            'initialSort'
        ));
    }
}
