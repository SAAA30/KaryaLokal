@extends('layouts.storefront')

@php($otherParty = $side === 'customer' ? $chat->artisan : $chat->customer)

@section('title', 'Chat dengan ' . $otherParty->name . ' — KaryaLokal.id')

@section('content')
<section class="shell chat-thread-page">
    <a class="muted" style="font-size:11px" href="{{ $side === 'customer' ? route('chat.customer.index') : route('artisan.chats.index') }}">← Kembali ke daftar chat</a>
    <header class="thread-heading">
        <div><div class="eyebrow">{{ $side === 'customer' ? 'Perajin' : 'Customer' }}</div><h1 class="page-title">{{ $otherParty->name }}</h1></div>
        @if($chat->product)<a class="thread-product" href="{{ route('products.show', $chat->product->slug) }}">{{ $chat->product->name }}</a>@endif
    </header>
    @if(session('success'))<p class="notice" role="status">{{ session('success') }}</p>@endif
    <div class="messages" aria-label="Riwayat pesan">
        @forelse($chat->messages as $message)
            <article @class(['message', 'is-mine' => $message->sender_id === auth()->id()])>
                <div class="message-meta">{{ $message->sender->name }} · {{ $message->created_at->format('d M Y, H:i') }}</div>
                <p>{{ $message->body }}</p>
            </article>
        @empty
            <p class="muted" style="font-size:12px">Mulai percakapan ini dengan mengirim pesan.</p>
        @endforelse
    </div>
    <form method="POST" action="{{ $side === 'customer' ? route('chat.customer.message', $chat) : route('artisan.chats.message', $chat) }}" class="message-form">
        @csrf
        <label for="message-body">Pesan</label>
        <textarea id="message-body" name="body" rows="3" maxlength="2000" required placeholder="Tulis pesan...">{{ old('body') }}</textarea>
        @error('body')<span class="chat-error">{{ $message }}</span>@enderror
        <button class="primary" type="submit">Kirim pesan</button>
    </form>
</section>
@endsection

@push('styles')
<style>
    .chat-thread-page{max-width:850px;padding:36px 0 52px}
    .thread-heading{display:flex;justify-content:space-between;align-items:end;gap:15px;padding:18px 0;border-bottom:1px solid var(--line)}
    .thread-heading .page-title{margin-bottom:0}
    .thread-product{font-size:11px;color:var(--green);text-align:right}
    .messages{display:grid;gap:12px;min-height:220px;max-height:55vh;overflow:auto;padding:18px 0}
    .message{align-self:start;justify-self:start;max-width:min(78%,620px);padding:11px 13px;background:var(--white);border:1px solid var(--line)}
    .message.is-mine{justify-self:end;background:#e8eedf;border-color:#d4dfc7}
    .message-meta{font-size:9px;color:var(--muted)}
    .message p{margin:5px 0 0;font-size:12px;line-height:1.65;white-space:pre-wrap;overflow-wrap:anywhere}
    .message-form{display:grid;gap:8px;padding-top:14px;border-top:1px solid var(--line)}
    .message-form label{font-size:10px;font-weight:700}
    .message-form textarea{width:100%;border:1px solid var(--line);padding:10px;background:var(--white);color:var(--ink);font:12px var(--sans);resize:vertical}
    .message-form button{justify-self:end}
    .chat-error{font-size:10px;color:#a33}
    .notice{padding:10px;background:#e6efdf;color:var(--green);font-size:11px}
    @media(max-width:600px){.thread-heading{align-items:start;flex-direction:column}.thread-product{text-align:left}.message{max-width:92%}}
</style>
@endpush