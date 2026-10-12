@extends('layouts.storefront')

@section('title', 'Verifikasi Pembayaran — KaryaLokal.id')

@section('content')
<section class="shell" style="max-width:1000px;padding:38px 0 48px">
    <div class="eyebrow">Administrasi transaksi</div>
    <h1 class="page-title">Verifikasi pembayaran</h1>
    <p class="muted" style="font-size:12px">Periksa bukti transfer sebelum pesanan diteruskan ke perajin.</p>
    @if(session('success'))<p role="status" class="notice">{{ session('success') }}</p>@endif
    @if(session('error'))<p role="alert" class="notice is-error">{{ session('error') }}</p>@endif

    <div style="border-top:1px solid var(--line);margin-top:22px">
        @forelse($orders as $order)
            <article class="payment-row">
                <div class="payment-summary">
                    <div>
                        <strong>{{ $order->order_number }}</strong>
                        <div class="muted" style="font-size:10px;margin-top:5px">{{ $order->customer->name }} · {{ $order->customer->email }}</div>
                        <div class="muted" style="font-size:10px;margin-top:4px">{{ $order->items->pluck('product_name')->implode(', ') }}</div>
                    </div>
                    <div class="payment-total">
                        <strong>Rp{{ number_format((float) $order->total, 0, ',', '.') }}</strong>
                        <span>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                        <span>{{ $order->payment_submitted_at?->format('d M Y, H:i') }}</span>
                    </div>
                </div>
                <div class="payment-actions">
                    <a class="signin" href="{{ route('admin.payments.proof', $order) }}">Unduh bukti</a>
                    <form method="POST" action="{{ route('admin.payments.update', $order) }}" class="decision-form">
                        @csrf
                        @method('PUT')
                        <label class="field">Alasan penolakan <span class="muted">(wajib jika ditolak)</span>
                            <textarea name="rejection_reason" rows="2" maxlength="2000">{{ old('rejection_reason') }}</textarea>
                        </label>
                        @error('decision')<span class="error">{{ $message }}</span>@enderror
                        @error('rejection_reason')<span class="error">{{ $message }}</span>@enderror
                        <div style="display:flex;gap:8px;justify-content:flex-end">
                            <button class="signin" type="submit" name="decision" value="rejected">Tolak</button>
                            <button class="primary" type="submit" name="decision" value="approved">Setujui pembayaran</button>
                        </div>
                    </form>
                </div>
            </article>
        @empty
            <p class="muted" style="padding:24px 0;font-size:12px">Tidak ada pembayaran yang menunggu verifikasi.</p>
        @endforelse
    </div>
    <div class="pagination">{{ $orders->links() }}</div>
</section>
@endsection

@push('styles')
<style>
    .payment-row{padding:18px 0;border-bottom:1px solid var(--line)}
    .payment-summary{display:flex;justify-content:space-between;gap:16px}
    .payment-summary>div:first-child{min-width:0}
    .payment-total{display:grid;gap:4px;text-align:right;font-size:10px;white-space:nowrap}
    .payment-total strong{font-size:13px}
    .payment-actions{display:grid;grid-template-columns:auto minmax(0,1fr);align-items:start;gap:16px;margin-top:14px}
    .payment-actions>.signin{justify-self:start}
    .decision-form{display:grid;gap:9px}
    .field{display:grid;gap:5px;font-size:10px;font-weight:700;color:var(--muted)}
    .field textarea{width:100%;border:1px solid var(--line);padding:9px;background:var(--white);color:var(--ink);font:12px var(--sans)}
    .error{color:#a33;font-size:10px}
    .notice{padding:10px;background:#e6efdf;color:var(--green);font-size:11px}
    .notice.is-error{background:#fae8e3;color:#8b3325}
    @media(max-width:600px){.payment-summary{align-items:start}.payment-actions{grid-template-columns:1fr}.payment-total{text-align:left}.decision-form>div{flex-wrap:wrap}}
</style>
@endpush