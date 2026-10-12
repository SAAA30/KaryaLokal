@extends('layouts.storefront')

@section('title', 'Invoice Pembelian ' . $order->order_number . ' — KaryaLokal.id')

@section('content')
<section class="shell" style="max-width:900px;padding:42px 0">
    <div style="display:flex;justify-content:space-between;align-items:end;gap:16px;flex-wrap:wrap;margin-bottom:22px">
        <div>
            <div class="eyebrow">Invoice Pembelian</div>
            <h1 class="page-title" style="margin-bottom:0">{{ $order->order_number }}</h1>
        </div>
        <a href="{{ route('orders.show', $order) }}" class="signin">Kembali ke pesanan</a>
    </div>

    <section style="background:var(--white);border:1px solid var(--line);padding:24px;display:grid;gap:24px">
        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;border-bottom:1px solid var(--line);padding-bottom:18px">
            <div>
                <div class="eyebrow">KaryaLokal.id</div>
                <p style="margin:8px 0 0;font-size:12px;line-height:1.8">
                    Jl. Raya Kledokan No. 17<br>
                    Bantul, Yogyakarta 55184<br>
                    hello@karyalokal.id
                </p>
            </div>
            <div style="text-align:right;min-width:180px">
                <div class="muted" style="font-size:10px">Tanggal</div>
                <strong style="font-size:12px;display:block;margin-top:6px">{{ $order->created_at->format('d M Y') }}</strong>
                <div class="muted" style="font-size:10px;margin-top:10px">Status</div>
                <strong style="font-size:12px;display:block;margin-top:6px">{{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</strong>
                @if($order->payment_status === 'paid')
                    <div class="muted" style="font-size:10px;margin-top:10px">Nomor resi</div>
                    <strong style="font-size:12px;display:block;margin-top:5px">{{ $order->tracking_number ?: 'Menunggu pengiriman' }}</strong>
                @endif
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div>
                <div class="eyebrow">Ditagihkan kepada</div>
                <p style="margin:10px 0 0;font-size:12px;line-height:1.8">
                    {{ $order->recipient_name }}<br>
                    {{ $order->recipient_phone }}<br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}
                </p>
            </div>
            <div>
                <div class="eyebrow">Metode pembayaran</div>
                <p style="margin:10px 0 0;font-size:12px;line-height:1.8">
                    {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}<br>
                    Pengiriman: {{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }}
                </p>
            </div>
        </div>

        <div>
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:#f3f1ea">
                        <th style="padding:12px 10px;text-align:left;border:1px solid var(--line)">Produk</th>
                        <th style="padding:12px 10px;text-align:center;border:1px solid var(--line)">Qty</th>
                        <th style="padding:12px 10px;text-align:right;border:1px solid var(--line)">Harga</th>
                        <th style="padding:12px 10px;text-align:right;border:1px solid var(--line)">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td style="padding:12px 10px;border:1px solid var(--line)">{{ $item->product_name }}<br><span class="muted">Perajin: {{ $item->artisan?->name ?? '-' }}</span></td>
                            <td style="padding:12px 10px;border:1px solid var(--line);text-align:center">{{ $item->quantity }}</td>
                            <td style="padding:12px 10px;border:1px solid var(--line);text-align:right">Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                            <td style="padding:12px 10px;border:1px solid var(--line);text-align:right">Rp{{ number_format((float) ($item->unit_price * $item->quantity), 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <div style="min-width:280px;width:100%;max-width:280px">
                <div style="display:flex;justify-content:space-between;gap:12px;padding:8px 0;font-size:12px">
                    <span class="muted">Subtotal</span>
                    <strong>Rp{{ number_format((float) $order->subtotal, 0, ',', '.') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;gap:12px;padding:8px 0;font-size:12px">
                    <span class="muted">Ongkir</span>
                    <strong>Rp{{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;gap:12px;padding:8px 0;font-size:12px;border-top:1px solid var(--line);margin-top:4px">
                    <span>Total</span>
                    <strong>Rp{{ number_format((float) $order->total, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </section>
</section>
@endsection
