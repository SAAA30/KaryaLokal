@extends('layouts.storefront')

@section('title', 'Jelajahi Karya — KaryaLokal.id')

@section('content')
<style>
    .rating-inline{display:flex;align-items:center;gap:6px;margin:8px 0 6px;font-size:12px;color:var(--ink)}
    .rating-inline .rating-stars{color:#ffb400;font-size:14px}
    .rating-inline .muted{color:var(--muted)}
</style>
<section class="shell" style="padding:42px 0 0">
    <div class="eyebrow">Pasar karya Nusantara</div>
    <h1 class="page-title">Temukan karya yang punya cerita.</h1>
    <p class="muted" style="font-size:13px;margin:0">{{ $products->total() }} karya terverifikasi dari para perajin lokal.</p>

    <form class="filters" method="GET" action="{{ route('storefront.catalog') }}">
        <label>Kata kunci<input class="wide" type="search" name="q" value="{{ request('q') }}" placeholder="Produk, perajin, daerah"></label>
        <label>Kategori<select name="kategori"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('kategori') === $category->slug)>{{ $category->name }}</option>@endforeach</select></label>
        <label>Harga min.<input type="number" min="0" name="min_harga" value="{{ request('min_harga') }}" placeholder="Rp"></label>
        <label>Harga maks.<input type="number" min="0" name="max_harga" value="{{ request('max_harga') }}" placeholder="Rp"></label>
        <label>Urutkan<select name="sort"><option value="terbaru" @selected(request('sort', 'terbaru') === 'terbaru')>Terbaru</option><option value="harga_terendah" @selected(request('sort') === 'harga_terendah')>Harga terendah</option><option value="harga_tertinggi" @selected(request('sort') === 'harga_tertinggi')>Harga tertinggi</option><option value="terpopuler" @selected(request('sort') === 'terpopuler')>Paling banyak dipindai</option></select></label>
        <label style="display:flex;align-items:center;gap:6px;height:37px"><input type="checkbox" name="custom" value="1" @checked(request()->boolean('custom'))> Bisa custom</label>
        <label style="display:flex;align-items:center;gap:6px;height:37px"><input type="checkbox" name="preorder" value="1" @checked(request()->boolean('preorder'))> Pre-order</label>
        <button class="primary" type="submit">Terapkan filter</button>
    </form>

    @if($products->count())
        <div class="card-grid">
            @foreach($products as $product)
                @php($image = $product->images[0] ?? null)
                <article class="product-card">
                    <a class="product-photo" href="{{ route('products.show', $product->slug) }}"><img src="{{ $image ? (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image)) : 'https://images.unsplash.com/photo-1493106641515-6b5631de4bb9?auto=format&fit=crop&w=700&q=80' }}" alt="{{ $product->name }}"></a>
                    <div class="product-info"><div class="artisan-caption">{{ $product->artisan->name }}@if($product->artisan->artisanProfile?->city) · {{ $product->artisan->artisanProfile->city }}@endif</div><h2 class="product-name"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h2>
                        <div class="rating-inline" aria-label="Rating rata-rata {{ number_format((float) ($product->reviews_avg_product_rating ?? 0), 1) }} dari 5">
                            <span class="rating-stars">★</span>
                            <span>{{ number_format((float) ($product->reviews_avg_product_rating ?? 0), 1) }}</span>
                            <span class="muted">({{ $product->reviews_count ?? 0 }})</span>
                        </div>
                        <div class="product-price">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</div><div class="product-origin">{{ $product->material }} · {{ $product->origin }}</div></div>
                </article>
            @endforeach
        </div>
        <div class="pagination">{{ $products->links() }}</div>
    @else
        <div style="padding:50px 0 75px;text-align:center"><div class="eyebrow" style="justify-content:center">Belum ada karya yang cocok</div><p class="muted" style="font-size:13px">Coba ubah pencarian atau filter kategori.</p><a class="primary" href="{{ route('storefront.catalog') }}">Hapus filter</a></div>
    @endif
</section>
@endsection
