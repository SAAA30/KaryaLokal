@extends('layouts.storefront')
@section('title', 'Admin Marketplace — KaryaLokal.id')
@section('content')
<section class="shell admin-dashboard">
	<div class="eyebrow">Admin KaryaLokal</div>
	<h1 class="page-title">Ringkasan marketplace</h1>
	<div class="admin-actions">
		<a class="primary" href="{{ route('admin.payments.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="M2.5 9h19M6 14h5"/></svg>Verifikasi pembayaran</a>
		<a class="signin" href="{{ route('admin.qr.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM20 14v2M17 20h4M20 18v3"/></svg>Kelola QR</a>
		<a class="signin" href="{{ route('admin.saw.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V5M4 20h17M8 17v-5h3v5M14 17V7h3v10M20 17v-3h2"/></svg>SPK rekomendasi produk</a>
		<a class="signin" href="{{ route('admin.users.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.4-3.4 2.7-5.3 6.5-5.3 2 0 3.5.5 4.6 1.5M16 4.8a3.4 3.4 0 0 1 0 6.5M16 14.7c3.1.3 4.9 2 5.3 5.3"/></svg>Kelola akun</a>
	</div>
	@php($metricLinks = [
		'users' => route('admin.users.index'),
		'artisans' => route('admin.users.index', ['role' => 'artisan']),
		'customers' => route('admin.users.index', ['role' => 'customer']),
		'products' => route('storefront.catalog'),
		'pending_products' => route('admin.pending-products.index'),
		'orders' => route('admin.dashboard') . '#recent-orders',
		'paid_orders' => route('admin.sales.index'),
		'sales' => route('admin.sales.index'),
		'average_paid_order' => route('admin.sales.index'),
		'open_tickets' => route('admin.tickets.index', ['status' => 'active']),
		'qr_codes' => route('admin.qr.index'),
		'qr_scans' => route('admin.qr.index'),
	])
	<div class="admin-metrics">
		@foreach(['users'=>'Total pengguna','artisans'=>'Perajin','customers'=>'Pembeli','products'=>'Produk','pending_products'=>'Menunggu verifikasi','orders'=>'Pesanan','paid_orders'=>'Order dibayar','sales'=>'Penjualan dibayar','average_paid_order'=>'Rata-rata order dibayar','open_tickets'=>'Komplain aktif','qr_codes'=>'QR aktif','qr_scans'=>'Total scan'] as $key=>$label)
			<a class="metric-link" href="{{ $metricLinks[$key] }}"><article><div class="muted">{{ $label }}</div><strong>{{ in_array($key, ['sales', 'average_paid_order'], true) ? 'Rp'.number_format((float) $metrics[$key], 0, ',', '.') : number_format($metrics[$key]) }}</strong></article></a>
		@endforeach
	</div>
	<div class="dashboard-charts">
		<x-dashboard-chart title="Pesanan per bulan" :data="$monthlyOrderSeries" type="bar" />
		<x-dashboard-chart title="Penjualan per bulan" :data="$monthlySalesSeries" type="line" color="#397cf6" />
	</div>
	<section class="sales-recap">
		<div class="sales-recap-heading"><div><div class="eyebrow">Performa perajin</div><h2 class="section-title">Rekap penjualan produk</h2></div><span class="muted">Pembayaran terverifikasi, belum termasuk ongkir.</span><a href="{{ route('admin.sales.index') }}">Lihat rincian →</a></div>
		<div class="sales-table-wrap">
			<table class="sales-table"><thead><tr><th>Perajin</th><th>Produk terjual</th><th>Order dibayar</th><th>Nilai produk</th></tr></thead><tbody>
				@forelse($artisanSales as $artisanSale)
					<tr><td>{{ $artisanSale->artisan_name }}</td><td>{{ number_format((int) $artisanSale->units_sold) }}</td><td>{{ number_format((int) $artisanSale->paid_orders) }}</td><td>Rp{{ number_format((float) $artisanSale->product_sales, 0, ',', '.') }}</td></tr>
				@empty
					<tr><td colspan="4" class="muted">Belum ada penjualan terverifikasi.</td></tr>
				@endforelse
			</tbody></table>
		</div>
	</section>
	<div class="admin-columns">
		<section class="admin-dashboard-panel" id="recent-orders">
			<div class="dashboard-panel-heading"><h2 class="section-title">Pesanan terbaru</h2><a href="{{ route('admin.dashboard') }}#recent-orders">Lihat ringkasan</a></div>
			<div class="dashboard-list">
				@forelse($recentOrders as $order)
					<a class="recent-order" href="{{ route('admin.orders.show', $order) }}">
						<span><strong>{{ $order->order_number }}</strong><span class="muted"> · {{ $order->customer->name }}</span></span>
						<span class="order-status">{{ str_replace('_', ' ', $order->status) }} <span aria-hidden="true">→</span></span>
					</a>
				@empty
					<p class="muted" style="font-size:13px">Belum ada pesanan.</p>
				@endforelse
			</div>
		</section>
		<section class="admin-dashboard-panel">
			<div class="dashboard-panel-heading"><h2 class="section-title">Produk paling banyak dipindai</h2><a href="{{ route('admin.qr.index') }}">Lihat semua</a></div>
			<div class="dashboard-list">
				@forelse($popularProducts as $product)
					<a class="popular-product" href="{{ route('craft.story', $product->craft_id) }}"><span><strong>{{ $product->name }}</strong><span class="muted"> · {{ $product->artisan->name }}</span></span><strong>{{ number_format($product->scan_count) }} scan</strong></a>
				@empty
					<p class="muted" style="font-size:13px">Belum ada data QR.</p>
				@endforelse
			</div>
		</section>
	</div>
	<div class="admin-columns">
		<section class="admin-dashboard-panel" id="pending-products">
			<div class="dashboard-panel-heading"><h2 class="section-title">Karya menunggu verifikasi <span class="dashboard-count">{{ number_format($metrics['pending_products']) }}</span></h2><a href="{{ route('admin.pending-products.index') }}">Lihat semua</a></div>
			<div class="dashboard-list">
				@forelse($pendingProducts as $product)
					<a class="recent-order" href="{{ route('admin.pending-products.show', $product) }}"><span><strong>{{ $product->name }}</strong><span class="muted"> · {{ $product->artisan?->name ?? 'Perajin' }}</span></span><span class="order-status">Menunggu</span></a>
				@empty
					<p class="muted" style="font-size:13px">Tidak ada karya menunggu verifikasi.</p>
				@endforelse
			</div>
		</section>
		<section class="admin-dashboard-panel" id="open-tickets">
			<div class="dashboard-panel-heading"><h2 class="section-title">Komplain aktif <span class="dashboard-count">{{ number_format($metrics['open_tickets']) }}</span></h2><a href="{{ route('admin.tickets.index') }}">Lihat semua</a></div>
			<div class="dashboard-list">
				@forelse($openTickets as $ticket)
					<a class="recent-order" href="{{ route('admin.tickets.show', $ticket) }}"><span><strong>{{ $ticket->ticket_number }}</strong><span class="muted"> · {{ $ticket->subject }}</span><span class="muted" style="display:block">{{ $ticket->customer?->name ?? 'Pembeli' }}</span></span><span class="order-status">{{ str_replace('_', ' ', $ticket->status) }}</span></a>
				@empty
					<p class="muted" style="font-size:13px">Tidak ada komplain aktif.</p>
				@endforelse
			</div>
		</section>
	</div>
