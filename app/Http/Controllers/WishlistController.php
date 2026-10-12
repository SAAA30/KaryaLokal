<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $wishlists = Wishlist::with('product.artisan.artisanProfile')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('storefront.wishlist', compact('wishlists'));
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->verification_status === Product::STATUS_APPROVED, 404);
        $saved = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($saved) {
            $saved->delete();
            $message = 'Karya dihapus dari wishlist.';
        } else {
            Wishlist::create(['user_id' => $request->user()->id, 'product_id' => $product->id]);
            $message = 'Karya disimpan ke wishlist.';
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, Wishlist $wishlist): RedirectResponse
    {
        abort_unless($wishlist->user_id === $request->user()->id, 404);
        $wishlist->delete();

        return back()->with('success', 'Karya dihapus dari wishlist.');
    }
}
