@extends('layouts.storefront')

@section('title', 'Checkout — KaryaLokal.id')

@section('content')
<section class="shell" style="padding:42px 0 0">
    <div class="eyebrow">Hampir jadi milikmu</div><h1 class="page-title">Selesaikan pesanan</h1>
    <form method="POST" action="{{ route('checkout.store') }}" style="display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:35px;margin-top:22px;align-items:start">
        @csrf
        @foreach($items as $item)
            <input type="hidden" name="selected_items[]" value="{{ $item->id }}">
        @endforeach
        <div style="display:grid;gap:22px">
            <section style="padding:20px;background:var(--white);border:1px solid var(--line)"><h2 class="product-name">1. Alamat pengiriman</h2><div style="display:grid;grid-template-columns:1fr 1fr;gap:12px"><label class="checkout-field">Nama penerima<input name="recipient_name" value="{{ old('recipient_name', auth()->user()->name) }}" required maxlength="150"></label><label class="checkout-field">Nomor HP<input name="recipient_phone" value="{{ old('recipient_phone', auth()->user()->phone) }}" required maxlength="30"></label><label class="checkout-field" style="grid-column:1/-1">Alamat lengkap<textarea name="shipping_address" required maxlength="1000" rows="3">{{ old('shipping_address') }}</textarea></label><label class="checkout-field">Kota<input name="city" required maxlength="100" value="{{ old('city') }}"></label><label class="checkout-field">Provinsi<input name="province" required maxlength="100" value="{{ old('province') }}"></label><label class="checkout-field">Kode pos<input name="postal_code" required maxlength="12" value="{{ old('postal_code') }}"></label></div></section>
            <section style="padding:20px;background:var(--white);border:1px solid var(--line)">
                <h2 class="product-name">2. Pengiriman</h2>
                <label for="shipping-method" class="checkout-field" style="margin-top:12px">Pilih metode pengiriman
                    <select id="shipping-method" name="shipping_method" required>
                        <option value="regular" @selected(old('shipping_method', 'regular') === 'regular')>Reguler · Rp18.000</option>
                        <option value="express" @selected(old('shipping_method') === 'express')>Express · Rp30.000</option>
                        <option value="same_day" @selected(old('shipping_method') === 'same_day')>Same Day · Rp50.000 (kota tertentu)</option>
                    </select>
                </label>
                @error('shipping_method')<span role="alert" style="color:#8b3325;font-size:10px">{{ $message }}</span>@enderror
            </section>
            <section class="payment-section">
                <h2 class="product-name">3. Pembayaran</h2>
                <label for="payment-method" class="checkout-field" style="margin-top:12px">Pilih metode pembayaran
                    <select id="payment-method" name="payment_method" required>
                        <option value="bank_transfer" @selected(old('payment_method', 'bank_transfer') === 'bank_transfer')>Transfer bank manual</option>
                        <option value="qris" @selected(old('payment_method') === 'qris')>QRIS statis</option>
                        <option value="virtual_account" @selected(old('payment_method') === 'virtual_account')>Virtual account statis</option>
                    </select>
                </label>
                @error('payment_method')<span role="alert" style="color:#8b3325;font-size:10px">{{ $message }}</span>@enderror
                <div class="payment-instructions" data-payment-method="bank_transfer" @if(old('payment_method', 'bank_transfer') !== 'bank_transfer') hidden @endif>
                    <strong>Transfer bank manual</strong>
                    @if(collect(config('services.karyalokal_payment.bank_accounts'))->contains(fn($account) => filled($account['number'])))
                        @foreach(config('services.karyalokal_payment.bank_accounts') as $account)
                            @if(filled($account['number']))
                                <div class="payment-account"><span>{{ $account['bank'] }}</span><strong class="payment-number">{{ $account['number'] }}</strong><span>a.n. {{ $account['account_name'] }}</span></div>
                            @endif
                        @endforeach
                    @else
                        <span class="payment-warning">Rekening merchant belum dikonfigurasi. Hubungi customer service sebelum membayar.</span>
                    @endif
                    <span class="payment-deadline">Batas pembayaran: {{ config('services.karyalokal_payment.bank_expiry_hours') }} jam</span>
                    <small>Unggah bukti transfer pada detail pesanan setelah checkout.</small>
                </div>
                <div class="payment-instructions" data-payment-method="virtual_account" @if(old('payment_method') !== 'virtual_account') hidden @endif>
                    <strong>Virtual account statis</strong>
                    @if(collect(config('services.karyalokal_payment.virtual_accounts'))->contains(fn($account) => filled($account['number'])))
                        @foreach(config('services.karyalokal_payment.virtual_accounts') as $account)
                            @if(filled($account['number']))
                                <div class="payment-account"><span>{{ $account['bank'] }}</span><strong class="payment-number">{{ $account['number'] }}</strong><span>a.n. {{ $account['account_name'] }}</span></div>
                            @endif
                        @endforeach
                    @else
                        <span class="payment-warning">Nomor virtual account belum dikonfigurasi. Hubungi customer service sebelum membayar.</span>
                    @endif
                    <span class="payment-deadline">Batas pembayaran: {{ config('services.karyalokal_payment.va_expiry_hours') }} jam</span>
                    <small>Unggah bukti pembayaran pada detail pesanan setelah checkout.</small>
                </div>
                <div class="payment-instructions qris-instructions" data-payment-method="qris" @if(old('payment_method') !== 'qris') hidden @endif>
                    <strong>QRIS statis</strong>
                    <span>Merchant: {{ config('services.karyalokal_payment.merchant_name') }}</span>
                    @if(config('services.karyalokal_payment.qris_image'))
                        <img src="{{ asset(config('services.karyalokal_payment.qris_image')) }}" alt="QRIS simulasi KaryaLokal">
                    @elseif($qrisImage)
                        <img src="{{ $qrisImage }}" alt="Kode QR simulasi KaryaLokal">
                    @else
                        <span class="payment-warning">QRIS merchant belum dikonfigurasi. Hubungi customer service sebelum membayar.</span>
                    @endif
                    <span class="qris-estimate">Batas pembayaran: <strong>{{ config('services.karyalokal_payment.qris_expiry_minutes') }} menit</strong></span>
                    <small>Unggah bukti pembayaran pada detail pesanan setelah checkout.</small>
                </div>
            </section>
        </div>
        <aside style="padding:20px;background:#eeede5"><h2 class="product-name">Ringkasan pesanan</h2>@foreach($items as $item)<div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid var(--line);font-size:11px"><span>{{ $item->product->name }} × {{ $item->quantity }}</span><strong>Rp{{ number_format((float) $item->product->price * $item->quantity, 0, ',', '.') }}</strong></div>@endforeach<div style="display:flex;justify-content:space-between;margin:15px 0;font-size:12px"><span>Subtotal</span><strong>Rp{{ number_format($subtotal, 0, ',', '.') }}</strong></div><p class="muted" style="font-size:10px;line-height:1.6">Biaya kirim ditambahkan sesuai metode pilihan.</p><button class="primary" type="submit" style="width:100%;height:45px">Buat pesanan</button></aside>
    </form>
