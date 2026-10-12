@extends('layouts.storefront')
@section('title', $product->name . ' — Detail Verifikasi')
@section('content')
<section class="shell admin-pending-detail">
    <a class="muted" href="{{ route('admin.pending-products.index') }}">← Kembali ke antrean</a>
    <div class="pending-detail-heading">
        <div><div class="eyebrow">Detail antrean verifikasi</div><h1 class="page-title">{{ $product->name }}</h1><p class="muted">Diajukan oleh {{ $product->artisan?->name }} · {{ $product->artisan?->artisanProfile?->city }}, {{ $product->artisan?->artisanProfile?->province }}</p></div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap"><span class="verified">Menunggu verifikasi</span><a class="signin" href="{{ route('admin.products.edit', ['product' => $product, 'return_to' => route('admin.pending-products.index')]) }}">Edit karya</a></div>
    </div>
    @php($image = $product->images[0] ?? null)
    @if($image)
        <img class="pending-product-image" src="{{ \Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image) }}" alt="{{ $product->name }}">
    @endif
    <div class="pending-detail-grid">
        <section class="pending-detail-panel">
            <h2>Informasi produk</h2>
            <p>{{ $product->description }}</p>
            <dl><div><dt>Harga</dt><dd>Rp{{ number_format((float) $product->price, 0, ',', '.') }}</dd></div><div><dt>Stok</dt><dd>{{ $product->stock }}</dd></div><div><dt>Kategori</dt><dd>{{ $product->category?->name ?? '-' }}</dd></div><div><dt>Asal</dt><dd>{{ $product->origin }}</dd></div><div><dt>Material</dt><dd>{{ $product->material }}</dd></div><div><dt>Teknik</dt><dd>{{ $product->technique }}</dd></div></dl>
        </section>
        <section class="pending-detail-panel">
            <h2>Cerita karya</h2>
            <p style="white-space:pre-line">{{ $product->story }}</p>
        </section>
    </div>
    @if($product->processSteps->isNotEmpty())
        <section class="pending-detail-panel process-panel">
            <h2>Proses pembuatan</h2>
            @foreach($product->processSteps as $step)
                <article><strong>{{ $step->step_number }}. {{ $step->title }}</strong><p>{{ $step->description }}</p></article>
            @endforeach
        </section>
    @endif
</section>
@endsection
@push('styles')
<style>
    .admin-pending-detail{max-width:1000px;padding:36px 0 50px}
    .admin-pending-detail>a{font-size:12px}
    .pending-detail-heading{display:flex;justify-content:space-between;align-items:end;gap:14px;margin:18px 0}
    .pending-detail-heading .page-title{margin-bottom:6px}
    .pending-detail-heading p{margin:0;font-size:13px}
    .pending-product-image{display:block;width:100%;max-height:480px;object-fit:cover;background:#e8e5dc}
    .pending-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:18px}
    .pending-detail-panel{padding:18px;background:var(--white);border:1px solid var(--line)}
    .pending-detail-panel h2{margin:0 0 10px;font:600 21px var(--serif)}
    .pending-detail-panel p{font-size:13px;line-height:1.8}
    .pending-detail-panel dl{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:16px 0 0}
    .pending-detail-panel dl div{display:grid;gap:4px}
    .pending-detail-panel dt{color:var(--muted);font-size:11px}
    .pending-detail-panel dd{margin:0;font-size:13px;font-weight:700}
    .process-panel{margin-top:16px}
    .process-panel article{padding:10px 0;border-top:1px solid var(--line)}
    .process-panel article strong{font-size:13px}
    .process-panel article p{margin:5px 0 0;color:var(--muted)}
    @media(max-width:700px){.pending-detail-grid{grid-template-columns:1fr}.pending-detail-heading{align-items:start;flex-direction:column}.pending-detail-panel dl{grid-template-columns:1fr 1fr}}
</style>
@endpush
