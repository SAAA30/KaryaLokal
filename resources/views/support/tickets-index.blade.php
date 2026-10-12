@extends('layouts.storefront')
@php($isAdmin = auth()->user()->isAdmin())
@section('title', $isAdmin ? 'Komplain Aktif — Admin' : 'Tiket bantuan — Customer Service')
@section('content')
<section class="shell support-tickets-page">
	<a class="back-link" href="{{ $isAdmin ? route('admin.dashboard') : route('support.dashboard') }}">Kembali ke dashboard</a>
	<div class="page-heading">
		<div class="eyebrow">{{ $isAdmin ? 'Admin KaryaLokal' : 'Customer service' }}</div>
		<h1 class="page-title">{{ $isAdmin ? 'Komplain aktif' : 'Tiket bantuan' }}</h1>
	</div>
	<nav class="status-filters" aria-label="Filter status tiket">
		<a href="{{ route($isAdmin ? 'admin.tickets.index' : 'support.tickets.index') }}" @if(!$selectedStatus) aria-current="page" @endif>Semua</a>
		<a href="{{ route($isAdmin ? 'admin.tickets.index' : 'support.tickets.index', ['status' => 'active']) }}" @if($selectedStatus === 'active') aria-current="page" @endif>Aktif</a>
		@foreach(['open'=>'Tiket baru','in_progress'=>'Diproses','waiting_customer'=>'Menunggu customer','resolved'=>'Selesai'] as $key => $label)
			<a href="{{ route($isAdmin ? 'admin.tickets.index' : 'support.tickets.index', ['status' => $key]) }}" @if($selectedStatus === $key) aria-current="page" @endif>{{ $label }}</a>
		@endforeach
	</nav>
	<div class="ticket-list">
		@forelse($tickets as $ticket)
			<a class="ticket-row" href="{{ route($isAdmin ? 'admin.tickets.show' : 'support.agent.show', ['ticket' => $ticket->id]) }}" aria-label="Buka tiket {{ $ticket->ticket_number }}: {{ $ticket->subject }}">
				<span class="ticket-copy">
					<strong>{{ $ticket->ticket_number }} · {{ $ticket->subject }}</strong>
					<span class="muted">{{ $ticket->customer->name }} · {{ $ticket->order?->order_number ?? 'Tanpa order' }} · {{ $ticket->created_at->format('d M Y') }}</span>
				</span>
				<span class="ticket-status">{{ str_replace('_', ' ', $ticket->status) }}</span>
			</a>
		@empty
			<p class="empty-state">Belum ada tiket untuk filter ini.</p>
		@endforelse
	</div>
	@if($tickets->hasPages())
		<nav class="ticket-pagination" aria-label="Navigasi tiket">
			<span class="muted">Menampilkan {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} dari {{ $tickets->total() }} tiket</span>
			<div class="pagination-links">
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
	.support-tickets-page{padding:32px 0 52px}.back-link{display:inline-block;margin-bottom:22px;color:var(--muted);font-size:11px}.back-link:hover{color:var(--green)}.page-heading{margin-bottom:20px}.page-heading .page-title{margin-bottom:0}.status-filters{display:flex;flex-wrap:wrap;gap:8px;padding-bottom:14px;border-bottom:1px solid var(--line)}.status-filters a{padding:9px 12px;border:1px solid var(--line);background:var(--white);font-size:10px;color:var(--muted)}.status-filters a[aria-current="page"],.status-filters a:hover{border-color:var(--green);color:var(--green);background:#e9f1eb}.ticket-list{margin-top:4px;border:1px solid var(--line);background:rgba(255,255,255,.5);border-radius:12px;overflow:hidden}.ticket-row{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:15px 18px;border-bottom:1px solid var(--line)}.ticket-row:last-child{border-bottom:0}.ticket-row:hover{background:#f7f5ee;color:var(--green)}.ticket-copy{display:grid;gap:6px;min-width:0}.ticket-copy .muted{font-size:10px}.ticket-status{padding:7px 10px;border-radius:999px;background:#e9f1eb;color:var(--green);font-size:10px;font-weight:700;text-transform:capitalize;white-space:nowrap}.empty-state{margin:0;padding:24px;color:var(--muted);font-size:12px}.ticket-pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:18px;font-size:10px}.pagination-links{display:flex;gap:8px}.pagination-links a,.pagination-links span{display:inline-flex;align-items:center;min-height:34px;padding:0 12px;border:1px solid var(--line);background:var(--white);color:var(--ink)}.pagination-links a:hover{border-color:var(--green);color:var(--green)}.pagination-links span[aria-disabled="true"]{color:var(--muted);background:#eeede5}.ticket-row:focus-visible,.status-filters a:focus-visible{outline:2px solid var(--orange);outline-offset:2px}
	@media(max-width:700px){.ticket-row{align-items:flex-start;flex-direction:column}.ticket-pagination{align-items:flex-start;flex-direction:column}}
</style>
@endpush