</section>
@endsection

@push('styles')
<style>
    .checkout-field{display:grid;gap:6px;font-size:10px;font-weight:700;color:var(--muted)}
    .checkout-field input,.checkout-field textarea,.checkout-field select{width:100%;border:1px solid var(--line);background:#fff;padding:10px;color:var(--ink);font:12px var(--sans)}
    .payment-section{padding:20px;background:var(--white);border:1px solid var(--line)}
    .payment-instructions{display:grid;gap:6px;margin-top:14px;padding:13px;background:#eeede5;font-size:11px;line-height:1.6}
    .payment-instructions[hidden]{display:none}
    .payment-instructions small{color:var(--muted);font-size:10px}
    .payment-account{display:grid;gap:2px;padding:8px 0;border-bottom:1px solid var(--line)}
    .payment-number{font-size:16px;font-weight:700;letter-spacing:.4px}
    .payment-deadline{font-weight:700}
    .payment-warning{color:#8b3325}
    .qris-instructions img{width:min(220px,100%);height:auto;aspect-ratio:1;object-fit:contain;background:white}
    .qris-estimate{color:var(--green)}
    @media(max-width:760px){form[style*="grid-template-columns:minmax(0,1fr) 330px"]{grid-template-columns:1fr!important}}
</style>
@endpush

@push('scripts')
<script>
    const paymentMethod = document.querySelector('#payment-method');
    paymentMethod?.addEventListener('change', () => {
        document.querySelectorAll('[data-payment-method]').forEach((panel) => {
            panel.hidden = panel.dataset.paymentMethod !== paymentMethod.value;
        });
    });
</script>
@endpush
