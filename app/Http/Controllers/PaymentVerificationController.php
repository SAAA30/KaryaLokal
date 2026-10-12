<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentVerificationController extends Controller
{
    public function submitProof(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        if (!in_array($order->payment_status, ['pending', 'rejected'], true)) {
            return back()->with('error', 'Bukti pembayaran tidak dapat diubah pada status saat ini.');
        }

        if ($order->paymentDeadline()?->isPast()) {
            $expired = DB::transaction(function () use ($order): bool {
                $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if (!in_array($lockedOrder->payment_status, ['pending', 'rejected'], true)
                    || !$lockedOrder->paymentDeadline()?->isPast()) {
                    return false;
                }

                foreach ($lockedOrder->items as $item) {
                    if ($item->product_id) {
                        $product = \App\Models\Product::whereKey($item->product_id)->lockForUpdate()->first();
                        $product?->increment('stock', $item->quantity);
                    }
                }

                $lockedOrder->update(['payment_status' => 'expired', 'status' => 'cancelled']);

                return true;
            });

            if ($expired) {
                return back()->with('error', 'Batas pembayaran telah habis. Silakan buat pesanan baru.');
            }
        }

        $data = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $previousProof = $order->payment_proof_path;
        $proofPath = $data['payment_proof']->store('payment-proofs', 'local');
        $order->update([
            'payment_proof_path' => $proofPath,
            'payment_submitted_at' => now(),
            'payment_status' => 'pending_verification',
            'payment_rejection_reason' => null,
        ]);

        if ($previousProof) {
            Storage::disk('local')->delete($previousProof);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi admin.');
    }

    public function index(): View
    {
        $orders = Order::with(['customer', 'items'])
            ->where('payment_status', 'pending_verification')
            ->latest('payment_submitted_at')
            ->paginate(20);

        return view('admin.payments.index', compact('orders'));
    }

    public function downloadProof(Order $order): StreamedResponse
    {
        abort_unless($order->payment_status === 'pending_verification' && $order->payment_proof_path, 404);
        abort_unless(Storage::disk('local')->exists($order->payment_proof_path), 404);

        return Storage::disk('local')->download($order->payment_proof_path);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        if ($order->payment_status !== 'pending_verification') {
            return back()->with('error', 'Pembayaran ini sudah diproses atau belum memiliki bukti.');
        }

        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'required_if:decision,rejected', 'string', 'max:2000'],
        ]);

        if ($data['decision'] === 'approved') {
            $order->update([
                'payment_status' => 'paid',
                'tracking_number' => $order->tracking_number ?: $this->generatedTrackingNumber($order),
                'status' => 'processing',
                'payment_verified_at' => now(),
                'paid_at' => now(),
                'payment_rejection_reason' => null,
            ]);
        } else {
            $order->update([
                'payment_status' => 'rejected',
                'payment_rejection_reason' => $data['rejection_reason'],
                'payment_verified_at' => null,
            ]);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    private function generatedTrackingNumber(Order $order): string
    {
        return 'KL-RESI-' . strtoupper(preg_replace('/[^A-Z0-9]/', '', $order->order_number));
    }
}