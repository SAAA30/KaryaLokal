@extends('layouts.storefront')
@section('title', 'Ruang Perajin — KaryaLokal.id')
@section('content')
<section class="shell role-dashboard">
	<div class="eyebrow">Ruang perajin</div>
	<h1 class="page-title">Selamat datang, {{ auth()->user()->name }}.</h1>
	<p class="muted" style="font-size:12px">Kelola karya, cerita, dan proses pembuatannya.</p>
	<div class="dashboard-actions">
		<a class="primary" href="{{ route('artisan.products.create') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/></svg>Tambah karya</a>
		<a class="signin" href="{{ route('artisan.products.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 9 4.5v9L12 21l-9-4.5v-9z"/><path d="m3.5 7.7 8.5 4.5 8.5-4.5M12 12.2V21"/></svg>Karya saya</a>
		<a class="signin" href="{{ route('artisan.chats.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 11.5a8.2 8.2 0 0 1-8.5 8 9 9 0 0 1-4-.9L3 20l1.4-4.1A7.7 7.7 0 0 1 3.5 11.5a8.2 8.2 0 0 1 8.5-8 8.2 8.2 0 0 1 8.5 8Z"/></svg>Chat pembeli</a>
		<a class="signin" href="{{ route('profile.edit') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.5-3.5 3.1-5.5 7-5.5s6.5 2 7 5.5"/></svg>Profil perajin</a>
	</div>
	<div class="metric-grid">
		@foreach(['all'=>'Semua karya','active'=>'Aktif','pending'=>'Menunggu kurator','revision'=>'Perlu revisi'] as $key=>$label)
			@php($status = ['active' => 'approved', 'pending' => 'pending', 'revision' => 'revision'][$key] ?? null)
			<a class="metric-link" href="{{ route('artisan.products.index', $status ? ['status' => $status] : []) }}"><article><div class="muted">{{ $label }}</div><strong>{{ $metrics[$key] }}</strong></article></a>
		@endforeach
		<a class="metric-link" href="{{ route('artisan.products.index', ['status' => 'approved']) }}"><article><div class="muted">Rating produk disetujui</div><strong>{{ number_format((float) ($metrics['approved_rating'] ?? 0), 1) }}</strong></article></a>
	</div>
	<section class="artisan-sales-recap" aria-label="Rekap penjualan perajin">
		<div class="sales-recap-copy"><div class="eyebrow">Pembayaran terverifikasi</div><strong>Rp{{ number_format($metrics['sales'], 0, ',', '.') }}</strong><span>Nilai produk terjual</span></div>
		<div class="sales-mini-stat"><strong>{{ number_format($metrics['paid_orders']) }}</strong><span>Order dibayar</span></div>
		<div class="sales-mini-stat"><strong>{{ number_format($metrics['units_sold']) }}</strong><span>Produk terjual</span></div>
		<a class="sales-report-link" href="{{ route('artisan.sales.index') }}">Lihat rincian penjualan →</a>
	</section>
	@php($artisanStatusSeries = collect(['active'=>'Aktif','pending'=>'Menunggu kurator','revision'=>'Revisi','draft'=>'Draft','rejected'=>'Ditolak','inactive'=>'Nonaktif'])->map(fn($label,$key)=>['label'=>$label,'value'=>$metrics[$key]])->all())
	<div class="dashboard-charts">
		<x-dashboard-chart title="Status karya" :data="$artisanStatusSeries" type="bar" />
		<x-dashboard-chart title="Penjualan per bulan" :data="$monthlySalesSeries" type="line" color="#397cf6" />
	</div>
	<h2 class="section-title recent-heading">Karya terbaru</h2>
	<div class="recent-list">
		@forelse($recentProducts as $product)
			<a href="{{ route('artisan.products.edit', $product) }}"><span><strong>{{ $product->name }}</strong><span class="muted"> · {{ $product->category?->name }}</span></span><span class="eyebrow">{{ str_replace('_',' ',$product->verification_status) }}</span></a>
		@empty
			<p class="muted" style="font-size:12px;padding:12px 0">Belum ada karya. Mulai dengan menambahkan produk pertamamu.</p>
		@endforelse
	</div>
</section>
@endsection
@push('styles')
<style>
	.role-dashboard{padding:38px 0 52px}.dashboard-actions{display:flex;flex-wrap:wrap;gap:10px;margin:20px 0}.dashboard-actions a{display:inline-flex;align-items:center;gap:8px}.dashboard-actions svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round;flex:none}.metric-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.metric-link{display:block;color:inherit;text-decoration:none}.metric-grid article{height:100%;padding:16px;background:var(--white);border:1px solid var(--line);transition:border-color .15s ease,background .15s ease,transform .15s ease}.metric-link:hover article{border-color:var(--green);background:#f0efe8;transform:translateY(-1px)}.metric-link:focus-visible{outline:2px solid var(--orange);outline-offset:2px}.metric-grid article div{font-size:10px}.metric-grid article strong{display:block;font:600 27px var(--serif);margin-top:8px}
	.artisan-sales-recap{display:grid;grid-template-columns:minmax(0,1.5fr) repeat(2,minmax(130px,.65fr));gap:20px;align-items:center;margin:18px 0;padding:18px;background:#eeede5}.sales-recap-copy,.sales-mini-stat{display:grid;gap:4px}.sales-recap-copy strong{font:600 25px var(--serif)}.sales-recap-copy span,.sales-mini-stat span{color:var(--muted);font-size:10px}.sales-mini-stat strong{font:600 20px var(--serif)}.sales-report-link{grid-column:1/-1;justify-self:end;color:var(--green);font-size:10px;font-weight:700;text-decoration:underline;text-underline-offset:3px}
	.dashboard-charts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:22px}
	.recent-heading{margin-top:32px}.recent-list{border-top:1px solid var(--line)}.recent-list>a{display:flex;justify-content:space-between;gap:15px;padding:13px 0;border-bottom:1px solid var(--line);font-size:11px}.recent-list>a:hover{color:var(--green)}
	@media(max-width:700px){.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.dashboard-charts{grid-template-columns:1fr}.artisan-sales-recap{grid-template-columns:1fr 1fr}.sales-recap-copy{grid-column:1/-1}}
</style>
@endpush
