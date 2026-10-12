@extends('layouts.storefront')
@section('title', 'Kelola Artikel — KaryaLokal.id')
@section('content')
<section class="shell" style="padding:38px 0">
    @if(session('success'))<p role="status" style="padding:11px 14px;background:#e6efdf;color:var(--green);font-size:12px">{{ session('success') }}</p>@endif
    <div class="eyebrow">Konten KaryaLokal</div>
    <div style="display:flex;justify-content:space-between;align-items:end;gap:15px;flex-wrap:wrap">
        <div><h1 class="page-title">Kelola artikel</h1><p class="muted" style="font-size:12px">Artikel yang diterbitkan akan tampil untuk pembeli.</p></div>
        <a class="primary" href="{{ route('admin.articles.create') }}">+ Tulis artikel</a>
    </div>
    <div style="margin-top:20px;border-top:1px solid var(--line)">
        @forelse($articles as $article)
            <article style="display:grid;grid-template-columns:92px minmax(0,1fr) auto;gap:16px;align-items:center;padding:14px 0;border-bottom:1px solid var(--line)">
                <div style="width:92px;height:72px;background:#e8e5dc;overflow:hidden">
                    @if($article->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($article->image_path) }}" alt="{{ $article->title }}" style="width:100%;height:100%;object-fit:cover">@endif
                </div>
                <div>
                    <strong style="font:600 17px var(--serif)">{{ $article->title }}</strong>
                    <p class="muted" style="margin:5px 0;font-size:11px">{{ $article->excerpt }}</p>
                    <span class="muted" style="font-size:10px">{{ $article->author?->name ?? 'Admin' }} · {{ $article->is_published ? 'Terbit' : 'Draft' }}</span>
                </div>
                <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap">
                    <a class="signin" href="{{ route('admin.articles.edit', $article) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Hapus artikel ini?')">
                        @csrf @method('DELETE')
                        <button class="signin" type="submit" style="background:var(--white);cursor:pointer">Hapus</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="muted" style="padding:24px 0;font-size:12px">Belum ada artikel.</p>
        @endforelse
    </div>
    <div class="pagination">{{ $articles->links() }}</div>
</section>
@endsection
@push('styles')
<style>@media(max-width:620px){.shell article{grid-template-columns:68px minmax(0,1fr)!important}.shell article>div:last-child{grid-column:2}.shell article>div:first-child{width:68px!important;height:68px!important}}</style>
@endpush
