@extends('layouts.storefront')

@section('title', $product->name . ' — KaryaLokal.id')

@section('content')
@php($image = $product->images[0] ?? null)
<section class="shell" style="padding:38px 0 0">
    <div class="muted" style="font-size:11px;margin-bottom:24px"><a href="{{ route('storefront.catalog') }}">Jelajahi karya</a> / {{ $product->name }}</div>
    <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(320px,.9fr);gap:42px;align-items:start">
        <img src="{{ $image ? (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image)) : 'https://images.unsplash.com/photo-1493106641515-6b5631de4bb9?auto=format&fit=crop&w=900&q=85' }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1/1.08;object-fit:cover;background:#e7e4d9">
        <div>
            <div class="eyebrow">{{ $product->category?->name ?? 'Karya lokal' }} · {{ $product->fulfillment_type === 'pre_order' ? 'Pre-order' : 'Siap dikirim' }}</div>
            <h1 class="page-title" style="font-size:42px;margin:12px 0">{{ $product->name }}</h1>
            <div style="font-size:20px;font-weight:700;margin-bottom:14px">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</div>
            <div class="verified">✓ Karya terverifikasi KaryaLokal</div>
            <p class="muted" style="font-size:13px;line-height:1.8;margin:19px 0">{{ $product->description }}</p>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:16px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);font-size:11px">
                <div><span class="muted">Material</span><br><strong>{{ $product->material ?: 'Belum dicantumkan' }}</strong></div>
                <div><span class="muted">Teknik</span><br><strong>{{ $product->technique ?: 'Buatan tangan' }}</strong></div>
                <div><span class="muted">Asal karya</span><br><strong>{{ $product->origin ?: 'Indonesia' }}</strong></div>
                <div><span class="muted">Stok</span><br><strong>{{ $product->stock }} tersedia</strong></div>
            </div>
            <form method="POST" action="{{ route('cart.store') }}" style="padding:18px 0 0">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <label style="display:grid;gap:6px;font-size:11px;font-weight:700">Jumlah<input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" required style="width:100px;height:38px;border:1px solid var(--line);background:var(--white);padding:0 9px"></label>
                @if($product->is_customizable)<label style="display:grid;gap:6px;font-size:11px;font-weight:700;margin-top:13px">Permintaan custom<textarea name="custom_request" rows="3" maxlength="1000" placeholder="Warna, ukuran, atau detail yang diinginkan" style="border:1px solid var(--line);background:var(--white);padding:10px"></textarea></label>@endif
                <details style="margin:15px 0"><summary style="font-size:11px;font-weight:700;cursor:pointer">Jadikan hadiah personal</summary><div style="display:grid;gap:10px;margin-top:12px"><input name="recipient_name" maxlength="100" placeholder="Nama penerima" style="height:38px;border:1px solid var(--line);background:var(--white);padding:0 10px"><input name="sender_name" maxlength="100" placeholder="Nama pengirim" style="height:38px;border:1px solid var(--line);background:var(--white);padding:0 10px"><textarea name="gift_message" maxlength="500" rows="3" placeholder="Pesan personal" style="border:1px solid var(--line);background:var(--white);padding:10px"></textarea></div></details>
                <button class="primary" type="submit" style="width:100%;height:46px">Tambah ke keranjang</button>
            </form>
            <div style="margin-top:30px;padding-top:22px;border-top:1px solid var(--line);display:flex;gap:13px;align-items:center">
                <div style="width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:var(--lime);font:600 16px var(--serif)">{{ mb_substr($product->artisan->name, 0, 1) }}</div>
                <div><div class="muted" style="font-size:10px">Dibuat oleh</div><strong style="font:600 17px var(--serif)">{{ $product->artisan->name }}</strong><div class="muted" style="font-size:10px">{{ $product->artisan->artisanProfile?->city }}{{ $product->artisan->artisanProfile?->province ? ', ' . $product->artisan->artisanProfile->province : '' }}</div></div>
            </div>
            @if($product->artisan->artisanProfile?->bio)<p class="muted" style="font-size:12px;line-height:1.7;margin-top:14px">{{ $product->artisan->artisanProfile->bio }}</p>@endif
            @auth
                @if(auth()->user()->isCustomer())
                    <form method="POST" action="{{ route('chat.customer.start') }}" class="artisan-chat-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <label for="artisan-chat-message">Tanya langsung ke perajin</label>
                        <textarea id="artisan-chat-message" name="body" rows="3" maxlength="2000" placeholder="Tanyakan detail bahan, ukuran, atau ketersediaan..." required></textarea>
                        @error('body')<span class="chat-error">{{ $message }}</span>@enderror
                        <button class="signin" type="submit">Kirim pesan ke {{ $product->artisan->name }}</button>
                    </form>
                @endif
            @else
                <a class="signin artisan-chat-login" href="{{ route('login') }}">Masuk untuk chat dengan perajin</a>
            @endauth
            @if($product->craft_id && $product->qr_active)
                <section class="craft-qr-panel" aria-label="QR Digital Craft Story">
                    <a href="{{ route('craft.story', $product->craft_id) }}" class="craft-qr-image" aria-label="Buka cerita {{ $product->name }}">
                        <img src="{{ url('storage/craft-qr/' . $product->craft_id . '.svg') }}" alt="Kode QR untuk cerita {{ $product->name }}">
                    </a>
                    <div class="craft-qr-copy">
                        <div class="eyebrow">Cerita karya</div>
                        <strong>Pindai QR untuk membuka cerita digital.</strong>
                        <span>Craft ID: {{ $product->craft_id }}</span>
                        <a href="{{ route('craft.story', $product->craft_id) }}">Buka Digital Craft Story</a>
                    </div>
                </section>
            @endif
        </div>
    </div>

    @if($product->story)
        <section class="story-section"><div class="eyebrow">Cerita karya</div><h2 class="page-title" style="font-size:30px">Dari ide menjadi karya.</h2><p class="muted" style="max-width:760px;font-size:13px;line-height:1.9;white-space:pre-line">{{ $product->story }}</p></section>
    @endif
    @if($product->processSteps->isNotEmpty())
        <section class="story-section"><div class="eyebrow">Proses pembuatan</div><h2 class="page-title" style="font-size:30px">Dibuat dengan teliti.</h2><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:18px;margin-top:22px">@foreach($product->processSteps as $step)<article style="border-top:1px solid var(--line);padding-top:13px"><div style="font:500 20px var(--serif);color:var(--orange)">{{ str_pad($step->step_number, 2, '0', STR_PAD_LEFT) }}</div><strong style="display:block;margin:8px 0 5px;font:600 16px var(--serif)">{{ $step->title }}</strong><p class="muted" style="font-size:11px;line-height:1.7">{{ $step->description }}</p>@if($step->media_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($step->media_path) }}" alt="{{ $step->title }}" style="width:100%;aspect-ratio:4/3;object-fit:cover">@endif</article>@endforeach</div></section>
    @endif

    <section id="reviews" class="story-section" style="padding-bottom:30px;scroll-margin-top:24px">
        <div class="eyebrow">Ulasan pembeli</div>
        <h2 class="page-title" style="font-size:30px">Apa kata pembeli?</h2>

        @php($averageRating = $reviews->isNotEmpty() ? round($reviews->avg('product_rating'), 1) : 0)
        @php($totalReviews = $reviews->count())

        <div class="review-summary" style="display:flex;align-items:center;gap:18px;flex-wrap:wrap;margin:20px 0 12px;padding:18px;border:1px solid var(--line);background:var(--panel)">
            <div class="review-summary-score">
                <span>{{ number_format($averageRating, 1) }}</span>
                <small>/ 5</small>
            </div>
            <div class="review-summary-stars" aria-label="Rating rata-rata {{ $averageRating }} dari 5">
                @for($i = 1; $i <= 5; $i++)
                    <span class="review-star {{ $i <= round($averageRating) ? 'filled' : '' }}">★</span>
                @endfor
            </div>
            <div class="review-summary-copy muted">{{ $totalReviews }} ulasan</div>
        </div>

        @auth
            @if(auth()->user()->isCustomer())
                @if($canReview)
                    <p class="muted" style="margin:18px 0;font-size:11px">Pesananmu sudah selesai. Kirim ulasan melalui halaman detail pesanan.</p>
                @elseif($hasCompletedPurchase)
                    <p class="muted" style="margin:18px 0;font-size:11px">Kamu sudah mengirim ulasan untuk karya yang dibeli.</p>
                @else
                    <p class="muted" style="margin:18px 0;font-size:11px">Ulasan dapat dikirim setelah pesanan diterima dan diselesaikan.</p>
                @endif
            @endif
        @else
            <p class="muted" style="margin:18px 0 0"><a href="{{ route('login') }}">Masuk</a> untuk menulis ulasan.</p>
        @endauth

        @if($reviews->isNotEmpty())
            <div style="display:grid;gap:18px;margin-top:20px">
                @foreach($reviews as $review)
                    <article style="padding:18px;border:1px solid var(--line);background:var(--panel)">
                        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
                            <strong>{{ $review->customer?->name ?? 'Pembeli' }}</strong>
                            <span class="muted" style="font-size:10px">{{ $review->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                        <div style="margin-top:8px;color:var(--orange);font-size:12px">@for($i=1; $i<=5; $i++){{ $i <= $review->product_rating ? '★' : '☆' }}@endfor</div>
                        <p class="muted" style="margin-top:10px;line-height:1.8">{{ $review->body }}</p>

                        @if($review->artisan_reply)
                            <div style="margin-top:14px;padding:12px;border-left:3px solid var(--green);background:rgba(41, 170, 123, 0.05)">
                                <strong style="display:block;margin-bottom:5px">Balasan perajin</strong>
                                <p class="muted" style="margin:0;line-height:1.7">{{ $review->artisan_reply }}</p>
                            </div>
                        @endif

                        @auth
                            @if(auth()->user()->isArtisan() && auth()->user()->id === $product->artisan_id)
                                <form method="POST" action="{{ route('artisan.products.reviews.reply', [$product, $review]) }}" style="display:grid;gap:8px;margin-top:14px;padding-top:14px;border-top:1px solid var(--line)">
                                    @csrf
                                    <label style="font-size:11px;font-weight:700">Balas komentar pembeli</label>
                                    <textarea name="artisan_reply" rows="3" maxlength="2000" placeholder="Tulis balasan Anda..." style="border:1px solid var(--line);background:var(--white);padding:10px;resize:vertical">{{ old('artisan_reply', $review->artisan_reply) }}</textarea>
                                    <button type="submit" class="signin" style="justify-self:start">Kirim balasan</button>
                                </form>
                            @endif
                        @endauth
                    </article>
                @endforeach
            </div>
        @else
            <p class="muted" style="margin-top:18px">Belum ada ulasan untuk karya ini.</p>
        @endif
    </section>
</section>
@endsection
@push('styles')
<style>
    .craft-qr-panel{display:grid;grid-template-columns:112px minmax(0,1fr);gap:15px;align-items:center;margin-top:18px;padding:14px;border:1px solid var(--line);background:var(--white)}
    .craft-qr-image{display:block;width:112px;height:112px;background:#fff}
    .craft-qr-image img{display:block;width:100%;height:100%;object-fit:contain}
    .craft-qr-copy{display:grid;gap:7px;font-size:11px}
    .craft-qr-copy .eyebrow{font-size:9px}
    .craft-qr-copy strong{font:600 15px/1.35 var(--serif)}
    .craft-qr-copy span{color:var(--muted);font-size:10px}
    .craft-qr-copy>a{color:var(--green);font-size:11px;font-weight:700;text-decoration:underline;text-underline-offset:3px}
    .artisan-chat-form{display:grid;gap:8px;margin-top:18px;padding-top:15px;border-top:1px solid var(--line)}
    .artisan-chat-form label{font-size:10px;font-weight:700}
    .artisan-chat-form textarea{width:100%;border:1px solid var(--line);background:var(--white);padding:10px;color:var(--ink);font:12px var(--sans);resize:vertical}
    .artisan-chat-form .signin,.artisan-chat-login{justify-self:start;font-size:10px}
    .chat-error{color:#a33;font-size:10px}
    .star-rating{display:inline-flex;flex-direction:row-reverse;justify-content:flex-start;align-items:center;gap:4px;line-height:1;padding:8px 2px}
    .star-rating input{display:none}
    .star-rating label{cursor:pointer;color:#d9d9d9;font-size:30px;transition:transform .12s ease,color .12s ease,filter .12s ease;user-select:none}
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label{color:#ffb400;filter:drop-shadow(0 1px 0 rgba(0,0,0,.08));}
    .star-rating label:hover{transform:translateY(-1px)}
    .star-rating input:checked + label,
    .star-rating input:checked ~ label{color:#ffb400}
    .review-summary{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
    .review-summary-score{display:flex;align-items:flex-end;gap:4px;color:var(--ink)}
    .review-summary-score span{font:700 34px/1 var(--serif);letter-spacing:-.04em}
    .review-summary-score small{font-size:14px;color:var(--muted);padding-bottom:6px}
    .review-summary-stars{display:flex;gap:2px;font-size:22px;line-height:1;color:#d9d9d9}
    .review-summary-stars .review-star{display:inline-block}
    .review-summary-stars .review-star.filled{color:#ffb400}
    @media(max-width:760px){.craft-qr-panel{grid-template-columns:88px minmax(0,1fr);gap:12px}.craft-qr-image{width:88px;height:88px}.star-rating label{font-size:26px}.review-summary-score span{font-size:28px}.review-summary-stars{font-size:18px}}
</style>
@endpush
