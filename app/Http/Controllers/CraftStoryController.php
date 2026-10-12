<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\QrScan;
use Illuminate\Http\Request;

class CraftStoryController extends Controller
{
    public function show(Request $request, string $craftId)
    {
        $product = Product::with(['artisan.artisanProfile', 'category', 'processSteps'])
            ->where('craft_id', $craftId)
            ->where('qr_active', true)
            ->where('verification_status', Product::STATUS_APPROVED)
            ->firstOrFail();

        QrScan::create([
            'product_id' => $product->id,
            'user_id' => $request->user()?->id,
            'visitor_hash' => $request->ip()
                ? hash_hmac('sha256', $request->ip(), (string) config('app.key'))
                : null,
            'scanned_at' => now(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);
        $product->increment('scan_count');

        return view('storefront.craft-story', compact('product'));
    }
}
