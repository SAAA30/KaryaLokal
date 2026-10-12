@extends('layouts.storefront')

@section('title', 'Wishlist — KaryaLokal.id')

@section('content')
<section class="shell" style="padding:42px 0 0">
    <div class="eyebrow">Karya yang ingin kamu ingat</div>
    <h1 class="page-title">Wishlist saya</h1>
    @if($wishlists->isEmpty())
        <div style="padding:38px 0 65px;border-top:1px solid var(--line);margin-top:18px"><p class="muted" style="font-size:13px">Belum ada karya tersimpan.</p><a class="primary" href="{{ route('storefront.catalog') }}">Jelajahi karya</a></div>
    @else
        <div class="card-grid" style="border-top:1px solid var(--line);padding-top:20px">
            @foreach($wishlists as $wishlist)
                @php($product = $wishlist->product)
                @php($image = $product->images[0] ?? null)
                <article class="product-card"><a class="product-photo" href="{{ route('products.show', $product->slug) }}"><img src="{{ $image ? (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image)) : 'https://images.unsplash.com/photo-1493106641515-6b5631de4bb9?auto=format&fit=crop&w=700&q=80' }}" alt="{{ $product->name }}"></a><div class="product-info"><div class="artisan-caption">{{ $product->artisan->name }}</div><h2 class="product-name"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h2><div class="product-price">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</div><div style="display:flex;gap:8px;margin-top:11px"><form method="POST" action="{{ route('cart.store') }}">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><input type="hidden" name="quantity" value="1"><button class="primary" type="submit">Tambah ke keranjang</button></form><form method="POST" action="{{ route('wishlist.destroy', $wishlist) }}">@csrf @method('DELETE')<button class="signin" type="submit" aria-label="Hapus {{ $product->name }} dari wishlist" title="Hapus dari wishlist">Hapus</button></form></div></div></article>
            @endforeach
        </div>
        <div class="pagination">{{ $wishlists->links() }}</div>
    @endif
</section>
@endsection
