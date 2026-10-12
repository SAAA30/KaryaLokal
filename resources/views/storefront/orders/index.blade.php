@extends('layouts.storefront')

@section('title', 'Pesanan Saya — KaryaLokal.id')

@section('content')
<section class="shell" style="padding:42px 0 0">
    <div class="eyebrow">Perjalanan karya pilihanmu</div><h1 class="page-title">Pesanan saya</h1>
    @forelse($orders as $order)
        <div style="display:grid;grid-template-columns:1fr auto;gap:15px;padding:17px 0;border-top:1px solid var(--line)">
            <a href="{{ route('orders.show', $order) }}" style="display:block">
                <strong style="font-size:12px">{{ $order->order_number }}</strong>
                <div class="muted" style="font-size:10px;margin-top:5px">{{ $order->items->count() }} jenis karya · {{ $order->created_at->format('d M Y') }}</div>
            </a>
            <div style="text-align:right">
                <strong style="font-size:12px">Rp{{ number_format((float) $order->total, 0, ',', '.') }}</strong>
                <div class="eyebrow" style="margin-top:6px">{{ str_replace('_', ' ', $order->status) }}</div>
                @if($order->tracking_number)
                    <div style="margin-top:6px;color:var(--green);font-size:10px">Resi: <strong>{{ $order->tracking_number }}</strong></div>
                @elseif($order->payment_submitted_at && in_array($order->payment_status, ['pending_verification', 'paid'], true))
                    <div class="muted" style="margin-top:6px;font-size:10px">Resi menunggu pesanan dikirim</div>
                @endif
                <a href="{{ route('orders.invoice', $order) }}" class="signin" style="display:inline-block;margin-top:8px;padding:8px 10px">Invoice</a>
            </div>
        </div>
    @empty
        <div style="padding:35px 0;border-top:1px solid var(--line)"><p class="muted" style="font-size:13px">Belum ada pesanan.</p><a class="primary" href="{{ route('storefront.catalog') }}">Jelajahi karya</a></div>
    @endforelse
    <div class="pagination">{{ $orders->links() }}</div>
</section>
@endsection
