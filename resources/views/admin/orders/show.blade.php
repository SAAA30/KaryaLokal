@extends('layouts.storefront')

@php
    $orderLabels = [
        'awaiting_payment' => 'Menunggu pembayaran',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $paymentLabels = [
        'pending' => 'Menunggu pembayaran',
        'pending_verification' => 'Menunggu verifikasi',
        'paid' => 'Terverifikasi',
        'rejected' => 'Ditolak',
        'expired' => 'Kedaluwarsa',
        'refunded' => 'Dikembalikan',
    ];
@endphp

@section('title', 'Pesanan ' . $order->order_number . ' — Admin KaryaLokal')

@section('content')
<section class="shell" style="max-width:1000px;padding:38px 0 48px">
    @if(session('success'))<p role="status" style="padding:10px;background:#e6efdf;color:var(--green);font-size:11px">{{ session('success') }}</p>@endif
    @if(session('error'))<p role="alert" style="padding:10px;background:#fae8e3;color:#8b3325;font-size:11px">{{ session('error') }}</p>@endif
    <a href="{{ route('admin.dashboard') }}" class="muted" style="font-size:11px">← Dashboard admin</a>
    <div style="display:flex;justify-content:space-between;align-items:end;gap:16px;margin:20px 0">
        <div>
            <div class="eyebrow">Detail pesanan</div>
            <h1 class="page-title">{{ $order->order_number }}</h1>
            <p class="muted" style="font-size:11px;margin:0">Dibuat {{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>
        <span class="verified">{{ $orderLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}</span>
    </div>

    <div class="order-grid">
        <section class="order-section">
            <h2 class="product-name">Produk pesanan</h2>
            @foreach($order->items as $item)
                <article class="order-item">
                    <strong>{{ $item->product_name }}</strong>
                    <div class="muted" style="font-size:10px;margin-top:5px">{{ $item->artisan->name }} · {{ $item->quantity }} × Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}</div>
                    @if($item->custom_request)<p class="muted" style="font-size:10px">Permintaan custom: {{ $item->custom_request }}</p>@endif
                    @if($item->gift_message)<p class="muted" style="font-size:10px">Pesan hadiah: {{ $item->gift_message }}</p>@endif
                </article>
            @endforeach
            <div class="order-total"><span>Total pesanan</span><strong>Rp{{ number_format((float) $order->total, 0, ',', '.') }}</strong></div>
        </section>

        <div style="display:grid;gap:16px">
            <section class="order-section">
                <h2 class="product-name">Customer</h2>
                <p style="font-size:11px;line-height:1.8;margin-bottom:0">{{ $order->customer->name }}<br>{{ $order->customer->email }}<br>{{ $order->recipient_phone }}</p>
            </section>
            <section class="order-section">
                <h2 class="product-name">Pengiriman</h2>
                <p style="font-size:11px;line-height:1.8;margin-bottom:0">{{ $order->recipient_name }}<br>{{ $order->shipping_address }}<br>{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}<br>{{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }}</p>
                @if($order->tracking_number)<p style="font-size:11px;margin-bottom:0">Resi: <strong>{{ $order->tracking_number }}</strong></p>@endif
                @if($order->payment_status === 'paid' && in_array($order->status, ['processing', 'shipped'], true))
                    <form method="POST" action="{{ route('admin.orders.tracking', $order) }}" style="display:grid;gap:8px;margin-top:12px">
                        @csrf @method('PUT')
                        <label for="tracking-number" style="font-size:10px;font-weight:700">Nomor resi pengiriman</label>
                        <p class="muted" style="margin:0;font-size:9px;line-height:1.5">Resi referensi dibuat otomatis saat pembayaran lunas. Ganti dengan nomor resmi kurir saat paket dikirim.</p>
                        <input id="tracking-number" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" maxlength="100" required style="width:100%;padding:9px;border:1px solid var(--line);background:var(--white)">
                        @error('tracking_number')<span role="alert" style="color:#a33;font-size:10px">{{ $message }}</span>@enderror
                        <button class="primary" type="submit">Simpan nomor resi</button>
                    </form>
                @elseif($order->payment_status !== 'paid')
                    <p class="muted" style="font-size:10px;margin:8px 0 0">Resi dapat diisi setelah pembayaran terverifikasi.</p>
                @endif
            </section>
            <section class="order-section">
                <h2 class="product-name">Pembayaran</h2>
                <p style="font-size:11px;line-height:1.8;margin-bottom:8px">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}<br>Status: <strong>{{ $paymentLabels[$order->payment_status] ?? ucfirst(str_replace('_', ' ', $order->payment_status)) }}</strong></p>
                @if($order->payment_rejection_reason)<p class="payment-error">Alasan penolakan: {{ $order->payment_rejection_reason }}</p>@endif
                @if($order->payment_status === 'pending_verification')
                    <a class="primary" href="{{ route('admin.payments.proof', $order) }}">Unduh bukti pembayaran</a>
                    <a class="muted" href="{{ route('admin.payments.index') }}" style="display:block;font-size:10px;margin-top:10px">Kembali ke antrean verifikasi</a>
                @elseif($order->payment_status === 'pending')
                    <p class="muted" style="font-size:10px;margin:0">Menunggu customer mengunggah bukti pembayaran.</p>
                @endif
            </section>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .order-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:18px;align-items:start}
    .order-section{padding:18px;background:var(--white);border:1px solid var(--line)}
    .order-item{padding:12px 0;border-top:1px solid var(--line);font-size:11px}
    .order-total{display:flex;justify-content:space-between;padding-top:12px;border-top:1px solid var(--line);font-size:12px}
    .payment-error{padding:9px;background:#fae8e3;color:#8b3325;font-size:10px;line-height:1.6}
    @media(max-width:700px){.order-grid{grid-template-columns:1fr}}
</style>
@endpush