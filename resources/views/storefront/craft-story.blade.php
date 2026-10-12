@extends('layouts.storefront')

@section('title', 'Craft Story ' . $product->craft_id . ' — KaryaLokal.id')

@section('content')
@php($image = $product->images[0] ?? null)
<section class="shell" style="max-width:850px;padding:38px 0 0">
    <div style="text-align:center;padding:12px 0 25px"><div class="eyebrow" style="justify-content:center">Identitas digital karya</div><div class="brand" style="margin-top:9px">karya<span style="color:var(--orange)">lokal</span><small style="font:600 8px var(--sans);vertical-align:top">.id</small></div></div>
    <img src="{{ $image ? (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image)) : 'https://images.unsplash.com/photo-1493106641515-6b5631de4bb9?auto=format&fit=crop&w=1200&q=85' }}" alt="{{ $product->name }}" style="width:100%;max-height:460px;object-fit:cover;background:#e7e4d9">
    <div style="padding:24px 0 20px;border-bottom:1px solid var(--line)"><span class="verified">✓ Karya terverifikasi KaryaLokal.id</span><h1 class="page-title" style="font-size:42px;margin:13px 0 7px">{{ $product->name }}</h1><div class="muted" style="font-size:12px">Craft ID: <strong style="color:var(--ink)">{{ $product->craft_id }}</strong></div></div>
    <section class="story-section" style="display:grid;grid-template-columns:1fr 1fr;gap:25px">
        <div><div class="eyebrow">Dibuat oleh</div><h2 class="page-title" style="font-size:26px;margin-top:9px">{{ $product->artisan->name }}</h2><p class="muted" style="font-size:12px;line-height:1.7">{{ $product->artisan->artisanProfile?->city }}{{ $product->artisan->artisanProfile?->province ? ', ' . $product->artisan->artisanProfile->province : '' }}</p><p class="muted" style="font-size:12px;line-height:1.8">{{ $product->artisan->artisanProfile?->maker_story }}</p></div>
        <div><div class="eyebrow">Bahan dan teknik</div><p style="font:500 18px/1.6 var(--serif);margin:11px 0">{{ $product->material ?: 'Material pilihan perajin' }}</p><p class="muted" style="font-size:12px">{{ $product->technique }} · {{ $product->origin }}</p></div>
    </section>
    @if($product->story)<section class="story-section"><div class="eyebrow">Cerita karya</div><p style="font:500 20px/1.7 var(--serif);white-space:pre-line;margin:13px 0">{{ $product->story }}</p></section>@endif
    @if($product->processSteps->isNotEmpty())<section class="story-section"><div class="eyebrow">Jejak proses</div><div style="display:grid;gap:17px;margin-top:18px">@foreach($product->processSteps as $step)<article style="display:grid;grid-template-columns:38px 1fr;gap:12px"><div style="font:500 19px var(--serif);color:var(--orange)">{{ str_pad($step->step_number, 2, '0', STR_PAD_LEFT) }}</div><div><strong style="font:600 16px var(--serif)">{{ $step->title }}</strong><p class="muted" style="font-size:11px;line-height:1.7;margin:5px 0 0">{{ $step->description }}</p>@if($step->media_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($step->media_path) }}" alt="{{ $step->title }}" style="width:100%;max-height:340px;object-fit:cover;margin-top:10px">@endif</div></article>@endforeach</div></section>@endif
    <section style="padding:24px 0 45px;text-align:center"><div class="verified">✓ Authentic Craft · Terverifikasi KaryaLokal.id</div><p class="muted" style="font-size:10px;margin-top:12px">{{ number_format($product->scan_count) }} kali cerita ini dipindai</p><a class="primary" href="{{ route('products.show', $product->slug) }}">Lihat detail produk</a></section>
</section>
@endsection
