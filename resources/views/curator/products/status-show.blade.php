@extends('layouts.storefront')
@section('title', $product->name . ' — Status Verifikasi')
@section('content')
@php($statusLabel = ['approved' => 'Disetujui', 'revision' => 'Revisi', 'rejected' => 'Ditolak'][$product->verification_status] ?? str_replace('_', ' ', $product->verification_status))
<section class="shell curator-status-detail">
    <a class="muted" href="{{ route('curator.products.index', ['status' => $product->verification_status]) }}">← Kembali ke daftar {{ $statusLabel }}</a>
    <header>
        <div><div class="eyebrow">{{ $product->verification_status === \App\Models\Product::STATUS_REVISION ? 'Detail revisi' : ($product->verification_status === \App\Models\Product::STATUS_REJECTED ? 'Detail penolakan' : 'Detail status karya') }}</div><h1 class="page-title">{{ $product->name }}</h1><p class="muted">Perajin {{ $product->artisan->name }} · {{ $product->category?->name }} · {{ $product->artisan->artisanProfile?->city }}, {{ $product->artisan->artisanProfile?->province }}</p></div>
        <span class="verified">{{ $statusLabel }}</span>
    </header>
    @if(in_array($product->verification_status, [\App\Models\Product::STATUS_REVISION, \App\Models\Product::STATUS_REJECTED], true))
        <aside class="revision-note"><strong>{{ $product->verification_status === \App\Models\Product::STATUS_REVISION ? 'Catatan revisi dari kurator' : 'Alasan penolakan dari kurator' }}</strong><p>{{ $product->verification_note ?: 'Tidak ada catatan dari kurator.' }}</p></aside>
    @endif
    @php($image = $product->images[0] ?? null)
    @if($image)
        <img class="status-product-image" src="{{ \Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image) }}" alt="{{ $product->name }}">
    @endif
    <div class="status-product-info">
        <section><h2>Informasi karya</h2><p>{{ $product->description }}</p><dl><div><dt>Jenis pemenuhan</dt><dd>{{ $product->fulfillment_type === 'pre_order' ? 'Pre-order' : 'Siap dikirim' }}</dd></div><div><dt>Harga</dt><dd>Rp{{ number_format((float) $product->price, 0, ',', '.') }}</dd></div><div><dt>Stok</dt><dd>{{ $product->stock }}</dd></div><div><dt>Material</dt><dd>{{ $product->material }}</dd></div><div><dt>Teknik</dt><dd>{{ $product->technique }}</dd></div><div><dt>Asal</dt><dd>{{ $product->origin }}</dd></div><div><dt>Dimensi</dt><dd>{{ $product->dimensions ?: 'Belum diisi' }}</dd></div><div><dt>Berat</dt><dd>{{ $product->weight_grams ? number_format($product->weight_grams, 0, ',', '.') . ' gram' : 'Belum diisi' }}</dd></div><div><dt>Estimasi produksi</dt><dd>{{ $product->production_days ? $product->production_days . ' hari' : 'Tidak berlaku' }}</dd></div><div><dt>Kustomisasi</dt><dd>{{ $product->is_customizable ? 'Bisa disesuaikan' : 'Tidak tersedia' }}</dd></div></dl></section>
        <section><h2>Cerita karya</h2><p style="white-space:pre-line">{{ $product->story }}</p></section>
        @if($product->processSteps->isNotEmpty())
            <section><h2>Proses pembuatan</h2>@foreach($product->processSteps as $step)<article class="status-process-step"><strong>{{ $step->step_number }}. {{ $step->title }}</strong><p>{{ $step->description }}</p></article>@endforeach</section>
        @endif
    </div>
</section>
@endsection
@push('styles')
<style>
    .curator-status-detail{max-width:1000px;padding:36px 0 50px}
    .curator-status-detail>a{font-size:12px}
    .curator-status-detail header{display:flex;justify-content:space-between;align-items:end;gap:14px;margin:18px 0}
    .curator-status-detail .page-title{margin-bottom:5px}
    .curator-status-detail header p{margin:0;font-size:13px}
    .status-product-image{display:block;width:100%;max-height:460px;object-fit:cover;background:#e8e5dc}
    .status-product-info{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:18px}
    .status-product-info section{padding:18px;background:var(--white);border:1px solid var(--line)}
    .status-product-info h2{margin:0 0 10px;font:600 21px var(--serif)}
    .status-product-info p{font-size:13px;line-height:1.8}
    .status-product-info dl{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .status-product-info dl div{display:grid;gap:4px}.status-product-info dt{font-size:11px;color:var(--muted)}
    .status-product-info dd{margin:0;font-size:13px;font-weight:700}
    .revision-note{margin:0 0 18px;padding:15px 18px;border-left:3px solid var(--orange);background:#f7eee6;font-size:13px;line-height:1.7}
    .revision-note p{margin:5px 0 0;white-space:pre-line}
    .status-process-step{padding:10px 0;border-top:1px solid var(--line)}
    .status-process-step p{margin:5px 0 0}
    @media(max-width:700px){.status-product-info{grid-template-columns:1fr}.curator-status-detail header{align-items:start;flex-direction:column}}
</style>
@endpush
