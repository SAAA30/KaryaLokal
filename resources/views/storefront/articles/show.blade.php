@extends('layouts.storefront')
@section('title', $article->title . ' — KaryaLokal.id')
@section('content')
<article class="shell" style="max-width:900px;padding:38px 0 10px">
    <a href="{{ route('articles.index') }}" class="muted" style="font-size:11px">← Semua artikel</a>
    <header style="max-width:760px;margin:24px auto 22px">
        <div class="eyebrow">Cerita dan inspirasi</div>
        <h1 class="page-title" style="font-size:clamp(30px,5vw,48px);margin-top:11px">{{ $article->title }}</h1>
        <div class="muted" style="font-size:11px;margin-top:12px">{{ $article->published_at?->translatedFormat('d F Y') }} · {{ $article->author?->name ?? 'KaryaLokal' }}</div>
        @if($article->excerpt)<p style="font:500 17px/1.7 var(--serif);margin-top:18px">{{ $article->excerpt }}</p>@endif
    </header>
    @if($article->image_path)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($article->image_path) }}" alt="{{ $article->title }}" style="display:block;width:100%;max-height:520px;aspect-ratio:16/9;object-fit:cover;background:#e8e5dc">
    @endif
    <div style="max-width:760px;margin:28px auto 0;font-size:14px;line-height:2;white-space:pre-line">{{ $article->body }}</div>
</article>

<section class="shell" style="padding:12px 0 60px">
    <div style="margin-bottom:18px">
        <button id="articleAppreciationButton" type="button" aria-label="Apresiasi artikel" style="display:inline-flex;align-items:center;gap:10px;padding:8px 14px 8px 10px;border:1px solid #d9d4ca;border-radius:999px;background:#f3f0ea;color:var(--green);font-size:12px;font-weight:700;box-shadow:0 1px 0 rgba(24,53,45,.04);cursor:pointer;transition:all .2s ease">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:#e6f2eb;color:var(--green)">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.5c.8 1.8 3.2 2.8 4.8 5.1 1.7 2.4 1.2 5.5-1.1 7.2-2 1.5-4.7 1.7-6.8.4-2.1-1.3-3.2-3.8-2.8-6.3.5-2.7 2.9-4.8 5.9-6.4Z"/><path d="M7.5 15.5 12 20l4.5-4.5"/></svg>
            </span>
            <span>Apresiasi Artikel</span>
            <span id="articleAppreciationCount" style="display:inline-flex;align-items:center;justify-content:center;min-width:26px;height:26px;padding:0 8px;border-radius:999px;background:#fff;border:1px solid #d9d4ca;color:var(--ink);font-size:11px;font-weight:700">43</span>
        </button>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px">
        <h2 style="margin:0;font:600 24px/1.2 var(--serif);color:var(--ink)">Artikel Lainnya</h2>
        <a href="{{ route('articles.index') }}" style="font-size:11px;font-weight:700;color:var(--green)">Lihat Semua →</a>
    </div>

    @if($relatedArticles->isNotEmpty())
        <div class="card-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));gap:18px">
            @foreach($relatedArticles as $relatedArticle)
                <article style="min-width:0">
                    <a href="{{ route('articles.show', $relatedArticle->slug) }}" style="display:block;aspect-ratio:4/3;background:#e8e5dc;overflow:hidden;border-radius:10px">
                        @if($relatedArticle->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($relatedArticle->image_path) }}" alt="{{ $relatedArticle->title }}" style="width:100%;height:100%;object-fit:cover;display:block">
                        @endif
                    </a>
                    <div class="muted" style="margin-top:10px;font-size:10px;line-height:1.6">{{ $relatedArticle->published_at?->translatedFormat('d M Y') }} · {{ $relatedArticle->author?->name ?? 'KaryaLokal' }}</div>
                    <h3 style="margin:6px 0 0;font:600 19px/1.3 var(--serif)"><a href="{{ route('articles.show', $relatedArticle->slug) }}">{{ $relatedArticle->title }}</a></h3>
                </article>
            @endforeach
        </div>
    @else
        <p class="muted" style="font-size:12px;padding-top:8px">Belum ada artikel lain yang dipublikasikan.</p>
    @endif
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('articleAppreciationButton');
        const countEl = document.getElementById('articleAppreciationCount');
        const storageKey = 'karyalokal_article_appreciation_{{ $article->id }}';
        const initialCount = Number(countEl.textContent.trim());
        let count = initialCount;
        let appreciated = localStorage.getItem(storageKey) === '1';

        const render = () => {
            countEl.textContent = count;
            button.style.background = appreciated ? '#e6f2eb' : '#f3f0ea';
            button.style.borderColor = appreciated ? '#b7d9c2' : '#d9d4ca';
            button.style.color = appreciated ? '#1f5b47' : '#245a46';
            button.style.transform = appreciated ? 'translateY(-1px)' : 'translateY(0)';
        };

        button.addEventListener('click', function () {
            appreciated = !appreciated;
            count += appreciated ? 1 : -1;
            localStorage.setItem(storageKey, appreciated ? '1' : '0');
            render();
        });

        render();
    });
</script>
@endpush

@push('styles')
<style>
    @media(max-width:900px){.card-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
    @media(max-width:560px){.card-grid{grid-template-columns:1fr!important}}
</style>
@endpush
