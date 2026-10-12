@extends('layouts.storefront')

@section('title', $product->name . ' — Detail Karya Admin')

@section('content')
<section class="shell" style="max-width:1040px;padding:38px 0">
    @if(session('success'))
        <p role="status" style="padding:11px 14px;background:#e6efdf;color:var(--green);font-size:12px">{{ session('success') }}</p>
    @endif

    <a href="{{ route('admin.qr.index') }}" class="muted" style="font-size:11px">← Kembali ke QR & craft ID</a>

    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin:18px 0">
        <div>
            <div class="eyebrow">Detail karya</div>
            <h1 class="page-title" style="margin:7px 0 0">{{ $product->name }}</h1>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <span class="verified">{{ str_replace('_', ' ', $product->verification_status) }}</span>
            <a href="{{ route('admin.products.edit', ['product' => $product, 'return_to' => route('admin.qr.index')]) }}" class="signin">Edit karya</a>
        </div>
    </div>

    <section style="display:grid;grid-template-columns:minmax(0,1fr) minmax(260px,1fr);gap:24px;padding:18px 0 28px;border-top:1px solid var(--line);border-bottom:1px solid var(--line)">
        <div style="aspect-ratio:4/3;background:#e8e5dc;overflow:hidden">
            @php($images = collect($product->images ?? []))
            @php($firstImage = $images->first())
            @php($localImage = $images->filter(fn ($image) => !\Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($image))->last())
            @php($image = $firstImage && !\Illuminate\Support\Str::startsWith($firstImage, ['http://', 'https://']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($firstImage) ? $firstImage : ($localImage ?? $firstImage))
            @if($image)
                <img src="{{ \Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover">
            @else
                <div class="muted" style="height:100%;display:grid;place-items:center">Belum ada foto produk</div>
            @endif
        </div>

        <div style="align-self:center">
            <div class="muted" style="font-size:11px">{{ $product->category?->name ?? 'Tanpa kategori' }} · {{ $product->origin }}</div>
            <h2 style="font:600 28px/1.2 var(--serif);margin:10px 0">{{ $product->name }}</h2>
            <p style="font-size:16px;font-weight:700;color:var(--green)">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</p>
            <p class="muted" style="font-size:12px;line-height:1.8">{{ $product->stock }} tersedia · {{ $product->material }} · {{ $product->technique }}</p>

            <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--line);display:grid;gap:8px;font-size:12px;line-height:1.7">
                <div><strong>Perajin:</strong> {{ $product->artisan?->name ?? '-' }}</div>
                <div><strong>Asal:</strong> {{ $product->origin ?: '-' }}</div>
                <div><strong>Material:</strong> {{ $product->material ?: '-' }}</div>
                <div><strong>Teknik:</strong> {{ $product->technique ?: '-' }}</div>
                <div><strong>Dimensi:</strong> {{ $product->dimensions ?: '-' }}</div>
                <div><strong>Berat:</strong> {{ $product->weight_grams ? $product->weight_grams . ' gram' : '-' }}</div>
                <div><strong>Fulfillment:</strong> {{ $product->fulfillment_type === 'pre_order' ? 'Pre-order' : 'Ready stock' }}</div>
            </div>

            @if($product->verification_note)
                <p style="padding:10px;background:#f7eee6;color:var(--orange);font-size:11px;line-height:1.6;margin-top:16px">Catatan kurator: {{ $product->verification_note }}</p>
            @endif
        </div>
    </section>

    <section style="padding-top:28px;display:grid;gap:22px">
        <div>
            <div class="eyebrow">Deskripsi</div>
            <p class="muted" style="margin-top:8px;font-size:12px;line-height:1.9;white-space:pre-line">{{ $product->description ?: 'Belum ada deskripsi produk.' }}</p>
        </div>

        @if($product->story)
            <div>
                <div class="eyebrow">Cerita karya</div>
                <p class="muted" style="margin-top:8px;font-size:12px;line-height:1.9;white-space:pre-line">{{ $product->story }}</p>
            </div>
        @endif

        @if($product->processSteps->isNotEmpty())
            <div>
                <div class="eyebrow">Proses pembuatan</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:18px;margin-top:16px">
                    @foreach($product->processSteps as $step)
                        <article style="border-top:1px solid var(--line);padding-top:13px">
                            <div style="font:500 20px var(--serif);color:var(--orange)">{{ str_pad($step->step_number, 2, '0', STR_PAD_LEFT) }}</div>
                            <strong style="display:block;margin:8px 0 5px;font:600 16px var(--serif)">{{ $step->title }}</strong>
                            <p class="muted" style="font-size:11px;line-height:1.7">{{ $step->description }}</p>
                            @if($step->media_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($step->media_path) }}" alt="{{ $step->title }}" style="width:100%;aspect-ratio:4/3;object-fit:cover;margin-top:8px">
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        @if($product->verification_status === \App\Models\Product::STATUS_APPROVED)
            <section>
                <div class="eyebrow">Ulasan pembeli</div>
                <h2 class="page-title" style="font-size:29px;margin-top:7px">Komentar untuk karya ini</h2>
                @php($productReviews = $product->reviews)
                @if($productReviews->isNotEmpty())
                    <div style="display:grid;gap:14px;margin-top:18px">
                        @foreach($productReviews as $review)
                            <article style="padding:17px;border:1px solid var(--line);background:var(--white)">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
                                    <strong>{{ $review->customer?->name ?? 'Pembeli' }}</strong>
                                    <span class="muted" style="font-size:10px">{{ $review->created_at?->translatedFormat('d M Y') }}</span>
                                </div>
                                <div aria-label="Rating {{ $review->product_rating }} dari 5" style="margin-top:8px;color:#d5633a;font-size:14px">
                                    @for($i = 1; $i <= 5; $i++){{ $i <= $review->product_rating ? '★' : '☆' }}@endfor
                                </div>
                                <p style="margin:8px 0 0;font-size:12px;line-height:1.8;white-space:pre-line">{{ $review->body ?: 'Pembeli memberikan rating tanpa komentar.' }}</p>
                                @if($review->artisan_reply)
                                    <div style="margin-top:13px;padding:11px 13px;border-left:3px solid var(--green);background:#f1f5ef">
                                        <strong style="display:block;font-size:11px;margin-bottom:5px">Balasan perajin</strong>
                                        <p class="muted" style="margin:0;font-size:11px;line-height:1.7;white-space:pre-line">{{ $review->artisan_reply }}</p>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="muted" style="margin-top:15px;font-size:12px">Belum ada ulasan atau komentar untuk karya ini.</p>
                @endif
            </section>
        @endif
    </section>
</section>
@endsection