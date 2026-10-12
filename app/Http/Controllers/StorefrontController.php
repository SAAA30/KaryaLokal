<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cart;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(Request $request)
    {
        if ($request->user()?->isArtisan()) {
            return redirect()->route('artisan.dashboard');
        }

        $featuredProducts = Product::with(['artisan.artisanProfile', 'category'])
            ->withAvg('reviews', 'product_rating')
            ->withCount('reviews')
            ->where('verification_status', Product::STATUS_APPROVED)
            ->where('stock', '>', 0)
            ->latest()
            ->orderByDesc('id')
            ->limit(12)
            ->get();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->limit(7)->get();
        $cartCount = Cart::query()
            ->when($request->user(), fn ($query, $user) => $query->where('user_id', $user->id))
            ->when(!$request->user(), fn ($query) => $query->where('session_id', $request->session()->getId()))
            ->sum('quantity');
        $wishlistProductIds = $request->user()?->isCustomer()
            ? $request->user()->wishlists()->pluck('product_id')->all()
            : [];

        return view('storefront.index', compact('featuredProducts', 'categories', 'cartCount', 'wishlistProductIds'));
    }

    public function about(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if ($request->user()?->isArtisan()) {
            return redirect()->route('artisan.dashboard');
        }

        return view('storefront.about');
    }

    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['artisan.artisanProfile', 'category'])
            ->withAvg('reviews', 'product_rating')
            ->withCount('reviews')
            ->where('verification_status', Product::STATUS_APPROVED)
            ->where('stock', '>', 0)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->string('q')->toString());
                $query->where(function ($products) use ($term) {
                    $products->where('name', 'like', "%{$term}%")
                        ->orWhere('material', 'like', "%{$term}%")
                        ->orWhere('origin', 'like', "%{$term}%")
                        ->orWhereHas('artisan', fn ($artisan) => $artisan->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('kategori'), fn ($query) => $query->whereHas(
                'category', fn ($category) => $category->where('slug', $request->string('kategori')->toString())
            ))
            ->when($request->boolean('custom'), fn ($query) => $query->where('is_customizable', true))
            ->when($request->boolean('preorder'), fn ($query) => $query->where('fulfillment_type', 'pre_order'))
            ->when($request->filled('min_harga'), fn ($query) => $query->where('price', '>=', $request->integer('min_harga')))
            ->when($request->filled('max_harga'), fn ($query) => $query->where('price', '<=', $request->integer('max_harga')))
            ->when($request->filled('sort'), function ($query) use ($request) {
                match ($request->string('sort')->toString()) {
                    'harga_terendah' => $query->orderBy('price'),
                    'harga_tertinggi' => $query->orderByDesc('price'),
                    'terbaru' => $query->latest(),
                    'terpopuler' => $query->orderByDesc('scan_count'),
                    default => $query->latest(),
                };
            }, fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->orderBy('sort_order')->get();

        return view('storefront.catalog', compact('products', 'categories'));
    }

    public function show(Request $request, string $slug)
    {
        $product = Product::with(['artisan.artisanProfile', 'category', 'processSteps', 'reviews.customer'])
            ->where('slug', $slug)
            ->where('verification_status', Product::STATUS_APPROVED)
            ->firstOrFail();

        if ($request->user()?->isAdmin()) {
            return view('admin.products.show', compact('product'));
        }

        if ($request->user()?->isArtisan() && $product->artisan_id === $request->user()->id) {
            return view('artisan.products.show', compact('product'));
        }

        $product->loadMissing('reviews');
        $reviews = $product->reviews()->with('customer')->where('is_published', true)->latest()->get();

        $completedOrderItems = $request->user()?->isCustomer()
            ? OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', fn ($query) => $query->where('customer_id', $request->user()->id)->where('status', 'completed'))
                ->with('review')
                ->get()
            : collect();
        $canReview = $completedOrderItems->contains(fn ($item) => !$item->review);
        $hasCompletedPurchase = $completedOrderItems->isNotEmpty();

        return view('storefront.product', compact('product', 'reviews', 'canReview', 'hasCompletedPurchase'));
    }
}
