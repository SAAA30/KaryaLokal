@extends('layouts.storefront')
@section('title', 'Karya Saya — KaryaLokal.id')
@section('content')
<section class="shell" style="padding:38px 0">
	<div class="eyebrow">Ruang perajin</div>
	<div style="display:flex;justify-content:space-between;align-items:end;gap:15px">
		<h1 class="page-title">Karya saya</h1>
		<a class="primary" href="{{ route('artisan.products.create') }}">+ Tambah karya</a>
	</div>
	<nav class="artisan-product-filters" aria-label="Filter status karya">
		<a href="{{ route('artisan.products.index') }}" @class(['is-active' => !$selectedStatus])>Semua</a>
		@foreach(['approved' => 'Aktif', 'pending' => 'Menunggu kurator', 'revision' => 'Perlu revisi', 'draft' => 'Draft', 'rejected' => 'Ditolak', 'inactive' => 'Nonaktif'] as $status => $label)
			<a href="{{ route('artisan.products.index', ['status' => $status]) }}" @class(['is-active' => $selectedStatus === $status])>{{ $label }}</a>
		@endforeach
	</nav>
	<div style="border-top:1px solid var(--line);margin-top:15px">
		@forelse($products as $product)
			<article style="display:grid;grid-template-columns:96px minmax(0,1fr) auto auto;gap:18px;align-items:center;padding:15px 0;border-bottom:1px solid var(--line);font-size:11px">
				<a href="{{ route('artisan.products.show', $product) }}" aria-label="Lihat {{ $product->name }}" style="display:block;width:96px;height:96px;background:#e8e5dc;overflow:hidden">
					@php($images = collect($product->images ?? []))
					@php($firstImage = $images->first())
					@php($localImage = $images->filter(fn ($image) => !\Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($image))->last())
					@php($image = $firstImage && !\Illuminate\Support\Str::startsWith($firstImage, ['http://', 'https://']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($firstImage) ? $firstImage : ($localImage ?? $firstImage))
					@if($image)
						<img src="{{ \Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) ? $image : \Illuminate\Support\Facades\Storage::url($image) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover">
					@else
						<span class="muted" style="height:100%;display:grid;place-items:center">Belum ada foto</span>
					@endif
				</a>
				<div>
					<a href="{{ route('artisan.products.show', $product) }}" style="color:inherit;text-decoration:none"><strong style="font:600 16px var(--serif)">{{ $product->name }}</strong></a>
					<div class="muted" style="margin-top:4px">{{ $product->category?->name }} · {{ $product->stock }} tersedia</div>
					@if($product->verification_status === \App\Models\Product::STATUS_APPROVED)
						<div class="muted" style="margin-top:6px"><span aria-label="Rating produk" style="color:#d5633a">★</span> {{ number_format((float) ($product->reviews_avg_product_rating ?? 0), 1) }} · {{ $product->reviews_count ?? 0 }} ulasan</div>
					@elseif($product->verification_note)
						<div style="color:var(--orange);margin-top:5px">Catatan kurator: {{ $product->verification_note }}</div>
					@endif
				</div>
				<span class="verified">{{ str_replace('_', ' ', $product->verification_status) }}</span>
				<a href="{{ route('artisan.products.edit', $product) }}" class="signin">Edit</a>
			</article>
		@empty
			<p class="muted" style="padding:25px 0;font-size:12px">Belum ada karya yang dibuat.</p>
		@endforelse
	</div>
	<div class="pagination">{{ $products->links() }}</div>
</section>
@endsection
@push('styles')
<style>
	.artisan-product-filters{display:flex;flex-wrap:wrap;gap:8px;margin:14px 0 18px;border-bottom:1px solid var(--line)}
	.artisan-product-filters a{padding:9px 11px;border-bottom:2px solid transparent;color:var(--muted);font-size:12px}
	.artisan-product-filters a:hover,.artisan-product-filters a.is-active{border-color:var(--green);color:var(--green)}
</style>
@endpush
