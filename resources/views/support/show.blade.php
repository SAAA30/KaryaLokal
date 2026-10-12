@extends('layouts.storefront')

@php($isAgent = in_array(auth()->user()->role, ['customer_service', 'admin'], true))

@section('title', $ticket->ticket_number . ' — Pusat Bantuan')

@section('content')
<section class="shell" style="max-width:900px;padding:38px 0">
    <a href="{{ auth()->user()->isAdmin() ? route('admin.tickets.index') : ($isAgent ? route('support.dashboard') : route('support.customer.index')) }}" class="muted" style="font-size:11px">← {{ $isAgent ? 'Antrean bantuan' : 'Tiket bantuan' }}</a>
    <div style="display:flex;justify-content:space-between;align-items:end;gap:16px;margin:20px 0">
        <div>
            <div class="eyebrow">{{ $ticket->ticket_number }} · {{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</div>
            <h1 class="page-title">{{ $ticket->subject }}</h1>
            <p class="muted" style="font-size:11px;margin:0">Dibuat {{ $ticket->created_at->format('d M Y, H:i') }}@if($ticket->order) · Pesanan {{ $ticket->order->order_number }}@endif</p>
        </div>
        <span class="verified">{{ str_replace('_', ' ', $ticket->status) }}</span>
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:24px;align-items:start">
        <div>
            <article style="padding:18px;background:var(--white);border:1px solid var(--line)">
                <div class="muted" style="font-size:10px;margin-bottom:8px">{{ $ticket->customer->name }} · {{ $ticket->created_at->format('d M Y, H:i') }}</div>
                <p style="font-size:13px;line-height:1.8;margin:0;white-space:pre-line">{{ $ticket->description }}</p>
                @if($ticket->evidence)
                    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:16px">
                        @foreach($ticket->evidence as $file)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($file) }}" target="_blank" rel="noopener" class="muted" style="font-size:11px">Lampiran {{ $loop->iteration }}</a>
                        @endforeach
                    </div>
                @endif
            </article>

            @if($ticket->resolution)
                <article style="padding:18px;margin-top:14px;background:#eeede5;border-left:3px solid var(--green)">
                    <div class="eyebrow">{{ $ticket->status === 'resolved' ? 'Penyelesaian' : 'Pembaruan dari CS' }}</div>
                    <p style="font-size:13px;line-height:1.8;margin:8px 0 0;white-space:pre-line">{{ $ticket->resolution }}</p>
                    @if($ticket->assignee)
                        <div class="muted" style="font-size:10px;margin-top:10px">{{ $ticket->assignee->name }}</div>
                    @endif
                </article>
            @endif
        </div>

        @if($isAgent)
            <form method="POST" action="{{ route(auth()->user()->isAdmin() ? 'admin.tickets.update' : 'support.agent.update', $ticket) }}" style="display:grid;gap:12px;padding:18px;background:var(--white);border:1px solid var(--line)">
                @csrf
                @method('PUT')
                <h2 class="product-name" style="margin:0">Perbarui tiket</h2>
                <label class="field">Status
                    <select name="status" required>
                        @foreach(['in_progress' => 'Diproses', 'waiting_customer' => 'Menunggu customer', 'resolved' => 'Selesai'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $ticket->status === 'open' ? 'in_progress' : $ticket->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">Catatan / penyelesaian
                    <textarea name="resolution" rows="5" maxlength="2000">{{ old('resolution', $ticket->resolution) }}</textarea>
                </label>
                @error('status')<p class="error">{{ $message }}</p>@enderror
                @error('resolution')<p class="error">{{ $message }}</p>@enderror
                <button class="primary" type="submit">Simpan pembaruan</button>
            </form>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
    .field{display:grid;gap:6px;font-size:10px;font-weight:700;color:var(--muted)}
    .field select,.field textarea{width:100%;border:1px solid var(--line);background:var(--white);padding:10px;color:var(--ink);font:12px var(--sans)}
    .error{font-size:11px;color:#a33;margin:0}
    @media(max-width:700px){.shell>div[style*="grid-template-columns:minmax"]{grid-template-columns:1fr!important}}
</style>
@endpush