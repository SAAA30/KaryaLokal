@extends('layouts.storefront')

@section('title', $side === 'customer' ? 'Chat dengan perajin — KaryaLokal.id' : 'Chat pembeli — KaryaLokal.id')

@section('content')
<section class="shell chat-page">
    <div class="eyebrow">Percakapan</div>
    <h1 class="page-title">{{ $side === 'customer' ? 'Chat dengan perajin' : 'Chat pembeli' }}</h1>
    <div class="chat-list">
        @forelse($chats as $chat)
            @php($otherParty = $side === 'customer' ? $chat->artisan : $chat->customer)
            <a href="{{ $side === 'customer' ? route('chat.customer.show', $chat) : route('artisan.chats.show', $chat) }}" class="chat-list-row">
                <span class="chat-list-copy">
                    <strong>{{ $otherParty->name }}</strong>
                    <span class="muted">{{ $chat->product?->name ?? 'Percakapan langsung' }}</span>
                    <span class="chat-preview">{{ $chat->latestMessage?->body ?? 'Belum ada pesan' }}</span>
                </span>
                <span class="chat-date">{{ $chat->last_message_at?->format('d M, H:i') }}</span>
            </a>
        @empty
            <p class="muted chat-empty">{{ $side === 'customer' ? 'Belum ada percakapan. Mulai dari halaman produk perajin.' : 'Belum ada pesan dari customer.' }}</p>
        @endforelse
    </div>
    <div class="pagination">{{ $chats->links() }}</div>
</section>
@endsection

@push('styles')
<style>
    .chat-page{max-width:900px;padding:38px 0 52px}
    .chat-list{border-top:1px solid var(--line);margin-top:20px}
    .chat-list-row{display:flex;justify-content:space-between;gap:15px;padding:15px 0;border-bottom:1px solid var(--line)}
    .chat-list-row:hover strong{color:var(--green)}
    .chat-list-copy{display:grid;gap:4px;min-width:0;font-size:11px}
    .chat-list-copy>span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .chat-preview,.chat-date{color:var(--muted);font-size:10px}
    .chat-date{white-space:nowrap}
    .chat-empty{padding:22px 0;font-size:12px}
</style>
@endpush