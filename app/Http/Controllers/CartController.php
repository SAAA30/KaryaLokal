<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $this->mergeDuplicateCartItems($request);

        $items = $this->cartQuery($request)->with('product.artisan')->get();
        $subtotal = $items->sum(fn (Cart $item) => (float) $item->product->price * $item->quantity);

        return view('storefront.cart', compact('items', 'subtotal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'custom_request' => ['nullable', 'string', 'max:1000'],
            'recipient_name' => ['nullable', 'string', 'max:100'],
            'sender_name' => ['nullable', 'string', 'max:100'],
            'gift_message' => ['nullable', 'string', 'max:500'],
            'gift_theme' => ['nullable', 'string', 'max:50'],
        ]);
        $product = Product::whereKey($data['product_id'])
            ->where('verification_status', Product::STATUS_APPROVED)
            ->firstOrFail();

        if ($data['quantity'] > $product->stock) {
            return back()->withErrors(['quantity' => 'Jumlah melebihi stok karya yang tersedia.'])->withInput();
        }

        $existingItems = $this->cartQuery($request)
            ->where('product_id', $product->id)
            ->get();

        if ($existingItems->isNotEmpty()) {
            $newQuantity = $existingItems->sum('quantity') + $data['quantity'];

            if ($newQuantity > $product->stock) {
                return back()->withErrors(['quantity' => 'Jumlah melebihi stok karya yang tersedia.'])->withInput();
            }

            $primaryItem = $existingItems->first();
            $primaryItem->update(['quantity' => $newQuantity]);

            $existingItems->skip(1)->each->delete();
        } else {
            Cart::create($data + [
                'user_id' => $request->user()?->id,
                'session_id' => $request->user() ? null : $this->guestCartSessionId($request),
            ]);
        }

        $this->mergeDuplicateCartItems($request);

        return redirect()->route('cart.index')->with('success', 'Karya ditambahkan ke keranjang.');
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        abort_unless($this->ownsCart($request, $cart), 404);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        if ($data['quantity'] > $cart->product->stock) {
            return back()->withErrors(['quantity' => 'Jumlah melebihi stok karya yang tersedia.']);
        }

        $cart->update($data);

        return back()->with('success', 'Jumlah karya diperbarui.');
    }

    public function destroy(Request $request, Cart $cart): RedirectResponse
    {
        abort_unless($this->ownsCart($request, $cart), 404);
        $cart->delete();

        return back()->with('success', 'Karya dihapus dari keranjang.');
    }

    private function cartQuery(Request $request)
    {
        return Cart::query()->when(
            $request->user(),
            fn ($query, $user) => $query->where('user_id', $user->id),
            fn ($query) => $query->where('session_id', $this->guestCartSessionId($request))
        );
    }

    private function ownsCart(Request $request, Cart $cart): bool
    {
        return $request->user()
            ? $cart->user_id === $request->user()->id
            : $cart->user_id === null && $cart->session_id === $this->guestCartSessionId($request);
    }

    private function mergeDuplicateCartItems(Request $request): void
    {
        $items = $this->cartQuery($request)->get()->groupBy('product_id');

        foreach ($items as $productId => $group) {
            if ($group->count() <= 1) {
                continue;
            }

            $primary = $group->first();
            $totalQuantity = $group->sum('quantity');
            $primary->update(['quantity' => $totalQuantity]);
            $group->skip(1)->each->delete();
        }
    }

    private function guestCartSessionId(Request $request): string
    {
        $sessionId = $request->session()->get('cart_session_id');
        if (is_string($sessionId) && $sessionId !== '') {
            return $sessionId;
        }

        $sessionId = Str::random(48);
        $request->session()->put('cart_session_id', $sessionId);

        return $sessionId;
    }
}
