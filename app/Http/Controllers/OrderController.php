<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PaymentQrCodeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function checkout(Request $request, PaymentQrCodeService $paymentQrCode): View|RedirectResponse
    {
        $selectedIds = $request->input('selected', []);
        $query = $request->user()->cartItems()->with('product.artisan');

        if (!empty($selectedIds)) {
            $selectedIds = collect($selectedIds)
                ->map(fn ($id) => (int) $id)
                ->filter()->unique()->values()->all();

            if ($selectedIds === []) {
                return redirect()->route('cart.index')->with('error', 'Pilih minimal satu karya dulu sebelum checkout.');
            }

            $items = $query->whereIn('id', $selectedIds)->get();
            if ($items->count() !== count($selectedIds)) {
                return redirect()->route('cart.index')->with('error', 'Karya yang dipilih tidak tersedia di keranjang.');
            }
        } else {
            $items = $query->get();
        }

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $subtotal = $items->sum(fn (Cart $item) => (float) $item->product->price * $item->quantity);

        $qrisImage = $paymentQrCode->checkoutImage();

        return view('storefront.checkout', compact('items', 'subtotal', 'qrisImage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:12'],
            'shipping_method' => ['required', 'in:regular,express,same_day'],
            'payment_method' => ['required', 'in:bank_transfer,qris,virtual_account'],
            'selected_items' => ['sometimes', 'array'],
            'selected_items.*' => ['integer', 'exists:carts,id'],
        ]);
        $selectedIds = $data['selected_items'] ?? $request->input('selected_items', []);
        if (empty($selectedIds)) {
            $selectedIds = $request->user()->cartItems()->pluck('id')->toArray();
        }

        $paymentExpiresAt = match ($data['payment_method']) {
            'qris' => now()->addMinutes((int) config('services.karyalokal_payment.qris_expiry_minutes', 30)),
            'virtual_account' => now()->addHours((int) config('services.karyalokal_payment.va_expiry_hours', 2)),
            default => now()->addHours((int) config('services.karyalokal_payment.bank_expiry_hours', 24)),
        };

        $order = DB::transaction(function () use ($request, $data, $selectedIds, $paymentExpiresAt): Order {
            if ($selectedIds === []) {
                throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
            }

            $items = $request->user()->cartItems()->with('product')->whereIn('id', $selectedIds)->lockForUpdate()->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['selected_items' => 'Karya yang dipilih tidak tersedia di keranjang.']);
            }

            $subtotal = 0;
            foreach ($items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                if (!$product || $product->verification_status !== Product::STATUS_APPROVED || $product->stock < $item->quantity) {
                    throw ValidationException::withMessages(['cart' => "Stok atau status {$item->product->name} sudah berubah."]);
                }
                $subtotal += (float) $product->price * $item->quantity;
            }

            $shippingCost = match ($data['shipping_method']) {
                'express' => 30000,
                'same_day' => 50000,
                default => 18000,
            };
            $order = Order::create($data + [
                'order_number' => $this->newOrderNumber(),
                'customer_id' => $request->user()->id,
                'status' => 'awaiting_payment',
                'payment_status' => 'pending',
                'payment_expires_at' => $paymentExpiresAt,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_total' => 0,
                'total' => $subtotal + $shippingCost,
            ]);

            foreach ($items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'artisan_id' => $product->artisan_id,
                    'product_name' => $product->name,
                    'craft_id' => $product->craft_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $product->price,
                    'variation' => $item->variation,
                    'custom_request' => $item->custom_request,
                    'recipient_name' => $item->recipient_name,
                    'sender_name' => $item->sender_name,
                    'gift_message' => $item->gift_message,
                ]);
                $product->decrement('stock', $item->quantity);
            }

            $request->user()->cartItems()->whereIn('id', $selectedIds)->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan berhasil dibuat. Pembayaran masih menunggu konfirmasi.');
    }

    public function index(Request $request): View
    {
        $orders = $request->user()->customerOrders()->with('items')->latest()->paginate(12);

        return view('storefront.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order, PaymentQrCodeService $paymentQrCode): View
    {
        abort_unless($order->customer_id === $request->user()->id, 404);
        $order->load('items.artisan', 'items.review');
        $qrisImage = $paymentQrCode->checkoutImage();
        $paymentDeadline = $order->paymentDeadline();

        return view('storefront.orders.show', compact('order', 'qrisImage', 'paymentDeadline'));
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        if ($order->payment_status !== 'paid'
            || !in_array($order->status, ['processing', 'shipped'], true)
            || !filled($order->tracking_number)) {
            return back()->with('error', 'Pesanan hanya dapat diselesaikan setelah pembayaran lunas dan nomor resi tersedia.');
        }

        $order->update([
            'status' => 'completed',
            'received_at' => now(),
        ]);

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan selesai. Silakan berikan ulasan untuk karya yang dibeli.');
    }

    public function invoice(Request $request, Order $order): View
    {
        abort_unless($order->customer_id === $request->user()->id, 404);
        $order->load('items.artisan', 'customer');

        return view('storefront.orders.invoice', compact('order'));
    }

    private function newOrderNumber(): string
    {
        do {
            $number = 'KL-' . now()->format('ymd') . '-' . Str::upper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
