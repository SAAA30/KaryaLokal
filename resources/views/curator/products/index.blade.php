@extends('layouts.storefront')
@php($statusLabels = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'revision' => 'Revisi', 'rejected' => 'Ditolak'])
@section('title', 'Karya ' . ($selectedStatus ? ($statusLabels[$selectedStatus] ?? 'Kurator') : 'dalam Antrean') . ' — KaryaLokal.id')
@section('content')
<section class="shell curator-products-page" style="padding:38px 0">
	<div class="eyebrow">Ruang kurator</div>
	<h1 class="page-title">{{ $selectedStatus ? 'Karya: ' . $statusLabels[$selectedStatus] : 'Antrean verifikasi' }}</h1>
	<nav class="product-status-filters" aria-label="Filter status karya">
		<a href="{{ route('curator.products.index') }}" @class(['is-active' => !$selectedStatus])>Antrean</a>
		@foreach($statusLabels as $status => $label)
			<a href="{{ route('curator.products.index', ['status' => $status]) }}" @class(['is-active' => $selectedStatus === $status])>{{ $label }}</a>
		@endforeach
	</nav>
	<div class="curator-product-list">
		@forelse($products as $product)
			<a href="{{ route('curator.products.show', $product) }}" class="curator-product-row">
				<span><strong>{{ $product->name }}</strong><small>{{ $product->artisan->name }} · {{ $product->category?->name }} · {{ $product->created_at->format('d M Y') }}</small></span>
				<span class="verified">{{ $statusLabels[$product->verification_status] ?? str_replace('_', ' ', $product->verification_status) }}</span>
			</a>
		@empty
			<p class="muted" style="padding:20px 0;font-size:13px">Tidak ada karya untuk status ini.</p>
		@endforelse
	</div>
	<div class="pagination">{{ $products->links() }}</div>
</section>
@endsection
@push('styles')
<style>
	.curator-products-page{max-width:1050px}
	.product-status-filters{display:flex;flex-wrap:wrap;gap:8px;margin:17px 0;border-bottom:1px solid var(--line)}
	.product-status-filters a{padding:10px 12px;border-bottom:2px solid transparent;color:var(--muted);font-size:13px}
	.product-status-filters a:hover,.product-status-filters a.is-active{border-color:var(--green);color:var(--green)}
	.curator-product-list{padding:0 16px;background:var(--white);border:1px solid var(--line)}
	.curator-product-row{display:flex;justify-content:space-between;align-items:center;gap:14px;min-height:62px;padding:11px 4px;border-bottom:1px solid var(--line)}
	.curator-product-row:last-child{border-bottom:0}
	.curator-product-row:hover{color:var(--green);background:#f7f5ef}
	.curator-product-row>span:first-child{display:grid;gap:4px}
	.curator-product-row strong{font-size:14px}
	.curator-product-row small{color:var(--muted);font-size:12px}
	@media(max-width:600px){.curator-product-row{align-items:flex-start;flex-direction:column}}
</style>
@endpush
