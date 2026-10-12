@extends('layouts.storefront')

@section('title', 'Pesanan ' . $order->order_number . ' — KaryaLokal.id')

@section('content')
@php($paymentLabels = ['pending' => 'Menunggu pembayaran', 'pending_verification' => 'Menunggu verifikasi', 'paid' => 'Terverifikasi', 'rejected' => 'Ditolak', 'expired' => 'Kedaluwarsa', 'refunded' => 'Dikembalikan'])
@php($productIds = $order->items->pluck('product_id')->filter()->unique()->all())
@php($orderReviews = $productIds ? \App\Models\Review::whereIn('product_id', $productIds)->where('is_published', true)->with('customer')->latest()->get() : collect())
<section class="shell" style="max-width:900px;padding:42px 0 0">
    @if(session('success'))<p role="status" style="padding:10px;background:#e6efdf;color:var(--green);font-size:11px">{{ session('success') }}</p>@endif
    @if(session('error'))<p role="alert" style="padding:10px;background:#fae8e3;color:#8b3325;font-size:11px">{{ session('error') }}</p>@endif
    <a href="{{ route('orders.index') }}" class="muted" style="font-size:11px">← Kembali ke pesanan</a>
    <div style="display:flex;justify-content:space-between;gap:20px;align-items:end;margin:18px 0"><div><div class="eyebrow">Detail pesanan</div><h1 class="page-title">{{ $order->order_number }}</h1><div class="muted" style="font-size:11px">{{ $order->created_at->format('d M Y, H:i') }}</div></div><div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap"><span class="verified">{{ str_replace('_', ' ', $order->status) }}</span><a href="{{ route('orders.invoice', $order) }}" class="signin">Lihat invoice</a></div></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">
        <section style="padding:18px;background:var(--white);border:1px solid var(--line)">
            <h2 class="product-name">Karya pesanan</h2>
            @foreach($order->items as $item)
                <div style="padding:12px 0;border-top:1px solid var(--line);font-size:11px">
                    <strong>{{ $item->product_name }}</strong>
                    <div class="muted" style="margin-top:5px">{{ $item->artisan->name }} · {{ $item->quantity }} × Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}</div>
                    @if($item->custom_request)
                        <p class="muted" style="margin:6px 0 0">Request: {{ $item->custom_request }}</p>
                    @endif
                    @if($item->gift_message)
                        <p class="muted" style="margin:6px 0 0">Pesan hadiah: {{ $item->gift_message }}</p>
                    @endif
                </div>
            @endforeach
        </section>
        <section style="padding:18px;background:#eeede5">
            <h2 class="product-name">{{ 'Pengiriman & pembayaran' }}</h2>
            <p style="font-size:11px;line-height:1.8">{{ $order->recipient_name }}<br>{{ $order->recipient_phone }}<br>{{ $order->shipping_address }}<br>{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
            <p class="muted" style="font-size:10px">{{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }} · {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
            <div style="display:flex;justify-content:space-between;padding-top:12px;border-top:1px solid var(--line);font-size:12px"><strong>Total</strong><strong>Rp{{ number_format((float) $order->total, 0, ',', '.') }}</strong></div>
            <p style="font-size:11px;margin:14px 0 8px">Status pembayaran: <strong>{{ $paymentLabels[$order->payment_status] ?? ucfirst(str_replace('_', ' ', $order->payment_status)) }}</strong></p>
            @if(in_array($order->payment_status, ['pending', 'rejected'], true) && $paymentDeadline)
                @if($paymentDeadline)
                    <p class="payment-countdown" data-payment-countdown="{{ $paymentDeadline->toIso8601String() }}" role="timer" aria-live="polite">Waktu pembayaran tersisa: menghitung...</p>
                @endif
            @endif
            @if(in_array($order->payment_status, ['pending', 'rejected'], true) && (!$paymentDeadline || $paymentDeadline->isFuture()))
                <div style="padding:12px;background:var(--white);font-size:10px;line-height:1.7">
                    @if($order->payment_method === 'bank_transfer')
                        <strong>Transfer bank manual</strong><br>
                        @if(collect(config('services.karyalokal_payment.bank_accounts'))->contains(fn($account) => filled($account['number'])))
                            @foreach(config('services.karyalokal_payment.bank_accounts') as $account)
                                @if(filled($account['number']))<p style="margin:8px 0 0"><strong>{{ $account['bank'] }}</strong><br>{{ $account['number'] }}<br>a.n. {{ $account['account_name'] }}</p>@endif
                            @endforeach
                        @else
                            Rekening merchant belum dikonfigurasi. Hubungi customer service sebelum melakukan transfer.
                        @endif
                        <p style="margin:10px 0 0">Batas pembayaran: {{ config('services.karyalokal_payment.bank_expiry_hours') }} jam</p>
                    @elseif($order->payment_method === 'virtual_account')
                        <strong>Virtual account statis</strong><br>
                        @if(collect(config('services.karyalokal_payment.virtual_accounts'))->contains(fn($account) => filled($account['number'])))
                            @foreach(config('services.karyalokal_payment.virtual_accounts') as $account)
                                @if(filled($account['number']))<p style="margin:8px 0 0"><strong>{{ $account['bank'] }}</strong><br>{{ $account['number'] }}<br>a.n. {{ $account['account_name'] }}</p>@endif
                            @endforeach
                        @else
                            Nomor virtual account belum dikonfigurasi. Hubungi customer service sebelum melakukan pembayaran.
                        @endif
                        <p style="margin:10px 0 0">Batas pembayaran: {{ config('services.karyalokal_payment.va_expiry_hours') }} jam</p>
                    @elseif($order->payment_method === 'qris')
                        <strong>QRIS statis</strong><br>
                        <span>Merchant: {{ config('services.karyalokal_payment.merchant_name') }}</span>
                        @if(config('services.karyalokal_payment.qris_image'))
                            <img src="{{ asset(config('services.karyalokal_payment.qris_image')) }}" alt="QRIS simulasi KaryaLokal" style="display:block;width:min(220px,100%);height:auto;margin-top:10px">
                        @elseif($qrisImage)
                            <img src="{{ $qrisImage }}" alt="Kode QR simulasi KaryaLokal" style="display:block;width:min(220px,100%);height:auto;margin-top:10px">
                        @else
                            QRIS belum dikonfigurasi. Hubungi customer service sebelum melakukan pembayaran.
                        @endif
                        <p style="margin:10px 0 0;color:var(--green)">Batas pembayaran QRIS: <strong>{{ config('services.karyalokal_payment.qris_expiry_minutes') }} menit</strong></p>
                    @endif
                </div>
            @endif
            @if($order->payment_rejection_reason)
                <p role="alert" style="padding:10px;background:#fae8e3;color:#8b3325;font-size:10px;line-height:1.6">Bukti ditolak: {{ $order->payment_rejection_reason }}</p>
            @endif
            @if(in_array($order->payment_status, ['pending', 'rejected'], true) && (!$paymentDeadline || $paymentDeadline->isFuture()))
                <form method="POST" enctype="multipart/form-data" action="{{ route('orders.payment-proof', $order) }}" data-payment-upload style="display:grid;gap:8px;padding-top:12px">
                    @csrf
                    <label style="font-size:10px;font-weight:700">{{ $order->payment_status === 'rejected' ? 'Unggah ulang bukti pembayaran' : 'Unggah bukti pembayaran' }}<input type="file" name="payment_proof" accept="image/jpeg,image/png,application/pdf" required style="display:block;width:100%;margin-top:7px;font-size:10px"></label>
                    @error('payment_proof')<span role="alert" style="color:#a33;font-size:10px">{{ $message }}</span>@enderror
                    <button class="primary" type="submit">Kirim bukti pembayaran</button>
                </form>
            @elseif(in_array($order->payment_status, ['pending', 'rejected'], true))
                <p class="payment-expired" role="status">Batas pembayaran telah habis. Silakan buat pesanan baru.</p>
            @elseif($order->payment_status === 'pending_verification')
                <p class="muted" style="font-size:10px">Bukti diterima pada {{ $order->payment_submitted_at?->format('d M Y, H:i') }} dan sedang diperiksa admin.</p>
            @endif
            @if($order->tracking_number)
                <p style="font-size:11px;margin-top:12px;padding:10px;background:var(--white)">Nomor resi: <strong>{{ $order->tracking_number }}</strong></p>
            @elseif($order->payment_submitted_at && in_array($order->payment_status, ['pending_verification', 'paid'], true))
                <p class="muted" role="status" style="font-size:10px;margin-top:12px">Nomor resi akan tersedia setelah pembayaran diverifikasi dan pesanan dikirim.</p>
            @endif
        </section>
    </div>

    @if($order->payment_status === 'paid' && in_array($order->status, ['processing', 'shipped'], true) && filled($order->tracking_number))
        <section style="margin-top:18px;padding:16px;background:#e6efdf;border:1px solid #c8d8bf;display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap">
            <div>
                <strong style="font-size:12px">Pesanan sudah diterima?</strong>
                <p class="muted" style="margin:5px 0 0;font-size:10px">Pastikan barang sudah diterima. Konfirmasi selesai untuk membuka ulasan dan komentar produk.</p>
            </div>
            <form method="POST" action="{{ route('orders.complete', $order) }}">
                @csrf
                <button class="primary" type="submit">Pesanan selesai</button>
            </form>
        </section>
    @endif

    @if($order->status === 'completed')
        <section style="margin-top:22px;padding:18px;background:var(--white);border:1px solid var(--line)">
            <div class="eyebrow">Ulasan pesanan</div>
            <h2 class="product-name" style="margin-top:7px">Bagikan pengalamanmu</h2>
            <div style="display:grid;gap:16px;margin-top:14px">
                @foreach($order->items as $item)
                    <article style="padding:14px;border:1px solid var(--line);background:#f7f5ef">
                        <strong style="font:600 16px var(--serif)">{{ $item->product_name }}</strong>
                        @if($item->review)
                            <div aria-label="Rating {{ $item->review->product_rating }} dari 5" style="margin-top:8px;color:#d5633a">
                                @for($i = 1; $i <= 5; $i++){{ $i <= $item->review->product_rating ? '★' : '☆' }}@endfor
                            </div>
                            <p style="margin:7px 0 0;font-size:11px;line-height:1.7">{{ $item->review->body ?: 'Rating dikirim tanpa komentar.' }}</p>
                            @if($item->review->artisan_reply)
                                <p class="muted" style="margin:10px 0 0;font-size:10px">Balasan perajin: {{ $item->review->artisan_reply }}</p>
                            @endif
                        @elseif($item->product_id)
                            <form method="POST" action="{{ route('orders.items.reviews.store', [$order, $item]) }}" style="display:grid;gap:10px;margin-top:12px">
                                @csrf
                                <span style="font-size:10px;font-weight:700">Rating produk</span>
                                <div class="order-star-rating" role="radiogroup" aria-label="Rating {{ $item->product_name }}">
                                    @for($rating = 5; $rating >= 1; $rating--)
                                        <input type="radio" name="product_rating" id="rating-{{ $order->id }}-{{ $item->id }}-{{ $rating }}" value="{{ $rating }}" @checked((string) old('product_rating') === (string) $rating) required>
                                        <label for="rating-{{ $order->id }}-{{ $item->id }}-{{ $rating }}" title="{{ $rating }} bintang" aria-label="{{ $rating }} bintang">★</label>
                                    @endfor
                                </div>
                                <textarea name="body" rows="3" maxlength="2000" placeholder="Tulis ulasan dan komentar tentang produk ini..." style="width:100%;padding:10px;border:1px solid var(--line);background:var(--white);resize:vertical">{{ old('body') }}</textarea>
                                @error('product_rating')<span role="alert" style="color:#a33;font-size:10px">{{ $message }}</span>@enderror
                                @error('body')<span role="alert" style="color:#a33;font-size:10px">{{ $message }}</span>@enderror
                                <button type="submit" class="primary" style="justify-self:start">Kirim ulasan</button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section id="reviews" style="margin-top:26px;padding:18px;background:var(--white);border:1px solid var(--line)">
        <div class="eyebrow">Ulasan pembeli</div>
        @if($orderReviews->isNotEmpty())
            <div style="display:grid;gap:14px;margin-top:14px">
                @foreach($orderReviews as $review)
                    <article style="padding:14px;border:1px solid var(--line);background:#f7f5ef">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                            <strong>{{ $review->customer?->name ?? 'Pembeli' }}</strong>
                            <span class="muted" style="font-size:10px">{{ $review->created_at?->format('d M Y') }}</span>
                        </div>
                        <div style="margin-top:8px;color:#d5633a;font-size:12px">
                            @for($i = 1; $i <= 5; $i++){{ $i <= $review->product_rating ? '★' : '☆' }}@endfor
                        </div>
                        <p class="muted" style="margin:8px 0 0;line-height:1.8">{{ $review->body }}</p>
                        @if($review->artisan_reply)
                            <div style="margin-top:12px;padding:10px 12px;border-left:3px solid var(--green);background:rgba(36,90,70,0.04)">
                                <strong style="display:block;margin-bottom:4px">Balasan perajin</strong>
                                <p class="muted" style="margin:0;line-height:1.7">{{ $review->artisan_reply }}</p>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <p class="muted" style="margin-top:12px">Belum ada data ulasan atau komentar untuk pesanan ini.</p>
        @endif
    </section>
</section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-payment-countdown]').forEach((countdown) => {
        const deadline = Date.parse(countdown.dataset.paymentCountdown);
        const uploadForm = document.querySelector('[data-payment-upload]');
        const updateCountdown = () => {
            const remaining = Math.max(0, deadline - Date.now());
            if (remaining === 0) {
                countdown.textContent = 'Waktu pembayaran habis';
                countdown.classList.add('is-expired');
                uploadForm?.querySelectorAll('input, button').forEach((control) => { control.disabled = true; });
                return;
            }

            const totalSeconds = Math.floor(remaining / 1000);
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            const time = [hours, minutes, seconds].map((part) => String(part).padStart(2, '0')).join(':');
            countdown.textContent = days > 0 ? `Waktu pembayaran tersisa: ${days} hari ${time}` : `Waktu pembayaran tersisa: ${time}`;
        };

        updateCountdown();
        window.setInterval(updateCountdown, 1000);
    });
</script>
@endpush

@push('styles')
<style>
    .order-star-rating{display:inline-flex;flex-direction:row-reverse;justify-content:flex-end;gap:4px;width:max-content}
    .order-star-rating input{position:absolute;opacity:0;pointer-events:none}
    .order-star-rating label{cursor:pointer;color:#d9d9d9;font-size:30px;line-height:1;user-select:none}
    .order-star-rating label:hover,.order-star-rating label:hover~label,.order-star-rating input:checked~label{color:#ffb400}
    .order-star-rating input:focus-visible+label{outline:2px solid var(--green);outline-offset:3px}
    @media(max-width:620px){.order-star-rating label{font-size:27px}}
</style>
@endpush