</section>
@endsection
@push('styles')
<style>
	.admin-dashboard{padding:38px 0}
	.admin-actions{display:flex;flex-wrap:wrap;gap:10px;margin:14px 0}
	.admin-actions a{display:inline-flex;align-items:center;gap:8px;min-height:44px;padding:10px 14px;font-size:13px}
	.admin-actions svg{width:18px;height:18px;flex:none;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
	.admin-metrics{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin:20px 0}
	.admin-metrics article{padding:14px;background:var(--white);border:1px solid var(--line)}
	.metric-link{display:block;color:inherit;text-decoration:none}.metric-link article{height:100%;transition:border-color .15s ease,background .15s ease,transform .15s ease}.metric-link:hover article{border-color:var(--green);background:#f0efe8;transform:translateY(-1px)}.metric-link:focus-visible{outline:2px solid var(--orange);outline-offset:2px}
	.admin-metrics article div{font-size:12px;line-height:1.45}
	.admin-metrics article strong{display:block;font:600 23px var(--serif);margin-top:7px}
	.dashboard-charts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:20px}
	.sales-recap,.admin-dashboard-panel{margin-top:18px;padding:18px;background:var(--white);border:1px solid var(--line)}
	.dashboard-panel-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:8px}
	.dashboard-panel-heading .section-title{margin:0}
	.dashboard-panel-heading>a{color:var(--green);font-size:12px;font-weight:700;white-space:nowrap}
	.dashboard-count{display:inline-flex;align-items:center;justify-content:center;min-width:26px;height:26px;margin-left:5px;padding:0 7px;border-radius:20px;background:#e6efdf;color:var(--green);font:700 12px var(--sans);vertical-align:middle}
	.sales-recap-heading{display:flex;justify-content:space-between;align-items:end;gap:14px}.sales-recap-heading .muted{font-size:12px}.sales-recap-heading>a{color:var(--green);font-size:12px;font-weight:700;text-decoration:underline;text-underline-offset:3px;white-space:nowrap}
	.sales-table-wrap{overflow-x:auto}.sales-table{width:100%;border-collapse:collapse;text-align:left;font-size:13px}.sales-table th{padding:10px;color:var(--muted);font-size:11px;text-transform:uppercase}.sales-table td{padding:11px 10px;border-top:1px solid var(--line)}
	.admin-columns{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-top:28px}
	.section-title{font:600 24px var(--serif);margin:0 0 10px}
	.dashboard-list{border-top:1px solid var(--line)}
	.recent-order,.popular-product{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--line);font-size:13px;line-height:1.5;text-decoration:none;color:inherit}
	.recent-order:hover{color:var(--green);background:#f0efe8}
	.recent-order:focus-visible{outline:2px solid var(--orange);outline-offset:2px}
	.order-status{white-space:nowrap}
	.popular-product strong{white-space:nowrap}
	@media(max-width:800px){.admin-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.admin-columns,.dashboard-charts{grid-template-columns:1fr}.sales-recap-heading{align-items:start;flex-direction:column}}
</style>
@endpush
