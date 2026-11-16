<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $favorites = Favorite::with(['product.category', 'product.seller'])
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->get();

        $favoriteIds = $favorites->pluck('product_id')->all();

        return view('buyer.favorit', compact('favorites', 'favoriteIds'));
    }

    public function toggle(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $userId = $request->user()->id;

        $existing = Favorite::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
            $message = "{$product->name} dihapus dari Favorit.";
        } else {
            Favorite::create([
                'user_id' => $userId,
                'product_id' => $product->id,
            ]);
            $favorited = true;
            $message = "{$product->name} disimpan ke Favorit.";
        }

        $count = Favorite::where('user_id', $userId)->count();

        if ($request->expectsJson()) {
            return response()->json([
                'favorited' => $favorited,
                'count' => $count,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
