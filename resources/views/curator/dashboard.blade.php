@extends('layouts.storefront')
@section('title', 'Dashboard Kurator — KaryaLokal.id')
@section('content')
<section class="shell role-dashboard">
	<div class="eyebrow">Ruang kurator</div><h1 class="page-title">Verifikasi karya</h1>
	<p class="muted" style="font-size:12px">Periksa kualitas, cerita, proses, dan keaslian sebelum karya dipublikasikan.</p>
	<div class="metric-grid">
		@foreach(['pending'=>'Menunggu','approved'=>'Disetujui','revision'=>'Revisi','rejected'=>'Ditolak'] as $key=>$label)
			<a class="metric-link" href="{{ route('curator.products.index', ['status' => $key]) }}"><article><div class="muted">{{ $label }}</div><strong>{{ $metrics[$key] }}</strong></article></a>
		@endforeach
	</div>
	@php($curatorStatusSeries = collect(['pending'=>'Menunggu','approved'=>'Disetujui','revision'=>'Revisi','rejected'=>'Ditolak'])->map(fn($label,$key)=>['label'=>$label,'value'=>$metrics[$key]])->all())
	<div class="dashboard-charts">
		<x-dashboard-chart title="Status verifikasi" :data="$curatorStatusSeries" type="bar" />
		<x-dashboard-chart title="Produk masuk per bulan" :data="$monthlyProductSeries" type="line" color="#397cf6" />
	</div>
	<div class="queue-heading"><h2 class="section-title">Antrean verifikasi</h2><a class="text-link" href="{{ route('curator.products.index') }}">Semua antrean →</a></div>
	<div class="recent-list">
		@forelse($queue as $product)
			<a href="{{ route('curator.products.show',$product) }}"><span><strong>{{ $product->name }}</strong><span class="muted"> · {{ $product->artisan->name }} · {{ $product->category?->name }}</span></span><span class="eyebrow">{{ str_replace('_',' ',$product->verification_status) }}</span></a>
		@empty
			<p class="muted" style="font-size:12px;padding:12px 0">Tidak ada karya menunggu pemeriksaan.</p>
		@endforelse
	</div>
</section>
@endsection
@push('styles')
<style>
	.role-dashboard{padding:38px 0 52px}.metric-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:22px 0}.metric-link{display:block;color:inherit;text-decoration:none}.metric-grid article{height:100%;padding:16px;background:var(--white);border:1px solid var(--line);transition:border-color .15s ease,background .15s ease,transform .15s ease}.metric-link:hover article{border-color:var(--green);background:#f0efe8;transform:translateY(-1px)}.metric-link:focus-visible{outline:2px solid var(--orange);outline-offset:2px}.metric-grid article div{font-size:10px}.metric-grid article strong{display:block;font:600 27px var(--serif);margin-top:8px}
	.dashboard-charts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:22px 0}
	.queue-heading{display:flex;justify-content:space-between;align-items:end;gap:12px}.recent-list{border-top:1px solid var(--line)}.recent-list>a{display:flex;justify-content:space-between;gap:15px;padding:13px 0;border-bottom:1px solid var(--line);font-size:11px}.recent-list>a:hover{color:var(--green)}
	@media(max-width:700px){.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.dashboard-charts{grid-template-columns:1fr}.queue-heading{align-items:center}}
</style>
@endpush
