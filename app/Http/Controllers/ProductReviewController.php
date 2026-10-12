<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->verification_status === Product::STATUS_APPROVED, 404);

        $orderItem = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query
                ->where('customer_id', $request->user()->id)
                ->where('status', 'completed'))
            ->whereDoesntHave('review')
            ->latest('id')
            ->firstOrFail();

        return $this->createReview($request, $orderItem, route('products.show', $product->slug));
    }

    public function storeForOrderItem(Request $request, Order $order, OrderItem $orderItem): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);
        abort_unless($order->status === 'completed' && $order->payment_status === 'paid', 403);
        abort_unless($orderItem->order_id === $order->id && $orderItem->product_id, 404);
        abort_unless($orderItem->product?->verification_status === Product::STATUS_APPROVED, 404);
        abort_if($orderItem->review()->exists(), 409);

        return $this->createReview(
            $request,
            $orderItem,
            route('products.show', $orderItem->product->slug) . '#reviews'
        );
    }

    private function createReview(Request $request, OrderItem $orderItem, string $redirect): RedirectResponse
    {
        $data = $request->validate([
            'product_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $orderItem->review()->create([
            'customer_id' => $request->user()->id,
            'product_id' => $orderItem->product_id,
            'product_rating' => $data['product_rating'],
            'body' => $data['body'] ?? null,
            'is_published' => true,
        ]);

        return redirect($redirect)->with('success', 'Ulasan berhasil dikirim.');
    }
}
