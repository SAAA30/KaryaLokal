@extends('layouts.storefront')
@section('title', 'Artikel — KaryaLokal.id')
@section('content')
<section class="shell" style="padding:42px 0">
    <div class="eyebrow">Cerita dan inspirasi</div>
    <h1 class="page-title">Artikel KaryaLokal</h1>
    <p class="muted" style="max-width:680px;font-size:13px;line-height:1.8">Kenali proses, bahan, dan cerita di balik karya para perajin lokal.</p>
    @if($articles->isNotEmpty())
        <div class="card-grid" style="margin-top:26px">
            @foreach($articles as $article)
                <article style="min-width:0">
                    <a href="{{ route('articles.show', $article->slug) }}" style="display:block;aspect-ratio:4/3;background:#e8e5dc;overflow:hidden">
                        @if($article->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($article->image_path) }}" alt="{{ $article->title }}" style="width:100%;height:100%;object-fit:cover">@endif
                    </a>
                    <div class="muted" style="margin-top:12px;font-size:10px">{{ $article->published_at?->translatedFormat('d M Y') }} · {{ $article->author?->name ?? 'KaryaLokal' }}</div>
                    <h2 style="font:600 21px/1.3 var(--serif);margin:7px 0"><a href="{{ route('articles.show', $article->slug) }}">{{ $article->title }}</a></h2>
                    @if($article->excerpt)<p class="muted" style="font-size:11px;line-height:1.7">{{ $article->excerpt }}</p>@endif
                    <a href="{{ route('articles.show', $article->slug) }}" style="font-size:11px;font-weight:700;color:var(--green)">Baca artikel →</a>
                </article>
            @endforeach
        </div>
        <div class="pagination" style="margin-top:24px">{{ $articles->links() }}</div>
    @else
        <p class="muted" style="margin-top:25px;padding:18px 0;border-top:1px solid var(--line);font-size:12px">Belum ada artikel yang diterbitkan.</p>
    @endif
</section>
@endsection
@push('styles')
<style>@media(max-width:900px){.card-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:560px){.card-grid{grid-template-columns:1fr}}</style>
@endpush
