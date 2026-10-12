@extends('layouts.storefront')
@section('title', 'Customer Service — KaryaLokal.id')
@section('content')
<section class="shell role-dashboard">
	<div class="dashboard-top">
		<div>
			<div class="eyebrow">Customer service</div>
			<h1 class="page-title">Antrean bantuan</h1>
		</div>
		<a href="{{ route('support.tickets.index') }}" class="dashboard-link">Lihat semua</a>
	</div>
	<div class="metric-grid">
		@foreach(['open'=>'Tiket baru','in_progress'=>'Diproses','waiting_customer'=>'Menunggu customer','resolved'=>'Selesai'] as $key=>$label)
			<a class="metric-card {{ $selectedStatus === $key ? 'is-selected' : '' }}" href="{{ route('support.tickets.index', ['status' => $key]) }}" aria-label="Lihat {{ strtolower($label) }}: {{ $metrics[$key] }} tiket" @if($selectedStatus === $key) aria-current="page" @endif><span class="muted">{{ $label }}</span><strong>{{ $metrics[$key] }}</strong></a>
		@endforeach
	</div>
	@if($selectedStatus)
		<div class="ticket-filter-heading"><h2 class="section-title">{{ ['open'=>'Tiket baru','in_progress'=>'Diproses','waiting_customer'=>'Menunggu customer','resolved'=>'Selesai'][$selectedStatus] }}</h2><a href="{{ route('support.tickets.index') }}">Hapus filter</a></div>
	@endif
	@php($ticketStatusSeries = collect(['open'=>'Tiket baru','in_progress'=>'Diproses','waiting_customer'=>'Menunggu customer','resolved'=>'Selesai'])->map(fn($label,$key)=>['label'=>$label,'value'=>$metrics[$key]])->all())
	<div class="dashboard-charts">
		<x-dashboard-chart title="Status tiket" :data="$ticketStatusSeries" type="bar" />
		<x-dashboard-chart title="Tiket masuk per bulan" :data="$monthlyTicketSeries" type="line" color="#397cf6" />
	</div>
	<div class="recent-list">
		@forelse($tickets as $ticket)
			<a class="ticket-row" href="{{ route('support.agent.show', ['ticket' => $ticket->id]) }}" aria-label="Buka tiket {{ $ticket->ticket_number }}: {{ $ticket->subject }}">
				<span class="ticket-copy">
					<strong>{{ $ticket->ticket_number }} · {{ $ticket->subject }}</strong>
					<span class="muted ticket-customer">{{ $ticket->customer->name }} · {{ $ticket->order?->order_number ?? 'Tanpa order' }}</span>
				</span>
				<span class="ticket-badge">{{ str_replace('_',' ',$ticket->status) }}</span>
			</a>
		@empty
			<div class="empty-state">
				<strong>Tidak ada tiket di antrean saat ini.</strong>
				<span class="muted">Semua tiket sudah ditangani atau belum masuk ke antrian.</span>
			</div>
		@endforelse
	</div>
	@if($tickets->hasPages())
		<nav class="ticket-pagination" aria-label="Navigasi tiket">
			<span class="muted">Menampilkan {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} dari {{ $tickets->total() }} tiket</span>
			<div class="ticket-pagination-links">
				@if($tickets->onFirstPage())
					<span aria-disabled="true">Sebelumnya</span>
				@else
					<a href="{{ $tickets->previousPageUrl() }}" rel="prev">Sebelumnya</a>
				@endif
				@if($tickets->hasMorePages())
					<a href="{{ $tickets->nextPageUrl() }}" rel="next">Berikutnya</a>
				@else
					<span aria-disabled="true">Berikutnya</span>
				@endif
			</div>
		</nav>
	@endif
</section>
@endsection
@push('styles')
<style>
	.role-dashboard{padding:38px 0 52px}.dashboard-top{display:flex;justify-content:space-between;align-items:end;gap:16px;margin-bottom:16px}.dashboard-link{font-size:10px;color:var(--green);font-weight:700;text-decoration:underline;text-underline-offset:3px}.metric-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:20px 0}.metric-card{display:grid;gap:6px;padding:16px 18px;background:var(--white);border:1px solid var(--line);border-radius:12px;transition:all .2s ease}.metric-card>span{font-size:10px}.metric-card strong{font:600 25px var(--serif)}.metric-card:hover,.metric-card.is-selected{border-color:var(--green);background:#f0efe8;transform:translateY(-1px)}.metric-card:focus-visible,.ticket-row:focus-visible{outline:2px solid var(--orange);outline-offset:2px}
	.dashboard-charts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:22px 0}
	.ticket-filter-heading{display:flex;justify-content:space-between;align-items:center;gap:10px}.ticket-filter-heading>a{font-size:10px;color:var(--green);text-decoration:underline;text-underline-offset:3px}.section-title{font:600 20px var(--serif);margin:10px 0}.recent-list{border:1px solid var(--line);background:rgba(255,255,255,.5);border-radius:16px;overflow:hidden}.ticket-row{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:16px 18px;border-bottom:1px solid var(--line);transition:background .2s ease}.ticket-row:last-child{border-bottom:0}.ticket-row:hover{background:#f7f5ee;color:var(--green)}.ticket-copy{display:grid;gap:6px;min-width:0}.ticket-customer{display:block;font-size:10px;margin-top:4px}.ticket-badge{display:inline-flex;align-items:center;padding:7px 10px;background:#e9f1eb;color:var(--green);border-radius:999px;font-size:10px;font-weight:700;text-transform:capitalize;white-space:nowrap}.ticket-pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:18px;font-size:10px}.ticket-pagination-links{display:flex;gap:8px}.ticket-pagination-links a,.ticket-pagination-links span{display:inline-flex;align-items:center;min-height:34px;padding:0 12px;border:1px solid var(--line);background:var(--white);color:var(--ink)}.ticket-pagination-links a:hover{border-color:var(--green);color:var(--green)}.ticket-pagination-links span[aria-disabled="true"]{color:var(--muted);background:#eeede5}.empty-state{display:grid;place-items:center;gap:8px;min-height:170px;padding:24px;text-align:center;background:#f3f1ea;border:1px dashed var(--line)}.empty-state strong{font:600 18px var(--serif)}
	@media(max-width:700px){.dashboard-top{align-items:flex-start;flex-direction:column}.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.dashboard-charts{grid-template-columns:1fr}.ticket-row{align-items:flex-start;flex-direction:column}.ticket-badge{align-self:flex-start}.ticket-pagination{align-items:flex-start;flex-direction:column}}
</style>
@endpush
